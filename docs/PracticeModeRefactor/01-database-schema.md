# Phase 1: Database Schema

## Tasks

1. Create `drills` migration
2. Create `session_completed_events` migration
3. Add new columns to `training_sessions`
4. Create `Drill` and `SessionCompletedEvent` models
5. Seed initial drills from existing practice modes

---

## Global Instruction Set

Stored in config. Single source of truth for all AI interactions.

```php
// config/mentalgym.php
return [
    'main_instruction_set' => <<<'PROMPT'
        Always respond in valid JSON format.
        Be direct. No fluff or filler phrases.
        Feedback should be specific, actionable, and constructive.
        Reference the user's actual words when giving feedback.
        Never be condescending. Assume competence.
    PROMPT,
];
```

---

## PracticeMode (Existing)

```php
// Existing fields
id, name, slug, description, icon, color, is_active

// Clarified field
instruction_set    // Mode-specific: evaluation criteria, what "good" looks like
```

**Example mode instruction_set:**
```
Executive Communication:
- Evaluate for clarity, brevity, and audience calibration
- Flag jargon, passive voice, and buried leads
- Good responses get to the point in the first sentence
- Penalize hedging language ("I think maybe we could...")
- Reward confident, direct phrasing
```

---

## Drill (New Model)

### Migration

```php
Schema::create('drills', function (Blueprint $table) {
    $table->id();
    $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
    $table->string('name');                           // "Bad News Delivery"
    $table->text('scenario_instruction_set');         // How to GENERATE the scenario
    $table->text('evaluation_instruction_set');       // How to EVALUATE the response
    $table->integer('position')->default(0);          // Order in the sequence
    $table->integer('timer_seconds')->nullable();     // null = no timer
    $table->string('input_type')->default('text');    // 'text', 'multiple_choice'
    $table->json('config')->nullable();               // Additional drill-specific config
    $table->timestamps();

    $table->index(['practice_mode_id', 'position']);
});
```

### Model

```php
// app/Models/Drill.php
class Drill extends Model
{
    protected $fillable = [
        'practice_mode_id',
        'name',
        'scenario_instruction_set',
        'evaluation_instruction_set',
        'position',
        'timer_seconds',
        'input_type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'timer_seconds' => 'integer',
        'position' => 'integer',
    ];

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }
}
```

### Example Drill Data

**Bad News Delivery:**

```php
[
    'name' => 'Bad News Delivery',
    'scenario_instruction_set' => <<<'PROMPT'
        Generate a scenario where user must deliver bad news to their team.
        - Types: project cancellation, budget cuts, layoffs, missed deadline
        - Must communicate in under 60 words
        - Include specific context: team size, relationship, stakes
        - Make it emotionally complex but realistic
    PROMPT,
    'evaluation_instruction_set' => <<<'PROMPT'
        Evaluate for:
        - Empathy: Did they acknowledge impact on team?
        - Directness: Bad news in first sentence?
        - Clarity: No corporate jargon or weasel words?
        - Word count: Under 60 words?

        Score harshly on burying the lead.
        Praise clear, human language.
    PROMPT,
    'timer_seconds' => 60,
    'input_type' => 'text',
]
```

**Executive Pushback:**

```php
[
    'name' => 'Executive Pushback',
    'scenario_instruction_set' => <<<'PROMPT'
        Generate a scenario where user must push back on an unreasonable
        executive request.
        - Request types: impossible deadline, scope creep, resource cuts
        - Executive is senior but not their direct manager
        - Stakes are real (team burnout, quality, reputation)
    PROMPT,
    'evaluation_instruction_set' => <<<'PROMPT'
        Evaluate for:
        - Diplomacy: Respectful tone while disagreeing?
        - Evidence: Did they cite data/constraints, not just opinion?
        - Alternative: Did they offer a counter-proposal?
        - Firmness: Clear "no" or wishy-washy hedge?

        Reward standing ground professionally.
        Flag passive-aggressive or apologetic over-hedging.
    PROMPT,
    'timer_seconds' => null,
    'input_type' => 'text',
]
```

---

## TrainingSession (Updated)

### Migration

```php
Schema::table('training_sessions', function (Blueprint $table) {
    // Simplified state tracking
    $table->integer('drill_index')->default(0);
    $table->string('phase')->default('scenario');  // 'scenario' | 'responding' | 'feedback' | 'complete'

    // Current drill state (for resume on refresh)
    $table->text('current_scenario')->nullable();
    $table->text('current_task')->nullable();
    $table->json('current_options')->nullable();        // For MC drills
    $table->integer('current_correct_option')->nullable();

    // Score accumulation
    $table->json('drill_scores')->nullable();  // [{drill_id, drill_name, score}, ...]
});
```

### Model Updates

```php
// app/Models/TrainingSession.php
protected $casts = [
    // ... existing
    'drill_index' => 'integer',
    'current_options' => 'array',
    'current_correct_option' => 'integer',
    'drill_scores' => 'array',
];
```

---

## SessionCompletedEvent (New Model)

### Migration

```php
Schema::create('session_completed_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
    $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
    $table->integer('drills_completed');
    $table->integer('total_duration_seconds');
    $table->json('scores')->nullable();           // [{drill_id, drill_name, score}, ...]
    $table->timestamp('completed_at');
    $table->timestamps();

    // Indexes for common queries
    $table->index(['user_id', 'completed_at']);
    $table->index(['practice_mode_id', 'completed_at']);
});
```

### Model

```php
// app/Models/SessionCompletedEvent.php
class SessionCompletedEvent extends Model
{
    protected $fillable = [
        'user_id',
        'practice_mode_id',
        'training_session_id',
        'drills_completed',
        'total_duration_seconds',
        'scores',
        'completed_at',
    ];

    protected $casts = [
        'scores' => 'array',
        'completed_at' => 'datetime',
        'drills_completed' => 'integer',
        'total_duration_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}
```

---

## PracticeMode Relationship

```php
// app/Models/PracticeMode.php
public function drills(): HasMany
{
    return $this->hasMany(Drill::class)->orderBy('position');
}
```

---

## Files to Create

- `config/mentalgym.php`
- `database/migrations/xxxx_create_drills_table.php`
- `database/migrations/xxxx_add_drill_columns_to_training_sessions.php`
- `database/migrations/xxxx_create_session_completed_events_table.php`
- `app/Models/Drill.php`
- `app/Models/SessionCompletedEvent.php`
- `database/seeders/DrillSeeder.php`

## Files to Modify

- `app/Models/PracticeMode.php` - add drills relationship
- `app/Models/TrainingSession.php` - add new casts
