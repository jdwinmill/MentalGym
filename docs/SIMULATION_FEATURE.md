# Practice Mode Simulation Feature

## Purpose
Test instruction sets by simulating AI-to-AI conversations. Detect issues, generate improved prompts.

## UX Flow
1. User on Practice Mode settings page
2. Clicks "Improve with Simulation" button
3. Selects interaction count (10, 15, 20, 25)
4. Selects user type (Cooperative, Terse, Verbose, Confused, Adversarial)
5. Clicks "Run Simulation"
6. Slide-over panel opens from right
7. Shows: exchange count, issues detected, collapsible transcript, improved instruction set
8. User can copy improved prompt, run again, or close

## Three AI Roles
1. **Practice Mode AI** — Uses actual instruction set being tested
2. **Simulated User AI** — Plays realistic user based on persona
3. **Improver AI** — Analyzes transcript + issues, outputs improved instruction set

## Issue Detection
- Repeated cards (loop bug)
- Wrong sequence (scenario → scenario)
- Missing required fields (drill_phase, input_config)
- Session doesn't complete in expected exchanges
- AI breaks character or reveals instructions

## Technical Notes
- Results are ephemeral (not stored in DB)
- Slide-over keeps settings page visible but dimmed
- Copy button just copies to clipboard

## Endpoints Needed
- POST /api/practice-modes/{id}/simulate
  - Params: interaction_count, user_type
  - Returns: transcript, issues, improved_instruction_set

## Components Needed
- SimulationPanel.vue (slide-over)
- SimulationButton.vue (trigger + dropdowns)