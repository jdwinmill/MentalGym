<?php

use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function createUserWithSessionsAndScores(array $userAttributes = [], int $sessionCount = 5, int $daysAgo = 2): User
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

    BlindSpot::factory()
        ->count($sessionCount * 3)
        ->forUser($user)
        ->forDrill($drill)
        ->withLowScores()
        ->createdAt(now()->subDays($daysAgo))
        ->create();

    return $user;
}

describe('GET /blind-spots', function () {
    it('requires authentication', function () {
        $response = $this->get('/blind-spots');

        $response->assertRedirect('/login');
    });

    it('renders blind spots page for authenticated user', function () {
        $user = User::factory()->create(['plan' => 'free']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->has('analysis')
            ->has('isPro')
        );
    });

    it('shows full data for pro user with enough sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'pro']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('analysis.isUnlocked', true)
            ->where('isPro', true)
            ->has('history')
        );
    });

    it('shows gated view for free user with enough sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'free']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('analysis.isUnlocked', false)
            ->where('analysis.gateReason', 'requires_upgrade')
            ->where('isPro', false)
            ->where('history', null)
        );
    });

    it('shows insufficient data for user with less than 5 sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'pro'], 3);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('analysis.hasEnoughData', false)
            ->where('analysis.gateReason', 'insufficient_data')
        );
    });

    it('returns historical trends for pro user', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'pro'], 6);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->has('history', 8) // 8 weeks of trend data
            ->has('history.0.week')
        );
    });

    it('does not return historical trends for free user', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'free'], 6);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('history', null)
        );
    });

    it('returns unlimited user as isPro', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'unlimited']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('isPro', true)
            ->where('analysis.isUnlocked', true)
        );
    });
});

describe('BlindSpotService historical trends', function () {
    it('calculates weekly scores', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        // Create sessions across multiple weeks
        for ($week = 0; $week < 3; $week++) {
            TrainingSession::factory()
                ->completed()
                ->forUser($user)
                ->forMode($mode)
                ->create([
                    'created_at' => now()->subWeeks($week),
                ]);

            BlindSpot::factory()
                ->count(5)
                ->forUser($user)
                ->forDrill($drill)
                ->withLowScores()
                ->createdAt(now()->subWeeks($week))
                ->create();
        }

        $service = app(\App\Services\BlindSpotService::class);
        $trends = $service->getHistoricalTrends($user, 4);

        expect($trends)->toHaveCount(4);
        expect($trends[3]['week'])->not->toBeEmpty();

        // At least one week should have data
        $hasData = collect($trends)->some(fn ($t) => $t['data'] !== null);
        expect($hasData)->toBeTrue();
    });
});
