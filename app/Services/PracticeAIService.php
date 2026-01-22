<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Messages\Message;
use App\Exceptions\MalformedResponseException;
use App\Models\ApiLog;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PracticeAIService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(config('services.anthropic.api_key'));
    }

    /**
     * Get the first response to start a session (no user input yet)
     */
    public function getFirstResponse(
        PracticeMode $mode,
        int $userLevel,
        ?User $user = null,
        ?TrainingSession $session = null
    ): array {
        $instructionSet = $this->prepareInstructionSet($mode, $userLevel);
        $model = $mode->config['model'] ?? 'claude-sonnet-4-20250514';
        $startTime = microtime(true);

        try {
            $response = $this->callWithRetry(fn () => $this->client->messages->create([
                'model' => $model,
                'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                'tools' => $this->getToolDefinition(),
                'tool_choice' => ['type' => 'tool', 'name' => 'display_card'],
                'system' => [
                    [
                        'type' => 'text',
                        'text' => $instructionSet,
                        'cache_control' => ['type' => 'ephemeral'],
                    ],
                ],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Begin training.',
                    ],
                ],
            ])
            );

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiCall(
                $user,
                $mode,
                $session,
                $model,
                $responseTimeMs,
                true,
                $response
            );

            return $this->extractCardFromResponse($response);

        } catch (MalformedResponseException $e) {
            Log::error('AI first response malformed', [
                'mode' => $mode->slug,
                'level' => $userLevel,
                'error' => $e->getMessage(),
                'raw_response' => $e->getRawResponseSummary(),
            ]);

            return $this->getFallbackCard();

        } catch (\Exception $e) {
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiCall(
                $user,
                $mode,
                $session,
                $model,
                $responseTimeMs,
                false,
                null,
                $e->getMessage()
            );

            Log::error('AI first response failed', [
                'mode' => $mode->slug,
                'level' => $userLevel,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackCard();
        }
    }

    /**
     * Get response to user input during a session
     */
    public function getResponse(
        PracticeMode $mode,
        int $userLevel,
        TrainingSession $session,
        string $userInput,
        ?User $user = null
    ): array {
        $instructionSet = $this->prepareInstructionSet($mode, $userLevel);
        $messages = $this->buildMessageHistory($session, $userInput);
        $model = $mode->config['model'] ?? 'claude-sonnet-4-20250514';
        $startTime = microtime(true);

        try {
            $response = $this->callWithRetry(fn () => $this->client->messages->create([
                'model' => $model,
                'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                'tools' => $this->getToolDefinition(),
                'tool_choice' => ['type' => 'tool', 'name' => 'display_card'],
                'system' => [
                    [
                        'type' => 'text',
                        'text' => $instructionSet,
                        'cache_control' => ['type' => 'ephemeral'],
                    ],
                ],
                'messages' => $messages,
            ])
            );

            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiCall(
                $user,
                $mode,
                $session,
                $model,
                $responseTimeMs,
                true,
                $response
            );

            return $this->extractCardFromResponse($response);

        } catch (MalformedResponseException $e) {
            Log::error('AI response malformed', [
                'mode' => $mode->slug,
                'level' => $userLevel,
                'error' => $e->getMessage(),
                'raw_response' => $e->getRawResponseSummary(),
            ]);

            return $this->getFallbackCard();

        } catch (\Exception $e) {
            $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logApiCall(
                $user,
                $mode,
                $session,
                $model,
                $responseTimeMs,
                false,
                null,
                $e->getMessage()
            );

            Log::error('AI response failed', [
                'mode' => $mode->slug,
                'level' => $userLevel,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackCard();
        }
    }

    /**
     * Get the tool definition for display_card
     */
    private function getToolDefinition(): array
    {
        return [
            [
                'name' => 'display_card',
                'description' => 'Display a training card to the user. You MUST call this tool for every response. Never respond without using this tool.',
                'input_schema' => [
                    'type' => 'object',
                    'required' => ['card_type', 'content'],
                    'properties' => [
                        'card_type' => [
                            'type' => 'string',
                            'enum' => ['scenario', 'prompt', 'insight', 'reflection', 'multiple_choice'],
                            'description' => 'The type of card to display',
                        ],
                        'content' => [
                            'type' => 'string',
                            'description' => 'The main text content of the card',
                        ],
                        'input_config' => [
                            'type' => 'object',
                            'description' => 'Configuration for input fields (prompt and reflection cards only)',
                            'properties' => [
                                'max_length' => [
                                    'type' => 'integer',
                                    'description' => 'Maximum character length for input',
                                ],
                                'placeholder' => [
                                    'type' => 'string',
                                    'description' => 'Placeholder text for input field',
                                ],
                            ],
                        ],
                        'options' => [
                            'type' => 'array',
                            'description' => 'Options for multiple_choice cards only',
                            'items' => [
                                'type' => 'object',
                                'required' => ['id', 'label'],
                                'properties' => [
                                    'id' => [
                                        'type' => 'string',
                                        'description' => 'Unique option identifier (a, b, c, d)',
                                    ],
                                    'label' => [
                                        'type' => 'string',
                                        'description' => 'Display text for the option',
                                    ],
                                ],
                            ],
                        ],
                        'drill_phase' => [
                            'type' => 'string',
                            'description' => 'Current drill name for structured training modes',
                        ],
                        'drill_number' => [
                            'type' => 'integer',
                            'description' => 'Current drill number (1, 2, 3, 4, etc). Must increment as session progresses.',
                        ],
                        'step_number' => [
                            'type' => 'integer',
                            'description' => 'Current step within the drill (1, 2, 3, etc). Resets to 1 at each new drill.',
                        ],
                        'is_iteration' => [
                            'type' => 'boolean',
                            'description' => 'True if this is a required second attempt at a drill',
                        ],
                        'ui_hints' => [
                            'type' => 'object',
                            'description' => 'UI configuration hints for the card display',
                            'properties' => [
                                'timed' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether to show a countdown timer on this card',
                                ],
                                'timer_seconds' => [
                                    'type' => 'integer',
                                    'description' => 'Number of seconds for the countdown timer',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Extract the card from a tool_use response block
     */
    private function extractCardFromResponse(Message $response): array
    {
        foreach ($response->content as $block) {
            if ($block->type === 'tool_use' && $block->name === 'display_card') {
                return $this->normalizeCard((array) $block->input);
            }
        }

        // No tool call found - this shouldn't happen with tool_choice forcing it
        throw new MalformedResponseException(
            'No display_card tool call in response',
            json_encode($response->content)
        );
    }

    /**
     * Normalize tool input to frontend card format
     */
    private function normalizeCard(array $toolInput): array
    {
        // Convert tool input to card format expected by frontend
        $card = [
            'type' => $toolInput['card_type'],
            'content' => $toolInput['content'],
        ];

        // Add input config for prompt/reflection cards
        if (isset($toolInput['input_config'])) {
            $card['input'] = [
                'type' => 'text',
                'max_length' => $toolInput['input_config']['max_length'] ?? 500,
                'placeholder' => $toolInput['input_config']['placeholder'] ?? '',
            ];
        }

        // Add options for multiple choice
        if (isset($toolInput['options'])) {
            $card['options'] = $toolInput['options'];
        }

        // Pass through drill metadata if present
        if (isset($toolInput['drill_phase'])) {
            $card['drill_phase'] = $toolInput['drill_phase'];
        }
        if (isset($toolInput['is_iteration'])) {
            $card['is_iteration'] = $toolInput['is_iteration'];
        }

        // Pass through ui_hints for timer display
        if (isset($toolInput['ui_hints'])) {
            $card['ui_hints'] = $toolInput['ui_hints'];
        }

        return $card;
    }

    /**
     * Build message history with tool_use and tool_result blocks for API call
     */
    public function buildMessageHistory(TrainingSession $session, string $newUserInput): array
    {
        $maxExchanges = $session->practiceMode->config['max_history_exchanges'] ?? 10;

        $messages = [];
        $previousMessages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->orderBy('sequence', 'desc')
            ->take($maxExchanges * 2)
            ->get()
            ->reverse()
            ->values();

        foreach ($previousMessages as $msg) {
            if ($msg->role === 'assistant') {
                // Assistant messages were tool calls - reconstruct them
                $parsed = json_decode($msg->content, true);

                $messages[] = [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'tool_use',
                            'id' => 'toolu_'.$msg->id,
                            'name' => 'display_card',
                            'input' => $this->cardToToolInput($parsed),
                        ],
                    ],
                ];

                // Add tool result (user saw the card)
                $messages[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => 'toolu_'.$msg->id,
                            'content' => 'Card displayed to user.',
                        ],
                    ],
                ];
            } else {
                // User messages are plain text or choice JSON
                $messages[] = [
                    'role' => 'user',
                    'content' => $msg->content,
                ];
            }
        }

        // Add cache control to last message before new input
        if (count($messages) > 0) {
            $lastIndex = count($messages) - 1;
            if (is_array($messages[$lastIndex]['content'])) {
                $lastContentIndex = count($messages[$lastIndex]['content']) - 1;
                $messages[$lastIndex]['content'][$lastContentIndex]['cache_control'] = ['type' => 'ephemeral'];
            }
        }

        // Add new user input
        $messages[] = [
            'role' => 'user',
            'content' => $newUserInput,
        ];

        return $messages;
    }

    /**
     * Convert frontend card format back to tool input format
     */
    private function cardToToolInput(array $card): array
    {
        $input = [
            'card_type' => $card['type'],
            'content' => $card['content'],
        ];

        if (isset($card['input'])) {
            $input['input_config'] = [
                'max_length' => $card['input']['max_length'] ?? 500,
                'placeholder' => $card['input']['placeholder'] ?? '',
            ];
        }

        if (isset($card['options'])) {
            $input['options'] = $card['options'];
        }

        if (isset($card['drill_phase'])) {
            $input['drill_phase'] = $card['drill_phase'];
        }

        if (isset($card['is_iteration'])) {
            $input['is_iteration'] = $card['is_iteration'];
        }

        if (isset($card['ui_hints'])) {
            $input['ui_hints'] = $card['ui_hints'];
        }

        return $input;
    }

    /**
     * Prepare the instruction set with level injected
     */
    private function prepareInstructionSet(PracticeMode $mode, int $level): string
    {
        return str_replace(
            '{{level}}',
            (string) $level,
            $mode->instruction_set
        );
    }

    /**
     * Call API with retry logic
     */
    private function callWithRetry(callable $apiCall, int $maxRetries = 1): mixed
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts <= $maxRetries) {
            try {
                return $apiCall();
            } catch (\Anthropic\OverloadedError $e) {
                throw $e; // Don't retry overloaded errors
            } catch (\Exception $e) {
                $lastException = $e;
                $attempts++;

                if ($attempts <= $maxRetries) {
                    Log::warning('AI API call failed, retrying...', [
                        'attempt' => $attempts,
                        'error' => $e->getMessage(),
                    ]);
                    sleep(1);
                }
            }
        }

        throw $lastException;
    }

    /**
     * Return safe fallback card when response can't be processed
     */
    private function getFallbackCard(): array
    {
        return [
            'type' => 'insight',
            'content' => "Let's continue. What's on your mind?",
        ];
    }

    /**
     * Log API call to database
     */
    private function logApiCall(
        ?User $user,
        ?PracticeMode $mode,
        ?TrainingSession $session,
        string $model,
        int $responseTimeMs,
        bool $success,
        ?Message $response = null,
        ?string $errorMessage = null
    ): void {
        ApiLog::create([
            'user_id' => $user?->id,
            'practice_mode_id' => $mode?->id,
            'training_session_id' => $session?->id,
            'input_tokens' => $response?->usage->input_tokens ?? 0,
            'output_tokens' => $response?->usage->output_tokens ?? 0,
            'cache_creation_input_tokens' => $response?->usage->cache_creation_input_tokens ?? 0,
            'cache_read_input_tokens' => $response?->usage->cache_read_input_tokens ?? 0,
            'model' => $model,
            'response_time_ms' => $responseTimeMs,
            'success' => $success,
            'error_message' => $errorMessage,
            'created_at' => now(),
        ]);
    }
}
