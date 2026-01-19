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
     * Prepare the full system prompt with JSON formatting requirements
     */
    private function prepareInstructionSet(PracticeMode $mode, int $level): string
    {
        $modeInstructions = str_replace('{{level}}', (string) $level, $mode->instruction_set);

        return <<<PROMPT
You are an AI training assistant. You must ALWAYS respond with a single valid JSON object (no markdown, no extra text).

## Response Format

Every response must be a JSON object with this structure:
{
  "type": "<card_type>",
  "content": "<your message>",
  "options": ["option1", "option2", ...] // ONLY for multiple_choice type
}

## Card Types

Use these card types based on context:
- "scenario": Present a situation, context, or information. Use when setting up an exercise or providing background.
- "prompt": Ask the user to write or explain something. Use when you need a text response.
- "multiple_choice": Present options for the user to choose from. Include an "options" array with 2-4 choices.
- "insight": Provide feedback, coaching, or analysis of the user's response. Use after they submit something.
- "reflection": Ask the user to reflect briefly on what they learned or noticed.

## Rules
1. Output ONLY the JSON object - no markdown code blocks, no explanations before/after
2. The "content" field should contain your actual message/question/feedback
3. Keep responses concise and focused
4. Progress through exercises naturally based on user responses

## Training Program Instructions

{$modeInstructions}

Remember: Respond with raw JSON only. No markdown formatting around the JSON.
PROMPT;
    }

    /**
     * Build messages array for API call
     */
    private function buildMessages(array $history, ?string $userInput = null): array
    {
        $messages = [];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
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
        // Try to extract JSON from the response (handle markdown code blocks, extra text, etc.)
        $json = $this->extractJson($responseText);

        $parsed = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Failed to parse AI response', [
                'raw_response' => substr($responseText, 0, 500),
                'extracted_json' => substr($json, 0, 500),
                'error' => json_last_error_msg(),
            ]);
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
     * Extract JSON from response text (handles markdown code blocks, extra text, etc.)
     */
    private function extractJson(string $text): string
    {
        $text = trim($text);

        // If it's already valid JSON, return as-is
        if ($this->isValidJson($text)) {
            return $text;
        }

        // Try to extract from markdown code block: ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $text, $matches)) {
            return trim($matches[1]);
        }

        // Try to find JSON object in the text (first { to last })
        $firstBrace = strpos($text, '{');
        $lastBrace = strrpos($text, '}');

        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $extracted = substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
            if ($this->isValidJson($extracted)) {
                return $extracted;
            }
        }

        // Return original text if extraction fails
        return $text;
    }

    /**
     * Check if string is valid JSON
     */
    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
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
            'type' => 'prompt',
            'content' => "I'd like to hear your thoughts. What's on your mind right now?",
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
