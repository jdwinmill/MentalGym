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

        /*
        |--------------------------------------------------------------------------
        | Manipulation Resistance Skills (Cognitive Saboteur)
        |--------------------------------------------------------------------------
        */

        'detection_speed' => [
            'name' => 'Detection Speed',
            'description' => 'How quickly you recognize manipulation is occurring',
            'target' => 'Catch tactics in real-time, not just in retrospect',
            'positive' => ['early_recognition', 'real_time_detection', 'pattern_spotted_quickly'],
            'negative' => ['missed_until_debrief', 'late_recognition', 'only_retrospective'],
            'tips' => [
                'Notice when your emotions shift suddenly—that\'s often a signal',
                'If you feel rushed, guilty, or flattered out of nowhere, pause',
                'Trust the "something\'s off" feeling before you can name it',
            ],
            'score_anchors' => [
                1 => 'Only recognized manipulation during debrief',
                3 => 'Noticed something was off but couldn\'t act on it in time',
                5 => 'Recognized manipulation mid-conversation but after key concessions',
                7 => 'Caught tactics early enough to adjust approach',
                10 => 'Identified manipulation immediately and responded from awareness',
            ],
        ],

        'tactic_identification' => [
            'name' => 'Tactic Identification',
            'description' => 'Accurately naming the specific technique being used',
            'target' => '"This is anchoring" vs. vague sense something\'s off',
            'positive' => ['tactic_named', 'technique_identified', 'pattern_articulated'],
            'negative' => ['vague_suspicion', 'couldnt_name_tactic', 'misidentified_technique'],
            'tips' => [
                'Learn the names: anchoring, false dichotomy, manufactured consensus',
                'Naming it—even silently—breaks its power',
                'Ask yourself: "What category does this fall into?"',
            ],
            'score_anchors' => [
                1 => 'Couldn\'t identify any tactics used',
                3 => 'Sensed manipulation but couldn\'t name what was happening',
                5 => 'Identified the general category (e.g., "emotional pressure")',
                7 => 'Named specific tactics accurately (e.g., "anchoring", "false dichotomy")',
                10 => 'Precisely identified all tactics including layered/secondary techniques',
            ],
        ],

        'frame_awareness' => [
            'name' => 'Frame Awareness',
            'description' => 'Recognizing when you\'re operating inside someone else\'s frame',
            'target' => 'Notice you\'re debating "how" when you never agreed to "whether"',
            'positive' => ['frame_recognized', 'reframed_conversation', 'rejected_false_frame'],
            'negative' => ['accepted_frame', 'debated_within_frame', 'frame_blindness'],
            'tips' => [
                'If you\'re defending, ask: "Who set the terms of this debate?"',
                'Watch for questions that assume conclusions you haven\'t agreed to',
                'Step back: "Wait—should we even be discussing this?"',
            ],
            'score_anchors' => [
                1 => 'Fully operated within manipulator\'s frame without noticing',
                3 => 'Felt constrained but couldn\'t identify why',
                5 => 'Recognized the frame late, after making concessions within it',
                7 => 'Identified the frame and partially stepped outside it',
                10 => 'Immediately recognized and rejected false framing, set own terms',
            ],
        ],

        'emotional_regulation' => [
            'name' => 'Emotional Regulation',
            'description' => 'Maintaining clear thinking under pressure, guilt, flattery, or urgency',
            'target' => 'Respond from assessment, not reaction',
            'positive' => ['stayed_regulated', 'thinking_preserved', 'emotional_awareness'],
            'negative' => ['reactive_response', 'emotionally_hijacked', 'pressure_caved'],
            'tips' => [
                'Name the emotion they\'re trying to trigger: "I notice I feel guilty"',
                'Buy time: "Let me think about that" breaks the pressure loop',
                'Flattery that feels too good is usually doing work',
            ],
            'score_anchors' => [
                1 => 'Emotional response completely drove decision-making',
                3 => 'Recognized emotional pressure but still got swept up',
                5 => 'Managed some emotional tactics but others landed',
                7 => 'Maintained composure, made decisions from reason not reaction',
                10 => 'Fully regulated—acknowledged emotions without being controlled by them',
            ],
        ],

        'premise_challenging' => [
            'name' => 'Premise Challenging',
            'description' => 'Questioning hidden assumptions before engaging with the surface question',
            'target' => '"That assumes X—do we agree on X?"',
            'positive' => ['premise_questioned', 'assumption_challenged', 'refused_loaded_question'],
            'negative' => ['accepted_premise', 'engaged_surface_only', 'missed_hidden_assumption'],
            'tips' => [
                'Before answering, ask: "What does this question assume?"',
                'Loaded questions have hidden premises—don\'t accept them by answering',
                '"I don\'t agree with how that\'s framed" is a valid response',
            ],
            'score_anchors' => [
                1 => 'Accepted all premises without question',
                3 => 'Answered questions without examining assumptions',
                5 => 'Challenged some premises but missed others',
                7 => 'Consistently questioned assumptions before engaging',
                10 => 'Identified and rejected hidden premises, reframed on own terms',
            ],
        ],

        'boundary_assertion' => [
            'name' => 'Boundary Assertion',
            'description' => 'Clearly declining or redirecting without over-explaining or caving',
            'target' => 'Clean "no" vs. waffling, apologizing, or eventual capitulation',
            'positive' => ['boundary_held', 'clean_decline', 'position_maintained'],
            'negative' => ['boundary_eroded', 'over_explained', 'eventually_caved'],
            'tips' => [
                'State your position once clearly—repetition weakens it',
                'You don\'t need to justify your "no" with reasons they\'ll accept',
                'Silence after declining is powerful—don\'t fill it',
            ],
            'score_anchors' => [
                1 => 'No boundaries set; agreed to everything requested',
                3 => 'Attempted boundaries but caved under pressure',
                5 => 'Held some boundaries but over-explained or apologized excessively',
                7 => 'Set clear boundaries with minimal justification',
                10 => 'Maintained firm boundaries calmly, without defensiveness or excessive explanation',
            ],
        ],

        'counter_move' => [
            'name' => 'Counter-Move Execution',
            'description' => 'Deploying effective responses that shift the dynamic',
            'target' => 'Reframing, naming, redirecting—not just enduring',
            'positive' => ['effective_counter', 'dynamic_shifted', 'took_initiative'],
            'negative' => ['passive_response', 'only_defended', 'no_counter_move'],
            'tips' => [
                'Name the tactic out loud: "It sounds like you\'re creating urgency"',
                'Ask questions that shift burden: "What makes you say that?"',
                'Redirect to your terms: "Before we go there, let\'s establish..."',
            ],
            'score_anchors' => [
                1 => 'No counter-moves; purely reactive or passive',
                3 => 'Attempted resistance but didn\'t shift the dynamic',
                5 => 'Some effective pushback but manipulator maintained control',
                7 => 'Deployed counter-moves that changed the conversation flow',
                10 => 'Skillfully reframed, redirected, or named tactics—took control of dynamic',
            ],
        ],

        'recovery' => [
            'name' => 'Recovery',
            'description' => 'Regaining footing after getting caught or partially conceding',
            'target' => 'Return to solid ground mid-conversation',
            'positive' => ['recovered_position', 'regained_footing', 'corrected_course'],
            'negative' => ['stayed_off_balance', 'compounded_concession', 'couldnt_recover'],
            'tips' => [
                '"Actually, let me reconsider what I just said" is always available',
                'A partial concession doesn\'t mean total surrender',
                'Pause, breathe, reset—you can change direction mid-conversation',
            ],
            'score_anchors' => [
                1 => 'Once caught, stayed off-balance for remainder',
                3 => 'Recognized slip but couldn\'t course-correct',
                5 => 'Partial recovery—stopped bleeding but didn\'t regain ground',
                7 => 'Recovered effectively after initial misstep',
                10 => 'Turned a caught moment into a stronger position',
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

        // Manipulation Resistance - Detection Speed
        'early_recognition' => 'Didn\'t recognize early',
        'real_time_detection' => 'Missed in real-time',
        'pattern_spotted_quickly' => 'Pattern spotted late',
        'missed_until_debrief' => 'Only saw it in hindsight',
        'late_recognition' => 'Recognized too late',
        'only_retrospective' => 'Only retrospective awareness',

        // Manipulation Resistance - Tactic Identification
        'tactic_named' => 'Couldn\'t name the tactic',
        'technique_identified' => 'Technique not identified',
        'pattern_articulated' => 'Couldn\'t articulate pattern',
        'vague_suspicion' => 'Only vague suspicion',
        'couldnt_name_tactic' => 'Couldn\'t name what happened',
        'misidentified_technique' => 'Misidentified the technique',

        // Manipulation Resistance - Frame Awareness
        'frame_recognized' => 'Didn\'t recognize the frame',
        'reframed_conversation' => 'Failed to reframe',
        'rejected_false_frame' => 'Accepted false framing',
        'accepted_frame' => 'Operated in their frame',
        'debated_within_frame' => 'Debated within their frame',
        'frame_blindness' => 'Frame blindness',

        // Manipulation Resistance - Emotional Regulation
        'stayed_regulated' => 'Lost emotional regulation',
        'thinking_preserved' => 'Thinking got clouded',
        'emotional_awareness' => 'Lacked emotional awareness',
        'reactive_response' => 'Responded reactively',
        'emotionally_hijacked' => 'Got emotionally hijacked',
        'pressure_caved' => 'Caved under pressure',

        // Manipulation Resistance - Premise Challenging
        'premise_questioned' => 'Didn\'t question premise',
        'assumption_challenged' => 'Accepted hidden assumptions',
        'refused_loaded_question' => 'Fell for loaded question',
        'accepted_premise' => 'Accepted false premise',
        'engaged_surface_only' => 'Only engaged surface level',
        'missed_hidden_assumption' => 'Missed hidden assumption',

        // Manipulation Resistance - Boundary Assertion
        'boundary_held' => 'Boundary not held',
        'clean_decline' => 'Messy decline',
        'position_maintained' => 'Position eroded',
        'boundary_eroded' => 'Boundary eroded',
        'over_explained' => 'Over-explained',
        'eventually_caved' => 'Eventually caved',

        // Manipulation Resistance - Counter-Move
        'effective_counter' => 'No effective counter',
        'dynamic_shifted' => 'Didn\'t shift dynamic',
        'took_initiative' => 'Stayed passive',
        'passive_response' => 'Passive response',
        'only_defended' => 'Only defended, didn\'t counter',
        'no_counter_move' => 'No counter-move attempted',

        // Manipulation Resistance - Recovery
        'recovered_position' => 'Didn\'t recover position',
        'regained_footing' => 'Couldn\'t regain footing',
        'corrected_course' => 'Failed to course-correct',
        'stayed_off_balance' => 'Stayed off-balance',
        'compounded_concession' => 'Compounded the concession',
        'couldnt_recover' => 'Couldn\'t recover',
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
