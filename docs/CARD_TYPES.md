# Card Types

AI responses are delivered via the `display_card` tool. The API enforces the schema, guaranteeing valid structure. The frontend renders the appropriate Vue component based on `card_type`.

---

## Tool Schema

The `display_card` tool is defined in the API call and forces Claude to output structured data:

```php
[
    'name' => 'display_card',
    'description' => 'Display a training card to the user. You MUST call this tool for every response.',
    'input_schema' => [
        'type' => 'object',
        'required' => ['card_type', 'content'],
        'properties' => [
            'card_type' => [
                'type' => 'string',
                'enum' => ['scenario', 'prompt', 'insight', 'reflection', 'multiple_choice'],
            ],
            'content' => [
                'type' => 'string',
            ],
            'input_config' => [
                'type' => 'object',
                'properties' => [
                    'max_length' => ['type' => 'integer'],
                    'placeholder' => ['type' => 'string'],
                ],
            ],
            'options' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'required' => ['id', 'label'],
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'label' => ['type' => 'string'],
                    ],
                ],
            ],
            'drill_phase' => ['type' => 'string'],
            'is_iteration' => ['type' => 'boolean'],
        ],
    ],
]
```

---

## Tool Output → Frontend Card Mapping

The service layer normalizes tool output to frontend card format:

| Tool Output | Frontend Card |
|-------------|---------------|
| `card_type: "scenario"` | `type: "scenario"` |
| `card_type: "prompt"` + `input_config` | `type: "prompt"` + `input` |
| `card_type: "multiple_choice"` + `options` | `type: "multiple_choice"` + `options` |
| `card_type: "insight"` | `type: "insight"` |
| `card_type: "reflection"` + `input_config` | `type: "reflection"` + `input` |

Backend-injected (not from AI):
| Injected | Frontend Card |
|----------|---------------|
| Level up detected | `type: "level_up"` |

---

## Card Type: scenario

Sets the scene. No user input required.

### Tool Call
```json
{
  "card_type": "scenario",
  "content": "You're two weeks into a new VP role. Your first big initiative is stalling because a peer VP keeps missing commitments. You've mentioned it twice in 1:1s. Today, they missed another deadline.",
  "drill_phase": "Problem-Solving"
}
```

### Frontend Format
```json
{
  "type": "scenario",
  "content": "You're two weeks into a new VP role...",
  "drill_phase": "Problem-Solving"
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| card_type | string | "scenario" |
| content | string | The scene description |

### Optional Fields

| Field | Type | Description |
|-------|------|-------------|
| drill_phase | string | Current drill name (for structured modes) |

### UI Behavior

- Display text content
- "Continue" button to proceed
- No input field

### Visual Style

- Neutral gray accent
- No icon (or subtle location pin)

---

## Card Type: prompt

Asks for a text response from the user.

### Tool Call
```json
{
  "card_type": "prompt",
  "content": "The CEO just asked in the leadership Slack channel: 'What's the holdup?' The channel has 20 leaders watching. What do you type?",
  "input_config": {
    "max_length": 500,
    "placeholder": "Type your response..."
  },
  "drill_phase": "Executive Communication",
  "is_iteration": false
}
```

### Frontend Format
```json
{
  "type": "prompt",
  "content": "The CEO just asked in the leadership Slack channel...",
  "input": {
    "type": "text",
    "max_length": 500,
    "placeholder": "Type your response..."
  },
  "drill_phase": "Executive Communication",
  "is_iteration": false
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| card_type | string | "prompt" |
| content | string | The question/prompt |
| input_config.max_length | int | Character limit |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| input_config.placeholder | string | "" | Textarea placeholder text |
| drill_phase | string | — | Current drill name |
| is_iteration | boolean | false | True if this is a required second attempt |

### UI Behavior

- Display prompt content
- Textarea with character counter
- "Submit" button (disabled until input)
- Enforce max_length in UI
- If `is_iteration: true`, may show visual indicator of "second attempt"

### User Response Format

Store as plain text:
```
"I'll sync with [peer] and have an update by EOD..."
```

### Visual Style

- Blue accent
- Pencil icon (✏️)

---

## Card Type: multiple_choice

Presents options. User selects one.

### Tool Call
```json
{
  "card_type": "multiple_choice",
  "content": "What's your primary concern right now?",
  "options": [
    {"id": "a", "label": "How this makes me look to the CEO"},
    {"id": "b", "label": "Protecting my peer from public embarrassment"},
    {"id": "c", "label": "Getting the project unblocked"},
    {"id": "d", "label": "Understanding why they keep missing deadlines"}
  ]
}
```

### Frontend Format
```json
{
  "type": "multiple_choice",
  "content": "What's your primary concern right now?",
  "options": [
    {"id": "a", "label": "How this makes me look to the CEO"},
    {"id": "b", "label": "Protecting my peer from public embarrassment"},
    {"id": "c", "label": "Getting the project unblocked"},
    {"id": "d", "label": "Understanding why they keep missing deadlines"}
  ]
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| card_type | string | "multiple_choice" |
| content | string | The question |
| options | array | Array of option objects |
| options[].id | string | Unique identifier (a, b, c, d) |
| options[].label | string | Display text |

### UI Behavior

- Display question content
- Render clickable option cards
- Clicking an option submits immediately
- Highlight selected option

### User Response Format

Store as JSON:
```json
{"selected": "b"}
```

### Visual Style

- Blue accent
- Radio button icon (◉)

---

## Card Type: insight

Feedback on user's response. No input required.

### Tool Call
```json
{
  "card_type": "insight",
  "content": "Notice what you just did. You framed this as a political problem, not a decision quality problem. You optimized for relationship preservation over clarity. That's a pattern worth examining.",
  "drill_phase": "Problem-Solving"
}
```

### Frontend Format
```json
{
  "type": "insight",
  "content": "Notice what you just did. You framed this as a political problem...",
  "drill_phase": "Problem-Solving"
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| card_type | string | "insight" |
| content | string | The feedback/observation |

### Optional Fields

| Field | Type | Description |
|-------|------|-------------|
| drill_phase | string | Current drill name |

### UI Behavior

- Display insight content
- "Continue" button to proceed
- No input field
- Visually distinct from scenario (different color)

### Visual Style

- Amber/yellow accent
- Lightbulb icon (💡)

---

## Card Type: reflection

Short prompted response. Smaller input than prompt.

### Tool Call
```json
{
  "card_type": "reflection",
  "content": "What's the one principle from today's session you want to carry forward?",
  "input_config": {
    "max_length": 200,
    "placeholder": "Be honest with yourself..."
  }
}
```

### Frontend Format
```json
{
  "type": "reflection",
  "content": "What's the one principle from today's session you want to carry forward?",
  "input": {
    "type": "text",
    "max_length": 200,
    "placeholder": "Be honest with yourself..."
  }
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| card_type | string | "reflection" |
| content | string | The reflection prompt |
| input_config.max_length | int | Character limit (typically 200) |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| input_config.placeholder | string | "" | Textarea placeholder text |

### UI Behavior

- Display reflection prompt
- Smaller textarea than prompt card
- Character counter
- "Submit" button

### User Response Format

Store as plain text.

### Visual Style

- Purple accent
- Mirror icon (🪞)

---

## Card Type: level_up

**NOT returned by AI.** Injected by backend when level-up condition is met.

### Frontend Format (Backend-Generated)
```json
{
  "type": "level_up",
  "new_level": 3,
  "message": "Scenarios will now include more ambiguity and competing priorities."
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| type | string | "level_up" |
| new_level | int | The level just achieved |
| message | string | What changes at this level |

### UI Behavior

- Celebratory/distinct visual treatment
- Display new level prominently
- Show message about what changes
- "Continue Training" button

### Visual Style

- Green accent
- Up arrow icon (⬆️)
- More prominent styling than other cards

---

## Frontend Component Mapping

| card_type | Vue Component | Input? |
|-----------|---------------|--------|
| scenario | ScenarioCard.vue | No |
| prompt | PromptCard.vue | Yes (textarea) |
| multiple_choice | ChoiceCard.vue | Yes (selection) |
| insight | InsightCard.vue | No |
| reflection | ReflectionCard.vue | Yes (small textarea) |
| level_up | LevelUpCard.vue | No |

---

## Visual Differentiation Summary

| Card Type | Accent Color | Icon | Has Input |
|-----------|--------------|------|-----------|
| scenario | Neutral gray | — | No |
| prompt | Blue | ✏️ | Yes |
| multiple_choice | Blue | ◉ | Yes |
| insight | Amber | 💡 | No |
| reflection | Purple | 🪞 | Yes |
| level_up | Green | ⬆️ | No |

---

## Validation

Frontend validates incoming cards after backend normalization:

```javascript
const validators = {
  scenario: (card) => {
    return typeof card.content === 'string' && card.content.length > 0;
  },
  
  prompt: (card) => {
    return typeof card.content === 'string' 
      && card.content.length > 0
      && typeof card.input?.max_length === 'number';
  },
  
  multiple_choice: (card) => {
    return typeof card.content === 'string'
      && card.content.length > 0
      && Array.isArray(card.options)
      && card.options.length > 0
      && card.options.every(o => o.id && o.label);
  },
  
  insight: (card) => {
    return typeof card.content === 'string' && card.content.length > 0;
  },
  
  reflection: (card) => {
    return typeof card.content === 'string'
      && card.content.length > 0
      && typeof card.input?.max_length === 'number';
  },
  
  level_up: (card) => {
    return typeof card.new_level === 'number' && card.new_level > 0;
  },
};

function validateCard(card) {
  const validator = validators[card.type];
  if (!validator) {
    console.error(`Unknown card type: ${card.type}`);
    return false;
  }
  return validator(card);
}
```

---

## Fallback Handling

With tool use, malformed JSON is rare. However, if the tool call fails or returns unexpected data:

1. Log the error with full response for debugging
2. Show a fallback insight card:
```json
{
  "type": "insight",
  "content": "Let's continue. What's on your mind?"
}
```

This keeps the session alive rather than crashing.

---

## Example Session Flow (Tool Use)

```
Backend sends: messages=[{role: "user", content: "Begin training."}]
AI calls: display_card(card_type="scenario", content="You're in a board meeting...")
User: [clicks Continue]

Backend sends: messages=[...history..., {role: "user", content: "[Continue]"}]
AI calls: display_card(card_type="prompt", content="The CFO interrupts...", input_config={max_length: 500, placeholder: "..."})
User: "I would acknowledge their point and..."

Backend sends: messages=[...history..., {role: "user", content: "I would acknowledge their point and..."}]
AI calls: display_card(card_type="insight", content="Notice how you led with accommodation...")
User: [clicks Continue]

Backend sends: messages=[...history..., {role: "user", content: "[Continue]"}]
AI calls: display_card(card_type="multiple_choice", content="What drove that response?", options=[...])
User: [selects option B]

Backend sends: messages=[...history..., {role: "user", content: "{\"selected\": \"b\"}"}]
AI calls: display_card(card_type="reflection", content="Why did that feel safer?", input_config={max_length: 200, ...})
User: "Because I wasn't confident in..."

Backend sends: messages=[...history..., {role: "user", content: "Because I wasn't confident in..."}]
AI calls: display_card(card_type="insight", content="That's the pattern. When uncertain...")
User: [clicks Continue]

[Backend detects level-up condition, injects level_up card before next AI call]
Backend shows: {type: "level_up", new_level: 2, message: "..."}
User: [clicks Continue Training]

[Session continues with Level 2 scenarios]
```