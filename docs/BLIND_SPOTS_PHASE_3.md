# Phase 3: Pro Gating + Teaser

## Overview

Gate full Blind Spots insights behind Pro subscription. Free users see a teaser showing they have blind spots identified but can't see the details. This creates a natural upgrade moment tied to value they've already generated through training.

## Context

- Phase 1: Scoring infrastructure (all users scored silently)
- Phase 2: Pattern detection (BlindSpotAnalyzer returns structured insights)
- Phase 3: Control who sees what based on subscription tier

## Dependencies

- Phase 1 and 2 complete
- Existing subscription/plan system in place
- User has `plan` relationship or similar

## Goals

1. Define access rules for Blind Spots data
2. Build gated response that hides details for free users
3. Create teaser messaging that drives upgrades
4. Add API endpoint for checking blind spots status

---

## Access Rules

| User State | What They See |
|------------|---------------|
| Free, < 5 sessions | Nothing (not enough data) |
| Free, 5+ sessions | Teaser: "You have X blind spots identified. Upgrade to see them." |
| Pro, < 5 sessions | Message: "Complete more sessions to unlock insights" |
| Pro, 5+ sessions | Full analysis: all blind spots, trends, details |

---

## Gated Analysis DTO

Extend the BlindSpotAnalysis to support gated responses:

```php
<?php

namespace App\DTOs;

class GatedBlindSpotAnalysis
{
    public function __construct(
        // Always visible
        public bool $hasEnoughData,
        public int $totalSessions,
        public int $totalResponses,
        public int $blindSpotCount,
        public bool $hasBlindSpots,
        
        // Gating info
        public bool $isUnlocked,
        public string $requiredPlan,         // 'pro'
        public ?string $gateReason,          // 'insufficient_data' | 'requires_upgrade'
        public int $sessionsUntilInsights,   // 0 if enough data
        
        // Only populated if unlocked
        public ?array $blindSpots,
        public ?array $improving,
        public ?array $slipping,
        public ?array $stable,
        public ?array $universalPatterns,
        public ?string $biggestGap,
        public ?string $biggestWin,
        
        public Carbon $analyzedAt,
    ) {}

    public static function locked(
        int $blindSpotCount,
        int $totalSessions,
        int $totalResponses,
        string $reason
    ): self;

    public static function insufficientData(
        int $totalSessions,
        int $sessionsNeeded
    ): self;

    public static function unlocked(
        BlindSpotAnalysis $analysis
    ): self;

    public function toArray(): array;
}
```

---

## BlindSpotService

Create a service that wraps the analyzer with gating logic:

```php
<?php

namespace App\Services;

use App\Models\User;
use App\DTOs\GatedBlindSpotAnalysis;
use App\DTOs\BlindSpotAnalysis;

class BlindSpotService
{
    public function __construct(
        private BlindSpotAnalyzer $analyzer
    ) {}

    /**
     * Get gated analysis for a user
     */
    public function getAnalysis(User $user): GatedBlindSpotAnalysis
    {
        $minimumSessions = 5;
        $sessionCount = $this->getSessionCount($user);

        // Not enough data for anyone
        if ($sessionCount < $minimumSessions) {
            return GatedBlindSpotAnalysis::insufficientData(
                totalSessions: $sessionCount,
                sessionsNeeded: $minimumSessions - $sessionCount
            );
        }

        // Run the analysis
        $analysis = $this->analyzer->analyze($user);

        // Check if user has Pro access
        if ($this->hasProAccess($user)) {
            return GatedBlindSpotAnalysis::unlocked($analysis);
        }

        // Free user with enough data - show teaser
        return GatedBlindSpotAnalysis::locked(
            blindSpotCount: $analysis->getBlindSpotCount(),
            totalSessions: $analysis->totalSessions,
            totalResponses: $analysis->totalResponses,
            reason: 'requires_upgrade'
        );
    }

    /**
     * Check if user can see full insights
     */
    public function canAccessFullInsights(User $user): bool
    {
        return $this->hasProAccess($user) && $this->hasEnoughData($user);
    }

    /**
     * Check if user should see teaser
     */
    public function shouldShowTeaser(User $user): bool
    {
        return !$this->hasProAccess($user) 
            && $this->hasEnoughData($user)
            && $this->hasBlindSpots($user);
    }

    /**
     * Get teaser data only (lightweight, no full analysis)
     */
    public function getTeaserData(User $user): ?array
    {
        if (!$this->shouldShowTeaser($user)) {
            return null;
        }

        $analysis = $this->analyzer->analyze($user);

        return [
            'blind_spot_count' => $analysis->getBlindSpotCount(),
            'total_sessions' => $analysis->totalSessions,
            'message' => $this->getTeaserMessage($analysis->getBlindSpotCount()),
        ];
    }

    private function hasProAccess(User $user): bool
    {
        // Adjust based on your subscription system
        return $user->plan === 'pro' 
            || $user->plan === 'unlimited'
            || $user->subscribed('pro');
    }

    private function hasEnoughData(User $user): bool
    {
        return $this->analyzer->hasEnoughData($user);
    }

    private function hasBlindSpots(User $user): bool
    {
        $analysis = $this->analyzer->analyze($user);
        return $analysis->hasBlindSpots();
    }

    private function getSessionCount(User $user): int
    {
        return $user->practiceSessions()->completed()->count();
    }

    private function getTeaserMessage(int $count): string
    {
        if ($count === 1) {
            return "We've identified 1 blind spot in your training. Upgrade to Pro to see it.";
        }
        return "We've identified {$count} blind spots in your training. Upgrade to Pro to see them.";
    }
}
```

---

## API Endpoint

Create endpoint to fetch blind spots status:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Services\BlindSpotService;
use Illuminate\Http\JsonResponse;

class BlindSpotController extends Controller
{
    public function __construct(
        private BlindSpotService $service
    ) {}

    /**
     * GET /api/blind-spots
     * 
     * Returns gated analysis based on user's subscription
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $analysis = $this->service->getAnalysis($user);

        return response()->json($analysis->toArray());
    }

    /**
     * GET /api/blind-spots/teaser
     * 
     * Lightweight endpoint for showing teaser in UI
     */
    public function teaser(): JsonResponse
    {
        $user = auth()->user();
        $teaser = $this->service->getTeaserData($user);

        if (!$teaser) {
            return response()->json([
                'show_teaser' => false,
            ]);
        }

        return response()->json([
            'show_teaser' => true,
            ...$teaser,
        ]);
    }

    /**
     * GET /api/blind-spots/status
     * 
     * Quick check of user's blind spots status
     */
    public function status(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'has_enough_data' => $this->service->hasEnoughData($user),
            'is_unlocked' => $this->service->canAccessFullInsights($user),
            'show_teaser' => $this->service->shouldShowTeaser($user),
            'sessions_completed' => $user->practiceSessions()->completed()->count(),
            'sessions_needed' => max(0, 5 - $user->practiceSessions()->completed()->count()),
        ]);
    }
}
```

---

## Routes

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/blind-spots', [BlindSpotController::class, 'index']);
    Route::get('/blind-spots/teaser', [BlindSpotController::class, 'teaser']);
    Route::get('/blind-spots/status', [BlindSpotController::class, 'status']);
});
```

---

## Response Examples

### Free user, not enough data

```json
{
  "has_enough_data": false,
  "total_sessions": 3,
  "total_responses": 8,
  "blind_spot_count": 0,
  "has_blind_spots": false,
  "is_unlocked": false,
  "required_plan": "pro",
  "gate_reason": "insufficient_data",
  "sessions_until_insights": 2,
  "blind_spots": null,
  "improving": null,
  "slipping": null,
  "stable": null,
  "universal_patterns": null,
  "biggest_gap": null,
  "biggest_win": null
}
```

### Free user, 5+ sessions (TEASER)

```json
{
  "has_enough_data": true,
  "total_sessions": 8,
  "total_responses": 24,
  "blind_spot_count": 3,
  "has_blind_spots": true,
  "is_unlocked": false,
  "required_plan": "pro",
  "gate_reason": "requires_upgrade",
  "sessions_until_insights": 0,
  "blind_spots": null,
  "improving": null,
  "slipping": null,
  "stable": null,
  "universal_patterns": null,
  "biggest_gap": null,
  "biggest_win": null
}
```

### Pro user, full access

```json
{
  "has_enough_data": true,
  "total_sessions": 12,
  "total_responses": 34,
  "blind_spot_count": 2,
  "has_blind_spots": true,
  "is_unlocked": true,
  "required_plan": "pro",
  "gate_reason": null,
  "sessions_until_insights": 0,
  "blind_spots": [
    {
      "skill": "authority",
      "trend": "stuck",
      "current_rate": 0.67,
      "baseline_rate": 0.70,
      "sample_size": 15,
      "primary_issue": "hedging",
      "context": "Executive Communication drills",
      "failing_criteria": ["hedging", "declarative_sentences"]
    }
  ],
  "improving": [
    {
      "skill": "structure",
      "trend": "improving",
      "current_rate": 0.25,
      "baseline_rate": 0.55,
      "sample_size": 12
    }
  ],
  "slipping": [],
  "stable": [
    {
      "skill": "brevity",
      "trend": "stable",
      "current_rate": 0.40,
      "baseline_rate": 0.42,
      "sample_size": 18
    }
  ],
  "universal_patterns": [
    {
      "criteria": "hedging",
      "rate": 0.65,
      "count": 22,
      "total": 34,
      "trend": "stuck"
    }
  ],
  "biggest_gap": "authority",
  "biggest_win": "structure"
}
```

### Teaser endpoint response

```json
{
  "show_teaser": true,
  "blind_spot_count": 3,
  "total_sessions": 8,
  "message": "We've identified 3 blind spots in your training. Upgrade to Pro to see them."
}
```

---

## Vue Component: BlindSpotTeaser

Create a component to show the teaser in the UI:

```vue
<template>
  <div v-if="showTeaser" class="blind-spot-teaser">
    <div class="teaser-content">
      <div class="teaser-icon">🔒</div>
      <div class="teaser-text">
        <h3>{{ blindSpotCount }} Blind Spots Identified</h3>
        <p>
          You've completed {{ totalSessions }} sessions. We've found patterns 
          in your responses that could be holding you back.
        </p>
        <div class="redacted-preview">
          <div class="redacted-line"></div>
          <div class="redacted-line"></div>
          <div class="redacted-line short"></div>
        </div>
      </div>
      <button @click="upgrade" class="upgrade-button">
        Unlock Blind Spots
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const showTeaser = ref(false);
const blindSpotCount = ref(0);
const totalSessions = ref(0);

onMounted(async () => {
  const response = await fetch('/api/blind-spots/teaser');
  const data = await response.json();
  
  if (data.show_teaser) {
    showTeaser.value = true;
    blindSpotCount.value = data.blind_spot_count;
    totalSessions.value = data.total_sessions;
  }
});

const upgrade = () => {
  router.visit('/settings/subscription');
};
</script>

<style scoped>
.blind-spot-teaser {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border: 1px solid #e94560;
  border-radius: 12px;
  padding: 24px;
  margin: 16px 0;
}

.teaser-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
}

.teaser-icon {
  font-size: 32px;
}

.teaser-text h3 {
  color: #e94560;
  font-size: 20px;
  margin: 0;
}

.teaser-text p {
  color: #a0a0a0;
  margin: 8px 0;
}

.redacted-preview {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  width: 100%;
  max-width: 300px;
}

.redacted-line {
  height: 12px;
  background: linear-gradient(90deg, #333 0%, #444 50%, #333 100%);
  border-radius: 4px;
}

.redacted-line.short {
  width: 60%;
}

.upgrade-button {
  background: #e94560;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.upgrade-button:hover {
  background: #ff6b6b;
}
</style>
```

---

## Where to Show Teaser

Display the teaser component in strategic locations:

| Location | Trigger |
|----------|---------|
| Dashboard | Always check on load |
| After session completion | If threshold just crossed (5th session) |
| Practice Modes index | Subtle banner at top |
| Settings page | In subscription section |

---

## Teaser Copy Variations

### Standard teaser
> "We've identified 3 blind spots in your training. Upgrade to Pro to see them."

### After 5th session (first trigger)
> "You've hit 5 sessions. We've started tracking your patterns and found 2 blind spots. See what's holding you back."

### High count
> "6 blind spots identified across 12 sessions. There's a pattern here. Unlock it."

### Single blind spot
> "We found 1 recurring pattern in your responses. It's showing up consistently. Want to see it?"

---

## Tracking Teaser Impressions

Optionally track when users see the teaser for conversion analysis:

```php
// In teaser endpoint or component
TeaserImpression::create([
    'user_id' => $user->id,
    'blind_spot_count' => $count,
    'location' => $request->input('location', 'unknown'),
    'created_at' => now(),
]);
```

This helps answer: "Do users who see the teaser convert at higher rates?"

---

## File Structure

```
app/
├── DTOs/
│   └── GatedBlindSpotAnalysis.php
├── Http/
│   └── Controllers/
│       └── Api/
│           └── BlindSpotController.php
├── Services/
│   └── BlindSpotService.php
resources/
└── js/
    └── Components/
        └── BlindSpotTeaser.vue
routes/
└── api.php (updated)
```

---

## Testing

### Test: Free user with enough data sees teaser

```php
$user = User::factory()->create(['plan' => 'free']);
// Create 6 sessions with scores that produce blind spots
createSessionsWithBlindSpots($user, 6);

$response = $this->actingAs($user)->getJson('/api/blind-spots');

$response->assertOk();
$response->assertJson([
    'has_enough_data' => true,
    'is_unlocked' => false,
    'gate_reason' => 'requires_upgrade',
    'blind_spot_count' => 2, // or whatever was created
    'blind_spots' => null,   // Hidden
]);
```

### Test: Pro user sees full data

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithBlindSpots($user, 6);

$response = $this->actingAs($user)->getJson('/api/blind-spots');

$response->assertOk();
$response->assertJson([
    'is_unlocked' => true,
    'gate_reason' => null,
]);
$response->assertJsonStructure([
    'blind_spots' => [['skill', 'trend', 'current_rate']],
]);
```

### Test: User with insufficient data

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithBlindSpots($user, 3); // Below threshold

$response = $this->actingAs($user)->getJson('/api/blind-spots');

$response->assertOk();
$response->assertJson([
    'has_enough_data' => false,
    'gate_reason' => 'insufficient_data',
    'sessions_until_insights' => 2,
]);
```

### Test: Teaser endpoint

```php
$user = User::factory()->create(['plan' => 'free']);
createSessionsWithBlindSpots($user, 8);

$response = $this->actingAs($user)->getJson('/api/blind-spots/teaser');

$response->assertOk();
$response->assertJson([
    'show_teaser' => true,
    'blind_spot_count' => 3,
]);
$response->assertJsonMissing(['blind_spots']); // No details leaked
```

---

## Success Criteria

- [ ] GatedBlindSpotAnalysis DTO created
- [ ] BlindSpotService wraps analyzer with gating logic
- [ ] API endpoints return appropriate data per subscription tier
- [ ] Free users see count but not details
- [ ] Pro users see full analysis
- [ ] Teaser component displays correctly
- [ ] Teaser only shows when user has 5+ sessions AND blind spots exist
- [ ] All tests pass
- [ ] No data leakage (details never sent to free users)

---

## Next Steps

After Phase 3:
- Phase 4: Weekly email using this data
- Phase 5: Teaser email triggered at 5th session
- Phase 6: Dashboard visualization
