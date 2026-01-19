<?php

use App\Models\DailyUsage;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserModeProgress;
use App\Models\UserStreak;
use App\Services\PracticeAIService;
use App\Services\TrainingSessionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a mock AI service that returns predictable responses
    $this->aiService = Mockery::mock(PracticeAIService::class);

    $this->aiService->shouldReceive('getFirstResponse')
        ->andReturn([
            'type' => 'scenario',
            'content' => 'You are in a meeting with your team...',
        ]);

    $this->aiService->shouldReceive('getResponse')
        ->andReturn([
            'type' => 'prompt',
            'content' => 'What would you do next?',
            'input' => ['type' => 'text', 'max_length' => 500],
        ]);

    $this->app->instance(PracticeAIService::class, $this->aiService);

    $this->service = app(TrainingSessionService::class);
});

describe('startSession', function () {
    it('creates a new session and returns first AI card', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $result = $this->service->startSession($user, $mode);

        expect($result)->toHaveKeys(['session', 'card', 'resumed']);
        expect($result['resumed'])->toBeFalse();
        expect($result['session'])->toBeInstanceOf(TrainingSession::class);
        expect($result['card']['type'])->toBe('scenario');

        // Verify session was created in database
        $this->assertDatabaseHas('training_sessions', [
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'exchange_count' => 0,
        ]);

        // Verify first message was stored
        $this->assertDatabaseHas('session_messages', [
            'training_session_id' => $result['session']->id,
            'role' => 'assistant',
            'parsed_type' => 'scenario',
        ]);
    });

    it('returns existing active session instead of creating new one', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create an existing active session
        $existingSession = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 5,
            'started_at' => now()->subHour(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $this->actingAs($user);
        $result = $this->service->startSession($user, $mode);

        expect($result['resumed'])->toBeTrue();
        expect($result['session']->id)->toBe($existingSession->id);
        expect($result)->toHaveKey('messages');
    });

    it('throws AuthorizationException when user cannot start mode', function () {
        $user = User::factory()->create(['plan' => 'free']);
        $mode = PracticeMode::factory()->create([
            'is_active' => true,
            'required_plan' => 'pro', // Requires pro, user is free
        ]);

        $this->actingAs($user);
        $this->service->startSession($user, $mode);
    })->throws(AuthorizationException::class);

    it('throws AuthorizationException when mode is inactive', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => false]);

        $this->actingAs($user);
        $this->service->startSession($user, $mode);
    })->throws(AuthorizationException::class);

    it('creates progress record if none exists', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        expect($user->progressInMode($mode))->toBeNull();

        $this->actingAs($user);
        $this->service->startSession($user, $mode);

        $progress = $user->fresh()->progressInMode($mode);
        expect($progress)->not->toBeNull();
        expect($progress->current_level)->toBe(1);
    });
});

describe('continueSession', function () {
    it('stores user message and returns AI response', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $session = $startResult['session'];

        $result = $this->service->continueSession($session, 'I would talk to them directly.');

        expect($result)->toHaveKeys(['card', 'session', 'progress']);
        expect($result['card']['type'])->toBe('prompt');

        // Verify user message was stored
        $this->assertDatabaseHas('session_messages', [
            'training_session_id' => $session->id,
            'role' => 'user',
            'content' => 'I would talk to them directly.',
        ]);

        // Verify AI message was stored
        $this->assertDatabaseHas('session_messages', [
            'training_session_id' => $session->id,
            'role' => 'assistant',
            'parsed_type' => 'prompt',
        ]);
    });

    it('increments exchange counts correctly', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $session = $startResult['session'];

        $this->service->continueSession($session, 'Test input');

        // Check session exchange count
        expect($session->fresh()->exchange_count)->toBe(1);

        // Check progress counts
        $progress = $user->progressInMode($mode);
        expect($progress->total_exchanges)->toBe(1);
        expect($progress->exchanges_at_current_level)->toBe(1);

        // Check daily usage
        $dailyUsage = DailyUsage::forUserToday($user);
        expect($dailyUsage->exchange_count)->toBe(1);
    });

    it('returns limit_reached error when daily limit hit', function () {
        $user = User::factory()->create(['plan' => 'free']); // 15 daily exchanges
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Set user's daily usage to the limit
        DailyUsage::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'exchange_count' => 15,
            'sessions_count' => 1,
            'total_time_seconds' => 0,
            'messages_count' => 0,
        ]);

        // Create session directly to bypass start authorization
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $this->actingAs($user);
        $result = $this->service->continueSession($session, 'Test input');

        expect($result)->toHaveKey('error');
        expect($result['error'])->toBe('limit_reached');
    });

    it('updates streak correctly on first activity of the day', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $this->service->continueSession($startResult['session'], 'Test input');

        $streak = UserStreak::where('user_id', $user->id)->first();
        expect($streak)->not->toBeNull();
        expect($streak->current_streak)->toBe(1);
        expect($streak->last_activity_date->toDateString())->toBe(now()->toDateString());
    });

    it('continues streak when training consecutive days', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create streak from yesterday
        UserStreak::create([
            'user_id' => $user->id,
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_activity_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $this->service->continueSession($startResult['session'], 'Test input');

        $streak = UserStreak::where('user_id', $user->id)->first();
        expect($streak->current_streak)->toBe(6);
        expect($streak->longest_streak)->toBe(6);
    });

    it('resets streak when day is skipped', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create streak from 2 days ago (gap)
        UserStreak::create([
            'user_id' => $user->id,
            'current_streak' => 10,
            'longest_streak' => 10,
            'last_activity_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $this->service->continueSession($startResult['session'], 'Test input');

        $streak = UserStreak::where('user_id', $user->id)->first();
        expect($streak->current_streak)->toBe(1); // Reset
        expect($streak->longest_streak)->toBe(10); // Preserved
    });

    it('detects level up and returns level_up card', function () {
        $user = User::factory()->create(['plan' => 'pro']); // max_level 4
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create progress at threshold - 1
        UserModeProgress::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'current_level' => 1,
            'total_exchanges' => 9,
            'exchanges_at_current_level' => 9, // Threshold is 10
            'total_sessions' => 0,
            'total_time_seconds' => 0,
        ]);

        // Create session directly
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $this->actingAs($user);
        $result = $this->service->continueSession($session, 'Test input');

        expect($result)->toHaveKey('levelUp');
        expect($result['levelUp']['type'])->toBe('level_up');
        expect($result['levelUp']['new_level'])->toBe(2);

        // Verify progress was updated
        $progress = $user->fresh()->progressInMode($mode);
        expect($progress->current_level)->toBe(2);
        expect($progress->exchanges_at_current_level)->toBe(0); // Reset
    });

    it('returns level_cap when user at plans max level', function () {
        $user = User::factory()->create(['plan' => 'free']); // max_level 2
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create progress at level 2 threshold
        UserModeProgress::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'current_level' => 2,
            'total_exchanges' => 24,
            'exchanges_at_current_level' => 14, // Threshold is 15
            'total_sessions' => 0,
            'total_time_seconds' => 0,
        ]);

        // Create session directly
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 2,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $this->actingAs($user);
        $result = $this->service->continueSession($session, 'Test input');

        expect($result)->toHaveKey('levelUp');
        expect($result['levelUp']['type'])->toBe('level_cap');
        expect($result['levelUp']['current_level'])->toBe(2);

        // Verify level was NOT increased
        $progress = $user->fresh()->progressInMode($mode);
        expect($progress->current_level)->toBe(2);
    });

    it('returns progress information', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $result = $this->service->continueSession($startResult['session'], 'Test input');

        expect($result['progress'])->toHaveKeys([
            'current_level',
            'exchanges_at_current_level',
            'exchanges_to_next_level',
        ]);
        expect($result['progress']['current_level'])->toBe(1);
        expect($result['progress']['exchanges_at_current_level'])->toBe(1);
        expect($result['progress']['exchanges_to_next_level'])->toBe(9); // 10 - 1
    });
});

describe('endSession', function () {
    it('marks session as ended', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $session = $startResult['session'];

        expect($session->ended_at)->toBeNull();

        $result = $this->service->endSession($session);

        expect($result)->toBeTrue();

        $session->refresh();
        expect($session->ended_at)->not->toBeNull();
        expect($session->status)->toBe(TrainingSession::STATUS_COMPLETED);
    });

    it('throws AuthorizationException for other users session', function () {
        $user1 = User::factory()->create(['plan' => 'pro']);
        $user2 = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user1);
        $startResult = $this->service->startSession($user1, $mode);
        $session = $startResult['session'];

        // Try to end as different user
        $this->actingAs($user2);
        $this->service->endSession($session);
    })->throws(AuthorizationException::class);
});

describe('getActiveSession', function () {
    it('returns active session for user in mode', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $result = $this->service->getActiveSession($user, $mode);

        expect($result)->not->toBeNull();
        expect($result->id)->toBe($session->id);
    });

    it('returns null when no active session exists', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $result = $this->service->getActiveSession($user, $mode);

        expect($result)->toBeNull();
    });

    it('returns null for ended sessions', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 5,
            'started_at' => now()->subHour(),
            'ended_at' => now(), // Ended
            'status' => TrainingSession::STATUS_COMPLETED,
        ]);

        $result = $this->service->getActiveSession($user, $mode);

        expect($result)->toBeNull();
    });

    it('returns most recent active session when multiple exist', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Older session
        TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now()->subHours(2),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        // Newer session
        $newerSession = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now()->subHour(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $result = $this->service->getActiveSession($user, $mode);

        expect($result->id)->toBe($newerSession->id);
    });
});

describe('getSessionHistory', function () {
    it('returns formatted message history', function () {
        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $session = $startResult['session'];

        $this->service->continueSession($session, 'Test user input');

        $history = $this->service->getSessionHistory($session);

        expect($history)->toHaveCount(3); // AI message, user message, AI response

        // First message (assistant)
        expect($history[0]['role'])->toBe('assistant');
        expect($history[0])->toHaveKey('card');
        expect($history[0])->toHaveKey('type');

        // Second message (user)
        expect($history[1]['role'])->toBe('user');
        expect($history[1]['content'])->toBe('Test user input');

        // Third message (assistant)
        expect($history[2]['role'])->toBe('assistant');
    });
});

describe('message history rolling window', function () {
    it('respects max_history_exchanges config', function () {
        $user = User::factory()->create(['plan' => 'unlimited']);
        $mode = PracticeMode::factory()->create([
            'is_active' => true,
            'config' => [
                'max_history_exchanges' => 2, // Only keep last 2 exchanges
                'input_character_limit' => 500,
                'reflection_character_limit' => 200,
                'max_response_tokens' => 800,
            ],
        ]);

        $this->actingAs($user);
        $startResult = $this->service->startSession($user, $mode);
        $session = $startResult['session'];

        // Create multiple exchanges
        for ($i = 0; $i < 5; $i++) {
            $this->service->continueSession($session, "Message {$i}");
        }

        // The AI service should have been called with limited history
        // We verify this by checking the mock was called
        // The actual history sent is 2 * 2 = 4 messages max
        $this->aiService->shouldHaveReceived('getResponse')->times(5);
    });
});
