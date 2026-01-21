# Phase 5: Free User Teaser Email

## Overview

Send a one-time teaser email to free users when they hit 5 sessions. This is the moment we have enough data to identify blind spots, creating a natural upgrade trigger. The email shows they have blind spots but doesn't reveal what they are.

## Context

- Phase 1-4: Pro users get full insights via weekly email
- Phase 5: Free users get a single teaser email to drive upgrades
- This email is sent ONCE per user, triggered by hitting the 5-session threshold

## Dependencies

- Phases 1-3 complete (scoring, analysis, gating)
- Email infrastructure from Phase 4
- Session completion tracking

## Goals

1. Detect when free user hits 5 sessions
2. Send compelling teaser email with redacted blind spots
3. Track send to prevent duplicates
4. Clear CTA to upgrade

---

## Trigger Logic

The teaser email is sent when:

1. User is on free plan
2. User just completed their 5th session
3. User has at least 1 blind spot identified
4. User hasn't received this email before
5. User hasn't disabled teaser emails

---

## Email Structure

```
Subject: We found {{ count }} patterns in your training

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SHARPSTACK

You've completed 5 sessions. That's enough data to see patterns.

We analyzed {{ responseCount }} responses and found:

┌─────────────────────────────────────────┐
│                                         │
│   {{ count }} BLIND SPOTS IDENTIFIED    │
│                                         │
│   • ██████████████████████████          │
│   • ██████████████████████████          │
│   • ██████████████████                  │
│                                         │
└─────────────────────────────────────────┘

These patterns are showing up consistently in your responses.
They're costing you clarity, authority, or impact.

Pro members see exactly what these patterns are,
where they show up, and how to fix them.

[ Unlock Your Blind Spots ]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Keep training. Your patterns become clearer with more data.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Subject Line Variations

| Blind Spot Count | Subject Line |
|------------------|--------------|
| 1 | "We found a pattern in your training" |
| 2 | "We found 2 patterns in your training" |
| 3+ | "We found {{ count }} patterns in your training" |

Alternative subject lines to test:
- "5 sessions complete. Here's what we found."
- "Your blind spots are ready"
- "We've been watching your patterns"
- "Something's showing up in your responses"

---

## Event Listener

Trigger the email when a session is completed:

```php
<?php

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Jobs\SendBlindSpotTeaserEmail;
use App\Models\User;

class CheckBlindSpotTeaserTrigger
{
    public function handle(SessionCompleted $event): void
    {
        $user = $event->session->user;

        if ($this->shouldSendTeaser($user)) {
            SendBlindSpotTeaserEmail::dispatch($user);
        }
    }

    private function shouldSendTeaser(User $user): bool
    {
        // Must be free user
        if ($user->plan !== 'free') {
            return false;
        }

        // Must have exactly 5 sessions (just crossed threshold)
        $sessionCount = $user->practiceSessions()->completed()->count();
        if ($sessionCount !== 5) {
            return false;
        }

        // Must not have received teaser already
        if ($user->blindSpotEmails()->where('email_type', 'teaser')->exists()) {
            return false;
        }

        // Must have teaser emails enabled
        if (($user->email_preferences['teaser_emails'] ?? true) === false) {
            return false;
        }

        return true;
    }
}
```

---

## Job: SendBlindSpotTeaserEmail

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\BlindSpotEmail;
use App\Services\BlindSpotAnalyzer;
use App\Mail\BlindSpotTeaserEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBlindSpotTeaserEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(BlindSpotAnalyzer $analyzer): void
    {
        // Double-check eligibility (in case of race conditions)
        if (!$this->isEligible()) {
            return;
        }

        // Run analysis
        $analysis = $analyzer->analyze($this->user);

        // Only send if there are actual blind spots
        if (!$analysis->hasBlindSpots()) {
            return;
        }

        // Send email
        Mail::to($this->user)->send(
            new BlindSpotTeaserEmail(
                $this->user,
                $analysis->getBlindSpotCount(),
                $analysis->totalResponses
            )
        );

        // Record send
        BlindSpotEmail::create([
            'user_id' => $this->user->id,
            'email_type' => 'teaser',
            'week_number' => now()->isoWeek(),
            'year' => now()->year,
            'analysis_snapshot' => [
                'blind_spot_count' => $analysis->getBlindSpotCount(),
                'total_sessions' => $analysis->totalSessions,
                'total_responses' => $analysis->totalResponses,
            ],
            'subject_line' => $this->getSubjectLine($analysis->getBlindSpotCount()),
            'sent_at' => now(),
        ]);
    }

    private function isEligible(): bool
    {
        return $this->user->plan === 'free'
            && !$this->user->blindSpotEmails()->where('email_type', 'teaser')->exists();
    }

    private function getSubjectLine(int $count): string
    {
        if ($count === 1) {
            return "We found a pattern in your training";
        }
        return "We found {$count} patterns in your training";
    }
}
```

---

## Mailable: BlindSpotTeaserEmail

```php
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BlindSpotTeaserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $blindSpotCount,
        public int $totalResponses,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->blindSpotCount === 1
            ? "We found a pattern in your training"
            : "We found {$this->blindSpotCount} patterns in your training";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.blind-spots.teaser',
            with: [
                'userName' => $this->user->first_name,
                'blindSpotCount' => $this->blindSpotCount,
                'totalResponses' => $this->totalResponses,
                'upgradeUrl' => route('subscription.upgrade'),
                'unsubscribeUrl' => route('email.unsubscribe', ['type' => 'teaser_emails']),
            ],
        );
    }
}
```

---

## Email Template

```blade
{{-- resources/views/emails/blind-spots/teaser.blade.php --}}

<x-mail::message>
# SHARPSTACK

Hey {{ $userName }},

You've completed 5 sessions. That's enough data to see patterns.

We analyzed **{{ $totalResponses }} responses** and found:

<x-mail::panel>
<div style="text-align: center; padding: 20px 0;">
<div style="font-size: 24px; font-weight: bold; color: #e94560; margin-bottom: 16px;">
{{ $blindSpotCount }} BLIND {{ $blindSpotCount === 1 ? 'SPOT' : 'SPOTS' }} IDENTIFIED
</div>

<div style="font-family: monospace; color: #666; line-height: 1.8;">
• ██████████████████████████<br>
• ██████████████████████████<br>
@if($blindSpotCount > 2)
• ██████████████████<br>
@endif
</div>
</div>
</x-mail::panel>

These patterns are showing up consistently in your responses. They're costing you clarity, authority, or impact — and you can't see them.

**Pro members see exactly what these patterns are, where they show up, and how to fix them.**

<x-mail::button :url="$upgradeUrl">
Unlock Your Blind Spots
</x-mail::button>

---

Keep training. Your patterns become clearer with more data.

<small>
[Unsubscribe from these emails]({{ $unsubscribeUrl }})
</small>

</x-mail::message>
```

---

## Event Registration

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    SessionCompleted::class => [
        CheckBlindSpotTeaserTrigger::class,
    ],
];
```

---

## Session Completed Event

If not already existing:

```php
<?php

namespace App\Events;

use App\Models\PracticeSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PracticeSession $session
    ) {}
}
```

Dispatch this event when a session ends:

```php
// In your session completion logic
event(new SessionCompleted($session));
```

---

## Alternative: Scheduled Check

If you prefer a scheduled approach over event-driven:

```php
<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckPendingTeaserEmails implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Find free users with exactly 5+ sessions who haven't received teaser
        User::query()
            ->where('plan', 'free')
            ->whereHas('practiceSessions', function ($query) {
                $query->selectRaw('COUNT(*) as count')
                    ->havingRaw('COUNT(*) >= 5');
            })
            ->whereDoesntHave('blindSpotEmails', function ($query) {
                $query->where('email_type', 'teaser');
            })
            ->where('email_preferences->teaser_emails', '!=', false)
            ->each(function ($user) {
                SendBlindSpotTeaserEmail::dispatch($user);
            });
    }
}

// Schedule daily
$schedule->job(new CheckPendingTeaserEmails)->dailyAt('10:00');
```

---

## Copy Variations

### Standard (analytical)
> "We analyzed 24 responses and found 3 blind spots. These patterns are showing up consistently."

### Urgent (scarcity)
> "3 patterns identified. They're affecting your responses right now — and you can't see them."

### Curiosity (mystery)
> "Something's showing up in your responses. We found it. Do you want to know what it is?"

### Social proof
> "Most users have 2-3 blind spots. You have 3. Pro members fix theirs in weeks."

### Direct challenge
> "You've trained 5 times. You still have the same blind spots. Want to see what they are?"

---

## A/B Testing Setup (Optional)

```php
// In the job
$variant = collect(['standard', 'urgent', 'curiosity'])->random();

Mail::to($this->user)->send(
    new BlindSpotTeaserEmail($this->user, $count, $responses, $variant)
);

// Track variant
BlindSpotEmail::create([
    // ...
    'metadata' => ['variant' => $variant],
]);
```

---

## File Structure

```
app/
├── Events/
│   └── SessionCompleted.php
├── Jobs/
│   └── SendBlindSpotTeaserEmail.php
├── Listeners/
│   └── CheckBlindSpotTeaserTrigger.php
├── Mail/
│   └── BlindSpotTeaserEmail.php
├── Providers/
│   └── EventServiceProvider.php (updated)
resources/
└── views/
    └── emails/
        └── blind-spots/
            └── teaser.blade.php
```

---

## Testing

### Test: Email sent at 5th session

```php
$user = User::factory()->create(['plan' => 'free']);

// Complete 4 sessions - no email
for ($i = 1; $i <= 4; $i++) {
    $session = createSessionWithScores($user);
    event(new SessionCompleted($session));
}

Mail::assertNothingSent();

// Complete 5th session - email sent
$session = createSessionWithScores($user);
event(new SessionCompleted($session));

Mail::assertSent(BlindSpotTeaserEmail::class, function ($mail) use ($user) {
    return $mail->user->id === $user->id;
});
```

### Test: Email not sent twice

```php
$user = User::factory()->create(['plan' => 'free']);
createSessionsWithBlindSpots($user, 5);

event(new SessionCompleted($user->practiceSessions->last()));
event(new SessionCompleted($user->practiceSessions->last()));

Mail::assertSent(BlindSpotTeaserEmail::class, 1); // Only once
```

### Test: Pro users don't receive teaser

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithBlindSpots($user, 5);

event(new SessionCompleted($user->practiceSessions->last()));

Mail::assertNotSent(BlindSpotTeaserEmail::class);
```

### Test: No email if no blind spots

```php
$user = User::factory()->create(['plan' => 'free']);
// Create sessions with perfect scores (no blind spots)
createSessionsWithoutBlindSpots($user, 5);

event(new SessionCompleted($user->practiceSessions->last()));

Mail::assertNotSent(BlindSpotTeaserEmail::class);
```

### Test: Respects email preferences

```php
$user = User::factory()->create([
    'plan' => 'free',
    'email_preferences' => ['teaser_emails' => false],
]);
createSessionsWithBlindSpots($user, 5);

event(new SessionCompleted($user->practiceSessions->last()));

Mail::assertNotSent(BlindSpotTeaserEmail::class);
```

---

## Success Criteria

- [ ] SessionCompleted event exists and fires
- [ ] Listener triggers at exactly 5 sessions
- [ ] Job sends email only to eligible users
- [ ] Email template renders with redacted blind spots
- [ ] Blind spot count displayed correctly
- [ ] Duplicate sends prevented
- [ ] Pro users excluded
- [ ] Unsubscribe link works
- [ ] BlindSpotEmail record created
- [ ] All tests pass

---

## Conversion Tracking

To measure effectiveness:

```php
// When user upgrades, check if they received teaser
$receivedTeaser = $user->blindSpotEmails()
    ->where('email_type', 'teaser')
    ->exists();

// Log conversion
ConversionEvent::create([
    'user_id' => $user->id,
    'from_plan' => 'free',
    'to_plan' => 'pro',
    'teaser_received' => $receivedTeaser,
    'days_since_teaser' => $receivedTeaser 
        ? $user->blindSpotEmails()->where('email_type', 'teaser')->first()->sent_at->diffInDays(now())
        : null,
]);
```

This answers: "Do users who receive the teaser convert at higher rates?"
