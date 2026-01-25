<?php

use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/blind-spots', function () {
    it('returns insufficient_data for free user with less than 5 sessions', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(3)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'hasEnoughData' => false,
                'isUnlocked' => false,
                'gateReason' => 'insufficient_data',
                'blindSpots' => null,
            ],
        ]);

        expect($response->json('analysis.sessionsUntilInsights'))->toBe(2);
    });

    it('returns requires_upgrade for free user with 5+ sessions', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Create blind spots with low scores (to trigger blind spot detection)
        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'hasEnoughData' => true,
                'isUnlocked' => false,
                'gateReason' => 'requires_upgrade',
                'blindSpots' => null,
                'improving' => null,
                'slipping' => null,
            ],
        ]);

        // Should show blind spot count but not details
        expect($response->json('analysis.blindSpotCount'))->toBeGreaterThanOrEqual(0);
        expect($response->json('analysis.hasBlindSpots'))->toBeBool();
    });

    it('returns insufficient_data for pro user with less than 5 sessions', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(3)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'hasEnoughData' => false,
                'isUnlocked' => false,
                'gateReason' => 'insufficient_data',
            ],
        ]);
    });

    it('returns full data unlocked for pro user with 5+ sessions', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'hasEnoughData' => true,
                'isUnlocked' => true,
                'gateReason' => null,
            ],
        ]);

        // Should have actual arrays, not null
        expect($response->json('analysis.blindSpots'))->toBeArray();
        expect($response->json('analysis.improving'))->toBeArray();
        expect($response->json('analysis.slipping'))->toBeArray();
        expect($response->json('analysis.stable'))->toBeArray();
    });

    it('returns full data unlocked for unlimited user', function () {
        $user = User::factory()->create(['plan' => 'unlimited']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withGoodScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'analysis' => [
                'hasEnoughData' => true,
                'isUnlocked' => true,
                'gateReason' => null,
            ],
        ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/blind-spots');

        $response->assertUnauthorized();
    });
});

describe('GET /api/blind-spots/teaser', function () {
    it('returns showTeaser false for pro user', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/teaser');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'showTeaser' => false,
            'teaser' => null,
        ]);
    });

    it('returns showTeaser false for free user with insufficient data', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(3)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/teaser');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'showTeaser' => false,
            'teaser' => null,
        ]);
    });

    it('returns teaser data for free user with blind spots', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/teaser');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'showTeaser' => true,
        ]);

        expect($response->json('teaser'))->toHaveKeys([
            'blindSpotCount',
            'hasImprovements',
            'hasRegressions',
            'totalSessions',
        ]);
    });
});

describe('GET /api/blind-spots/status', function () {
    it('returns correct status flags for free user without enough data', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(2)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/status');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => [
                'hasEnoughData' => false,
                'hasProAccess' => false,
                'canAccessFullInsights' => false,
                'showTeaser' => false,
                'totalSessions' => 2,
                'minimumSessions' => 5,
                'sessionsUntilInsights' => 3,
            ],
        ]);
    });

    it('returns correct status flags for free user with enough data', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withLowScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/status');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => [
                'hasEnoughData' => true,
                'hasProAccess' => false,
                'canAccessFullInsights' => false,
                'totalSessions' => 5,
                'sessionsUntilInsights' => 0,
            ],
        ]);
    });

    it('returns correct status flags for pro user with enough data', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create();
        $drill = Drill::factory()->forMode($mode)->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        BlindSpot::factory()
            ->count(15)
            ->forUser($user)
            ->forDrill($drill)
            ->withGoodScores()
            ->create();

        $response = $this->actingAs($user)->getJson('/api/blind-spots/status');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'status' => [
                'hasEnoughData' => true,
                'hasProAccess' => true,
                'canAccessFullInsights' => true,
                'showTeaser' => false,
                'totalSessions' => 5,
                'sessionsUntilInsights' => 0,
            ],
        ]);
    });
});
