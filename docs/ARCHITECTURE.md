# SharpStack Architecture

## Overview

SharpStack is a mental fitness training platform built on "Practice Modes" - structured AI training sessions powered by curated instruction sets. Users engage in scenario-based training where AI responses are delivered as typed cards, not chat bubbles.

## Core Concepts

### Practice Mode
A training methodology wrapped in an instruction set. Defines how Claude engages with the user - questioning patterns, analytical frameworks, feedback style. Created and curated by admins.

### Training Session
An infinite loop of exchanges between user and AI. No defined end - users train until they choose to stop or hit their daily exchange limit. Sessions can be resumed.

### Exchange
One user input + one AI response. The atomic unit of training and cost tracking.

### Cards
AI responses are JSON, parsed into typed UI components. This creates a structured training experience, not a chat interface.

Card types:
- **Scenario** - Sets the scene (no input)
- **Prompt** - Asks for text response
- **Choice** - Multiple choice question
- **Insight** - Feedback on user's response (no input)
- **Reflection** - Short prompted response
- **Level Up** - Progress notification (backend-injected, not from AI)

### Levels
Users progress from Level 1-5 within each Practice Mode. Higher levels = more complex scenarios. Level progression is gated by subscription tier.

### Streaks
Consecutive days of training. Encourages habit formation.

## Tech Stack

- **Backend:** Laravel 11
- **Frontend:** Vue 3 + Inertia.js
- **Database:** MySQL
- **AI:** Anthropic Claude API (Sonnet) with prompt caching
- **Payments:** Stripe (existing infrastructure)
- **Auth:** Laravel built-in (existing infrastructure)

## Request Flow
```
User clicks "Begin Training"
    → Controller checks PracticeModePolicy@start
    → TrainingSessionService creates/resumes session
    → User's current level injected into instruction set
    → PracticeAIService sends request to Claude (with caching)
    → Claude responds with JSON
    → Frontend parses JSON → renders appropriate Card component
    → User responds (text input or choice selection)
    → Controller checks Gate: can-train
    → Exchange recorded in session_messages
    → DailyUsage incremented
    → UserModeProgress updated
    → Check for level-up condition
    → Loop continues until user ends or hits limit
```

## Key Services

| Service | Responsibility |
|---------|----------------|
| `TrainingSessionService` | Start, continue, end, resume sessions. Build message history. Detect level-ups. |
| `PracticeAIService` | Communicate with Claude API. Handle caching, token limits, errors. Parse responses. |
| `UsageService` | Track daily exchanges, check limits, return remaining count. |
| `ProgressService` | Track levels, calculate level-up thresholds, manage streaks. |

## Directory Structure (New Files)
```
app/
├── Models/
│   ├── PracticeMode.php
│   ├── Tag.php
│   ├── TrainingSession.php
│   ├── SessionMessage.php
│   ├── UserModeProgress.php
│   ├── DailyUsage.php
│   └── UserStreak.php
├── Policies/
│   ├── PracticeModePolicy.php
│   ├── TrainingSessionPolicy.php
│   └── TagPolicy.php
├── Services/
│   ├── TrainingSessionService.php
│   ├── PracticeAIService.php
│   ├── UsageService.php
│   └── ProgressService.php
├── Http/Controllers/
│   ├── PracticeModeController.php
│   ├── TrainingSessionController.php
│   └── Admin/
│       ├── PracticeModeController.php
│       └── TagController.php

config/
└── plans.php

resources/js/
├── Pages/
│   ├── PracticeModes/
│   │   └── Index.vue
│   └── Training/
│       └── Session.vue
└── Components/
    └── Cards/
        ├── ScenarioCard.vue
        ├── PromptCard.vue
        ├── ChoiceCard.vue
        ├── InsightCard.vue
        ├── ReflectionCard.vue
        └── LevelUpCard.vue
```

## Security Boundaries

1. **Instruction sets are hidden** - Users never see the system prompt. AI is instructed to refuse disclosure.
2. **Authorization centralized** - Gates and Policies control all access. No permission logic in views.
3. **Plan limits enforced server-side** - Frontend displays limits but backend enforces them.
4. **Session ownership** - Users can only access their own sessions and progress.

## Cost Management

- **Prompt caching** - Instruction set cached after first exchange (~90% savings)
- **Rolling context window** - Only last 10 exchanges sent to API
- **Token limits** - Max response tokens capped per mode
- **Character limits** - User input constrained in UI
- **Daily exchange limits** - Hard caps by plan tier