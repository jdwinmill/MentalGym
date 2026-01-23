# Phase 3: Events & Analytics

## Tasks

1. Create `SessionCompleted` event
2. Create `RecordSessionCompletion` listener
3. Register in EventServiceProvider
4. Implement level up logic

---

## Session Completed Event

Track session completion as a lightweight event. One write at the end, big longitudinal value.

### Event Schema

```
session_completed
├── user_id
├── practice_mode_id
├── drills_completed       → count
├── total_duration         → seconds
├── completed_at
└── scores[]               → array of drill scores (for Blind Spots)
```

### What This Enables

| Metric | Query |
|--------|-------|
| **Completion rate** | started sessions vs. session_completed events |
| **Session frequency** | session_completed per user per week |
| **Average session length** | avg(total_duration) |
| **Skill progression** | scores[] over time by practice_mode |
| **Mode popularity** | session_completed grouped by practice_mode_id |
| **User engagement** | sessions per user, time between sessions |

---

## Event Class

```php
// app/Events/SessionCompleted.php

namespace App\Events;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public TrainingSession $session,
        public int $drillsCompleted,
        public int $totalDurationSeconds,
        public array $scores = []
    ) {}
}
```

---

## Event Listener

```php
// app/Listeners/RecordSessionCompletion.php

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Models\SessionCompletedEvent;
use App\Models\UserModeProgress;

class RecordSessionCompletion
{
    public function handle(SessionCompleted $event): void
    {
        // 1. Record the completion event
        $this->recordEvent($event);

        // 2. Update user progress and check level up
        $this->updateUserProgress($event);
    }

    private function recordEvent(SessionCompleted $event): void
    {
        SessionCompletedEvent::create([
            'user_id' => $event->user->id,
            'practice_mode_id' => $event->session->practice_mode_id,
            'training_session_id' => $event->session->id,
            'drills_completed' => $event->drillsCompleted,
            'total_duration_seconds' => $event->totalDurationSeconds,
            'scores' => $event->scores,
            'completed_at' => now(),
        ]);
    }

    private function updateUserProgress(SessionCompleted $event): void
    {
        $progress = UserModeProgress::firstOrCreate(
            [
                'user_id' => $event->user->id,
                'practice_mode_id' => $event->session->practice_mode_id,
            ],
            [
                'current_level' => 1,
                'total_sessions' => 0,
                'total_drills_completed' => 0,
                'sessions_at_current_level' => 0,
            ]
        );

        $progress->increment('total_sessions');
        $progress->increment('total_drills_completed', $event->drillsCompleted);
        $progress->increment('sessions_at_current_level');

        // Level up check: every 3 sessions = level up (simple for MVP)
        if ($progress->sessions_at_current_level >= 3) {
            $progress->current_level++;
            $progress->sessions_at_current_level = 0;
        }

        $progress->save();
    }
}
```

---

## Register in EventServiceProvider

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    // ... existing events

    \App\Events\SessionCompleted::class => [
        \App\Listeners\RecordSessionCompletion::class,
    ],
];
```

---

## Scores Array Structure

```json
{
    "scores": [
        { "drill_id": 1, "drill_name": "Bad News Delivery", "score": 85 },
        { "drill_id": 2, "drill_name": "Executive Pushback", "score": 72 },
        { "drill_id": 3, "drill_name": "Pressure Test", "score": 90 }
    ]
}
```

---

## Level Up Logic

### Current Implementation (Simple)

```
Every 3 completed sessions = Level up
```

### UserModeProgress Model

```php
// Existing or updated table
user_id
practice_mode_id
current_level               // 1, 2, 3, 4, 5...
total_sessions              // Increment on session complete
total_drills_completed      // Increment by drills_completed
sessions_at_current_level   // Reset to 0 on level up
```

### Level Passed to AI

```php
// In PracticeAIService::buildGeneratePrompt
$level = $user->modeProgress($drill->practice_mode_id)?->current_level ?? 1;

return "User level: {$level}\n\nGenerate a scenario...";
```

This allows instruction sets to reference `{level}` for difficulty scaling:

```
Generate a scenario appropriate for level {level}.
- Level 1-2: Simple, clear-cut situations
- Level 3-4: Nuanced, multiple stakeholders
- Level 5+: High stakes, ambiguous, time pressure
```

---

## Future: Blind Spots Integration

The `scores[]` array enables Blind Spots analysis:

```php
// Find drills where user consistently scores low
public function getBlindSpots(User $user, int $days = 30): Collection
{
    return SessionCompletedEvent::query()
        ->where('user_id', $user->id)
        ->where('completed_at', '>=', now()->subDays($days))
        ->get()
        ->flatMap(fn($event) => $event->scores)
        ->groupBy('drill_id')
        ->map(fn($scores) => [
            'drill_name' => $scores->first()['drill_name'],
            'avg_score' => $scores->avg('score'),
            'attempts' => $scores->count(),
        ])
        ->sortBy('avg_score')
        ->take(3);  // Top 3 weak areas
}
```

**Output:**
```php
[
    ['drill_name' => 'Executive Pushback', 'avg_score' => 62, 'attempts' => 5],
    ['drill_name' => 'Bad News Delivery', 'avg_score' => 71, 'attempts' => 4],
    ['drill_name' => 'Delegation', 'avg_score' => 75, 'attempts' => 3],
]
```

---

## Analytics Queries

### Completion Rate

```php
$started = TrainingSession::where('user_id', $userId)
    ->where('started_at', '>=', now()->subDays(30))
    ->count();

$completed = SessionCompletedEvent::where('user_id', $userId)
    ->where('completed_at', '>=', now()->subDays(30))
    ->count();

$completionRate = $started > 0 ? ($completed / $started) * 100 : 0;
```

### Session Frequency

```php
$sessionsPerWeek = SessionCompletedEvent::where('user_id', $userId)
    ->where('completed_at', '>=', now()->subWeeks(4))
    ->count() / 4;
```

### Average Session Length

```php
$avgLength = SessionCompletedEvent::where('user_id', $userId)
    ->avg('total_duration_seconds');

$avgMinutes = round($avgLength / 60);
```

---

## Files to Create

- `app/Events/SessionCompleted.php`
- `app/Listeners/RecordSessionCompletion.php`

## Files to Modify

- `app/Providers/EventServiceProvider.php` - register listener
