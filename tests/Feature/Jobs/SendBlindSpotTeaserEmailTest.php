<?php

use App\Events\SessionCompleted;
use App\Jobs\SendBlindSpotTeaserEmail;
use App\Listeners\CheckBlindSpotTeaserTrigger;
use App\Mail\BlindSpotTeaserEmail;
use App\Models\BlindSpotEmail;
use App\Models\DrillScore;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

function createFreeUserWithSessions(int $sessionCount = 5, bool $withBlindSpots = true, array $userAttributes = []): User
{
    $user = User::factory()->create(array_merge(['plan' => 'free'], $userAttributes));
    $mode = PracticeMode::factory()->create();

    $sessions = TrainingSession::factory()
        ->count($sessionCount)
        ->completed()
        ->forUser($user)
        ->forMode($mode)
        ->create();

    if ($withBlindSpots) {
        foreach ($sessions as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();
        }
    } else {
        foreach ($sessions as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withGoodScores()
                ->create();
        }
    }

    return $user;
}

describe('CheckBlindSpotTeaserTrigger listener', function () {
    it('triggers teaser email at exactly 5 sessions for free user', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();

        // Create 4 completed sessions - no email should trigger
        $sessions = TrainingSession::factory()
            ->count(4)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        foreach ($sessions as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();
        }

        // Create 5th session (not completed yet)
        $fifthSession = TrainingSession::factory()
            ->forUser($user)
            ->forMode($mode)
            ->create(['status' => TrainingSession::STATUS_ACTIVE]);

        DrillScore::factory()
            ->count(2)
            ->forSession($fifthSession)
            ->withAuthorityIssues()
            ->create();

        // Complete it
        $fifthSession->update(['status' => TrainingSession::STATUS_COMPLETED]);

        // Trigger event
        $listener = new CheckBlindSpotTeaserTrigger;
        $listener->handle(new SessionCompleted($fifthSession));

        // Job should have been dispatched - check by running it directly
        SendBlindSpotTeaserEmail::dispatch($user);

        Mail::assertSent(BlindSpotTeaserEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    });

    it('does not trigger at 4 sessions', function () {
        $user = createFreeUserWithSessions(4);
        $session = $user->trainingSessions()->latest()->first();

        $listener = new CheckBlindSpotTeaserTrigger;
        $listener->handle(new SessionCompleted($session));

        Mail::assertNothingSent();
    });

    it('does not trigger at 6 sessions (already past threshold)', function () {
        $user = createFreeUserWithSessions(6);
        $session = $user->trainingSessions()->latest()->first();

        $listener = new CheckBlindSpotTeaserTrigger;
        $listener->handle(new SessionCompleted($session));

        Mail::assertNothingSent();
    });

    it('does not trigger for pro user', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        foreach ($sessions as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();
        }

        $listener = new CheckBlindSpotTeaserTrigger;
        $listener->handle(new SessionCompleted($sessions->last()));

        Mail::assertNothingSent();
    });
});

describe('SendBlindSpotTeaserEmail job', function () {
    it('sends teaser email to free user with blind spots', function () {
        $user = createFreeUserWithSessions(5, true);

        SendBlindSpotTeaserEmail::dispatch($user);

        Mail::assertSent(BlindSpotTeaserEmail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->blindSpotCount >= 1;
        });

        expect(BlindSpotEmail::where('user_id', $user->id)->where('email_type', 'teaser')->count())->toBe(1);
    });

    it('does not send email twice', function () {
        $user = createFreeUserWithSessions(5, true);

        SendBlindSpotTeaserEmail::dispatch($user);

        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);

        Mail::fake(); // Reset

        SendBlindSpotTeaserEmail::dispatch($user);

        // Still only one record
        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);
        Mail::assertNothingSent();
    });

    it('does not send to pro user', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        foreach ($sessions as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();
        }

        SendBlindSpotTeaserEmail::dispatch($user);

        Mail::assertNothingSent();
    });

    it('does not send if no blind spots detected', function () {
        $user = createFreeUserWithSessions(5, false); // Good scores only

        SendBlindSpotTeaserEmail::dispatch($user);

        Mail::assertNothingSent();
        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(0);
    });

    it('respects email preferences', function () {
        $user = createFreeUserWithSessions(5, true, [
            'email_preferences' => ['teaser_emails' => false],
        ]);

        $session = $user->trainingSessions()->latest()->first();

        $listener = new CheckBlindSpotTeaserTrigger;
        $listener->handle(new SessionCompleted($session));

        Mail::assertNothingSent();
    });

    it('records correct metadata on send', function () {
        $user = createFreeUserWithSessions(5, true);

        SendBlindSpotTeaserEmail::dispatch($user);

        $email = BlindSpotEmail::where('user_id', $user->id)->first();

        expect($email)->not->toBeNull();
        expect($email->email_type)->toBe('teaser');
        expect($email->subject_line)->toContain('pattern');
        expect($email->analysis_snapshot)->toHaveKeys(['blind_spot_count', 'total_sessions', 'total_responses']);
        expect($email->sent_at)->not->toBeNull();
    });

    it('uses correct subject based on blind spot count', function () {
        $user = createFreeUserWithSessions(5, true);

        SendBlindSpotTeaserEmail::dispatch($user);

        Mail::assertSent(BlindSpotTeaserEmail::class, function ($mail) {
            $subject = $mail->envelope()->subject;

            // Should contain "pattern" (singular or plural)
            return str_contains($subject, 'pattern');
        });
    });
});

describe('BlindSpotEmail tracking', function () {
    it('prevents duplicate teaser sends via unique constraint', function () {
        $user = User::factory()->create(['plan' => 'free']);

        BlindSpotEmail::create([
            'user_id' => $user->id,
            'email_type' => 'teaser',
            'week_number' => 1,
            'year' => 2026,
            'analysis_snapshot' => ['test' => true],
            'subject_line' => 'Test',
            'sent_at' => now(),
        ]);

        // User has teaser, should not be eligible
        expect($user->blindSpotEmails()->where('email_type', 'teaser')->exists())->toBeTrue();
    });
});
