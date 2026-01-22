<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Skill Tags and Their Criteria
    |--------------------------------------------------------------------------
    |
    | Maps skill tags to the scoring criteria that measure them.
    | When analyzing blind spots, we look at failure rates for these criteria
    | to determine if a user has a pattern in that skill area.
    |
    */

    'skill_criteria' => [
        'clarity' => [
            'name' => 'Clarity',
            'description' => 'Communicating ideas so anyone can understand them without context',
            'target' => 'Strip jargon, capture the core point, make it obvious',
            'positive' => ['core_point_captured', 'jargon_removed', 'meaning_preserved', 'clarity'],
            'negative' => [],
            'tips' => [
                'Ask: "Would someone outside my team understand this?"',
                'Replace every acronym and buzzword with plain language',
                'State the core point in the first sentence',
            ],
        ],

        'brevity' => [
            'name' => 'Brevity',
            'description' => 'Saying what needs to be said in fewer words',
            'target' => 'Hit the word limit, cut the fluff, respect the clock',
            'positive' => ['word_limit_met', 'concise', 'under_word_limit', 'brevity', 'under_60_seconds', 'appropriate_length'],
            'negative' => ['too_short', 'ran_long', 'no_rambling'],
            'tips' => [
                'Write your response, then cut 20%',
                'One idea per sentence—no stacking',
                'If you can remove a word without losing meaning, remove it',
            ],
        ],

        'authority' => [
            'name' => 'Authority',
            'description' => 'Speaking with confidence and conviction, not hedging',
            'target' => 'Sound decisive—no "I think", "maybe", or "probably"',
            'positive' => ['declarative_sentences', 'authority_tone', 'clear_position', 'confident_delivery'],
            'negative' => ['hedging', 'defensive_language'],
            'tips' => [
                'Delete "I think" and "I believe"—just state it',
                'Replace "We should maybe consider..." with "We should..."',
                'Take a position, even if you acknowledge tradeoffs',
            ],
        ],

        'structure' => [
            'name' => 'Structure',
            'description' => 'Organizing thoughts in a clear, followable framework',
            'target' => 'Use a recognizable structure (STAR, PREP, etc.)',
            'positive' => ['star_structure', 'prep_structure', 'structure_followed', 'logical_flow', 'present_past_future', 'acknowledge_position_reason'],
            'negative' => [],
            'tips' => [
                'Pick a framework before you start (STAR, PREP, Problem→Solution)',
                'Signal your structure: "Three things..." or "First... Second..."',
                'End where you started—restate your main point',
            ],
        ],

        'composure' => [
            'name' => 'Composure',
            'description' => 'Staying calm and measured under pressure',
            'target' => 'No defensiveness, no panic, no over-explaining',
            'positive' => ['calm_tone', 'composure', 'stayed_calm', 'non_defensive', 'maintained_composure'],
            'negative' => [],
            'tips' => [
                'Pause before responding—silence is okay',
                'Acknowledge the challenge, then respond (don\'t ignore it)',
                'Shorter responses often sound calmer',
            ],
        ],

        'directness' => [
            'name' => 'Directness',
            'description' => 'Leading with the point, not burying it',
            'target' => 'Main point in sentence one—no warm-up',
            'positive' => ['direct_opening', 'lead_with_news', 'headline_first', 'started_strong', 'bottom_line_first', 'no_stalling', 'direct_admission'],
            'negative' => ['no_buried_lead'],
            'tips' => [
                'Write your response, then move the last sentence to the top',
                'Delete throat-clearing phrases: "So basically...", "I wanted to..."',
                'Bad news goes first—don\'t make them wait for it',
            ],
        ],

        'ownership' => [
            'name' => 'Ownership',
            'description' => 'Taking responsibility and showing accountability',
            'target' => 'Own mistakes, bring solutions, no finger-pointing',
            'positive' => ['ownership', 'owned_it', 'accountability_shown', 'proactive'],
            'negative' => ['no_blame', 'blame_shifting'],
            'tips' => [
                'Replace "We couldn\'t because..." with "I should have..."',
                'Always pair a problem with a proposed solution',
                'Say "I" not "we" when describing your contribution',
            ],
        ],

        'authenticity' => [
            'name' => 'Authenticity',
            'description' => 'Sounding genuine without over-apologizing or humble-bragging',
            'target' => 'Be real—admit gaps honestly, share wins without disclaimers',
            'positive' => ['authentic', 'genuine_interest', 'no_excessive_apology'],
            'negative' => ['humble_brag_avoided'],
            'tips' => [
                'One apology max—then move forward',
                'State your win directly: "I led..." not "I was lucky to..."',
                'If you don\'t know, say so—then pivot to what you\'d do to find out',
            ],
        ],

        'specificity' => [
            'name' => 'Specificity',
            'description' => 'Being concrete and precise instead of vague or generic',
            'target' => 'Use real examples, actual numbers, specific actions',
            'positive' => ['specific_behavior', 'action_specific', 'example_specific', 'result_measurable', 'metrics_included', 'impact_quantified', 'example_relevant'],
            'negative' => [],
            'tips' => [
                'Replace "improved performance" with "reduced errors by 30%"',
                'Name the specific action YOU took, not what "the team" did',
                'If you can\'t remember the exact number, estimate and say so',
            ],
        ],

        'solution_focus' => [
            'name' => 'Solution Focus',
            'description' => 'Bringing options and recommendations, not just problems',
            'target' => 'Every problem comes with at least one proposed solution',
            'positive' => ['solution_oriented', 'options_presented', 'recommendation_clear', 'recommendation_included', 'forward_focused', 'forward_action', 'forward_looking', 'next_steps_clear', 'offered_alternative'],
            'negative' => [],
            'tips' => [
                'Before raising a problem, draft 2-3 possible solutions',
                'Lead with your recommendation: "I suggest X because..."',
                'End with a clear next step, even if it\'s "I\'ll research and follow up"',
            ],
        ],

        'empathy' => [
            'name' => 'Empathy',
            'description' => 'Acknowledging others\' perspectives and maintaining relationships',
            'target' => 'Show you understand their position before stating yours',
            'positive' => ['acknowledged_concern', 'empathy_shown', 'empathy_appropriate', 'relationship_preserved', 'respect_maintained'],
            'negative' => [],
            'tips' => [
                'Start tough conversations with "I understand this is difficult..."',
                'Name the other person\'s likely concern before addressing it',
                'You can be direct AND kind—they\'re not opposites',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Criteria Labels
    |--------------------------------------------------------------------------
    |
    | Human-readable labels for scoring criteria shown to users.
    |
    */

    'criteria_labels' => [
        // Positive criteria (failing these = problem)
        'core_point_captured' => 'Missing the core point',
        'jargon_removed' => 'Too much jargon',
        'meaning_preserved' => 'Lost the meaning',
        'clarity' => 'Unclear message',
        'word_limit_met' => 'Over word limit',
        'concise' => 'Not concise enough',
        'under_word_limit' => 'Exceeded limit',
        'brevity' => 'Too wordy',
        'under_60_seconds' => 'Ran over time',
        'appropriate_length' => 'Wrong length',
        'declarative_sentences' => 'Not declarative',
        'authority_tone' => 'Lacking authority',
        'clear_position' => 'No clear position',
        'confident_delivery' => 'Not confident',
        'star_structure' => 'Missing STAR structure',
        'prep_structure' => 'Missing PREP structure',
        'structure_followed' => 'No clear structure',
        'logical_flow' => 'Ideas don\'t flow',
        'present_past_future' => 'Missing time arc',
        'acknowledge_position_reason' => 'Didn\'t acknowledge + position',
        'calm_tone' => 'Tone not calm',
        'composure' => 'Lost composure',
        'stayed_calm' => 'Didn\'t stay calm',
        'non_defensive' => 'Got defensive',
        'maintained_composure' => 'Composure slipped',
        'direct_opening' => 'Buried the lead',
        'lead_with_news' => 'Didn\'t lead with news',
        'headline_first' => 'No headline first',
        'started_strong' => 'Weak opening',
        'bottom_line_first' => 'Bottom line buried',
        'no_stalling' => 'Stalled at start',
        'direct_admission' => 'Indirect admission',
        'ownership' => 'Didn\'t own it',
        'owned_it' => 'Avoided ownership',
        'accountability_shown' => 'No accountability',
        'proactive' => 'Not proactive',
        'authentic' => 'Didn\'t sound genuine',
        'genuine_interest' => 'Lacked genuine interest',
        'no_excessive_apology' => 'Over-apologized',

        // Negative criteria (having these = problem)
        'hedging' => 'Hedging language',
        'defensive_language' => 'Defensive language',
        'too_short' => 'Too short',
        'ran_long' => 'Ran too long',
        'no_rambling' => 'Rambled',
        'no_buried_lead' => 'Buried the lead',
        'no_blame' => 'Blamed others',
        'blame_shifting' => 'Shifted blame',
        'humble_brag_avoided' => 'Humble-bragged',

        // Specificity criteria
        'specific_behavior' => 'Too vague',
        'action_specific' => 'Actions not specific',
        'example_specific' => 'Example too generic',
        'result_measurable' => 'No measurable result',
        'metrics_included' => 'Missing metrics',
        'impact_quantified' => 'Impact not quantified',
        'example_relevant' => 'Example not relevant',

        // Solution focus criteria
        'solution_oriented' => 'Not solution-focused',
        'options_presented' => 'No options offered',
        'recommendation_clear' => 'No clear recommendation',
        'recommendation_included' => 'Missing recommendation',
        'forward_focused' => 'Not forward-focused',
        'forward_action' => 'No forward action',
        'forward_looking' => 'Not forward-looking',
        'next_steps_clear' => 'Unclear next steps',
        'offered_alternative' => 'No alternative offered',

        // Empathy criteria
        'acknowledged_concern' => 'Didn\'t acknowledge concern',
        'empathy_shown' => 'No empathy shown',
        'empathy_appropriate' => 'Empathy missing',
        'relationship_preserved' => 'Relationship damaged',
        'respect_maintained' => 'Respect not maintained',

        // Universal criteria
        'filler_phrases' => 'Filler words',
        'apology_detected' => 'Unnecessary apology',
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Thresholds
    |--------------------------------------------------------------------------
    */

    'thresholds' => [
        'blind_spot' => 0.6,        // 60% failure rate = blind spot
        'improvement' => 0.2,       // 20% improvement = improving
        'regression' => 0.15,       // 15% regression = slipping
        'minimum_responses' => 5,   // Need 5 responses to analyze
        'minimum_sessions' => 5,    // Need 5 sessions for analysis
    ],

    /*
    |--------------------------------------------------------------------------
    | Time Windows (in days)
    |--------------------------------------------------------------------------
    */

    'windows' => [
        'recent' => 7,              // Last 7 days for recent performance
        'baseline' => 30,           // Last 30 days for baseline
    ],
];
