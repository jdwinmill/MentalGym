<?php

namespace App\Services;

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\SkillAnalysis;
use App\DTOs\UniversalPattern;
use App\Models\DrillScore;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Collection;

class BlindSpotAnalyzer
{
    private float $blindSpotThreshold;
    private float $improvementThreshold;
    private float $regressionThreshold;
    private int $minimumResponses;
    private int $minimumSessions;
    private int $recentDays;
    private int $baselineDays;

    public function __construct()
    {
        $this->blindSpotThreshold = config('skills.thresholds.blind_spot', 0.6);
        $this->improvementThreshold = config('skills.thresholds.improvement', 0.2);
        $this->regressionThreshold = config('skills.thresholds.regression', 0.15);
        $this->minimumResponses = config('skills.thresholds.minimum_responses', 5);
        $this->minimumSessions = config('skills.thresholds.minimum_sessions', 5);
        $this->recentDays = config('skills.windows.recent', 7);
        $this->baselineDays = config('skills.windows.baseline', 30);
    }

    public function analyze(User $user): BlindSpotAnalysis
    {
        $totalSessions = $this->getSessionCount($user);
        $totalResponses = $this->getResponseCount($user);

        if (!$this->hasEnoughData($user)) {
            return BlindSpotAnalysis::insufficient($totalSessions, $totalResponses);
        }

        $allScores = $this->getScores($user, $this->baselineDays);
        $recentScores = $this->getScores($user, $this->recentDays);
        $baselineScores = $this->getBaselineScores($user);

        $universalPatterns = $this->analyzeUniversalCriteria($allScores, $recentScores, $baselineScores);

        $skills = $this->getSkillsFromConfig();
        $skillAnalyses = [];

        foreach ($skills as $skill) {
            $analysis = $this->analyzeSkillInternal($user, $skill, $allScores, $recentScores, $baselineScores);
            if ($analysis !== null) {
                $skillAnalyses[$skill] = $analysis;
            }
        }

        $blindSpots = array_values(array_filter($skillAnalyses, fn($s) => $s->isBlindSpot()));
        $improving = array_values(array_filter($skillAnalyses, fn($s) => $s->isImproving() && !$s->isBlindSpot()));
        $slipping = array_values(array_filter($skillAnalyses, fn($s) => $s->isSlipping()));
        $stable = array_values(array_filter($skillAnalyses, fn($s) => $s->trend === 'stable' && !$s->isBlindSpot()));

        $biggestGap = $this->findBiggestGap($skillAnalyses);
        $biggestWin = $this->findBiggestWin($skillAnalyses);

        return new BlindSpotAnalysis(
            hasEnoughData: true,
            totalSessions: $totalSessions,
            totalResponses: $allScores->count(),
            blindSpots: $blindSpots,
            improving: $improving,
            stable: $stable,
            slipping: $slipping,
            universalPatterns: $universalPatterns,
            biggestGap: $biggestGap,
            biggestWin: $biggestWin,
            analyzedAt: now(),
        );
    }

    public function hasEnoughData(User $user): bool
    {
        return $this->getSessionCount($user) >= $this->minimumSessions;
    }

    public function getMinimumSessions(): int
    {
        return $this->minimumSessions;
    }

    public function analyzeSkill(User $user, string $skill): ?SkillAnalysis
    {
        $allScores = $this->getScores($user, $this->baselineDays);
        $recentScores = $this->getScores($user, $this->recentDays);
        $baselineScores = $this->getBaselineScores($user);

        return $this->analyzeSkillInternal($user, $skill, $allScores, $recentScores, $baselineScores);
    }

    private function getScores(User $user, int $days): Collection
    {
        return DrillScore::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
    }

    private function getBaselineScores(User $user): Collection
    {
        return DrillScore::where('user_id', $user->id)
            ->whereBetween('created_at', [
                now()->subDays($this->baselineDays),
                now()->subDays($this->recentDays),
            ])
            ->get();
    }

    private function getSessionCount(User $user): int
    {
        return TrainingSession::where('user_id', $user->id)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->count();
    }

    private function getResponseCount(User $user): int
    {
        return DrillScore::where('user_id', $user->id)->count();
    }

    private function getSkillsFromConfig(): array
    {
        return array_keys(config('skills.skill_criteria', []));
    }

    private function analyzeSkillInternal(
        User $user,
        string $skill,
        Collection $allScores,
        Collection $recentScores,
        Collection $baselineScores
    ): ?SkillAnalysis {
        $criteria = config("skills.skill_criteria.{$skill}");
        if (!$criteria) {
            return null;
        }

        $positiveCriteria = $criteria['positive'] ?? [];
        $negativeCriteria = $criteria['negative'] ?? [];

        $allFailures = $this->calculateSkillFailureRate($allScores, $positiveCriteria, $negativeCriteria);
        $recentFailures = $this->calculateSkillFailureRate($recentScores, $positiveCriteria, $negativeCriteria);
        $baselineFailures = $this->calculateSkillFailureRate($baselineScores, $positiveCriteria, $negativeCriteria);

        if ($allFailures['total'] < $this->minimumResponses) {
            return null;
        }

        $currentRate = $recentFailures['total'] > 0
            ? $recentFailures['failures'] / $recentFailures['total']
            : $allFailures['failures'] / $allFailures['total'];

        $baselineRate = $baselineFailures['total'] > 0
            ? $baselineFailures['failures'] / $baselineFailures['total']
            : $currentRate;

        $trend = $this->calculateTrend($currentRate, $baselineRate, $baselineFailures['total']);

        $failingCriteria = $this->getFailingCriteria($allScores, $positiveCriteria, $negativeCriteria);
        $primaryIssue = $failingCriteria[0] ?? null;
        $context = $primaryIssue ? $this->findContext($user, $primaryIssue) : null;

        return new SkillAnalysis(
            skill: $skill,
            trend: $trend,
            currentRate: round($currentRate, 2),
            baselineRate: round($baselineRate, 2),
            sampleSize: $allFailures['total'],
            primaryIssue: $primaryIssue,
            context: $context,
            failingCriteria: $failingCriteria,
        );
    }

    private function calculateSkillFailureRate(Collection $scores, array $positiveCriteria, array $negativeCriteria): array
    {
        $total = 0;
        $failures = 0;

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            if (!is_array($scoreData)) {
                continue;
            }

            $hasRelevantCriteria = false;
            $hasFailed = false;

            foreach ($positiveCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($scoreData[$criterion] === false) {
                        $hasFailed = true;
                    }
                }
            }

            foreach ($negativeCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($scoreData[$criterion] === true) {
                        $hasFailed = true;
                    }
                }
            }

            if ($hasRelevantCriteria) {
                $total++;
                if ($hasFailed) {
                    $failures++;
                }
            }
        }

        return ['total' => $total, 'failures' => $failures];
    }

    private function getFailingCriteria(Collection $scores, array $positiveCriteria, array $negativeCriteria): array
    {
        $criteriaFailures = [];

        foreach ($positiveCriteria as $criterion) {
            $failures = 0;
            $total = 0;

            foreach ($scores as $score) {
                $scoreData = $score->scores;
                if (is_array($scoreData) && array_key_exists($criterion, $scoreData)) {
                    $total++;
                    if ($scoreData[$criterion] === false) {
                        $failures++;
                    }
                }
            }

            if ($total >= $this->minimumResponses && $failures / $total >= $this->blindSpotThreshold) {
                $criteriaFailures[$criterion] = $failures / $total;
            }
        }

        foreach ($negativeCriteria as $criterion) {
            $failures = 0;
            $total = 0;

            foreach ($scores as $score) {
                $scoreData = $score->scores;
                if (is_array($scoreData) && array_key_exists($criterion, $scoreData)) {
                    $total++;
                    if ($scoreData[$criterion] === true) {
                        $failures++;
                    }
                }
            }

            if ($total >= $this->minimumResponses && $failures / $total >= $this->blindSpotThreshold) {
                $criteriaFailures[$criterion] = $failures / $total;
            }
        }

        arsort($criteriaFailures);
        return array_keys($criteriaFailures);
    }

    private function calculateTrend(float $currentRate, float $baselineRate, int $baselineSampleSize): string
    {
        if ($baselineSampleSize < 3) {
            return 'new';
        }

        $delta = $baselineRate - $currentRate;

        if ($currentRate >= $this->blindSpotThreshold && $baselineRate >= $this->blindSpotThreshold) {
            if ($delta >= $this->improvementThreshold) {
                return 'improving';
            }
            return 'stuck';
        }

        if ($delta >= $this->improvementThreshold) {
            return 'improving';
        }

        if ($delta <= -$this->regressionThreshold) {
            return 'slipping';
        }

        return 'stable';
    }

    private function analyzeUniversalCriteria(Collection $allScores, Collection $recentScores, Collection $baselineScores): array
    {
        $universalCriteria = config('skills.universal_criteria', []);
        $patterns = [];

        foreach ($universalCriteria as $criterion) {
            $allStats = $this->calculateCriteriaStats($allScores, $criterion);
            $recentStats = $this->calculateCriteriaStats($recentScores, $criterion);
            $baselineStats = $this->calculateCriteriaStats($baselineScores, $criterion);

            if ($allStats['total'] < $this->minimumResponses) {
                continue;
            }

            $currentRate = $recentStats['total'] > 0
                ? $recentStats['count'] / $recentStats['total']
                : $allStats['count'] / $allStats['total'];

            $baselineRate = $baselineStats['total'] > 0
                ? $baselineStats['count'] / $baselineStats['total']
                : $currentRate;

            $trend = $this->calculateUniversalTrend($currentRate, $baselineRate, $baselineStats['total']);

            $patterns[] = new UniversalPattern(
                criteria: $criterion,
                rate: round($currentRate, 2),
                count: $allStats['count'],
                total: $allStats['total'],
                trend: $trend,
            );
        }

        usort($patterns, fn($a, $b) => $b->rate <=> $a->rate);

        return $patterns;
    }

    private function calculateCriteriaStats(Collection $scores, string $criterion): array
    {
        $total = 0;
        $count = 0;

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            if (!is_array($scoreData) || !array_key_exists($criterion, $scoreData)) {
                continue;
            }

            $total++;

            if ($criterion === 'filler_phrases') {
                if ($scoreData[$criterion] > 0) {
                    $count++;
                }
            } else {
                if ($scoreData[$criterion] === true) {
                    $count++;
                }
            }
        }

        return ['total' => $total, 'count' => $count];
    }

    private function calculateUniversalTrend(float $currentRate, float $baselineRate, int $baselineSampleSize): string
    {
        if ($baselineSampleSize < 3) {
            return 'new';
        }

        $delta = $baselineRate - $currentRate;

        if ($currentRate >= $this->blindSpotThreshold && $baselineRate >= $this->blindSpotThreshold) {
            if ($delta >= $this->improvementThreshold) {
                return 'improving';
            }
            return 'stuck';
        }

        if ($delta >= $this->improvementThreshold) {
            return 'improving';
        }

        if ($delta <= -$this->regressionThreshold) {
            return 'slipping';
        }

        return 'stable';
    }

    private function findContext(User $user, string $criteria): ?string
    {
        $result = DrillScore::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get()
            ->filter(function ($score) use ($criteria) {
                $scoreData = $score->scores;
                if (!is_array($scoreData) || !array_key_exists($criteria, $scoreData)) {
                    return false;
                }
                return $scoreData[$criteria] === true || $scoreData[$criteria] === false;
            })
            ->groupBy('drill_phase')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->keys()
            ->first();

        return $result;
    }

    private function findBiggestGap(array $skillAnalyses): ?string
    {
        $worst = null;
        $worstRate = 0;

        foreach ($skillAnalyses as $analysis) {
            if ($analysis->currentRate > $worstRate) {
                $worstRate = $analysis->currentRate;
                $worst = $analysis->skill;
            }
        }

        return $worstRate >= $this->blindSpotThreshold ? $worst : null;
    }

    private function findBiggestWin(array $skillAnalyses): ?string
    {
        $best = null;
        $bestDelta = 0;

        foreach ($skillAnalyses as $analysis) {
            if ($analysis->trend === 'improving') {
                $delta = $analysis->baselineRate - $analysis->currentRate;
                if ($delta > $bestDelta) {
                    $bestDelta = $delta;
                    $best = $analysis->skill;
                }
            }
        }

        return $best;
    }

    public function analyzeIterationPattern(User $user): array
    {
        $firstAttempts = DrillScore::where('user_id', $user->id)
            ->where('is_iteration', false)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get();

        $iterations = DrillScore::where('user_id', $user->id)
            ->where('is_iteration', true)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get();

        if ($firstAttempts->isEmpty() || $iterations->isEmpty()) {
            return [
                'has_data' => false,
                'improves_on_iteration' => null,
                'first_attempt_rate' => null,
                'iteration_rate' => null,
                'delta' => null,
            ];
        }

        $firstAttemptRate = $this->calculateOverallFailureRate($firstAttempts);
        $iterationRate = $this->calculateOverallFailureRate($iterations);

        return [
            'has_data' => true,
            'improves_on_iteration' => $iterationRate < $firstAttemptRate,
            'first_attempt_rate' => round($firstAttemptRate, 2),
            'iteration_rate' => round($iterationRate, 2),
            'delta' => round($firstAttemptRate - $iterationRate, 2),
        ];
    }

    private function calculateOverallFailureRate(Collection $scores): float
    {
        if ($scores->isEmpty()) {
            return 0;
        }

        $totalCriteria = 0;
        $totalFailures = 0;

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            if (!is_array($scoreData)) {
                continue;
            }

            foreach ($scoreData as $criterion => $value) {
                if (is_bool($value)) {
                    $totalCriteria++;
                    $isUniversalNegative = in_array($criterion, config('skills.universal_criteria', []));

                    if ($isUniversalNegative && $value === true) {
                        $totalFailures++;
                    } elseif (!$isUniversalNegative && $value === false) {
                        $totalFailures++;
                    }
                }
            }
        }

        return $totalCriteria > 0 ? $totalFailures / $totalCriteria : 0;
    }
}
