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
            'positive' => ['core_point_captured', 'jargon_removed', 'meaning_preserved', 'clarity'],
            'negative' => [],
        ],

        'brevity' => [
            'positive' => ['word_limit_met', 'concise', 'under_word_limit', 'brevity', 'under_60_seconds', 'appropriate_length'],
            'negative' => ['too_short', 'ran_long', 'no_rambling'],
        ],

        'authority' => [
            'positive' => ['declarative_sentences', 'authority_tone', 'clear_position', 'confident_delivery'],
            'negative' => ['hedging', 'defensive_language'],
        ],

        'structure' => [
            'positive' => ['star_structure', 'prep_structure', 'structure_followed', 'logical_flow', 'present_past_future', 'acknowledge_position_reason'],
            'negative' => [],
        ],

        'composure' => [
            'positive' => ['calm_tone', 'composure', 'stayed_calm', 'non_defensive', 'maintained_composure'],
            'negative' => [],
        ],

        'directness' => [
            'positive' => ['direct_opening', 'lead_with_news', 'headline_first', 'started_strong', 'bottom_line_first', 'no_stalling', 'direct_admission'],
            'negative' => ['no_buried_lead'],
        ],

        'ownership' => [
            'positive' => ['ownership', 'owned_it', 'accountability_shown', 'proactive'],
            'negative' => ['no_blame', 'blame_shifting'],
        ],

        'authenticity' => [
            'positive' => ['authentic', 'genuine_interest', 'no_excessive_apology'],
            'negative' => ['humble_brag_avoided'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Universal Criteria
    |--------------------------------------------------------------------------
    |
    | These are tracked separately across all responses, regardless of skill.
    |
    */

    'universal_criteria' => [
        'hedging',
        'filler_phrases',
        'apology_detected',
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
