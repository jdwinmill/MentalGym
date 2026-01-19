# Card Types

AI responses are JSON objects with a `type` field. The frontend parses and renders the appropriate Vue component. This creates a structured training experience, not a chat interface.

---

## Card Type: scenario

Sets the scene. No user input required.

### JSON Structure
```json
{
  "type": "scenario",
  "content": "You're two weeks into a new VP role. Your first big initiative is stalling because a peer VP keeps missing commitments. You've mentioned it twice in 1:1s. Today, they missed another deadline."
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| type | string | "scenario" |
| content | string | The scene description |

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

### JSON Structure
```json
{
  "type": "prompt",
  "content": "The CEO just asked in the leadership Slack channel: 'What's the holdup?' The channel has 20 leaders watching. What do you type?",
  "input": {
    "type": "text",
    "max_length": 500,
    "placeholder": "Type your response..."
  }
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| type | string | "prompt" |
| content | string | The question/prompt |
| input.type | string | "text" |
| input.max_length | int | Character limit |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| input.placeholder | string | "" | Textarea placeholder text |

### UI Behavior

- Display prompt content
- Textarea with character counter
- "Submit" button (disabled until input)
- Enforce max_length in UI

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

### JSON Structure
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
| type | string | "multiple_choice" |
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

### JSON Structure
```json
{
  "type": "insight",
  "content": "Notice what you just did. You framed this as a political problem, not a decision quality problem. You optimized for relationship preservation over clarity. That's a pattern worth examining."
}
```

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| type | string | "insight" |
| content | string | The feedback/observation |

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

### JSON Structure
```json
{
  "type": "reflection",
  "content": "Why did protecting your peer feel more urgent than answering the CEO's question directly?",
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
| type | string | "reflection" |
| content | string | The reflection prompt |
| input.type | string | "text" |
| input.max_length | int | Character limit (typically 200) |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| input.placeholder | string | "" | Textarea placeholder text |

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

### JSON Structure
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

| JSON type | Vue Component | Input? |
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

Frontend should validate incoming JSON:
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

If JSON is malformed or type is unknown:

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

## Example Session Flow
```
AI: {"type": "scenario", "content": "You're in a board meeting..."}
User: [clicks Continue]

AI: {"type": "prompt", "content": "The CFO interrupts...", "input": {...}}
User: "I would acknowledge their point and..."

AI: {"type": "insight", "content": "Notice how you led with accommodation..."}
User: [clicks Continue]

AI: {"type": "multiple_choice", "content": "What drove that response?", "options": [...]}
User: [selects option B]

AI: {"type": "reflection", "content": "Why did that feel safer?", "input": {...}}
User: "Because I wasn't confident in..."

AI: {"type": "insight", "content": "That's the pattern. When uncertain..."}
User: [clicks Continue]

[Backend detects level-up condition]
Backend injects: {"type": "level_up", "new_level": 2, "message": "..."}
User: [clicks Continue Training]

AI: {"type": "scenario", "content": "[Level 2 complexity scenario]..."}
...continues
```