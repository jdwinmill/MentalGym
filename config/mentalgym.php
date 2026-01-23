<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global Instruction Set
    |--------------------------------------------------------------------------
    |
    | Base instructions applied to all AI interactions across all practice
    | modes. Mode-specific and drill-specific instructions are layered on top.
    |
    */
    'main_instruction_set' => <<<'PROMPT'
Always respond in valid JSON format.
Be direct. No fluff or filler phrases.
Feedback should be specific, actionable, and constructive.
Reference the user's actual words when giving feedback.
Never be condescending. Assume competence.
PROMPT,

    /*
    |--------------------------------------------------------------------------
    | Level Up Threshold
    |--------------------------------------------------------------------------
    |
    | Number of completed sessions required to level up in a practice mode.
    |
    */
    'sessions_to_level_up' => 3,
];
