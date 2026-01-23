# Practice Mode Refactor: Platform-Controlled Architecture

## Overview

Replace the current AI-controlled flow with a platform-controlled architecture where:
- The **platform** drives progression through defined drills
- The **AI** is a focused tool for generating scenarios and providing feedback
- **Drills** are defined as data/config, not emergent from AI behavior

---

## Current Problems

1. **AI controls everything** - Drill type, progression, timer, iteration all decided by AI
2. **Huge instruction sets** - One massive prompt trying to define all behavior
3. **Unpredictable state** - Hard to know where we are or why AI did something
4. **Debugging is painful** - "Why did it get stuck on scenario 3?"
5. **Can't configure per-drill** - Timer, tools, format all baked into AI instructions

---

## New Architecture

### Core Concept

**Platform controls the flow. AI generates content.**

### Instruction Set Hierarchy

Three-tier inheritance: Global → Mode → Drill (with separate scenario/evaluation instructions)

```
main_instruction_set (GLOBAL)
│   "Always respond in JSON. Be direct. No fluff.
│    Feedback should be specific and actionable."
│
├── PracticeMode.instruction_set
│   │   "Executive Communication: Evaluate for clarity,
│   │    brevity, and appropriate audience calibration.
│   │    Flag jargon, passive voice, and buried leads."
│   │
│   ├── Drill: "Bad News Delivery"
│   │   ├── scenario_instruction_set
│   │   │     "Generate a scenario where user must deliver
│   │   │      bad news. Types: layoffs, budget cuts..."
│   │   │
│   │   └── evaluation_instruction_set
│   │         "Evaluate for empathy, directness, clarity.
│   │          Score harshly on burying the lead."
│   │
│   └── Drill: "Executive Pushback"
│       ├── scenario_instruction_set
│       └── evaluation_instruction_set
│
└── PracticeMode.instruction_set
    │   "Critical Thinking: Evaluate for logical structure..."
    │
    └── Drill: "Hidden Assumptions"
        ├── scenario_instruction_set
        └── evaluation_instruction_set
```

### What Each Level Handles

| Level | Scope | Examples |
|-------|-------|----------|
| **Global** | All modes, all drills | JSON format, tone, feedback structure |
| **Mode** | All drills in this mode | Skill definition, general evaluation philosophy |
| **Drill.scenario** | This specific drill | How to generate the scenario/task |
| **Drill.evaluation** | This specific drill | Specific criteria for scoring this drill type |

### Prompt Assembly

Two separate AI calls with different instruction combinations:

**GENERATE call:**
```
[Global]  +  [Mode]  +  [Drill.scenario_instruction_set]
```

**EVALUATE call:**
```
[Global]  +  [Mode]  +  [Drill.evaluation_instruction_set]
```

### Session Flow

```
drill_index = 0

while drill_index < drills.length:
    drill = drills[drill_index]

    ┌─────────────────────────────────────────────┐
    │ AI CALL 1: GENERATE                         │
    │ Input: drill.scenario_instruction_set       │
    │ Output: scenario + task                     │
    └─────────────────────────────────────────────┘
                        │
                        ▼
    ┌─────────────────────────────────────────────┐
    │ SHOW SCENARIO + TASK                        │
    │ Timer: drill.timer_seconds (if defined)     │
    │ Input type: drill.input_type                │
    └─────────────────────────────────────────────┘
                        │
                        ▼
    ┌─────────────────────────────────────────────┐
    │ USER SUBMITS RESPONSE                       │
    └─────────────────────────────────────────────┘
                        │
                        ▼
    ┌─────────────────────────────────────────────┐
    │ AI CALL 2: EVALUATE                         │
    │ Input: drill.evaluation_instruction_set     │
    │ Output: feedback + score                    │
    └─────────────────────────────────────────────┘
                        │
                        ▼
    ┌─────────────────────────────────────────────┐
    │ SHOW FEEDBACK                               │
    │ [Continue] button                           │
    └─────────────────────────────────────────────┘
                        │
                        ▼
                 drill_index++
```

---

## Migration Phases

| Phase | Focus | Doc |
|-------|-------|-----|
| 1 | Database Schema | [01-database-schema.md](./01-database-schema.md) |
| 2 | Backend Services | [02-backend-services.md](./02-backend-services.md) |
| 3 | Events & Analytics | [03-events-analytics.md](./03-events-analytics.md) |
| 4 | Frontend | [04-frontend.md](./04-frontend.md) |
| 5 | Cleanup | [05-cleanup.md](./05-cleanup.md) |

---

## Benefits Summary

| Aspect | Before | After |
|--------|--------|-------|
| Control | AI-driven | Platform-driven |
| Progression | AI decides | drill_index++ |
| Timer config | In AI prompt | drill.timer_seconds |
| Input type | AI decides | drill.input_type |
| Debugging | "Why did AI...?" | "Drill 3 config says..." |
| Instruction size | Huge, monolithic | Small, focused |
| Adding drills | Edit massive prompt | Add row to drills table |
| State tracking | Complex, fragmented | drill_index + phase |
