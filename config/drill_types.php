<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Drill Phase to Practice Mode Mapping
    |--------------------------------------------------------------------------
    |
    | Maps drill phases to their parent practice mode slug. Used by the
    | Blind Spots feature to link "Train This" buttons to the relevant mode.
    |
    */

    'practice_mode_mapping' => [
        // MBA+ Executive Training → mba-decision-lab
        'Compression' => 'mba-decision-lab',
        'Executive Communication' => 'mba-decision-lab',
        'Problem-Solving' => 'mba-decision-lab',
        'Writing Precision' => 'mba-decision-lab',

        // Interview Prep → interview-prep (future mode)
        'Story Compression' => 'interview-prep',
        'The Opener' => 'interview-prep',
        'Curveball Recovery' => 'interview-prep',
        'Closing Strong' => 'interview-prep',

        // Thinking on Your Feet → thinking-on-your-feet (future mode)
        'Unexpected Question' => 'thinking-on-your-feet',
        'Impromptu Structure' => 'thinking-on-your-feet',
        'Defending Your Position' => 'thinking-on-your-feet',
        'Graceful I Don\'t Know' => 'thinking-on-your-feet',

        // Difficult Conversations → difficult-conversations (future mode)
        'The Direct Open' => 'difficult-conversations',
        'Holding the Line' => 'difficult-conversations',
        'The Clean No' => 'difficult-conversations',
        'Bad News Delivery' => 'difficult-conversations',

        // Negotiation → negotiation (future mode)
        'Negotiation Anchor' => 'negotiation',
        'Negotiation Pushback' => 'negotiation',

        // Managing Up → managing-up (future mode)
        'Managing Up' => 'managing-up',
        'Status Update' => 'managing-up',
        'Escalation' => 'managing-up',
    ],

    /*
    |--------------------------------------------------------------------------
    | Drill Phase to Drill Type Mapping
    |--------------------------------------------------------------------------
    |
    | Maps the drill_phase value from AI responses to a canonical drill_type
    | used for scoring. Null values indicate phases that should not be scored
    | (like session completion reflections).
    |
    */

    'phase_mapping' => [
        // MBA+ Executive Training
        'Compression' => 'compression',
        'Executive Communication' => 'executive_communication',
        'Problem-Solving' => 'problem_solving',
        'Writing Precision' => 'writing_precision',

        // Interview Prep
        'Story Compression' => 'story_compression',
        'The Opener' => 'opener',
        'Curveball Recovery' => 'curveball_recovery',
        'Closing Strong' => 'closing_questions',

        // Thinking on Your Feet
        'Unexpected Question' => 'unexpected_question',
        'Impromptu Structure' => 'impromptu_structure',
        'Defending Your Position' => 'defending_position',
        'Graceful I Don\'t Know' => 'graceful_unknown',

        // Difficult Conversations
        'The Direct Open' => 'feedback_delivery',
        'Holding the Line' => 'holding_the_line',
        'The Clean No' => 'clean_no',
        'Bad News Delivery' => 'bad_news_delivery',

        // Negotiation
        'Negotiation Anchor' => 'negotiation_anchor',
        'Negotiation Pushback' => 'negotiation_pushback',

        // Managing Up
        'Managing Up' => 'managing_up',
        'Status Update' => 'status_update',
        'Escalation' => 'escalation',

        // Non-scored phases
        'Session Complete' => null,
        'Reflection' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Universal Scoring Criteria
    |--------------------------------------------------------------------------
    |
    | These criteria are evaluated for ALL drill types.
    |
    */

    'universal_criteria' => [
        'hedging' => [
            'type' => 'boolean',
            'description' => 'Uses weak language: "I think", "maybe", "probably", "perhaps", "might", "could be", "sort of", "kind of", "I believe", "it seems"',
        ],
        'filler_phrases' => [
            'type' => 'integer',
            'description' => 'Count of filler phrases: "you know", "like", "basically", "actually", "just", "really", "very", "obviously", "honestly", "literally"',
        ],
        'word_limit_met' => [
            'type' => 'boolean',
            'description' => 'Response stayed within the requested word/sentence limit',
        ],
        'apology_detected' => [
            'type' => 'boolean',
            'description' => 'Unnecessary apologizing: "sorry", "I apologize", "forgive me", self-deprecation',
        ],
        'ran_long' => [
            'type' => 'boolean',
            'description' => 'Response significantly exceeded expected length',
        ],
        'too_short' => [
            'type' => 'boolean',
            'description' => 'Response was too brief to be substantive',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Drill-Specific Scoring Criteria
    |--------------------------------------------------------------------------
    |
    | Each drill type has its own set of evaluation criteria.
    |
    */

    'drill_criteria' => [
        'compression' => [
            'core_point_captured' => ['type' => 'boolean', 'description' => 'Identified and stated the actual core message'],
            'concise' => ['type' => 'boolean', 'description' => 'No extra fluff or restating of original jargon'],
            'under_word_limit' => ['type' => 'boolean', 'description' => 'Met the specific word limit (usually 15 words)'],
            'clarity' => ['type' => 'boolean', 'description' => 'A stranger could understand this without context'],
            'jargon_removed' => ['type' => 'boolean', 'description' => 'Eliminated buzzwords and corporate speak'],
        ],

        'executive_communication' => [
            'declarative_sentences' => ['type' => 'boolean', 'description' => 'Used declarative statements, not questions or hedged phrasing'],
            'authority_tone' => ['type' => 'boolean', 'description' => 'Sounded decisive and confident, not tentative'],
            'clear_position' => ['type' => 'boolean', 'description' => 'Took a clear stance, not vague or noncommittal'],
            'appropriate_length' => ['type' => 'boolean', 'description' => '3-5 sentences as requested, not rambling'],
            'defensive_language' => ['type' => 'boolean', 'description' => 'Used defensive or justifying language'],
            'blame_shifting' => ['type' => 'boolean', 'description' => 'Blamed others or external factors'],
            'solution_oriented' => ['type' => 'boolean', 'description' => 'Focused on path forward, not just the problem'],
        ],

        'problem_solving' => [
            'decision_clear' => ['type' => 'boolean', 'description' => 'Decision was stated clearly and actionably'],
            'rationale_supports_decision' => ['type' => 'boolean', 'description' => 'The reasoning actually supports the stated decision'],
            'risk_realistic' => ['type' => 'boolean', 'description' => 'Risk identified is specific and plausible, not hand-wavy'],
            'mitigation_specific' => ['type' => 'boolean', 'description' => 'Mitigation is concrete, not vague'],
            'structure_followed' => ['type' => 'boolean', 'description' => 'Used the Decision/Rationale/Risk/Mitigation structure'],
            'tradeoff_acknowledged' => ['type' => 'boolean', 'description' => 'Explicitly named what they\'re trading off'],
            'avoided_analysis_paralysis' => ['type' => 'boolean', 'description' => 'Made a call instead of asking for more information'],
            'considered_stakeholders' => ['type' => 'boolean', 'description' => 'Thought about impact on relevant parties'],
        ],

        'writing_precision' => [
            'target_dimension_improved' => ['type' => 'boolean', 'description' => 'Actually improved the requested dimension (clarity/brevity/impact)'],
            'tighter_than_original' => ['type' => 'boolean', 'description' => 'Result is more concise than the source material'],
            'meaning_preserved' => ['type' => 'boolean', 'description' => 'Core meaning wasn\'t lost in the edit'],
            'stronger_verbs' => ['type' => 'boolean', 'description' => 'Used active, strong verbs instead of passive/weak ones'],
            'passive_voice_reduced' => ['type' => 'boolean', 'description' => 'Reduced passive constructions'],
            'redundancy_eliminated' => ['type' => 'boolean', 'description' => 'Removed redundant words and phrases'],
        ],

        'story_compression' => [
            'star_structure' => ['type' => 'boolean', 'description' => 'Used Situation/Task/Action/Result structure'],
            'situation_concise' => ['type' => 'boolean', 'description' => 'Situation was 1-2 sentences, not a novel'],
            'action_specific' => ['type' => 'boolean', 'description' => 'Actions were specific to what THEY did, not the team'],
            'result_measurable' => ['type' => 'boolean', 'description' => 'Result included metrics or concrete outcome'],
            'under_60_seconds' => ['type' => 'boolean', 'description' => 'Response would fit in ~60 seconds spoken'],
            'i_not_we' => ['type' => 'boolean', 'description' => 'Used "I" to describe their contribution, not hiding behind "we"'],
            'relevant_to_role' => ['type' => 'boolean', 'description' => 'Story demonstrates skills relevant to professional context'],
        ],

        'opener' => [
            'present_past_future' => ['type' => 'boolean', 'description' => 'Used Present → Past → Future arc'],
            'no_life_story' => ['type' => 'boolean', 'description' => 'Avoided childhood, irrelevant history, rambling'],
            'role_relevant' => ['type' => 'boolean', 'description' => 'Content connects to the type of role they\'d interview for'],
            'strong_ending' => ['type' => 'boolean', 'description' => 'Ended with forward momentum, not trailing off'],
            'appropriate_length' => ['type' => 'boolean', 'description' => '30-45 seconds spoken, not too short or long'],
            'professional_focus' => ['type' => 'boolean', 'description' => 'Kept focus on professional identity, not personal'],
            'hook_included' => ['type' => 'boolean', 'description' => 'Included something memorable or distinctive'],
        ],

        'curveball_recovery' => [
            'direct_acknowledgment' => ['type' => 'boolean', 'description' => 'Addressed the hard part directly, no dodging'],
            'authentic' => ['type' => 'boolean', 'description' => 'Sounded human and honest, not scripted'],
            'pivot_smooth' => ['type' => 'boolean', 'description' => 'Transition to strength felt natural, not forced'],
            'humble_brag_avoided' => ['type' => 'boolean', 'description' => 'Didn\'t disguise a strength as a weakness'],
            'ownership' => ['type' => 'boolean', 'description' => 'Took responsibility instead of blaming circumstances'],
            'growth_demonstrated' => ['type' => 'boolean', 'description' => 'Showed what they learned or how they improved'],
            'brevity' => ['type' => 'boolean', 'description' => 'Didn\'t over-explain or dwell on the negative'],
        ],

        'closing_questions' => [
            'insightful' => ['type' => 'boolean', 'description' => 'Questions show thought about the role/company'],
            'not_googleable' => ['type' => 'boolean', 'description' => 'Couldn\'t find the answer on the company website'],
            'no_salary_benefits' => ['type' => 'boolean', 'description' => 'Avoided premature salary/benefits questions'],
            'thought_provoking' => ['type' => 'boolean', 'description' => 'At least one question makes the interviewer think'],
            'genuine_interest' => ['type' => 'boolean', 'description' => 'Questions signal real curiosity, not box-checking'],
            'forward_looking' => ['type' => 'boolean', 'description' => 'Questions about future, growth, challenges'],
            'appropriate_number' => ['type' => 'boolean', 'description' => 'Asked 2-3 questions, not too few or too many'],
        ],

        'unexpected_question' => [
            'started_strong' => ['type' => 'boolean', 'description' => 'First sentence was substantive, no stalling'],
            'clear_position' => ['type' => 'boolean', 'description' => 'Stated a clear take, not waffling'],
            'reason_supported' => ['type' => 'boolean', 'description' => 'Gave a reason that actually supports the position'],
            'no_stalling' => ['type' => 'boolean', 'description' => 'Avoided "that\'s a great question" or similar filler'],
            'acknowledge_position_reason' => ['type' => 'boolean', 'description' => 'Used the Acknowledge/Position/Reason structure'],
            'composure' => ['type' => 'boolean', 'description' => 'Response felt calm, not panicked or rushed'],
        ],

        'impromptu_structure' => [
            'prep_structure' => ['type' => 'boolean', 'description' => 'Used Point/Reason/Example/Point structure'],
            'point_clear' => ['type' => 'boolean', 'description' => 'Opening point was clear and direct'],
            'example_specific' => ['type' => 'boolean', 'description' => 'Example was concrete, not generic'],
            'point_restated' => ['type' => 'boolean', 'description' => 'Closed by restating the main point'],
            'logical_flow' => ['type' => 'boolean', 'description' => 'Ideas connected logically'],
            'appropriate_length' => ['type' => 'boolean', 'description' => 'Response was ~60 seconds spoken'],
            'example_relevant' => ['type' => 'boolean', 'description' => 'Example actually supported the point'],
        ],

        'defending_position' => [
            'acknowledged_concern' => ['type' => 'boolean', 'description' => 'Recognized the other person\'s point'],
            'held_position' => ['type' => 'boolean', 'description' => 'Maintained their stance, didn\'t fold'],
            'non_defensive' => ['type' => 'boolean', 'description' => 'Avoided defensive or combative language'],
            'offered_concession' => ['type' => 'boolean', 'description' => 'Made appropriate concession or clarification'],
            'calm_tone' => ['type' => 'boolean', 'description' => 'Response felt measured, not reactive'],
            'didn\'t_over_explain' => ['type' => 'boolean', 'description' => 'Made the point without excessive justification'],
            'bridge_used' => ['type' => 'boolean', 'description' => 'Connected acknowledgment back to their position smoothly'],
        ],

        'graceful_unknown' => [
            'direct_admission' => ['type' => 'boolean', 'description' => 'Clearly stated they don\'t know'],
            'no_waffling' => ['type' => 'boolean', 'description' => 'Didn\'t pretend or guess'],
            'offered_alternative' => ['type' => 'boolean', 'description' => 'Shared what they do know or how they\'d find out'],
            'confident_delivery' => ['type' => 'boolean', 'description' => 'Sounded confident despite the gap'],
            'forward_action' => ['type' => 'boolean', 'description' => 'Ended with a concrete next step'],
            'no_excessive_apology' => ['type' => 'boolean', 'description' => 'Didn\'t over-apologize for the gap'],
            'credibility_maintained' => ['type' => 'boolean', 'description' => 'Still came across as competent'],
        ],

        'feedback_delivery' => [
            'direct_opening' => ['type' => 'boolean', 'description' => 'First sentence stated the issue, no burying'],
            'specific_behavior' => ['type' => 'boolean', 'description' => 'Referenced specific behavior, not character'],
            'impact_stated' => ['type' => 'boolean', 'description' => 'Explained the impact of the behavior'],
            'no_sandwich' => ['type' => 'boolean', 'description' => 'Avoided false praise wrapping'],
            'forward_focused' => ['type' => 'boolean', 'description' => 'Discussed what needs to change'],
            'respect_maintained' => ['type' => 'boolean', 'description' => 'Firm but not demeaning'],
            'brevity' => ['type' => 'boolean', 'description' => 'Made the point without over-explaining'],
            'actionable' => ['type' => 'boolean', 'description' => 'Clear on what change looks like'],
        ],

        'holding_the_line' => [
            'stayed_calm' => ['type' => 'boolean', 'description' => 'Didn\'t escalate or match emotional intensity'],
            'position_maintained' => ['type' => 'boolean', 'description' => 'Held the original position'],
            'empathy_shown' => ['type' => 'boolean', 'description' => 'Acknowledged the other person\'s feelings'],
            'didn\'t_cave' => ['type' => 'boolean', 'description' => 'Didn\'t give in to avoid discomfort'],
            'broken_record' => ['type' => 'boolean', 'description' => 'Restated position without new justifications'],
            'no_new_arguments' => ['type' => 'boolean', 'description' => 'Didn\'t get drawn into debate'],
            'boundary_clear' => ['type' => 'boolean', 'description' => 'The line being held was clear'],
        ],

        'clean_no' => [
            'no_stated_clearly' => ['type' => 'boolean', 'description' => 'The "no" was unambiguous'],
            'no_excessive_apology' => ['type' => 'boolean', 'description' => 'Didn\'t over-apologize'],
            'reason_brief' => ['type' => 'boolean', 'description' => 'Gave reason in one sentence max, or none'],
            'no_false_hope' => ['type' => 'boolean', 'description' => 'Didn\'t leave door open they don\'t intend to use'],
            'alternative_offered' => ['type' => 'boolean', 'description' => 'Offered alternative if appropriate'],
            'relationship_preserved' => ['type' => 'boolean', 'description' => 'Maintained respect and warmth'],
            'brevity' => ['type' => 'boolean', 'description' => 'Short response, not defensive explanation'],
        ],

        'bad_news_delivery' => [
            'lead_with_news' => ['type' => 'boolean', 'description' => 'Bad news stated in first sentence'],
            'no_buried_lead' => ['type' => 'boolean', 'description' => 'Didn\'t hide the news in the middle'],
            'owned_it' => ['type' => 'boolean', 'description' => 'Took appropriate responsibility'],
            'next_steps_clear' => ['type' => 'boolean', 'description' => 'Explained what happens now'],
            'empathy_appropriate' => ['type' => 'boolean', 'description' => 'Acknowledged impact without wallowing'],
            'no_excessive_softening' => ['type' => 'boolean', 'description' => 'Didn\'t dilute the message'],
            'composure' => ['type' => 'boolean', 'description' => 'Delivered with calm, not anxiety'],
            'solution_oriented' => ['type' => 'boolean', 'description' => 'Focused on path forward'],
        ],

        'negotiation_anchor' => [
            'anchor_stated' => ['type' => 'boolean', 'description' => 'Opened with a specific number or position'],
            'confident_delivery' => ['type' => 'boolean', 'description' => 'Stated anchor without hedging'],
            'justified_briefly' => ['type' => 'boolean', 'description' => 'Gave brief rationale for the anchor'],
            'no_immediate_concession' => ['type' => 'boolean', 'description' => 'Didn\'t undercut their own anchor'],
            'silence_comfort' => ['type' => 'boolean', 'description' => 'Let the anchor land without filling silence'],
            'ambitious_but_reasonable' => ['type' => 'boolean', 'description' => 'Anchor was strong but not absurd'],
        ],

        'negotiation_pushback' => [
            'didn\'t_fold' => ['type' => 'boolean', 'description' => 'Didn\'t immediately concede'],
            'asked_questions' => ['type' => 'boolean', 'description' => 'Probed to understand the objection'],
            'reframed_value' => ['type' => 'boolean', 'description' => 'Restated the value being offered'],
            'silence_used' => ['type' => 'boolean', 'description' => 'Used silence instead of rushing to fill'],
            'small_concession' => ['type' => 'boolean', 'description' => 'If conceding, gave small movement only'],
            'something_for_something' => ['type' => 'boolean', 'description' => 'Any concession tied to getting something back'],
            'maintained_composure' => ['type' => 'boolean', 'description' => 'Stayed calm under pressure'],
        ],

        'managing_up' => [
            'bottom_line_first' => ['type' => 'boolean', 'description' => 'Led with the key point, not background'],
            'options_presented' => ['type' => 'boolean', 'description' => 'Gave choices, not just problems'],
            'recommendation_clear' => ['type' => 'boolean', 'description' => 'Stated what they recommend'],
            'brevity' => ['type' => 'boolean', 'description' => 'Respected the senior person\'s time'],
            'no_excessive_detail' => ['type' => 'boolean', 'description' => 'Didn\'t bury them in minutiae'],
            'proactive' => ['type' => 'boolean', 'description' => 'Anticipated questions or concerns'],
            'accountability_shown' => ['type' => 'boolean', 'description' => 'Took ownership of their area'],
        ],

        'status_update' => [
            'headline_first' => ['type' => 'boolean', 'description' => 'Led with the most important thing'],
            'on_track_or_not' => ['type' => 'boolean', 'description' => 'Clearly stated if on track'],
            'blockers_named' => ['type' => 'boolean', 'description' => 'Identified blockers if any'],
            'ask_clear' => ['type' => 'boolean', 'description' => 'If asking for something, it was specific'],
            'no_rambling' => ['type' => 'boolean', 'description' => 'Tight, scannable update'],
            'metrics_included' => ['type' => 'boolean', 'description' => 'Included relevant numbers if applicable'],
            'forward_looking' => ['type' => 'boolean', 'description' => 'Mentioned next milestone or step'],
        ],

        'escalation' => [
            'issue_stated_clearly' => ['type' => 'boolean', 'description' => 'The problem was clear in first sentence'],
            'no_blame' => ['type' => 'boolean', 'description' => 'Focused on situation, not finger-pointing'],
            'impact_quantified' => ['type' => 'boolean', 'description' => 'Stated the impact or risk'],
            'options_presented' => ['type' => 'boolean', 'description' => 'Gave possible paths forward'],
            'recommendation_included' => ['type' => 'boolean', 'description' => 'Stated what they\'d recommend'],
            'urgency_appropriate' => ['type' => 'boolean', 'description' => 'Conveyed urgency without panic'],
            'ownership_taken' => ['type' => 'boolean', 'description' => 'Took responsibility for their part'],
        ],
    ],
];
