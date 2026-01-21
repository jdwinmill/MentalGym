# Phase 1: Response Scoring Infrastructure

## Overview

Build the foundation for Blind Spots by scoring every drill response and storing the results. This runs silently in the background. Users don't see scores directly — they power the pattern detection in Phase 2.

## Context

SharpStack is a mental fitness training platform using Laravel 12, React, Inertia.js, and Claude API. Users complete Practice Mode sessions with structured drills. Each drill has a prompt card where users submit text responses. We need to score these responses to identify patterns over time.

## Goals

1. Create database schema to store drill scores
2. Build scoring service that evaluates responses via Claude API
3. Dispatch async job after each user submission
4. Store scores without affecting session latency

## Database Schema

Create migration for `drill_scores` table:

```php
Schema::create('drill_scores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('practice_session_id')->constrained()->onDelete('cascade');
    $table->foreignId('practice_mode_id')->constrained()->onDelete('cascade');
    $table->string('drill_type', 50);
    $table->string('drill_phase', 100);
    $table->boolean('is_iteration')->default(false);
    $table->json('scores');
    $table->text('user_response');
    $table->unsignedSmallInteger('word_count');
    $table->unsignedSmallInteger('response_time_seconds')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'created_at']);
    $table->index(['user_id', 'drill_type']);
    $table->index(['user_id', 'practice_mode_id']);
});
```

Create the DrillScore model with appropriate relationships and casts.

## Drill Types and Scoring Criteria

Each drill type has universal criteria plus type-specific criteria. The scoring prompt should evaluate all applicable criteria and return structured JSON.

---

### Universal Criteria (apply to ALL drill types)

| Criteria | Type | Description |
|----------|------|-------------|
| hedging | boolean | Uses weak language: "I think", "maybe", "probably", "perhaps", "might", "could be", "sort of", "kind of", "I believe", "it seems" |
| filler_phrases | integer | Count of filler phrases: "you know", "like", "basically", "actually", "just", "really", "very", "obviously", "honestly", "literally" |
| word_limit_met | boolean | Response stayed within the requested word/sentence limit |
| apology_detected | boolean | Unnecessary apologizing: "sorry", "I apologize", "forgive me", self-deprecation |
| ran_long | boolean | Response significantly exceeded expected length |
| too_short | boolean | Response was too brief to be substantive |

---

### Drill Type: compression

**Used in:** MBA+ Executive Training, Interview Prep

**Purpose:** Distill verbose content to its core message.

| Criteria | Type | Description |
|----------|------|-------------|
| core_point_captured | boolean | Identified and stated the actual core message |
| concise | boolean | No extra fluff or restating of original jargon |
| under_word_limit | boolean | Met the specific word limit (usually 15 words) |
| clarity | boolean | A stranger could understand this without context |
| jargon_removed | boolean | Eliminated buzzwords and corporate speak |

---

### Drill Type: executive_communication

**Used in:** MBA+ Executive Training

**Purpose:** Communicate with authority to senior leadership.

| Criteria | Type | Description |
|----------|------|-------------|
| declarative_sentences | boolean | Used declarative statements, not questions or hedged phrasing |
| authority_tone | boolean | Sounded decisive and confident, not tentative |
| clear_position | boolean | Took a clear stance, not vague or noncommittal |
| appropriate_length | boolean | 3-5 sentences as requested, not rambling |
| defensive_language | boolean | Used defensive or justifying language |
| blame_shifting | boolean | Blamed others or external factors |
| solution_oriented | boolean | Focused on path forward, not just the problem |

---

### Drill Type: problem_solving

**Used in:** MBA+ Executive Training

**Purpose:** Make decisions with incomplete information.

| Criteria | Type | Description |
|----------|------|-------------|
| decision_clear | boolean | Decision was stated clearly and actionably |
| rationale_supports_decision | boolean | The reasoning actually supports the stated decision |
| risk_realistic | boolean | Risk identified is specific and plausible, not hand-wavy |
| mitigation_specific | boolean | Mitigation is concrete, not vague |
| structure_followed | boolean | Used the Decision/Rationale/Risk/Mitigation structure |
| tradeoff_acknowledged | boolean | Explicitly named what they're trading off |
| avoided_analysis_paralysis | boolean | Made a call instead of asking for more information |
| considered_stakeholders | boolean | Thought about impact on relevant parties |

---

### Drill Type: writing_precision

**Used in:** MBA+ Executive Training

**Purpose:** Tighten and strengthen written communication.

| Criteria | Type | Description |
|----------|------|-------------|
| target_dimension_improved | boolean | Actually improved the requested dimension (clarity/brevity/impact) |
| tighter_than_original | boolean | Result is more concise than the source material |
| meaning_preserved | boolean | Core meaning wasn't lost in the edit |
| stronger_verbs | boolean | Used active, strong verbs instead of passive/weak ones |
| passive_voice_reduced | boolean | Reduced passive constructions |
| redundancy_eliminated | boolean | Removed redundant words and phrases |

---

### Drill Type: story_compression

**Used in:** Interview Prep

**Purpose:** Tell compelling stories in structured, concise format.

| Criteria | Type | Description |
|----------|------|-------------|
| star_structure | boolean | Used Situation/Task/Action/Result structure |
| situation_concise | boolean | Situation was 1-2 sentences, not a novel |
| action_specific | boolean | Actions were specific to what THEY did, not the team |
| result_measurable | boolean | Result included metrics or concrete outcome |
| under_60_seconds | boolean | Response would fit in ~60 seconds spoken |
| i_not_we | boolean | Used "I" to describe their contribution, not hiding behind "we" |
| relevant_to_role | boolean | Story demonstrates skills relevant to professional context |

---

### Drill Type: opener

**Used in:** Interview Prep

**Purpose:** Nail "tell me about yourself" with structure and confidence.

| Criteria | Type | Description |
|----------|------|-------------|
| present_past_future | boolean | Used Present → Past → Future arc |
| no_life_story | boolean | Avoided childhood, irrelevant history, rambling |
| role_relevant | boolean | Content connects to the type of role they'd interview for |
| strong_ending | boolean | Ended with forward momentum, not trailing off |
| appropriate_length | boolean | 30-45 seconds spoken, not too short or long |
| professional_focus | boolean | Kept focus on professional identity, not personal |
| hook_included | boolean | Included something memorable or distinctive |

---

### Drill Type: curveball_recovery

**Used in:** Interview Prep

**Purpose:** Handle difficult questions (weakness, failure, gaps) with grace.

| Criteria | Type | Description |
|----------|------|-------------|
| direct_acknowledgment | boolean | Addressed the hard part directly, no dodging |
| authentic | boolean | Sounded human and honest, not scripted |
| pivot_smooth | boolean | Transition to strength felt natural, not forced |
| humble_brag_avoided | boolean | Didn't disguise a strength as a weakness |
| ownership | boolean | Took responsibility instead of blaming circumstances |
| growth_demonstrated | boolean | Showed what they learned or how they improved |
| brevity | boolean | Didn't over-explain or dwell on the negative |

---

### Drill Type: closing_questions

**Used in:** Interview Prep

**Purpose:** End interviews with smart, engaging questions.

| Criteria | Type | Description |
|----------|------|-------------|
| insightful | boolean | Questions show thought about the role/company |
| not_googleable | boolean | Couldn't find the answer on the company website |
| no_salary_benefits | boolean | Avoided premature salary/benefits questions |
| thought_provoking | boolean | At least one question makes the interviewer think |
| genuine_interest | boolean | Questions signal real curiosity, not box-checking |
| forward_looking | boolean | Questions about future, growth, challenges |
| appropriate_number | boolean | Asked 2-3 questions, not too few or too many |

---

### Drill Type: unexpected_question

**Used in:** Thinking on Your Feet

**Purpose:** Respond coherently when caught off guard.

| Criteria | Type | Description |
|----------|------|-------------|
| started_strong | boolean | First sentence was substantive, no stalling |
| clear_position | boolean | Stated a clear take, not waffling |
| reason_supported | boolean | Gave a reason that actually supports the position |
| no_stalling | boolean | Avoided "that's a great question" or similar filler |
| acknowledge_position_reason | boolean | Used the Acknowledge/Position/Reason structure |
| composure | boolean | Response felt calm, not panicked or rushed |

---

### Drill Type: impromptu_structure

**Used in:** Thinking on Your Feet

**Purpose:** Organize thoughts quickly using frameworks.

| Criteria | Type | Description |
|----------|------|-------------|
| prep_structure | boolean | Used Point/Reason/Example/Point structure |
| point_clear | boolean | Opening point was clear and direct |
| example_specific | boolean | Example was concrete, not generic |
| point_restated | boolean | Closed by restating the main point |
| logical_flow | boolean | Ideas connected logically |
| appropriate_length | boolean | Response was ~60 seconds spoken |
| example_relevant | boolean | Example actually supported the point |

---

### Drill Type: defending_position

**Used in:** Thinking on Your Feet

**Purpose:** Hold ground when challenged without being defensive.

| Criteria | Type | Description |
|----------|------|-------------|
| acknowledged_concern | boolean | Recognized the other person's point |
| held_position | boolean | Maintained their stance, didn't fold |
| non_defensive | boolean | Avoided defensive or combative language |
| offered_concession | boolean | Made appropriate concession or clarification |
| calm_tone | boolean | Response felt measured, not reactive |
| didn't_over_explain | boolean | Made the point without excessive justification |
| bridge_used | boolean | Connected acknowledgment back to their position smoothly |

---

### Drill Type: graceful_unknown

**Used in:** Thinking on Your Feet

**Purpose:** Admit not knowing something while maintaining credibility.

| Criteria | Type | Description |
|----------|------|-------------|
| direct_admission | boolean | Clearly stated they don't know |
| no_waffling | boolean | Didn't pretend or guess |
| offered_alternative | boolean | Shared what they do know or how they'd find out |
| confident_delivery | boolean | Sounded confident despite the gap |
| forward_action | boolean | Ended with a concrete next step |
| no_excessive_apology | boolean | Didn't over-apologize for the gap |
| credibility_maintained | boolean | Still came across as competent |

---

### Drill Type: feedback_delivery

**Used in:** Difficult Conversations

**Purpose:** Give critical feedback directly and constructively.

| Criteria | Type | Description |
|----------|------|-------------|
| direct_opening | boolean | First sentence stated the issue, no burying |
| specific_behavior | boolean | Referenced specific behavior, not character |
| impact_stated | boolean | Explained the impact of the behavior |
| no_sandwich | boolean | Avoided false praise wrapping |
| forward_focused | boolean | Discussed what needs to change |
| respect_maintained | boolean | Firm but not demeaning |
| brevity | boolean | Made the point without over-explaining |
| actionable | boolean | Clear on what change looks like |

---

### Drill Type: holding_the_line

**Used in:** Difficult Conversations

**Purpose:** Maintain position when met with pushback or emotion.

| Criteria | Type | Description |
|----------|------|-------------|
| stayed_calm | boolean | Didn't escalate or match emotional intensity |
| position_maintained | boolean | Held the original position |
| empathy_shown | boolean | Acknowledged the other person's feelings |
| didn't_cave | boolean | Didn't give in to avoid discomfort |
| broken_record | boolean | Restated position without new justifications |
| no_new_arguments | boolean | Didn't get drawn into debate |
| boundary_clear | boolean | The line being held was clear |

---

### Drill Type: clean_no

**Used in:** Difficult Conversations

**Purpose:** Decline requests without over-explaining or leaving false hope.

| Criteria | Type | Description |
|----------|------|-------------|
| no_stated_clearly | boolean | The "no" was unambiguous |
| no_excessive_apology | boolean | Didn't over-apologize |
| reason_brief | boolean | Gave reason in one sentence max, or none |
| no_false_hope | boolean | Didn't leave door open they don't intend to use |
| alternative_offered | boolean | Offered alternative if appropriate |
| relationship_preserved | boolean | Maintained respect and warmth |
| brevity | boolean | Short response, not defensive explanation |

---

### Drill Type: bad_news_delivery

**Used in:** Difficult Conversations

**Purpose:** Deliver bad news directly without softening it to meaninglessness.

| Criteria | Type | Description |
|----------|------|-------------|
| lead_with_news | boolean | Bad news stated in first sentence |
| no_buried_lead | boolean | Didn't hide the news in the middle |
| owned_it | boolean | Took appropriate responsibility |
| next_steps_clear | boolean | Explained what happens now |
| empathy_appropriate | boolean | Acknowledged impact without wallowing |
| no_excessive_softening | boolean | Didn't dilute the message |
| composure | boolean | Delivered with calm, not anxiety |
| solution_oriented | boolean | Focused on path forward |

---

### Drill Type: negotiation_anchor

**Used in:** Negotiation

**Purpose:** Set strong opening positions in negotiations.

| Criteria | Type | Description |
|----------|------|-------------|
| anchor_stated | boolean | Opened with a specific number or position |
| confident_delivery | boolean | Stated anchor without hedging |
| justified_briefly | boolean | Gave brief rationale for the anchor |
| no_immediate_concession | boolean | Didn't undercut their own anchor |
| silence_comfort | boolean | Let the anchor land without filling silence |
| ambitious_but_reasonable | boolean | Anchor was strong but not absurd |

---

### Drill Type: negotiation_pushback

**Used in:** Negotiation

**Purpose:** Handle "that's too high" or "we can't do that" without folding.

| Criteria | Type | Description |
|----------|------|-------------|
| didn't_fold | boolean | Didn't immediately concede |
| asked_questions | boolean | Probed to understand the objection |
| reframed_value | boolean | Restated the value being offered |
| silence_used | boolean | Used silence instead of rushing to fill |
| small_concession | boolean | If conceding, gave small movement only |
| something_for_something | boolean | Any concession tied to getting something back |
| maintained_composure | boolean | Stayed calm under pressure |

---

### Drill Type: managing_up

**Used in:** Managing Up

**Purpose:** Communicate effectively with superiors.

| Criteria | Type | Description |
|----------|------|-------------|
| bottom_line_first | boolean | Led with the key point, not background |
| options_presented | boolean | Gave choices, not just problems |
| recommendation_clear | boolean | Stated what they recommend |
| brevity | boolean | Respected the senior person's time |
| no_excessive_detail | boolean | Didn't bury them in minutiae |
| proactive | boolean | Anticipated questions or concerns |
| accountability_shown | boolean | Took ownership of their area |

---

### Drill Type: status_update

**Used in:** Managing Up

**Purpose:** Give updates that land with leadership.

| Criteria | Type | Description |
|----------|------|-------------|
| headline_first | boolean | Led with the most important thing |
| on_track_or_not | boolean | Clearly stated if on track |
| blockers_named | boolean | Identified blockers if any |
| ask_clear | boolean | If asking for something, it was specific |
| no_rambling | boolean | Tight, scannable update |
| metrics_included | boolean | Included relevant numbers if applicable |
| forward_looking | boolean | Mentioned next milestone or step |

---

### Drill Type: escalation

**Used in:** Managing Up

**Purpose:** Escalate issues without blame or panic.

| Criteria | Type | Description |
|----------|------|-------------|
| issue_stated_clearly | boolean | The problem was clear in first sentence |
| no_blame | boolean | Focused on situation, not finger-pointing |
| impact_quantified | boolean | Stated the impact or risk |
| options_presented | boolean | Gave possible paths forward |
| recommendation_included | boolean | Stated what they'd recommend |
| urgency_appropriate | boolean | Conveyed urgency without panic |
| ownership_taken | boolean | Took responsibility for their part |

---

## Scoring Service

Create `App\Services\DrillScoringService` with:

```php
class DrillScoringService
{
    public function scoreResponse(
        User $user,
        PracticeSession $session,
        string $drillType,
        string $drillPhase,
        string $userResponse,
        bool $isIteration = false
    ): DrillScore;

    public function getUniversalCriteria(): array;

    public function getCriteriaForDrillType(string $drillType): array;

    private function buildScoringPrompt(string $drillType, string $userResponse): string;

    private function parseScores(string $aiResponse): array;
}
```

## Scoring Prompt Template

The AI scoring call should use this structure:

```
You are evaluating a training response. Score it against the criteria below.

DRILL TYPE: {drill_type}
USER RESPONSE: {user_response}
WORD COUNT: {word_count}

UNIVERSAL CRITERIA (evaluate all):
- hedging: Uses weak language like "I think", "maybe", "probably", "perhaps", "might"
- filler_phrases: Count of fillers like "you know", "like", "basically", "actually", "just"
- word_limit_met: Response stayed within expected length
- apology_detected: Unnecessary apologizing or self-deprecation
- ran_long: Significantly exceeded expected length
- too_short: Too brief to be substantive

DRILL-SPECIFIC CRITERIA:
{criteria_list}

Respond with JSON only. Boolean values as true/false, counts as integers.

{
  "hedging": boolean,
  "filler_phrases": integer,
  "word_limit_met": boolean,
  "apology_detected": boolean,
  "ran_long": boolean,
  "too_short": boolean,
  {drill_specific_fields}
}
```

## Async Job

Create `App\Jobs\ScoreDrillResponse`:

```php
class ScoreDrillResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $practiceSessionId,
        public int $practiceModeId,
        public string $drillType,
        public string $drillPhase,
        public string $userResponse,
        public bool $isIteration = false
    ) {}

    public function handle(DrillScoringService $service): void
    {
        // Call scoring service
        // Store result in drill_scores table
    }
}
```

## Integration Point

In the existing controller/service that handles user drill submissions, dispatch the job after saving the response:

```php
// After saving user response to session
ScoreDrillResponse::dispatch(
    $user->id,
    $session->id,
    $practiceMode->id,
    $drillType,      // extracted from card's drill_phase or mapped
    $drillPhase,     // from the card
    $userResponse,
    $isIteration     // from the card
);
```

## Drill Type Mapping

Create a config file or constant that maps drill_phase values to drill_type:

```php
// config/drill_types.php
return [
    'Compression' => 'compression',
    'Executive Communication' => 'executive_communication',
    'Problem-Solving' => 'problem_solving',
    'Writing Precision' => 'writing_precision',
    'Story Compression' => 'story_compression',
    'The Opener' => 'opener',
    'Curveball Recovery' => 'curveball_recovery',
    'Closing Strong' => 'closing_questions',
    'Unexpected Question' => 'unexpected_question',
    'Impromptu Structure' => 'impromptu_structure',
    'Defending Your Position' => 'defending_position',
    'Graceful I Don\'t Know' => 'graceful_unknown',
    'The Direct Open' => 'feedback_delivery',
    'Holding the Line' => 'holding_the_line',
    'The Clean No' => 'clean_no',
    'Bad News Delivery' => 'bad_news_delivery',
    'Session Complete' => null, // Don't score reflections
];
```

## File Structure

```
app/
├── Jobs/
│   └── ScoreDrillResponse.php
├── Models/
│   └── DrillScore.php
├── Services/
│   └── DrillScoringService.php
config/
└── drill_types.php
database/
└── migrations/
    └── xxxx_xx_xx_create_drill_scores_table.php
```

## Testing

Create test that:
1. Submits a known response with hedging
2. Verifies job is dispatched
3. Verifies score is stored with hedging: true
4. Verifies universal + drill-specific criteria are present

## Success Criteria

- [ ] Migration runs successfully
- [ ] DrillScore model created with relationships
- [ ] DrillScoringService scores responses via Claude API
- [ ] Job dispatches after user submission
- [ ] Scores stored in database
- [ ] No impact on session latency (async)
- [ ] All drill types have defined criteria