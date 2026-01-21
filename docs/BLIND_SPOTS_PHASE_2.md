# Phase 2: Pattern Detection

## Overview

Analyze stored drill scores to identify user patterns over time. Surface "blind spots" (recurring weaknesses), improvements, and regressions. This powers the Blind Spots feature for Pro users.

## Context

Phase 1 established the scoring infrastructure. Every drill response is now scored and stored with skill-based criteria. Phase 2 reads this data and extracts meaningful patterns.

## Dependencies

- Phase 1 complete (drill_scores table populated)
- Skill tags defined on Practice Modes
- Minimum data threshold: 5 sessions before pattern detection runs

## Goals

1. Build BlindSpotAnalyzer service
2. Define pattern detection logic
3. Identify blind spots, improvements, and regressions
4. Return structured output for emails and future dashboard

---

## Skill-Based Scoring Model

Scores are tied to skill tags, not drill types. This keeps patterns meaningful and maintainable.

### Core Skill Tags

| Skill Tag | What It Measures | Key Criteria |
|-----------|------------------|--------------|
| clarity | Clear thinking, core message extraction | core_point_captured, jargon_removed, meaning_preserved |
| brevity | Concise communication, respecting limits | word_limit_met, concise, no_rambling, too_short, ran_long |
| authority | Decisive tone, executive presence | hedging, declarative_sentences, authority_tone, clear_position |
| structure | Framework usage, organized thinking | star_structure, prep_structure, structure_followed, logical_flow |
| composure | Calm under pressure, non-reactive | calm_tone, composure, stayed_calm, non_defensive |
| directness | Leading with the point, no burying | direct_opening, lead_with_news, headline_first, started_strong |
| ownership | Taking responsibility, no blame | ownership, owned_it, accountability_shown, no_blame |
| authenticity | Genuine, human, not scripted | authentic, genuine_interest, humble_brag_avoided |

### Universal Criteria (tracked separately)

These apply to all responses regardless of skill tags:

| Criteria | Description |
|----------|-------------|
| hedging | Weak language: "I think", "maybe", "probably" |
| filler_phrases | Count of fillers: "like", "basically", "actually" |
| apology_detected | Unnecessary apologizing |

---

## Pattern Detection Logic

### What Constitutes a Blind Spot

A blind spot is a recurring negative pattern. Thresholds:

```php
// Blind spot threshold
$blindSpotThreshold = 0.6; // 60% failure rate on a criteria

// Minimum sample size
$minimumResponses = 5; // Need at least 5 responses to identify pattern
```

**Example:**
- User has 12 responses scored for `authority`
- `hedging: true` in 8 of 12 responses (67%)
- This exceeds 60% threshold → Blind spot identified

### What Constitutes Improvement

Improvement is measured by comparing recent performance to historical baseline.

```php
// Time windows
$recentWindow = 7; // days
$baselineWindow = 30; // days

// Improvement threshold
$improvementThreshold = 0.2; // 20% improvement
```

**Example:**
- Baseline (30 days): hedging in 70% of responses
- Recent (7 days): hedging in 40% of responses
- Delta: 30% improvement → Mark as "improving"

### What Constitutes Regression

Same logic, opposite direction:

```php
$regressionThreshold = 0.15; // 15% decline triggers regression flag
```

**Example:**
- Baseline: hedging in 30% of responses
- Recent: hedging in 50% of responses
- Delta: 20% regression → Mark as "slipping"

### Trend Classification

| Trend | Logic |
|-------|-------|
| improving | Recent failure rate 20%+ lower than baseline |
| stable | Within 15% of baseline |
| slipping | Recent failure rate 15%+ higher than baseline |
| stuck | Failure rate above 60% in both windows |
| new | Not enough baseline data, only recent |

---

## BlindSpotAnalyzer Service

### Interface

```php
<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\BlindSpotAnalysis;

class BlindSpotAnalyzer
{
    /**
     * Analyze patterns for a user
     */
    public function analyze(User $user): BlindSpotAnalysis;

    /**
     * Check if user has enough data for analysis
     */
    public function hasEnoughData(User $user): bool;

    /**
     * Get minimum sessions required
     */
    public function getMinimumSessions(): int;

    /**
     * Get analysis for a specific skill
     */
    public function analyzeSkill(User $user, string $skill): SkillAnalysis;
}
```

### BlindSpotAnalysis DTO

```php
<?php

namespace App\DTOs;

class BlindSpotAnalysis
{
    public function __construct(
        public bool $hasEnoughData,
        public int $totalSessions,
        public int $totalResponses,
        public array $blindSpots,      // SkillAnalysis[]
        public array $improving,        // SkillAnalysis[]
        public array $stable,           // SkillAnalysis[]
        public array $slipping,         // SkillAnalysis[]
        public array $universalPatterns, // UniversalPattern[]
        public ?string $biggestGap,     // Primary weakness
        public ?string $biggestWin,     // Primary strength
        public Carbon $analyzedAt,
    ) {}

    public function toArray(): array;

    public function getBlindSpotCount(): int;

    public function hasBlindSpots(): bool;
}
```

### SkillAnalysis DTO

```php
<?php

namespace App\DTOs;

class SkillAnalysis
{
    public function __construct(
        public string $skill,           // e.g., "authority"
        public string $trend,           // improving, stable, slipping, stuck
        public float $currentRate,      // e.g., 0.67 (67% failure)
        public float $baselineRate,     // e.g., 0.80 (80% failure)
        public int $sampleSize,         // responses analyzed
        public string $primaryIssue,    // Most common failing criteria
        public ?string $context,        // Where this shows up most
        public array $failingCriteria,  // All criteria with high failure rates
    ) {}
}
```

### UniversalPattern DTO

```php
<?php

namespace App\DTOs;

class UniversalPattern
{
    public function __construct(
        public string $criteria,        // e.g., "hedging"
        public float $rate,             // e.g., 0.65 (65% of responses)
        public int $count,              // e.g., 8 occurrences
        public int $total,              // e.g., 12 total responses
        public string $trend,           // improving, stable, slipping
    ) {}
}
```

---

## Analysis Output Example

```json
{
  "hasEnoughData": true,
  "totalSessions": 12,
  "totalResponses": 34,
  "blindSpots": [
    {
      "skill": "authority",
      "trend": "stuck",
      "currentRate": 0.67,
      "baselineRate": 0.70,
      "sampleSize": 15,
      "primaryIssue": "hedging",
      "context": "Executive Communication drills",
      "failingCriteria": ["hedging", "declarative_sentences"]
    }
  ],
  "improving": [
    {
      "skill": "structure",
      "trend": "improving",
      "currentRate": 0.25,
      "baselineRate": 0.55,
      "sampleSize": 12,
      "primaryIssue": null,
      "context": null,
      "failingCriteria": []
    }
  ],
  "stable": [
    {
      "skill": "brevity",
      "trend": "stable",
      "currentRate": 0.40,
      "baselineRate": 0.42,
      "sampleSize": 18,
      "primaryIssue": "ran_long",
      "context": null,
      "failingCriteria": ["ran_long"]
    }
  ],
  "slipping": [],
  "universalPatterns": [
    {
      "criteria": "hedging",
      "rate": 0.65,
      "count": 22,
      "total": 34,
      "trend": "stuck"
    },
    {
      "criteria": "filler_phrases",
      "rate": 0.35,
      "count": 12,
      "total": 34,
      "trend": "improving"
    }
  ],
  "biggestGap": "authority",
  "biggestWin": "structure",
  "analyzedAt": "2026-01-20T14:30:00Z"
}
```

---

## Database Queries

### Get aggregated scores by skill

```php
// Get failure rates per skill for a user
DrillScore::where('user_id', $userId)
    ->where('created_at', '>=', $startDate)
    ->selectRaw('
        JSON_EXTRACT(scores, "$.hedging") as hedging,
        JSON_EXTRACT(scores, "$.clarity") as clarity,
        -- etc
    ')
    ->get();
```

### Get scores with Practice Mode tags

```php
DrillScore::where('user_id', $userId)
    ->with('practiceMode:id,tags')
    ->where('created_at', '>=', $startDate)
    ->get()
    ->groupBy(fn($score) => $score->practiceMode->tags);
```

### Compare time windows

```php
// Baseline: 8-30 days ago
$baseline = DrillScore::where('user_id', $userId)
    ->whereBetween('created_at', [$thirtyDaysAgo, $sevenDaysAgo])
    ->get();

// Recent: last 7 days
$recent = DrillScore::where('user_id', $userId)
    ->where('created_at', '>=', $sevenDaysAgo)
    ->get();
```

---

## Pattern Detection Algorithm

```php
public function analyze(User $user): BlindSpotAnalysis
{
    // 1. Check minimum data
    if (!$this->hasEnoughData($user)) {
        return BlindSpotAnalysis::insufficient($user);
    }

    // 2. Get all scores
    $allScores = $this->getScores($user, days: 30);
    $recentScores = $this->getScores($user, days: 7);

    // 3. Calculate universal patterns (hedging, filler, apologies)
    $universalPatterns = $this->analyzeUniversalCriteria($allScores, $recentScores);

    // 4. Get unique skills from user's Practice Modes
    $skills = $this->getSkillsFromUserHistory($user);

    // 5. Analyze each skill
    $skillAnalyses = [];
    foreach ($skills as $skill) {
        $skillAnalyses[$skill] = $this->analyzeSkill($user, $skill, $allScores, $recentScores);
    }

    // 6. Categorize by trend
    $blindSpots = array_filter($skillAnalyses, fn($s) => $s->currentRate >= 0.6);
    $improving = array_filter($skillAnalyses, fn($s) => $s->trend === 'improving');
    $slipping = array_filter($skillAnalyses, fn($s) => $s->trend === 'slipping');
    $stable = array_filter($skillAnalyses, fn($s) => $s->trend === 'stable');

    // 7. Identify biggest gap and win
    $biggestGap = $this->findBiggestGap($skillAnalyses);
    $biggestWin = $this->findBiggestWin($skillAnalyses);

    return new BlindSpotAnalysis(
        hasEnoughData: true,
        totalSessions: $this->getSessionCount($user),
        totalResponses: $allScores->count(),
        blindSpots: $blindSpots,
        improving: $improving,
        stable: $stable,
        slipping: $slipping,
        universalPatterns: $universalPatterns,
        biggestGap: $biggestGap,
        biggestWin: $biggestWin,
        analyzedAt: now(),
    );
}
```

---

## Contextual Insights

The analyzer should identify WHERE patterns show up most:

```php
private function findContext(User $user, string $skill, string $criteria): ?string
{
    // Find which drill_phase has highest failure rate for this criteria
    $byPhase = DrillScore::where('user_id', $user->id)
        ->whereRaw("JSON_EXTRACT(scores, '$.{$criteria}') = true")
        ->selectRaw('drill_phase, COUNT(*) as count')
        ->groupBy('drill_phase')
        ->orderByDesc('count')
        ->first();

    return $byPhase?->drill_phase;
}
```

This enables insights like:
- "Hedging appears most in Executive Communication drills"
- "You improve on iteration but slip in first attempts"
- "Composure drops in Defending Position scenarios"

---

## Iteration Analysis

Track whether users improve on second attempts:

```php
private function analyzeIterationPattern(User $user): array
{
    $firstAttempts = DrillScore::where('user_id', $user->id)
        ->where('is_iteration', false)
        ->get();

    $iterations = DrillScore::where('user_id', $user->id)
        ->where('is_iteration', true)
        ->get();

    // Compare failure rates
    $firstAttemptFailures = $this->calculateFailureRates($firstAttempts);
    $iterationFailures = $this->calculateFailureRates($iterations);

    return [
        'improves_on_iteration' => $iterationFailures < $firstAttemptFailures,
        'first_attempt_rate' => $firstAttemptFailures,
        'iteration_rate' => $iterationFailures,
        'delta' => $firstAttemptFailures - $iterationFailures,
    ];
}
```

This enables insights like:
- "You fix hedging on second attempts, which means you know how. You just don't do it first time."

---

## File Structure

```
app/
├── DTOs/
│   ├── BlindSpotAnalysis.php
│   ├── SkillAnalysis.php
│   └── UniversalPattern.php
├── Services/
│   └── BlindSpotAnalyzer.php
```

---

## Testing

### Test: User with clear blind spot

```php
// Create user with 10 responses, 8 have hedging: true
$user = User::factory()->create();
DrillScore::factory()
    ->count(8)
    ->state(['scores' => ['hedging' => true, 'authority_tone' => false]])
    ->create(['user_id' => $user->id]);
DrillScore::factory()
    ->count(2)
    ->state(['scores' => ['hedging' => false, 'authority_tone' => true]])
    ->create(['user_id' => $user->id]);

$analysis = app(BlindSpotAnalyzer::class)->analyze($user);

expect($analysis->hasBlindSpots())->toBeTrue();
expect($analysis->blindSpots)->toHaveCount(1);
expect($analysis->blindSpots[0]->skill)->toBe('authority');
```

### Test: User with improvement

```php
// Create baseline: 70% hedging (3 weeks ago)
// Create recent: 30% hedging (this week)
// Verify trend is "improving"
```

### Test: Insufficient data

```php
$user = User::factory()->create();
DrillScore::factory()->count(3)->create(['user_id' => $user->id]);

$analysis = app(BlindSpotAnalyzer::class)->analyze($user);

expect($analysis->hasEnoughData)->toBeFalse();
expect($analysis->blindSpots)->toBeEmpty();
```

### Test: Iteration pattern detection

```php
// Create first attempts with hedging
// Create iterations without hedging
// Verify improves_on_iteration is true
```

---

## Success Criteria

- [ ] BlindSpotAnalyzer service created
- [ ] DTOs created for analysis output
- [ ] Pattern detection identifies blind spots at 60%+ failure rate
- [ ] Trend detection compares 7-day vs 30-day windows
- [ ] Context detection identifies where patterns appear most
- [ ] Iteration analysis tracks first attempt vs. revision
- [ ] Minimum data check (5 sessions) works correctly
- [ ] All tests pass
- [ ] Can call `BlindSpotAnalyzer::analyze($user)` and get structured output

---

## Usage

```php
// In a controller or job
$analyzer = app(BlindSpotAnalyzer::class);
$analysis = $analyzer->analyze($user);

if ($analysis->hasBlindSpots()) {
    // User has patterns to address
    $primary = $analysis->biggestGap; // "authority"
    $count = $analysis->getBlindSpotCount(); // 3
}

// For email generation
$improving = $analysis->improving; // Skills getting better
$slipping = $analysis->slipping;   // Skills getting worse
```

This output feeds directly into Phase 4 (weekly email) and Phase 6 (dashboard).