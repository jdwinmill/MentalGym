<?php

use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\SkillDimension;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserModeProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

describe('GET /practice/{slug}', function () {
    it('requires authentication', function () {
        $mode = PracticeMode::factory()->create();

        $response = $this->get("/practice/{$mode->slug}");

        $response->assertRedirect('/login');
    });

    it('returns 404 for non-existent mode', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/practice/non-existent-slug');

        $response->assertNotFound();
    });

    it('renders the practice mode detail page', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create([
            'name' => 'Test Mode',
            'tagline' => 'Test tagline',
            'description' => 'Test description',
            'sample_scenario' => 'Test scenario',
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('practice/[slug]/show')
            ->where('mode.name', 'Test Mode')
            ->where('mode.tagline', 'Test tagline')
            ->where('mode.description', 'Test description')
            ->where('mode.sample_scenario', 'Test scenario')
        );
    });

    it('includes drills with correct data', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        Drill::factory()->forMode($mode)->create([
            'name' => 'First Drill',
            'position' => 0,
            'timer_seconds' => 60,
            'input_type' => 'text',
        ]);
        Drill::factory()->forMode($mode)->create([
            'name' => 'Second Drill',
            'position' => 1,
            'timer_seconds' => null,
            'input_type' => 'multiple_choice',
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('drills', 2)
            ->where('drills.0.name', 'First Drill')
            ->where('drills.0.timer_seconds', 60)
            ->where('drills.1.name', 'Second Drill')
            ->where('drills.1.timer_seconds', null)
        );
    });

    it('calculates estimated minutes from drill timers', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        // 90 seconds total = 2 minutes (ceiling)
        Drill::factory()->forMode($mode)->create(['position' => 0, 'timer_seconds' => 30]);
        Drill::factory()->forMode($mode)->create(['position' => 1, 'timer_seconds' => 60]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('estimatedMinutes', 2)
        );
    });

    it('uses 60s default for untimed drills in time estimate', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        // 60s (default) + 30s = 90s = 2 minutes
        Drill::factory()->forMode($mode)->create(['position' => 0, 'timer_seconds' => null]);
        Drill::factory()->forMode($mode)->create(['position' => 1, 'timer_seconds' => 30]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('estimatedMinutes', 2)
        );
    });

    it('returns correct userPlan for free user', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('userPlan', 'free')
        );
    });

    it('returns correct userPlan for pro user', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('userPlan', 'pro')
        );
    });

    it('returns hasPatternHistory false when no blind spots', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        Drill::factory()->forMode($mode)->create();

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hasPatternHistory', false)
            ->where('userPatterns', null)
        );
    });

    it('returns hasPatternHistory true with patterns when blind spots exist', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create([
            'dimensions' => ['test_dim'],
        ]);
        SkillDimension::factory()->create([
            'key' => 'test_dim',
            'label' => 'Test Dimension',
        ]);
        BlindSpot::factory()->forUser($user)->forDrill($drill)->create([
            'dimension_key' => 'test_dim',
            'score' => 7,
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hasPatternHistory', true)
            ->has('userPatterns.patterns', 1)
            ->where('userPatterns.patterns.0.dimension_key', 'test_dim')
            ->where('userPatterns.patterns.0.label', 'Test Dimension')
        );
    });

    it('returns modeDimensions from drill dimensions', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        Drill::factory()->forMode($mode)->create([
            'dimensions' => ['dim_one', 'dim_two'],
        ]);
        SkillDimension::factory()->create(['key' => 'dim_one', 'label' => 'Dimension One']);
        SkillDimension::factory()->create(['key' => 'dim_two', 'label' => 'Dimension Two']);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('modeDimensions', 2)
        );
    });

    it('returns canAccess true when mode has no required plan', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create(['required_plan' => null]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canAccess', true)
        );
    });

    it('returns canAccess false when user plan is below required', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create(['required_plan' => 'pro']);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canAccess', false)
        );
    });

    it('returns canAccess true when user plan meets required', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['required_plan' => 'pro']);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('canAccess', true)
        );
    });

    it('returns hasActiveSession true when user has active session', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        TrainingSession::factory()->forUser($user)->forMode($mode)->create([
            'status' => 'active',
            'ended_at' => null,
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hasActiveSession', true)
        );
    });

    it('returns hasActiveSession false when no active session', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('hasActiveSession', false)
        );
    });

    it('returns completedDrillCount from completed sessions', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        Drill::factory()->forMode($mode)->count(3)->create();
        TrainingSession::factory()->forUser($user)->forMode($mode)->completed()->create([
            'drill_index' => 2, // Completed up to drill index 2 (3 drills: 0, 1, 2)
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('completedDrillCount', 3) // drill_index + 1
        );
    });

    it('returns progress data when user has progress', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        UserModeProgress::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'current_level' => 3,
            'total_drills_completed' => 15,
            'total_sessions' => 5,
            'total_exchanges' => 0,
            'exchanges_at_current_level' => 0,
        ]);

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('progress.current_level', 3)
            ->where('progress.total_drills_completed', 15)
            ->where('progress.total_sessions', 5)
            ->where('isFirstTime', false)
        );
    });

    it('returns isFirstTime true when no progress', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $response = $this->actingAs($user)->get("/practice/{$mode->slug}");

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->where('progress', null)
            ->where('isFirstTime', true)
        );
    });
});
