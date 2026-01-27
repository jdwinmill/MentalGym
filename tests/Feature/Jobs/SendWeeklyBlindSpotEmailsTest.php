<?php

use App\Jobs\SendWeeklyBlindSpotEmails;
use App\Mail\WeeklyBlindSpotReport;
use App\Models\BlindSpot;
use App\Models\BlindSpotEmail;
use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

function createUserWithSessions(array $userAttributes = [], int $sessionCount = 5, int $daysAgo = 2): User
{
    $user = User::factory()->create($userAttributes);
    $mode = PracticeMode::factory()->create();
    $drill = Drill::factory()->forMode($mode)->create();

    TrainingSession::factory()
        ->count($sessionCount)
        ->completed()
        ->forUser($user)
        ->forMode($mode)
        ->create([
            'created_at' => now()->subDays($daysAgo),
        ]);

    // Create a dimension that we'll use for all blind spots
    // Analysis requires at least 3 records per dimension to be counted
    $dimension = \App\Models\SkillDimension::factory()->create();

    // Add blind spots with low scores to make blind spots detectable
    // All use the same dimension so we meet the 3+ threshold
    BlindSpot::factory()
        ->count($sessionCount * 3)
        ->forUser($user)
        ->forDrill($drill)
        ->forDimension($dimension->key)
        ->withLowScores()
        ->createdAt(now()->subDays($daysAgo))
        ->create();

    return $user;
}

describe('SendWeeklyBlindSpotEmails job', function () {
    it('sends email to pro user with recent sessions', function () {
        $user = createUserWithSessions(['plan' => 'pro']);

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertSent(WeeklyBlindSpotReport::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);
    });

    it('sends email to unlimited user with recent sessions', function () {
        $user = createUserWithSessions(['plan' => 'unlimited']);

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertSent(WeeklyBlindSpotReport::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    });

    it('does not send email to free user', function () {
        createUserWithSessions(['plan' => 'free']);

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertNothingSent();
        expect(BlindSpotEmail::count())->toBe(0);
    });

    it('does not send email twice in same week', function () {
        $user = createUserWithSessions(['plan' => 'pro']);

        // Send first email
        SendWeeklyBlindSpotEmails::dispatchSync();

        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);

        Mail::fake(); // Reset mail fake

        // Try to send again
        SendWeeklyBlindSpotEmails::dispatchSync();

        // Still only one email record
        expect(BlindSpotEmail::where('user_id', $user->id)->count())->toBe(1);
        Mail::assertNothingSent();
    });

    it('does not send to user without recent sessions', function () {
        // Sessions from 2 weeks ago
        createUserWithSessions(['plan' => 'pro'], 6, 14);

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertNothingSent();
        expect(BlindSpotEmail::count())->toBe(0);
    });

    it('does not send to unsubscribed user', function () {
        createUserWithSessions([
            'plan' => 'pro',
            'email_preferences' => ['weekly_report' => false],
        ]);

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertNothingSent();
        expect(BlindSpotEmail::count())->toBe(0);
    });

    it('does not send to user with insufficient data', function () {
        // Create user with only 1 response (need 6 minimum)
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(2)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create([
                'created_at' => now()->subDays(2),
            ]);

        // Only 3 responses (need 6)
        BlindSpot::factory()
            ->count(3)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->createdAt(now()->subDays(2))
            ->create();

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertNothingSent();
        expect(BlindSpotEmail::count())->toBe(0);
    });

    it('records email send with correct metadata', function () {
        $user = createUserWithSessions(['plan' => 'pro']);

        SendWeeklyBlindSpotEmails::dispatchSync();

        $email = BlindSpotEmail::where('user_id', $user->id)->first();

        expect($email)->not->toBeNull();
        expect($email->email_type)->toBe('weekly_report');
        expect($email->week_number)->toBe(now()->isoWeek());
        expect($email->year)->toBe(now()->year);
        expect($email->subject_line)->not->toBeEmpty();
        expect($email->analysis_snapshot)->toBeArray();
        expect($email->sent_at)->not->toBeNull();
    });

    it('sends to multiple eligible users', function () {
        $user1 = createUserWithSessions(['plan' => 'pro']);
        $user2 = createUserWithSessions(['plan' => 'unlimited']);
        createUserWithSessions(['plan' => 'free']); // Should be skipped

        SendWeeklyBlindSpotEmails::dispatchSync();

        Mail::assertSent(WeeklyBlindSpotReport::class, 2);
        expect(BlindSpotEmail::count())->toBe(2);
    });
});

describe('BlindSpotEmail model', function () {
    it('tracks emails per user per week', function () {
        $user = User::factory()->create();

        BlindSpotEmail::create([
            'user_id' => $user->id,
            'email_type' => 'weekly_report',
            'week_number' => 3,
            'year' => 2026,
            'analysis_snapshot' => ['test' => 'data'],
            'subject_line' => 'Test subject',
            'sent_at' => now(),
        ]);

        expect(BlindSpotEmail::weeklyReport()->forWeek(3, 2026)->count())->toBe(1);
        expect(BlindSpotEmail::weeklyReport()->forWeek(4, 2026)->count())->toBe(0);
    });
});
