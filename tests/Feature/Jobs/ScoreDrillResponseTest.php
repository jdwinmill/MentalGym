<?php

use App\Jobs\ScoreDrillResponse;
use App\Models\DrillScore;
use App\Models\PracticeMode;
use App\Models\SessionMessage;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\DrillScoringService;
use App\Services\PracticeAIService;
use App\Services\TrainingSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('ScoreDrillResponse job', function () {
    it('dispatches when user responds to a drill prompt', function () {
        Queue::fake();

        $user = User::factory()->create(['plan' => 'pro']);
        $mode = PracticeMode::factory()->create(['is_active' => true]);

        // Create session with a prompt card that has drill_phase
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        // Store a prompt card with drill_phase
        SessionMessage::create([
            'training_session_id' => $session->id,
            'role' => 'assistant',
            'content' => json_encode([
                'type' => 'prompt',
                'content' => 'Compress this message to 15 words or less.',
                'drill_phase' => 'Compression',
                'is_iteration' => false,
            ]),
            'parsed_type' => 'prompt',
            'sequence' => 1,
        ]);

        // Mock AI service
        $aiService = Mockery::mock(PracticeAIService::class);
        $aiService->shouldReceive('getResponse')
            ->andReturn([
                'type' => 'insight',
                'content' => 'Good compression!',
            ]);
        app()->instance(PracticeAIService::class, $aiService);

        $service = app(TrainingSessionService::class);

        $this->actingAs($user);
        $service->continueSession($session, 'I think maybe we should probably consider the thing.');

        Queue::assertPushed(ScoreDrillResponse::class, function ($job) use ($user, $session) {
            return $job->userId === $user->id
                && $job->trainingSessionId === $session->id
                && $job->drillType === 'compression'
                && $job->drillPhase === 'Compression'
                && $job->isIteration === false;
        });
    });

    it('does not dispatch for non-drill responses (no drill_phase)', function () {
        Queue::fake();

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

        // Store a card without drill_phase
        SessionMessage::create([
            'training_session_id' => $session->id,
            'role' => 'assistant',
            'content' => json_encode([
                'type' => 'scenario',
                'content' => 'Here is a scenario...',
            ]),
            'parsed_type' => 'scenario',
            'sequence' => 1,
        ]);

        $aiService = Mockery::mock(PracticeAIService::class);
        $aiService->shouldReceive('getResponse')
            ->andReturn([
                'type' => 'prompt',
                'content' => 'What would you do?',
            ]);
        app()->instance(PracticeAIService::class, $aiService);

        $service = app(TrainingSessionService::class);

        $this->actingAs($user);
        $service->continueSession($session, 'Test response');

        Queue::assertNotPushed(ScoreDrillResponse::class);
    });

    it('does not dispatch for reflection phases', function () {
        Queue::fake();

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

        // Store a reflection card
        SessionMessage::create([
            'training_session_id' => $session->id,
            'role' => 'assistant',
            'content' => json_encode([
                'type' => 'reflection',
                'content' => 'Reflect on your session...',
                'drill_phase' => 'Session Complete',
            ]),
            'parsed_type' => 'reflection',
            'sequence' => 1,
        ]);

        $aiService = Mockery::mock(PracticeAIService::class);
        $aiService->shouldReceive('getResponse')
            ->andReturn([
                'type' => 'insight',
                'content' => 'Great session!',
            ]);
        app()->instance(PracticeAIService::class, $aiService);

        $service = app(TrainingSessionService::class);

        $this->actingAs($user);
        $service->continueSession($session, 'That was helpful.');

        Queue::assertNotPushed(ScoreDrillResponse::class);
    });

    it('dispatches with is_iteration true when card is an iteration', function () {
        Queue::fake();

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

        // Store an iteration card
        SessionMessage::create([
            'training_session_id' => $session->id,
            'role' => 'assistant',
            'content' => json_encode([
                'type' => 'prompt',
                'content' => 'Try again with more confidence.',
                'drill_phase' => 'Executive Communication',
                'is_iteration' => true,
            ]),
            'parsed_type' => 'prompt',
            'sequence' => 1,
        ]);

        $aiService = Mockery::mock(PracticeAIService::class);
        $aiService->shouldReceive('getResponse')
            ->andReturn([
                'type' => 'insight',
                'content' => 'Much better!',
            ]);
        app()->instance(PracticeAIService::class, $aiService);

        $service = app(TrainingSessionService::class);

        $this->actingAs($user);
        $service->continueSession($session, 'We need to move forward with Option A.');

        Queue::assertPushed(ScoreDrillResponse::class, function ($job) {
            return $job->drillType === 'executive_communication'
                && $job->isIteration === true;
        });
    });
});

describe('DrillScoringService', function () {
    it('returns correct criteria for drill types', function () {
        $service = new DrillScoringService;

        $compressionCriteria = $service->getCriteriaForDrillType('compression');

        expect($compressionCriteria)->toHaveKeys([
            'core_point_captured',
            'concise',
            'under_word_limit',
            'clarity',
            'jargon_removed',
        ]);
    });

    it('returns universal criteria', function () {
        $service = new DrillScoringService;

        $universalCriteria = $service->getUniversalCriteria();

        expect($universalCriteria)->toHaveKeys([
            'hedging',
            'filler_phrases',
            'word_limit_met',
            'apology_detected',
            'ran_long',
            'too_short',
        ]);
    });

    it('maps drill phase to drill type correctly', function () {
        $service = new DrillScoringService;

        expect($service->getDrillTypeFromPhase('Compression'))->toBe('compression');
        expect($service->getDrillTypeFromPhase('Executive Communication'))->toBe('executive_communication');
        expect($service->getDrillTypeFromPhase('The Opener'))->toBe('opener');
        expect($service->getDrillTypeFromPhase('Session Complete'))->toBeNull();
    });
});

describe('DrillScore model', function () {
    it('stores scores as json and retrieves as array', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $scores = [
            'hedging' => true,
            'filler_phrases' => 3,
            'word_limit_met' => false,
            'core_point_captured' => true,
        ];

        $drillScore = DrillScore::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'compression',
            'drill_phase' => 'Compression',
            'is_iteration' => false,
            'scores' => $scores,
            'user_response' => 'I think maybe we should consider this.',
            'word_count' => 7,
        ]);

        $retrieved = DrillScore::find($drillScore->id);

        expect($retrieved->scores)->toBeArray();
        expect($retrieved->scores['hedging'])->toBeTrue();
        expect($retrieved->scores['filler_phrases'])->toBe(3);
        expect($retrieved->is_iteration)->toBeFalse();
    });

    it('has correct relationships', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $drillScore = DrillScore::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'compression',
            'drill_phase' => 'Compression',
            'is_iteration' => false,
            'scores' => ['hedging' => false],
            'user_response' => 'Test response',
            'word_count' => 2,
        ]);

        expect($drillScore->user->id)->toBe($user->id);
        expect($drillScore->trainingSession->id)->toBe($session->id);
        expect($drillScore->practiceMode->id)->toBe($mode->id);
    });

    it('scopes by user correctly', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $session1 = TrainingSession::create([
            'user_id' => $user1->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        $session2 = TrainingSession::create([
            'user_id' => $user2->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        DrillScore::create([
            'user_id' => $user1->id,
            'training_session_id' => $session1->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'compression',
            'drill_phase' => 'Compression',
            'scores' => [],
            'user_response' => 'Test',
            'word_count' => 1,
        ]);

        DrillScore::create([
            'user_id' => $user2->id,
            'training_session_id' => $session2->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'compression',
            'drill_phase' => 'Compression',
            'scores' => [],
            'user_response' => 'Test',
            'word_count' => 1,
        ]);

        $user1Scores = DrillScore::forUser($user1->id)->get();
        expect($user1Scores)->toHaveCount(1);
        expect($user1Scores->first()->user_id)->toBe($user1->id);
    });

    it('scopes by drill type correctly', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => 1,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        DrillScore::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'compression',
            'drill_phase' => 'Compression',
            'scores' => [],
            'user_response' => 'Test',
            'word_count' => 1,
        ]);

        DrillScore::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $mode->id,
            'drill_type' => 'executive_communication',
            'drill_phase' => 'Executive Communication',
            'scores' => [],
            'user_response' => 'Test',
            'word_count' => 1,
        ]);

        $compressionScores = DrillScore::forDrillType('compression')->get();
        expect($compressionScores)->toHaveCount(1);
        expect($compressionScores->first()->drill_type)->toBe('compression');
    });
});

describe('drill_types config', function () {
    it('has phase mapping for all documented drill types', function () {
        $phaseMapping = config('drill_types.phase_mapping');

        // MBA+ Executive Training
        expect($phaseMapping['Compression'])->toBe('compression');
        expect($phaseMapping['Executive Communication'])->toBe('executive_communication');
        expect($phaseMapping['Problem-Solving'])->toBe('problem_solving');
        expect($phaseMapping['Writing Precision'])->toBe('writing_precision');

        // Interview Prep
        expect($phaseMapping['Story Compression'])->toBe('story_compression');
        expect($phaseMapping['The Opener'])->toBe('opener');
        expect($phaseMapping['Curveball Recovery'])->toBe('curveball_recovery');
        expect($phaseMapping['Closing Strong'])->toBe('closing_questions');

        // Thinking on Your Feet
        expect($phaseMapping['Unexpected Question'])->toBe('unexpected_question');
        expect($phaseMapping['Impromptu Structure'])->toBe('impromptu_structure');
        expect($phaseMapping['Defending Your Position'])->toBe('defending_position');
        expect($phaseMapping["Graceful I Don't Know"])->toBe('graceful_unknown');

        // Difficult Conversations
        expect($phaseMapping['The Direct Open'])->toBe('feedback_delivery');
        expect($phaseMapping['Holding the Line'])->toBe('holding_the_line');
        expect($phaseMapping['The Clean No'])->toBe('clean_no');
        expect($phaseMapping['Bad News Delivery'])->toBe('bad_news_delivery');

        // Non-scorable
        expect($phaseMapping['Session Complete'])->toBeNull();
    });

    it('has criteria for all drill types', function () {
        $drillCriteria = config('drill_types.drill_criteria');

        $expectedTypes = [
            'compression',
            'executive_communication',
            'problem_solving',
            'writing_precision',
            'story_compression',
            'opener',
            'curveball_recovery',
            'closing_questions',
            'unexpected_question',
            'impromptu_structure',
            'defending_position',
            'graceful_unknown',
            'feedback_delivery',
            'holding_the_line',
            'clean_no',
            'bad_news_delivery',
            'negotiation_anchor',
            'negotiation_pushback',
            'managing_up',
            'status_update',
            'escalation',
        ];

        foreach ($expectedTypes as $type) {
            expect($drillCriteria)->toHaveKey($type);
            expect($drillCriteria[$type])->not->toBeEmpty();
        }
    });

    it('has universal criteria defined', function () {
        $universalCriteria = config('drill_types.universal_criteria');

        expect($universalCriteria)->toHaveKeys([
            'hedging',
            'filler_phrases',
            'word_limit_met',
            'apology_detected',
            'ran_long',
            'too_short',
        ]);
    });
});
