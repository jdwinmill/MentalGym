# Phase 4: Weekly Email — Blind Spots Report

## Overview

Send Pro users a personalized weekly email summarizing their training patterns, blind spots, improvements, and a targeted recommendation for the week ahead. This is the primary delivery mechanism for Blind Spots insights.

## Context

- Phase 1: Scoring infrastructure (responses scored)
- Phase 2: Pattern detection (BlindSpotAnalyzer)
- Phase 3: Pro gating (only Pro users get full insights)
- Phase 4: Deliver insights via weekly email

## Dependencies

- Phases 1-3 complete
- Email provider configured (Resend)
- Scheduled job infrastructure (Laravel scheduler)
- Pro subscription check working

## Goals

1. Create email template for weekly Blind Spots report
2. Build AI prompt that generates personalized insights
3. Create scheduled job to send emails weekly
4. Track email sends to prevent duplicates

---

## Email Structure

```
Subject: Your week: [Primary insight headline]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SHARPSTACK WEEKLY

You completed X sessions this week.

WHAT'S IMPROVING
→ [Skill]: [Specific observation]
→ [Skill]: [Specific observation]

WHAT NEEDS WORK
→ [Skill]: [Specific observation with data]

PATTERN TO WATCH
[1-2 sentences about a recurring pattern, with context]

THIS WEEK'S FOCUS
[Actionable recommendation — one specific thing to try]

[ Start a Session ]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

RECOMMENDED READ
"[Article title]"
[1 sentence description]
[Link]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## Subject Line Generation

Subject lines should be specific and attention-grabbing:

| Pattern | Subject Line |
|---------|--------------|
| Hedging is primary issue | "Your week: Hedging is creeping back" |
| Clear improvement | "Your week: Structure is clicking" |
| Regression detected | "Your week: Brevity slipped — here's why" |
| Stuck pattern | "Your week: Authority is still your gap" |
| Multiple blind spots | "Your week: 3 patterns to watch" |
| Strong week | "Your week: Real progress on composure" |

---

## Database: Email Tracking

Track sent emails to prevent duplicates and enable analytics:

```php
Schema::create('blind_spot_emails', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('email_type', 50);        // 'weekly_report', 'teaser', etc.
    $table->integer('week_number');           // ISO week number
    $table->integer('year');
    $table->json('analysis_snapshot');        // Snapshot of analysis at send time
    $table->string('subject_line');
    $table->timestamp('sent_at');
    $table->timestamp('opened_at')->nullable();
    $table->timestamp('clicked_at')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'email_type', 'week_number', 'year']);
    $table->index(['user_id', 'sent_at']);
});
```

---

## AI-Generated Content

Use Claude to generate the personalized sections. This ensures natural language that doesn't feel templated.

### WeeklyEmailContentGenerator Service

```php
<?php

namespace App\Services;

use App\DTOs\BlindSpotAnalysis;
use App\Models\User;

class WeeklyEmailContentGenerator
{
    public function __construct(
        private ClaudeClient $claude
    ) {}

    public function generate(User $user, BlindSpotAnalysis $analysis): WeeklyEmailContent
    {
        $prompt = $this->buildPrompt($user, $analysis);
        $response = $this->claude->complete($prompt);
        
        return $this->parseResponse($response);
    }

    private function buildPrompt(User $user, BlindSpotAnalysis $analysis): string
    {
        return <<<PROMPT
You are writing a weekly training report email for a professional using SharpStack, a mental fitness training platform.

USER DATA:
- First name: {$user->first_name}
- Sessions this week: {$analysis->sessionsThisWeek}
- Total sessions: {$analysis->totalSessions}

ANALYSIS DATA:
- Blind spots: {$this->formatBlindSpots($analysis->blindSpots)}
- Improving: {$this->formatSkills($analysis->improving)}
- Slipping: {$this->formatSkills($analysis->slipping)}
- Stable: {$this->formatSkills($analysis->stable)}
- Universal patterns: {$this->formatUniversal($analysis->universalPatterns)}
- Biggest gap: {$analysis->biggestGap}
- Biggest win: {$analysis->biggestWin}

ITERATION INSIGHT:
{$this->formatIterationData($analysis)}

Generate the following sections. Be direct, specific, and actionable. No fluff. No motivational clichés. Reference their actual data.

1. SUBJECT_LINE: Short, specific subject line (under 50 chars). Format: "Your week: [insight]"

2. IMPROVING: 1-2 bullet points about what's getting better. Be specific. Reference the skill and what changed. If nothing is improving, skip this section entirely and write "NONE".

3. NEEDS_WORK: 1-2 bullet points about blind spots or slipping skills. Include the frequency (e.g., "8 of 12 responses"). Be direct but not harsh.

4. PATTERN_TO_WATCH: 1-2 sentences about their most important pattern. Add context about WHERE it shows up if available. This should be insightful, not just restating data.

5. WEEKLY_FOCUS: One specific, actionable thing they can do this week. Should be concrete (e.g., "Before submitting any response, delete 'I think' and 'maybe'. See what's left."). Not generic advice.

Respond in this exact JSON format:
{
  "subject_line": "...",
  "improving": ["...", "..."] or [],
  "needs_work": ["...", "..."],
  "pattern_to_watch": "...",
  "weekly_focus": "..."
}
PROMPT;
    }

    private function parseResponse(string $response): WeeklyEmailContent
    {
        $data = json_decode($response, true);
        
        return new WeeklyEmailContent(
            subjectLine: $data['subject_line'],
            improving: $data['improving'],
            needsWork: $data['needs_work'],
            patternToWatch: $data['pattern_to_watch'],
            weeklyFocus: $data['weekly_focus'],
        );
    }
}
```

### WeeklyEmailContent DTO

```php
<?php

namespace App\DTOs;

class WeeklyEmailContent
{
    public function __construct(
        public string $subjectLine,
        public array $improving,
        public array $needsWork,
        public string $patternToWatch,
        public string $weeklyFocus,
    ) {}

    public function hasImprovements(): bool
    {
        return !empty($this->improving);
    }
}
```

---

## Email Mailable

```php
<?php

namespace App\Mail;

use App\DTOs\WeeklyEmailContent;
use App\DTOs\BlindSpotAnalysis;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyBlindSpotReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public WeeklyEmailContent $content,
        public BlindSpotAnalysis $analysis,
        public ?array $recommendedArticle = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->content->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.blind-spots.weekly-report',
            with: [
                'userName' => $this->user->first_name,
                'sessionsThisWeek' => $this->analysis->sessionsThisWeek,
                'improving' => $this->content->improving,
                'needsWork' => $this->content->needsWork,
                'patternToWatch' => $this->content->patternToWatch,
                'weeklyFocus' => $this->content->weeklyFocus,
                'article' => $this->recommendedArticle,
                'startSessionUrl' => route('practice-modes.index'),
                'unsubscribeUrl' => route('email.unsubscribe', ['type' => 'weekly_report']),
            ],
        );
    }
}
```

---

## Email Template

```blade
{{-- resources/views/emails/blind-spots/weekly-report.blade.php --}}

<x-mail::message>
# SHARPSTACK WEEKLY

You completed **{{ $sessionsThisWeek }} {{ Str::plural('session', $sessionsThisWeek) }}** this week.

@if(count($improving) > 0)
## WHAT'S IMPROVING

@foreach($improving as $item)
→ {{ $item }}
@endforeach

@endif
## WHAT NEEDS WORK

@foreach($needsWork as $item)
→ {{ $item }}
@endforeach

---

## PATTERN TO WATCH

{{ $patternToWatch }}

---

## THIS WEEK'S FOCUS

{{ $weeklyFocus }}

<x-mail::button :url="$startSessionUrl">
Start a Session
</x-mail::button>

@if($article)
---

## RECOMMENDED READ

**"{{ $article['title'] }}"**

{{ $article['description'] }}

<x-mail::button :url="$article['url']" color="secondary">
Read Article
</x-mail::button>
@endif

---

<small>
You're receiving this because you have a Pro subscription with weekly reports enabled.
[Unsubscribe from weekly reports]({{ $unsubscribeUrl }})
</small>

</x-mail::message>
```

---

## Scheduled Job

```php
<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\BlindSpotEmail;
use App\Services\BlindSpotAnalyzer;
use App\Services\WeeklyEmailContentGenerator;
use App\Services\ArticleRecommender;
use App\Mail\WeeklyBlindSpotReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeeklyBlindSpotEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        BlindSpotAnalyzer $analyzer,
        WeeklyEmailContentGenerator $contentGenerator,
        ArticleRecommender $articleRecommender,
    ): void {
        $weekNumber = now()->isoWeek();
        $year = now()->year;

        // Get eligible users
        $users = $this->getEligibleUsers($weekNumber, $year);

        foreach ($users as $user) {
            try {
                $this->sendEmailToUser(
                    $user,
                    $analyzer,
                    $contentGenerator,
                    $articleRecommender,
                    $weekNumber,
                    $year
                );
            } catch (\Exception $e) {
                report($e);
                // Continue with other users
            }
        }
    }

    private function getEligibleUsers(int $weekNumber, int $year)
    {
        return User::query()
            // Has Pro subscription
            ->where(function ($query) {
                $query->where('plan', 'pro')
                    ->orWhere('plan', 'unlimited');
            })
            // Has sessions in last 7 days
            ->whereHas('practiceSessions', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })
            // Has enough total sessions for insights
            ->whereHas('practiceSessions', function ($query) {
                $query->havingRaw('COUNT(*) >= 5');
            }, '>=', 5)
            // Hasn't received email this week
            ->whereDoesntHave('blindSpotEmails', function ($query) use ($weekNumber, $year) {
                $query->where('email_type', 'weekly_report')
                    ->where('week_number', $weekNumber)
                    ->where('year', $year);
            })
            // Has weekly emails enabled
            ->where('email_preferences->weekly_report', '!=', false)
            ->get();
    }

    private function sendEmailToUser(
        User $user,
        BlindSpotAnalyzer $analyzer,
        WeeklyEmailContentGenerator $contentGenerator,
        ArticleRecommender $articleRecommender,
        int $weekNumber,
        int $year,
    ): void {
        // Run analysis
        $analysis = $analyzer->analyze($user);

        // Skip if no meaningful data
        if (!$analysis->hasBlindSpots() && empty($analysis->improving)) {
            return;
        }

        // Generate personalized content
        $content = $contentGenerator->generate($user, $analysis);

        // Get recommended article based on biggest gap
        $article = $articleRecommender->recommend($analysis->biggestGap);

        // Send email
        Mail::to($user)->send(
            new WeeklyBlindSpotReport($user, $content, $analysis, $article)
        );

        // Record send
        BlindSpotEmail::create([
            'user_id' => $user->id,
            'email_type' => 'weekly_report',
            'week_number' => $weekNumber,
            'year' => $year,
            'analysis_snapshot' => $analysis->toArray(),
            'subject_line' => $content->subjectLine,
            'sent_at' => now(),
        ]);
    }
}
```

---

## Scheduler

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule): void
{
    // Send weekly blind spot emails on Sunday at 6pm user's timezone
    // For simplicity, using a fixed time (adjust based on your user base)
    $schedule->job(new SendWeeklyBlindSpotEmails)
        ->weeklyOn(Schedule::SUNDAY, '18:00')
        ->timezone('America/New_York');
}
```

---

## Article Recommender Service

Map blind spots to relevant content:

```php
<?php

namespace App\Services;

class ArticleRecommender
{
    private array $articles = [
        'authority' => [
            'title' => 'Why Smart People Hedge (And Why It Hurts Them)',
            'description' => 'The hidden cost of "I think" and "maybe" in professional settings.',
            'url' => '/blog/why-smart-people-hedge',
        ],
        'brevity' => [
            'title' => 'The First Draft Is Always Too Long',
            'description' => 'How to cut your communication in half without losing meaning.',
            'url' => '/blog/first-draft-too-long',
        ],
        'structure' => [
            'title' => 'Frameworks Are Thinking Tools',
            'description' => 'Why STAR, PREP, and other structures make you sound smarter.',
            'url' => '/blog/frameworks-thinking-tools',
        ],
        'composure' => [
            'title' => 'Pressure Reveals Training Gaps',
            'description' => 'What happens to your skills when stakes get real.',
            'url' => '/blog/pressure-reveals-gaps',
        ],
        'directness' => [
            'title' => 'Stop Burying the Lead',
            'description' => 'The first sentence problem and how to fix it.',
            'url' => '/blog/stop-burying-lead',
        ],
        'ownership' => [
            'title' => 'The Blame Reflex',
            'description' => 'How to take responsibility without taking all the heat.',
            'url' => '/blog/blame-reflex',
        ],
        'authenticity' => [
            'title' => 'Rehearsed vs. Prepared',
            'description' => 'The difference between sounding scripted and sounding ready.',
            'url' => '/blog/rehearsed-vs-prepared',
        ],
        'clarity' => [
            'title' => 'The Jargon Trap',
            'description' => 'Why complex language often hides unclear thinking.',
            'url' => '/blog/jargon-trap',
        ],
    ];

    public function recommend(?string $skill): ?array
    {
        if (!$skill || !isset($this->articles[$skill])) {
            return $this->getDefault();
        }

        return $this->articles[$skill];
    }

    private function getDefault(): array
    {
        return [
            'title' => 'The Case for Reps',
            'description' => 'Why practice beats tips every time.',
            'url' => '/blog/case-for-reps',
        ];
    }
}
```

---

## Email Preferences

Add to user preferences to allow unsubscribe:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->json('email_preferences')->nullable()->after('remember_token');
});

// Default preferences
$user->email_preferences = [
    'weekly_report' => true,
    'teaser_emails' => true,
    'product_updates' => true,
];
```

---

## Unsubscribe Handling

```php
// routes/web.php
Route::get('/email/unsubscribe/{type}', [EmailPreferenceController::class, 'unsubscribe'])
    ->name('email.unsubscribe')
    ->middleware('signed');

// Controller
public function unsubscribe(Request $request, string $type)
{
    $user = $request->user();
    
    $preferences = $user->email_preferences ?? [];
    $preferences[$type] = false;
    $user->update(['email_preferences' => $preferences]);

    return view('emails.unsubscribed', ['type' => $type]);
}
```

---

## Example Generated Email Content

**Input Analysis:**
```json
{
  "blindSpots": [{"skill": "authority", "currentRate": 0.67, "primaryIssue": "hedging"}],
  "improving": [{"skill": "structure", "currentRate": 0.25, "baselineRate": 0.55}],
  "slipping": [],
  "biggestGap": "authority",
  "biggestWin": "structure"
}
```

**Generated Content:**
```json
{
  "subject_line": "Your week: Hedging is still showing up",
  "improving": [
    "Structure is clicking. You used PREP in 9 of 12 responses this week, up from 5 of 11 last month."
  ],
  "needs_work": [
    "Authority: 'I think' and 'maybe' appeared in 8 of 12 responses. It softens your message.",
    "Hedging increases under pressure — it's worse in your iteration attempts than first drafts."
  ],
  "pattern_to_watch": "You know how to be direct — your second attempts often strip out the hedging. But you're not doing it the first time. The skill is there. The habit isn't.",
  "weekly_focus": "Before submitting any response this week, read it aloud. Delete every 'I think', 'maybe', and 'probably'. Then submit what's left. See if it changes your clarity."
}
```

---

## File Structure

```
app/
├── DTOs/
│   └── WeeklyEmailContent.php
├── Jobs/
│   └── SendWeeklyBlindSpotEmails.php
├── Mail/
│   └── WeeklyBlindSpotReport.php
├── Models/
│   └── BlindSpotEmail.php
├── Services/
│   ├── WeeklyEmailContentGenerator.php
│   └── ArticleRecommender.php
├── Http/
│   └── Controllers/
│       └── EmailPreferenceController.php
database/
└── migrations/
    ├── xxxx_create_blind_spot_emails_table.php
    └── xxxx_add_email_preferences_to_users.php
resources/
└── views/
    └── emails/
        └── blind-spots/
            └── weekly-report.blade.php
```

---

## Testing

### Test: Email generated with correct content

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithPatterns($user, [
    'hedging' => 0.7,
    'structure' => 0.3,
]);

$analyzer = app(BlindSpotAnalyzer::class);
$generator = app(WeeklyEmailContentGenerator::class);

$analysis = $analyzer->analyze($user);
$content = $generator->generate($user, $analysis);

expect($content->subjectLine)->toContain('hedging')
    ->or->toContain('authority');
expect($content->needsWork)->not->toBeEmpty();
expect($content->weeklyFocus)->not->toBeEmpty();
```

### Test: Email not sent twice in same week

```php
$user = User::factory()->create(['plan' => 'pro']);
createSessionsWithBlindSpots($user, 6);

// Send first email
SendWeeklyBlindSpotEmails::dispatch();

expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);

// Try to send again
SendWeeklyBlindSpotEmails::dispatch();

// Still only one
expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);
```

### Test: Free users don't receive email

```php
$user = User::factory()->create(['plan' => 'free']);
createSessionsWithBlindSpots($user, 10);

SendWeeklyBlindSpotEmails::dispatch();

expect(BlindSpotEmail::where('user_id', $user->id)->exists())->toBeFalse();
Mail::assertNothingSent();
```

### Test: Users without recent sessions don't receive email

```php
$user = User::factory()->create(['plan' => 'pro']);
// Sessions from 2 weeks ago
createSessionsWithBlindSpots($user, 6, weeksAgo: 2);

SendWeeklyBlindSpotEmails::dispatch();

expect(BlindSpotEmail::where('user_id', $user->id)->exists())->toBeFalse();
```

### Test: Unsubscribed users don't receive email

```php
$user = User::factory()->create([
    'plan' => 'pro',
    'email_preferences' => ['weekly_report' => false],
]);
createSessionsWithBlindSpots($user, 6);

SendWeeklyBlindSpotEmails::dispatch();

expect(BlindSpotEmail::where('user_id', $user->id)->exists())->toBeFalse();
```

---

## Success Criteria

- [ ] Migration created for blind_spot_emails table
- [ ] BlindSpotEmail model with relationships
- [ ] WeeklyEmailContentGenerator produces personalized JSON
- [ ] WeeklyBlindSpotReport mailable works
- [ ] Email template renders correctly
- [ ] Scheduled job runs weekly
- [ ] Duplicate sends prevented (week/year tracking)
- [ ] Free users excluded
- [ ] Inactive users excluded (no sessions in 7 days)
- [ ] Unsubscribe works
- [ ] Article recommendations based on blind spot
- [ ] All tests pass

---

## Next Steps

After Phase 4:
- Phase 5: Teaser email for free users at 5th session
- Phase 6: Dashboard visualization
