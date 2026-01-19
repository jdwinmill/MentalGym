<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Messages\Message;
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
            $response = $this->callWithRetry(fn () =>
                $this->client->messages->create([
                    'model' => $model,
                    'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                    'system' => [
                        [
                            'type' => 'text',
                            'text' => $instructionSet,
                            'cache_control' => ['type' => 'ephemeral'],
                        ]
                    ],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Begin.',
                        ]
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

            $responseText = $response->content[0]->text;

            return $this->parseResponse($responseText);

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

            Log::error("AI first response failed", [
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
        array $messageHistory,
        string $userInput,
        ?User $user = null,
        ?TrainingSession $session = null
    ): array {
        $instructionSet = $this->prepareInstructionSet($mode, $userLevel);
        $messages = $this->buildMessages($messageHistory, $userInput);
        $model = $mode->config['model'] ?? 'claude-sonnet-4-20250514';
        $startTime = microtime(true);

        try {
            $response = $this->callWithRetry(fn () =>
                $this->client->messages->create([
                    'model' => $model,
                    'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                    'system' => [
                        [
                            'type' => 'text',
                            'text' => $instructionSet,
                            'cache_control' => ['type' => 'ephemeral'],
                        ]
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

            $responseText = $response->content[0]->text;

            return $this->parseResponse($responseText);

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

            Log::error("AI response failed", [
                'mode' => $mode->slug,
                'level' => $userLevel,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackCard();
        }
    }

    /**
     * Inject user's level into instruction set
     */
    private function prepareInstructionSet(PracticeMode $mode, int $level): string
    {
        return str_replace('{{level}}', (string) $level, $mode->instruction_set);
    }

    /**
     * Build messages array for API call with caching
     */
    private function buildMessages(array $history, ?string $userInput = null): array
    {
        $messages = [];

        foreach ($history as $index => $msg) {
            $message = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];

            $messages[] = $message;
        }

        // Add cache control to last history message
        if (count($messages) > 0) {
            $messages[count($messages) - 1]['cache_control'] = ['type' => 'ephemeral'];
        }

        if ($userInput) {
            $messages[] = [
                'role' => 'user',
                'content' => $userInput,
            ];
        }

        return $messages;
    }

    /**
     * Parse and validate AI response JSON
     */
    private function parseResponse(string $responseText): array
    {
        $parsed = json_decode($responseText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON from AI: ' . json_last_error_msg());
        }

        if (!isset($parsed['type'])) {
            throw new \RuntimeException('Missing type field in AI response');
        }

        $validTypes = ['scenario', 'prompt', 'multiple_choice', 'insight', 'reflection'];

        if (!in_array($parsed['type'], $validTypes)) {
            throw new \RuntimeException("Unknown card type: {$parsed['type']}");
        }

        return $parsed;
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
                    Log::warning("AI API call failed, retrying...", [
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
     * Return safe fallback card when response can't be parsed
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
