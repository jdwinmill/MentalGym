<?php

use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\SkillDimension;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function createUserWithSessionsAndScores(array $userAttributes = [], int $sessionCount = 5, int $daysAgo = 2): User
{
    $user = User::factory()->create($userAttributes);
    $mode = PracticeMode::factory()->create();
    $drill = Drill::factory()->forMode($mode)->create([
        'dimensions' => ['test_dimension'],
    ]);

    // Create the skill dimension
    SkillDimension::factory()->create([
        'key' => 'test_dimension',
        'label' => 'Test Dimension',
        'category' => 'communication',
        'active' => true,
    ]);

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
        ->forDimension('test_dimension')
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
            ->has('pageData')
        );
    });

    it('shows unlocked view for pro user with enough sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'pro']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('pageData.isUnlocked', true)
            ->where('pageData.hasEnoughData', true)
            ->has('pageData.primaryBlindSpot')
        );
    });

    it('shows locked view for free user with enough sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'free']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('pageData.isUnlocked', false)
            ->where('pageData.gateReason', 'requires_pro')
            ->where('pageData.primaryBlindSpot', null)
        );
    });

    it('shows insufficient data for user with less than 5 sessions', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'pro'], 3);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->component('blind-spots/index')
            ->where('pageData.hasEnoughData', false)
            ->where('pageData.gateReason', 'insufficient_data')
        );
    });

    it('returns unlimited user as unlocked', function () {
        $user = createUserWithSessionsAndScores(['plan' => 'unlimited']);

        $response = $this->actingAs($user)->get('/blind-spots');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page->where('pageData.isUnlocked', true)
        );
    });
});

describe('BlindSpotService primary blind spot', function () {
    it('finds the primary blind spot with lowest average score', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create([
            'dimensions' => ['low_score_dim', 'high_score_dim'],
        ]);

        // Create dimensions
        SkillDimension::factory()->create([
            'key' => 'low_score_dim',
            'label' => 'Low Score Dimension',
            'active' => true,
        ]);
        SkillDimension::factory()->create([
            'key' => 'high_score_dim',
            'label' => 'High Score Dimension',
            'active' => true,
        ]);

        // Create sessions
        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Create low scores for one dimension
        BlindSpot::factory()
            ->count(5)
            ->forUser($user)
            ->forDrill($drill)
            ->forDimension('low_score_dim')
            ->withLowScores()
            ->create();

        // Create high scores for another dimension
        BlindSpot::factory()
            ->count(5)
            ->forUser($user)
            ->forDrill($drill)
            ->forDimension('high_score_dim')
            ->withGoodScores()
            ->create();

        $service = app(\App\Services\BlindSpotService::class);
        $blindSpot = $service->findPrimaryBlindSpot($user);

        expect($blindSpot)->not->toBeNull();
        expect($blindSpot->dimensionKey)->toBe('low_score_dim');
    });

    it('deprioritizes improving dimensions', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create([
            'dimensions' => ['improving_dim', 'stable_dim'],
        ]);

        SkillDimension::factory()->create([
            'key' => 'improving_dim',
            'label' => 'Improving Dimension',
            'active' => true,
        ]);
        SkillDimension::factory()->create([
            'key' => 'stable_dim',
            'label' => 'Stable Dimension',
            'active' => true,
        ]);

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Create improving trend (old low, recent high) - slightly lower average
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'improving_dim',
            'score' => 2,
            'created_at' => now()->subDays(10),
        ]);
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'improving_dim',
            'score' => 3,
            'created_at' => now()->subDays(8),
        ]);
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'improving_dim',
            'score' => 6,
            'created_at' => now()->subDays(2),
        ]);
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'improving_dim',
            'score' => 7,
            'created_at' => now()->subDays(1),
        ]);

        // Create stable trend with slightly higher average
        BlindSpot::factory()
            ->count(4)
            ->forUser($user)
            ->forDrill($drill)
            ->forDimension('stable_dim')
            ->create(['score' => 5]);

        $service = app(\App\Services\BlindSpotService::class);
        $blindSpot = $service->findPrimaryBlindSpot($user);

        // Should pick stable_dim because improving_dim is deprioritized
        expect($blindSpot->dimensionKey)->toBe('stable_dim');
    });

    it('returns null when no dimension has 3+ occurrences', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create([
            'dimensions' => ['sparse_dim'],
        ]);

        SkillDimension::factory()->create([
            'key' => 'sparse_dim',
            'label' => 'Sparse Dimension',
            'active' => true,
        ]);

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Only 2 occurrences - not enough
        BlindSpot::factory()
            ->count(2)
            ->forUser($user)
            ->forDrill($drill)
            ->forDimension('sparse_dim')
            ->withLowScores()
            ->create();

        $service = app(\App\Services\BlindSpotService::class);
        $blindSpot = $service->findPrimaryBlindSpot($user);

        expect($blindSpot)->toBeNull();
    });

    it('includes recent suggestions as evidence', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create([
            'dimensions' => ['feedback_dim'],
        ]);

        SkillDimension::factory()->create([
            'key' => 'feedback_dim',
            'label' => 'Feedback Dimension',
            'active' => true,
        ]);

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'feedback_dim',
            'score' => 3,
            'suggestion' => 'First suggestion',
            'created_at' => now()->subDays(3),
        ]);
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'feedback_dim',
            'score' => 4,
            'suggestion' => 'Second suggestion',
            'created_at' => now()->subDays(2),
        ]);
        BlindSpot::create([
            'user_id' => $user->id,
            'drill_id' => $drill->id,
            'dimension_key' => 'feedback_dim',
            'score' => 3,
            'suggestion' => 'Third suggestion',
            'created_at' => now()->subDays(1),
        ]);

        $service = app(\App\Services\BlindSpotService::class);
        $blindSpot = $service->findPrimaryBlindSpot($user);

        expect($blindSpot->recentSuggestions)->toHaveCount(3);
        expect($blindSpot->recentSuggestions[0]['suggestion'])->toBe('Third suggestion'); // Most recent first
        expect($blindSpot->recentSuggestions[0]['drillName'])->not->toBeNull(); // Includes drill context
    });
});
