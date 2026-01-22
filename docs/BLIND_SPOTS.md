# Blind Spots Feature

A pattern detection and insight system that identifies recurring weaknesses in user training responses and delivers personalized feedback to drive improvement.

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Response Scoring](#response-scoring)
4. [Pattern Detection](#pattern-detection)
5. [Pro Gating](#pro-gating)
6. [API Endpoints](#api-endpoints)
7. [Frontend Dashboard](#frontend-dashboard)
8. [Weekly Emails](#weekly-emails)
9. [Configuration Reference](#configuration-reference)

---

## Overview

The Blind Spots feature analyzes every drill response a user submits, scores it against skill-based criteria using Claude AI, and identifies recurring patterns over time. Pro users receive full insights via a dashboard and weekly emails; free users see a teaser to encourage upgrades.

### System Flow

```
User Submits Response
        ↓
ScoreDrillResponse Job (async)
        ↓
DrillScoringService calls Claude API
        ↓
Claude scores against universal + drill-specific criteria
        ↓
DrillScore record stored in database
        ↓

[On Dashboard Load / Weekly Email]
        ↓
BlindSpotAnalyzer reads DrillScore records
        ↓
Analyzes patterns across last 30 days
        ↓
Calculates failure rates by skill
        ↓
Compares recent (7 days) vs baseline (7-30 days)
        ↓
Determines trends & blind spots
        ↓
BlindSpotService gates by Pro status
        ↓
Frontend renders dashboard OR teaser
```

---

## Architecture

### File Structure

```
app/
├── DTOs/
│   ├── BlindSpotAnalysis.php
│   ├── GatedBlindSpotAnalysis.php
│   ├── SkillAnalysis.php
│   └── UniversalPattern.php
├── Http/Controllers/
│   ├── Api/BlindSpotController.php
│   └── BlindSpotDashboardController.php
├── Jobs/
│   ├── ScoreDrillResponse.php
│   ├── SendWeeklyBlindSpotEmails.php
│   └── SendBlindSpotTeaserEmail.php
├── Models/
│   ├── DrillScore.php
│   └── BlindSpotEmail.php
├── Services/
│   ├── BlindSpotAnalyzer.php
│   ├── BlindSpotService.php
│   ├── DrillScoringService.php
│   └── WeeklyEmailContentGenerator.php
config/
├── drill_types.php
└── skills.php
resources/js/
├── pages/blind-spots/index.tsx
└── components/blind-spot-teaser.tsx
```

### Database Tables

**drill_scores**
```sql
id                      BIGINT PRIMARY KEY
user_id                 BIGINT (FK users)
training_session_id     BIGINT (FK training_sessions)
practice_mode_id        BIGINT (FK practice_modes)
drill_type              VARCHAR(50)    -- e.g., 'compression', 'executive_communication'
drill_phase             VARCHAR(100)   -- e.g., 'Compression', 'Executive Communication'
is_iteration            BOOLEAN        -- true if this is a retry/revision
scores                  JSON           -- {"hedging": true, "filler_phrases": 2, ...}
user_response           TEXT           -- The user's actual response text
word_count              SMALLINT
response_time_seconds   SMALLINT NULL
created_at              TIMESTAMP
updated_at              TIMESTAMP

INDEXES: (user_id, created_at), (user_id, drill_type), (user_id, practice_mode_id)
```

**blind_spot_emails**
```sql
id                      BIGINT PRIMARY KEY
user_id                 BIGINT (FK users)
email_type              VARCHAR(50)    -- 'weekly_report' or 'teaser'
week_number             INT            -- ISO week number
year                    INT
analysis_snapshot       JSON           -- Snapshot of analysis when sent
subject_line            VARCHAR
sent_at                 TIMESTAMP
opened_at               TIMESTAMP NULL
clicked_at              TIMESTAMP NULL
created_at              TIMESTAMP
updated_at              TIMESTAMP

UNIQUE: (user_id, email_type, week_number, year)
```

---

## Response Scoring

### How It Works

When a user submits a drill response, `ScoreDrillResponse` job dispatches asynchronously to score the response via Claude API (model: `claude-sonnet-4-20250514`).

### Scoring Criteria

#### Universal Criteria (Applied to ALL drills)

| Criteria | Type | Description |
|----------|------|-------------|
| `hedging` | boolean | Weak language: "I think", "maybe", "probably", "perhaps", "might", "could be", "sort of", "kind of", "I believe", "it seems" |
| `filler_phrases` | integer | Count of fillers: "you know", "like", "basically", "actually", "just", "really", "very", "obviously", "honestly", "literally" |
| `word_limit_met` | boolean | Response stayed within requested word/sentence limit |
| `apology_detected` | boolean | Unnecessary apologizing: "sorry", "I apologize", "forgive me", self-deprecation |
| `ran_long` | boolean | Response significantly exceeded expected length |
| `too_short` | boolean | Response was too brief to be substantive |

#### Drill-Specific Criteria

Each drill type has additional criteria. Examples:

**compression** (5 criteria)
- `core_point_captured`, `concise`, `under_word_limit`, `clarity`, `jargon_removed`

**executive_communication** (7 criteria)
- `declarative_sentences`, `authority_tone`, `clear_position`, `appropriate_length`, `defensive_language`, `blame_shifting`, `solution_oriented`

**problem_solving** (8 criteria)
- `decision_clear`, `rationale_supports_decision`, `risk_realistic`, `mitigation_specific`, `structure_followed`, `tradeoff_acknowledged`, `avoided_analysis_paralysis`, `considered_stakeholders`

**story_compression** (7 criteria)
- `star_structure`, `situation_concise`, `action_specific`, `result_measurable`, `under_60_seconds`, `i_not_we`, `relevant_to_role`

**unexpected_question** (6 criteria)
- `started_strong`, `clear_position`, `reason_supported`, `no_stalling`, `acknowledge_position_reason`, `composure`

Full list: 21 drill types in `config/drill_types.php`

### DrillScoringService

```php
public function scoreResponse(
    User $user,
    TrainingSession $session,
    string $drillType,
    string $drillPhase,
    string $userResponse,
    bool $isIteration = false
): DrillScore
```

1. Counts words in response
2. Builds prompt with universal + drill-specific criteria
3. Calls Claude API (max 500 tokens)
4. Parses JSON response
5. Creates `DrillScore` record

---

## Pattern Detection

### Skill Taxonomy

Scores are aggregated into 8 core skills (defined in `config/skills.php`):

| Skill | Positive Criteria | Negative Criteria | What It Measures |
|-------|-------------------|-------------------|------------------|
| **clarity** | `core_point_captured`, `jargon_removed`, `meaning_preserved`, `clarity` | — | Clear thinking, core message extraction |
| **brevity** | `word_limit_met`, `concise`, `under_word_limit`, `brevity`, `under_60_seconds`, `appropriate_length` | `too_short`, `ran_long`, `no_rambling` | Concise communication, respecting limits |
| **authority** | `declarative_sentences`, `authority_tone`, `clear_position`, `confident_delivery` | `hedging`, `defensive_language` | Decisive tone, executive presence |
| **structure** | `star_structure`, `prep_structure`, `structure_followed`, `logical_flow`, `present_past_future`, `acknowledge_position_reason` | — | Framework usage, organized thinking |
| **composure** | `calm_tone`, `composure`, `stayed_calm`, `non_defensive`, `maintained_composure` | — | Calm under pressure, non-reactive |
| **directness** | `direct_opening`, `lead_with_news`, `headline_first`, `started_strong`, `bottom_line_first`, `no_stalling`, `direct_admission` | `no_buried_lead` | Leading with the point, no burying |
| **ownership** | `ownership`, `owned_it`, `accountability_shown`, `proactive` | `no_blame`, `blame_shifting` | Taking responsibility, no blame |
| **authenticity** | `authentic`, `genuine_interest`, `no_excessive_apology` | `humble_brag_avoided` | Genuine, human, not scripted |

### Failure Rate Calculation

For each skill:
1. Get all criteria (positive and negative) associated with that skill
2. For each `DrillScore` in the time window:
   - **Positive criteria**: failure = criterion is `false`
   - **Negative criteria**: failure = criterion is `true`
3. Calculate: `failureRate = totalFailures / totalCriteriaChecked`

### Trend Determination

Compare recent performance (last 7 days) to baseline (7-30 days ago):

```
delta = baselineRate - currentRate

If baseline sample < 3 responses:
  → trend = 'new'

If currentRate >= 0.6 AND baselineRate >= 0.6:
  If delta >= 0.2:
    → trend = 'improving'
  Else:
    → trend = 'stuck'

If delta >= 0.2:
  → trend = 'improving'

If delta <= -0.15:
  → trend = 'slipping'

Else:
  → trend = 'stable'
```

### Blind Spot Identification

A skill is flagged as a **blind spot** when:
- `currentRate >= 0.6` (60% failure rate)
- At least 5 responses analyzed for that skill

### BlindSpotAnalyzer Output

```php
BlindSpotAnalysis {
    bool $hasEnoughData          // true if user has 5+ sessions
    int $totalSessions
    int $totalResponses
    array $blindSpots            // SkillAnalysis[] - skills with rate >= 0.6
    array $improving             // SkillAnalysis[] - trend = 'improving'
    array $stable                // SkillAnalysis[] - trend = 'stable'
    array $slipping              // SkillAnalysis[] - trend = 'slipping'
    array $universalPatterns     // UniversalPattern[] - hedging, fillers, apologies
    ?string $biggestGap          // Highest failure rate blind spot
    ?string $biggestWin          // Best improving skill
}

SkillAnalysis {
    string $skill                // 'authority', 'brevity', etc.
    string $trend                // 'improving', 'stable', 'slipping', 'stuck', 'new'
    float $currentRate           // 0.0 - 1.0 failure rate
    float $baselineRate          // Previous period failure rate
    int $sampleSize              // Responses analyzed
    ?string $primaryIssue        // Most common failing criterion
    ?string $context             // Drill phase where issue appears most
    array $failingCriteria       // Sorted by failure rate
}

UniversalPattern {
    string $criteria             // 'hedging', 'filler_phrases', 'apology_detected'
    float $rate                  // Occurrence rate (0.0 - 1.0)
    int $count                   // Times detected
    int $total                   // Total responses
    string $trend                // Same as SkillAnalysis trends
}
```

---

## Pro Gating

### Access Rules

| User State | What They See |
|------------|---------------|
| Free, < 5 sessions | "Complete X more sessions to unlock insights" |
| Free, 5+ sessions | Teaser: "X blind spots identified. Upgrade to see them." |
| Pro, < 5 sessions | "Complete X more sessions to unlock insights" |
| Pro, 5+ sessions | Full analysis with all details |

### BlindSpotService Methods

```php
// Returns gated analysis based on subscription
getAnalysis(User $user): GatedBlindSpotAnalysis

// True if Pro AND 5+ sessions
canAccessFullInsights(User $user): bool

// True if Free AND 5+ sessions AND has blind spots
shouldShowTeaser(User $user): bool

// Lightweight data for teaser display
getTeaserData(User $user): array

// 8-week historical data for charts
getHistoricalTrends(User $user, int $weeks = 8): array
```

### GatedBlindSpotAnalysis

```php
// Always visible:
bool $hasEnoughData
int $totalSessions
int $totalResponses
int $blindSpotCount
bool $hasBlindSpots

// Gating info:
bool $isUnlocked
string $requiredPlan             // 'pro'
?string $gateReason              // 'requires_upgrade' | 'insufficient_data'
int $sessionsUntilInsights

// Only populated if unlocked:
?array $blindSpots
?array $improving
?array $slipping
?array $stable
?array $universalPatterns
?string $biggestGap
?string $biggestWin
```

---

## API Endpoints

All endpoints require authentication.

### GET /api/blind-spots

Returns full gated analysis.

**Response:**
```json
{
  "success": true,
  "analysis": {
    "hasEnoughData": true,
    "totalSessions": 12,
    "totalResponses": 34,
    "blindSpotCount": 2,
    "hasBlindSpots": true,
    "isUnlocked": true,
    "requiredPlan": "pro",
    "gateReason": null,
    "sessionsUntilInsights": 0,
    "blindSpots": [...],
    "improving": [...],
    "stable": [...],
    "slipping": [...],
    "universalPatterns": [...],
    "biggestGap": "authority",
    "biggestWin": "structure"
  }
}
```

### GET /api/blind-spots/teaser

Lightweight endpoint for free user teaser.

**Response:**
```json
{
  "success": true,
  "showTeaser": true,
  "teaser": {
    "blindSpotCount": 3,
    "hasImprovements": true,
    "hasRegressions": false,
    "totalSessions": 8
  }
}
```

### GET /api/blind-spots/status

Quick status check.

**Response:**
```json
{
  "hasEnoughData": true,
  "hasProAccess": false,
  "canAccessFullInsights": false,
  "showTeaser": true,
  "totalSessions": 8,
  "minimumSessions": 5,
  "sessionsUntilInsights": 0,
  "blindSpotCount": 3
}
```

---

## Frontend Dashboard

### Route

```
GET /blind-spots → BlindSpotDashboardController@index
```

### Components

**Pro User View:**
- **SummaryStats**: Sessions, Responses, Weeks Active
- **BiggestGapCard**: Worst skill with primary issue and "Train This" button
- **BiggestWinCard**: Best improving skill
- **SkillTrajectory**: Bar chart of all skills sorted by failure rate
- **PatternDetails**: Expandable sections for blind spots, improving, stable, slipping
- **UniversalPatterns**: Hedging, filler phrases, apologies rates
- **TrendChart**: 8-week line chart of skill progression

**Free User View (GatedView):**
- Lock icon with blind spot count
- Redacted/blurred pattern preview
- "Unlock Blind Spots — Upgrade to Pro" CTA

### Historical Trends Data

For the trend chart, `getHistoricalTrends()` returns:

```php
[
    [
        'week' => 'Jan 6',
        'data' => [
            'authority' => 0.67,    // failure rate
            'brevity' => 0.42,
            'structure' => 0.25,
            'composure' => 0.35,
            'directness' => 0.52
        ],
        'sessions' => 3,
        'responses' => 8
    ],
    // ... 8 weeks
]
```

---

## Weekly Emails

### Pro User Weekly Report

**Job:** `SendWeeklyBlindSpotEmails`
**Schedule:** Sunday 6 PM ET

**Eligibility:**
- Pro or Unlimited subscription
- At least 1 session in last 7 days
- Has 5+ total sessions
- Hasn't received email this week
- Weekly emails enabled (default: true)

**Email Structure:**
```
Subject: Your week: [AI-generated insight headline]

SHARPSTACK WEEKLY

You completed X sessions this week.

WHAT'S IMPROVING
→ [Skill]: [Specific observation]

WHAT NEEDS WORK
→ [Skill]: [Observation with data, e.g., "8 of 12 responses"]

PATTERN TO WATCH
[1-2 sentences about recurring pattern]

THIS WEEK'S FOCUS
[Actionable recommendation]

[ Start a Session ]
```

Content generated by `WeeklyEmailContentGenerator` using Claude API.

### Free User Teaser Email

**Job:** `SendBlindSpotTeaserEmail`
**Trigger:** When free user reaches 5 sessions and has blind spots

**Eligibility:**
- Free user (no Pro)
- Just completed 5th session
- Has at least 1 blind spot
- Hasn't received teaser before

**Email Structure:**
```
Subject: We found X patterns in your training

You've completed 5 sessions. That's enough data to see patterns.

We analyzed X responses and found:

┌─────────────────────────────────────┐
│   X BLIND SPOTS IDENTIFIED          │
│   • ████████████████████████        │
│   • ████████████████████████        │
└─────────────────────────────────────┘

Pro members see exactly what these patterns are,
where they show up, and how to fix them.

[ Unlock Your Blind Spots ]
```

---

## Configuration Reference

### Thresholds (`config/skills.php`)

| Setting | Value | Description |
|---------|-------|-------------|
| `blind_spot` | 0.6 | 60% failure rate = blind spot |
| `improvement` | 0.2 | 20% improvement needed for "improving" |
| `regression` | 0.15 | 15% regression triggers "slipping" |
| `minimum_responses` | 5 | Responses needed to flag a criterion |
| `minimum_sessions` | 5 | Sessions needed before analysis available |

### Time Windows

| Window | Value | Description |
|--------|-------|-------------|
| Recent | 7 days | "Current" performance measurement |
| Baseline | 30 days | Full historical period |
| Baseline minimum | 3 responses | Minimum data to calculate trend |

### API Limits

| Setting | Value |
|---------|-------|
| Scoring API max tokens | 500 |
| Email generation max tokens | 1000 |
| Claude model | `claude-sonnet-4-20250514` |

### Job Configuration

| Job | Queue | Retries | Backoff |
|-----|-------|---------|---------|
| ScoreDrillResponse | default | 3 | 5 seconds |
| SendWeeklyBlindSpotEmails | emails | 3 | 60 seconds |
| SendBlindSpotTeaserEmail | emails | 3 | 60 seconds |
