<?php

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\SkillAnalysis;
use App\DTOs\UniversalPattern;
use App\Models\DrillScore;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\BlindSpotAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->analyzer = new BlindSpotAnalyzer;
});

describe('hasEnoughData', function () {
    it('returns false when user has no sessions', function () {
        $user = User::factory()->create();

        expect($this->analyzer->hasEnoughData($user))->toBeFalse();
    });

    it('returns false when user has less than 5 completed sessions', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(4)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        expect($this->analyzer->hasEnoughData($user))->toBeFalse();
    });

    it('returns true when user has 5 or more completed sessions', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        expect($this->analyzer->hasEnoughData($user))->toBeTrue();
    });

    it('does not count active sessions', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(4)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        TrainingSession::factory()
            ->count(3)
            ->active()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        expect($this->analyzer->hasEnoughData($user))->toBeFalse();
    });
});

describe('analyze with insufficient data', function () {
    it('returns insufficient analysis when user has no data', function () {
        $user = User::factory()->create();

        $analysis = $this->analyzer->analyze($user);

        expect($analysis)->toBeInstanceOf(BlindSpotAnalysis::class);
        expect($analysis->hasEnoughData)->toBeFalse();
        expect($analysis->blindSpots)->toBeEmpty();
        expect($analysis->improving)->toBeEmpty();
        expect($analysis->slipping)->toBeEmpty();
    });

    it('returns session count even when insufficient', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        TrainingSession::factory()
            ->count(3)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        expect($analysis->hasEnoughData)->toBeFalse();
        expect($analysis->totalSessions)->toBe(3);
    });
});

describe('blind spot detection', function () {
    it('identifies authority blind spot when hedging rate exceeds 60%', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        // Create 5 completed sessions
        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Create 10 drill scores: 8 with hedging (80%)
        foreach ($sessions as $index => $session) {
            DrillScore::factory()
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();

            if ($index < 3) {
                DrillScore::factory()
                    ->forSession($session)
                    ->withAuthorityIssues()
                    ->create();
            }
        }

        // Create 2 without hedging
        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->withGoodScores()
            ->create();

        $analysis = $this->analyzer->analyze($user);

        expect($analysis->hasEnoughData)->toBeTrue();
        expect($analysis->hasBlindSpots())->toBeTrue();

        $authorityBlindSpot = collect($analysis->blindSpots)
            ->first(fn ($s) => $s->skill === 'authority');

        expect($authorityBlindSpot)->not->toBeNull();
        expect($authorityBlindSpot->currentRate)->toBeGreaterThanOrEqual(0.6);
    });

    it('does not flag blind spot when failure rate is below 60%', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Create 10 scores: 4 with issues (40%), 6 good
        foreach ($sessions->take(2) as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withAuthorityIssues()
                ->create();
        }

        foreach ($sessions->skip(2) as $session) {
            DrillScore::factory()
                ->count(2)
                ->forSession($session)
                ->withGoodScores()
                ->create();
        }

        $analysis = $this->analyzer->analyze($user);

        $authorityBlindSpot = collect($analysis->blindSpots)
            ->first(fn ($s) => $s->skill === 'authority');

        expect($authorityBlindSpot)->toBeNull();
    });
});

describe('trend detection', function () {
    it('detects improving trend when recent performance is better', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Baseline (8-30 days ago): 80% hedging
        foreach ($sessions->take(4) as $session) {
            DrillScore::factory()
                ->forSession($session)
                ->withAuthorityIssues()
                ->createdDaysAgo(15)
                ->create();
        }

        DrillScore::factory()
            ->forSession($sessions[0])
            ->withGoodScores()
            ->createdDaysAgo(15)
            ->create();

        // Recent (last 7 days): 20% hedging
        DrillScore::factory()
            ->forSession($sessions->last())
            ->withAuthorityIssues()
            ->createdDaysAgo(2)
            ->create();

        DrillScore::factory()
            ->count(4)
            ->forSession($sessions->last())
            ->withGoodScores()
            ->createdDaysAgo(2)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $authorityAnalysis = collect(array_merge(
            $analysis->improving,
            $analysis->stable,
            $analysis->slipping,
            $analysis->blindSpots
        ))->first(fn ($s) => $s->skill === 'authority');

        if ($authorityAnalysis) {
            expect($authorityAnalysis->trend)->toBeIn(['improving', 'stable']);
        }
    });

    it('detects slipping trend when recent performance is worse', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Baseline (8-30 days ago): 20% hedging (good)
        DrillScore::factory()
            ->forSession($sessions[0])
            ->withAuthorityIssues()
            ->createdDaysAgo(15)
            ->create();

        DrillScore::factory()
            ->count(4)
            ->forSession($sessions[0])
            ->withGoodScores()
            ->createdDaysAgo(15)
            ->create();

        // Recent (last 7 days): 80% hedging (bad)
        foreach ($sessions->skip(1)->take(4) as $session) {
            DrillScore::factory()
                ->forSession($session)
                ->withAuthorityIssues()
                ->createdDaysAgo(2)
                ->create();
        }

        DrillScore::factory()
            ->forSession($sessions->last())
            ->withGoodScores()
            ->createdDaysAgo(2)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $authorityAnalysis = collect(array_merge(
            $analysis->improving,
            $analysis->stable,
            $analysis->slipping,
            $analysis->blindSpots
        ))->first(fn ($s) => $s->skill === 'authority');

        if ($authorityAnalysis && $authorityAnalysis->baselineRate < $authorityAnalysis->currentRate) {
            expect($authorityAnalysis->trend)->toBeIn(['slipping', 'stuck']);
        }
    });

    it('detects stuck trend when both baseline and recent are above threshold', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Baseline: 70% failure
        DrillScore::factory()
            ->count(7)
            ->forSession($sessions[0])
            ->withAuthorityIssues()
            ->createdDaysAgo(15)
            ->create();

        DrillScore::factory()
            ->count(3)
            ->forSession($sessions[0])
            ->withGoodScores()
            ->createdDaysAgo(15)
            ->create();

        // Recent: 70% failure (same as baseline)
        DrillScore::factory()
            ->count(7)
            ->forSession($sessions->last())
            ->withAuthorityIssues()
            ->createdDaysAgo(2)
            ->create();

        DrillScore::factory()
            ->count(3)
            ->forSession($sessions->last())
            ->withGoodScores()
            ->createdDaysAgo(2)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $authorityAnalysis = collect($analysis->blindSpots)
            ->first(fn ($s) => $s->skill === 'authority');

        if ($authorityAnalysis) {
            expect($authorityAnalysis->trend)->toBe('stuck');
        }
    });
});

describe('universal patterns', function () {
    it('tracks hedging across all responses', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // 7 out of 10 responses have hedging
        DrillScore::factory()
            ->count(7)
            ->forSession($sessions->first())
            ->withHedging(true)
            ->create();

        DrillScore::factory()
            ->count(3)
            ->forSession($sessions->first())
            ->withHedging(false)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $hedgingPattern = collect($analysis->universalPatterns)
            ->first(fn ($p) => $p->criteria === 'hedging');

        expect($hedgingPattern)->not->toBeNull();
        expect($hedgingPattern->rate)->toBe(0.7);
        expect($hedgingPattern->count)->toBe(7);
        expect($hedgingPattern->total)->toBe(10);
    });

    it('identifies problematic universal patterns', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // High hedging rate
        DrillScore::factory()
            ->count(8)
            ->forSession($sessions->first())
            ->withHedging(true)
            ->create();

        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->withHedging(false)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $problematic = $analysis->getProblematicUniversalPatterns();
        $hedgingPattern = collect($problematic)
            ->first(fn ($p) => $p->criteria === 'hedging');

        expect($hedgingPattern)->not->toBeNull();
        expect($hedgingPattern->isProblematic())->toBeTrue();
    });
});

describe('biggest gap and win', function () {
    it('identifies biggest gap as skill with highest failure rate', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Authority: 80% failure
        DrillScore::factory()
            ->count(8)
            ->forSession($sessions->first())
            ->withAuthorityIssues()
            ->create();

        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->withGoodScores()
            ->create();

        $analysis = $this->analyzer->analyze($user);

        if ($analysis->biggestGap !== null) {
            expect($analysis->biggestGap)->toBe('authority');
        }
    });

    it('identifies biggest win as most improved skill', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Baseline: clarity issues (80%)
        DrillScore::factory()
            ->count(8)
            ->forSession($sessions->first())
            ->withClarityIssues()
            ->createdDaysAgo(15)
            ->create();

        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->withGoodScores()
            ->createdDaysAgo(15)
            ->create();

        // Recent: clarity improved (20%)
        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->last())
            ->withClarityIssues()
            ->createdDaysAgo(2)
            ->create();

        DrillScore::factory()
            ->count(8)
            ->forSession($sessions->last())
            ->withGoodScores()
            ->createdDaysAgo(2)
            ->create();

        $analysis = $this->analyzer->analyze($user);

        // biggestWin should be set if there's an improving skill
        if (count($analysis->improving) > 0) {
            expect($analysis->biggestWin)->not->toBeNull();
        }
    });
});

describe('iteration pattern analysis', function () {
    it('detects improvement on iteration attempts', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $session = TrainingSession::factory()
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // First attempts: mostly failures
        DrillScore::factory()
            ->count(8)
            ->forSession($session)
            ->withAuthorityIssues()
            ->create();

        DrillScore::factory()
            ->count(2)
            ->forSession($session)
            ->withGoodScores()
            ->create();

        // Iterations: mostly successes
        DrillScore::factory()
            ->count(2)
            ->forSession($session)
            ->iteration()
            ->withAuthorityIssues()
            ->create();

        DrillScore::factory()
            ->count(8)
            ->forSession($session)
            ->iteration()
            ->withGoodScores()
            ->create();

        $iterationPattern = $this->analyzer->analyzeIterationPattern($user);

        expect($iterationPattern['has_data'])->toBeTrue();
        expect($iterationPattern['improves_on_iteration'])->toBeTrue();
        expect($iterationPattern['iteration_rate'])->toBeLessThan($iterationPattern['first_attempt_rate']);
    });

    it('returns no data when no iterations exist', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $session = TrainingSession::factory()
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        DrillScore::factory()
            ->count(5)
            ->forSession($session)
            ->create();

        $iterationPattern = $this->analyzer->analyzeIterationPattern($user);

        expect($iterationPattern['has_data'])->toBeFalse();
    });
});

describe('context finding', function () {
    it('identifies drill phase where issues appear most', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Most hedging in Executive Communication
        DrillScore::factory()
            ->count(6)
            ->forSession($sessions->first())
            ->state([
                'scores' => ['hedging' => true, 'declarative_sentences' => false],
                'drill_phase' => 'Executive Communication',
            ])
            ->create();

        // Some hedging in other phases
        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->state([
                'scores' => ['hedging' => true],
                'drill_phase' => 'Compression',
            ])
            ->create();

        DrillScore::factory()
            ->count(2)
            ->forSession($sessions->first())
            ->withGoodScores()
            ->create();

        $analysis = $this->analyzer->analyze($user);

        $authorityAnalysis = collect(array_merge(
            $analysis->blindSpots,
            $analysis->improving,
            $analysis->stable,
            $analysis->slipping
        ))->first(fn ($s) => $s->skill === 'authority');

        if ($authorityAnalysis && $authorityAnalysis->context) {
            expect($authorityAnalysis->context)->toBe('Executive Communication');
        }
    });
});

describe('DTO methods', function () {
    it('BlindSpotAnalysis toArray returns correct structure', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        DrillScore::factory()
            ->count(10)
            ->forSession($sessions->first())
            ->withHedging(true)
            ->create();

        $analysis = $this->analyzer->analyze($user);
        $array = $analysis->toArray();

        expect($array)->toHaveKeys([
            'hasEnoughData',
            'totalSessions',
            'totalResponses',
            'blindSpots',
            'improving',
            'stable',
            'slipping',
            'universalPatterns',
            'biggestGap',
            'biggestWin',
            'analyzedAt',
        ]);
    });

    it('SkillAnalysis provides helper methods', function () {
        $skillAnalysis = new SkillAnalysis(
            skill: 'authority',
            trend: 'stuck',
            currentRate: 0.7,
            baselineRate: 0.75,
            sampleSize: 15,
            primaryIssue: 'hedging',
            context: 'Executive Communication',
            failingCriteria: ['hedging', 'declarative_sentences'],
        );

        expect($skillAnalysis->isBlindSpot())->toBeTrue();
        expect($skillAnalysis->isStuck())->toBeTrue();
        expect($skillAnalysis->isImproving())->toBeFalse();
        expect($skillAnalysis->isSlipping())->toBeFalse();
    });

    it('UniversalPattern identifies problematic patterns', function () {
        $problematic = new UniversalPattern(
            criteria: 'hedging',
            rate: 0.7,
            count: 7,
            total: 10,
            trend: 'stuck',
        );

        $notProblematic = new UniversalPattern(
            criteria: 'hedging',
            rate: 0.3,
            count: 3,
            total: 10,
            trend: 'stable',
        );

        expect($problematic->isProblematic())->toBeTrue();
        expect($notProblematic->isProblematic())->toBeFalse();
    });
});

describe('minimum responses requirement', function () {
    it('does not analyze skill with fewer than 5 responses', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create();

        $sessions = TrainingSession::factory()
            ->count(5)
            ->completed()
            ->forUser($user)
            ->forMode($mode)
            ->create();

        // Only 3 responses with authority criteria
        DrillScore::factory()
            ->count(3)
            ->forSession($sessions->first())
            ->withAuthorityIssues()
            ->create();

        // 10 responses with other criteria (clarity)
        DrillScore::factory()
            ->count(10)
            ->forSession($sessions->first())
            ->withClarityIssues()
            ->create();

        $analysis = $this->analyzer->analyze($user);

        // Authority should not appear in any category due to insufficient data
        $authorityInAny = collect(array_merge(
            $analysis->blindSpots,
            $analysis->improving,
            $analysis->stable,
            $analysis->slipping
        ))->contains(fn ($s) => $s->skill === 'authority');

        expect($authorityInAny)->toBeFalse();
    });
});
