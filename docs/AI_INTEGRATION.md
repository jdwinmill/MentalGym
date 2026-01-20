# AI Integration

## Overview

Practice Modes use Claude API (Sonnet) with **tool use** for structured output and prompt caching to reduce costs. AI responses are enforced by tool schema, eliminating JSON parsing failures.

## API Configuration

### Default Settings

| Setting | Value | Notes |
|---------|-------|-------|
| Model | claude-sonnet-4-20250514 | Configurable per mode |
| Max tokens | 800 | Configurable per mode |
| Caching | Enabled | Ephemeral cache control |
| Tools | display_card | Required for all responses |

### Per-Mode Config

Each Practice Mode has a `config` JSON field:
```json
{
  "input_character_limit": 500,
  "reflection_character_limit": 200,
  "max_response_tokens": 800,
  "max_history_exchanges": 10,
  "model": "claude-sonnet-4-20250514"
}
```

---

## Tool Definition

The `display_card` tool enforces response structure at the API level. Claude **must** call this tool for every response.

```php
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
                    'is_iteration' => [
                        'type' => 'boolean',
                        'description' => 'True if this is a required second attempt at a drill',
                    ],
                ],
            ],
        ],
    ];
}
```

---

## Request Structure

### Basic API Call with Tools

```php
use Anthropic\Anthropic;

$client = Anthropic::client(config('services.anthropic.api_key'));

$response = $client->messages()->create([
    'model' => $mode->config['model'] ?? 'claude-sonnet-4-20250514',
    'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
    'tools' => $this->getToolDefinition(),
    'tool_choice' => ['type' => 'tool', 'name' => 'display_card'], // Force tool use
    'system' => [
        [
            'type' => 'text',
            'text' => $instructionSetWithLevel,
            'cache_control' => ['type' => 'ephemeral']
        ]
    ],
    'messages' => $messageHistory,
]);
```

### Extracting Tool Response

```php
public function extractCardFromResponse($response): array
{
    foreach ($response->content as $block) {
        if ($block->type === 'tool_use' && $block->name === 'display_card') {
            return $this->normalizeCard((array) $block->input);
        }
    }
    
    throw new MalformedResponseException('No display_card tool call in response');
}

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
    
    return $card;
}
```

---

## Message History with Tool Results

When continuing a session, include previous tool calls and simulated results:

```php
public function buildMessageHistory(TrainingSession $session, string $newUserInput): array
{
    $maxExchanges = $session->practiceMode->config['max_history_exchanges'] ?? 10;
    
    $messages = [];
    $previousMessages = $session->messages()
        ->orderBy('created_at', 'desc')
        ->take($maxExchanges * 2)
        ->get()
        ->reverse();
    
    foreach ($previousMessages as $index => $msg) {
        if ($msg->role === 'assistant') {
            // Assistant messages were tool calls
            $parsed = json_decode($msg->content, true);
            
            $messages[] = [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'tool_use',
                        'id' => 'toolu_' . $msg->id,
                        'name' => 'display_card',
                        'input' => $this->cardToToolInput($parsed),
                    ]
                ],
            ];
            
            // Add tool result (user saw the card)
            $messages[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => 'toolu_' . $msg->id,
                        'content' => 'Card displayed to user.',
                    ]
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
            $messages[$lastIndex]['content'][count($messages[$lastIndex]['content']) - 1]['cache_control'] = ['type' => 'ephemeral'];
        }
    }
    
    // Add new user input
    $messages[] = [
        'role' => 'user',
        'content' => $newUserInput,
    ];
    
    return $messages;
}

private function cardToToolInput(array $card): array
{
    $input = [
        'card_type' => $card['type'],
        'content' => $card['content'],
    ];
    
    if (isset($card['input'])) {
        $input['input_config'] = [
            'max_length' => $card['input']['max_length'],
            'placeholder' => $card['input']['placeholder'] ?? '',
        ];
    }
    
    if (isset($card['options'])) {
        $input['options'] = $card['options'];
    }
    
    return $input;
}
```

---

## Level Injection

Before sending to API, inject user's current level into the instruction set:

```php
public function prepareInstructionSet(PracticeMode $mode, int $level): string
{
    return str_replace(
        '{{level}}',
        (string) $level,
        $mode->instruction_set
    );
}
```

---

## Error Handling

### Error Types and Responses

| Error | Detection | Response |
|-------|-----------|----------|
| API timeout | Request exceeds timeout | Retry once, then show error card |
| Rate limit | 429 status | Show "Please wait" message |
| No tool call | Response lacks tool_use block | Log error, show fallback |
| Network error | Connection exception | Show offline message |
| Invalid API key | 401 status | Log critical, show error |

### Fallback Card

When response can't be processed:

```php
public function getFallbackCard(): array
{
    return [
        'type' => 'insight',
        'content' => "Let's continue. What's on your mind?",
    ];
}
```

### Retry Logic

```php
public function callWithRetry(callable $apiCall, int $maxRetries = 1): mixed
{
    $attempts = 0;
    
    while ($attempts <= $maxRetries) {
        try {
            return $apiCall();
        } catch (RateLimitException $e) {
            throw $e; // Don't retry rate limits
        } catch (ApiException $e) {
            $attempts++;
            if ($attempts > $maxRetries) {
                throw $e;
            }
            sleep(1);
        }
    }
}
```

---

## Service Structure

```php
// app/Services/PracticeAIService.php

class PracticeAIService
{
    public function __construct(
        private Anthropic $client,
    ) {}
    
    public function getResponse(
        PracticeMode $mode,
        int $userLevel,
        array $messageHistory,
        string $userInput
    ): array {
        $instructionSet = $this->prepareInstructionSet($mode, $userLevel);
        $messages = $this->buildMessages($messageHistory, $userInput);
        
        $response = $this->callWithRetry(fn () => 
            $this->client->messages()->create([
                'model' => $mode->config['model'] ?? 'claude-sonnet-4-20250514',
                'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                'tools' => $this->getToolDefinition(),
                'tool_choice' => ['type' => 'tool', 'name' => 'display_card'],
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
        
        return $this->extractCardFromResponse($response);
    }
    
    public function getFirstResponse(PracticeMode $mode, int $userLevel): array
    {
        $instructionSet = $this->prepareInstructionSet($mode, $userLevel);
        
        $response = $this->callWithRetry(fn () => 
            $this->client->messages()->create([
                'model' => $mode->config['model'] ?? 'claude-sonnet-4-20250514',
                'max_tokens' => $mode->config['max_response_tokens'] ?? 800,
                'tools' => $this->getToolDefinition(),
                'tool_choice' => ['type' => 'tool', 'name' => 'display_card'],
                'system' => [
                    [
                        'type' => 'text',
                        'text' => $instructionSet,
                        'cache_control' => ['type' => 'ephemeral'],
                    ]
                ],
                'messages' => [
                    ['role' => 'user', 'content' => 'Begin training.'],
                ],
            ])
        );
        
        return $this->extractCardFromResponse($response);
    }
    
    private function getToolDefinition(): array { /* ... */ }
    private function extractCardFromResponse($response): array { /* ... */ }
    private function normalizeCard(array $toolInput): array { /* ... */ }
    private function prepareInstructionSet(PracticeMode $mode, int $level): string { /* ... */ }
    private function buildMessages(array $history, string $userInput): array { /* ... */ }
    private function callWithRetry(callable $call, int $retries = 1): mixed { /* ... */ }
}
```

---

## Caching Economics

### Token Rates (Claude Sonnet)

| Type | Rate |
|------|------|
| Input (uncached) | $3.00 / 1M tokens |
| Input (cache write) | $3.75 / 1M tokens |
| Input (cache hit) | $0.30 / 1M tokens |
| Output | $15.00 / 1M tokens |

### Savings

**First exchange (cache write):**
- Instruction set: 2,000 tokens × $3.75/1M = $0.0075

**Subsequent exchanges (cache hit):**
- Instruction set: 2,000 tokens × $0.30/1M = $0.0006

**Savings: ~92% on instruction set after first exchange**

---

## Environment Configuration

```env
ANTHROPIC_API_KEY=sk-ant-...
```

```php
// config/services.php

'anthropic' => [
    'api_key' => env('ANTHROPIC_API_KEY'),
],
```

---

## Why Tool Use Instead of JSON-in-Content

| Approach | Reliability | Failure Mode |
|----------|-------------|--------------|
| JSON-in-content | ~95% | Preamble text, missing brackets, markdown fences |
| Tool use | ~99.9% | API enforces schema; malformed output rejected |

Tool use eliminates the need for defensive JSON parsing. The API guarantees valid structure.
