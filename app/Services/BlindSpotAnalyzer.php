<?php

namespace App\Services;

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\SkillAnalysis;
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

        if (! $this->hasEnoughData($user)) {
            return BlindSpotAnalysis::insufficient($totalSessions, $totalResponses);
        }

        $allScores = $this->getScores($user, $this->baselineDays);
        $recentScores = $this->getScores($user, $this->recentDays);
        $baselineScores = $this->getBaselineScores($user);

        $skills = $this->getSkillsFromConfig();
        $skillAnalyses = [];

        foreach ($skills as $skill) {
            $analysis = $this->analyzeSkillInternal($user, $skill, $allScores, $recentScores, $baselineScores);
            if ($analysis !== null) {
                $skillAnalyses[$skill] = $analysis;
            }
        }

        $blindSpots = array_values(array_filter($skillAnalyses, fn ($s) => $s->isBlindSpot()));
        $improving = array_values(array_filter($skillAnalyses, fn ($s) => $s->isImproving() && ! $s->isBlindSpot()));
        $slipping = array_values(array_filter($skillAnalyses, fn ($s) => $s->isSlipping()));
        $stable = array_values(array_filter($skillAnalyses, fn ($s) => $s->trend === 'stable' && ! $s->isBlindSpot()));

        $biggestGap = $this->findBiggestGap($skillAnalyses);
        $biggestWin = $this->findBiggestWin($skillAnalyses);
        $growthEdge = $this->findGrowthEdge($skillAnalyses);

        // All skills sorted by success rate (best first)
        $allSkills = array_values($skillAnalyses);
        usort($allSkills, fn ($a, $b) => $a->currentRate <=> $b->currentRate);

        return new BlindSpotAnalysis(
            hasEnoughData: true,
            totalSessions: $totalSessions,
            totalResponses: $allScores->count(),
            blindSpots: $blindSpots,
            improving: $improving,
            stable: $stable,
            slipping: $slipping,
            biggestGap: $biggestGap,
            biggestWin: $biggestWin,
            analyzedAt: now(),
            growthEdge: $growthEdge,
            allSkills: $allSkills,
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
        if (! $criteria) {
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

        // Find context: use primaryIssue if available, otherwise look at all skill criteria
        $context = null;
        if ($primaryIssue) {
            $context = $this->findContext($user, $primaryIssue);
        }
        if (! $context && $currentRate >= $this->blindSpotThreshold) {
            $context = $this->findContextForSkill($user, $positiveCriteria, $negativeCriteria);
        }

        $practiceMode = $context ? $this->mapContextToPracticeMode($context) : null;

        // Get skill metadata from config
        $skillConfig = config("skills.skill_criteria.{$skill}", []);
        $criteriaLabels = config('skills.criteria_labels', []);

        // Map failing criteria to human-readable labels
        $failingCriteriaLabels = array_map(
            fn ($c) => $criteriaLabels[$c] ?? $c,
            $failingCriteria
        );

        // Calculate context breakdown (failure rate per drill phase)
        $contextBreakdown = $this->calculateContextBreakdown($allScores, $positiveCriteria, $negativeCriteria);

        return new SkillAnalysis(
            skill: $skill,
            trend: $trend,
            currentRate: round($currentRate, 2),
            baselineRate: round($baselineRate, 2),
            sampleSize: $allFailures['total'],
            primaryIssue: $primaryIssue,
            context: $context,
            failingCriteria: $failingCriteria,
            practiceMode: $practiceMode,
            name: $skillConfig['name'] ?? ucfirst($skill),
            description: $skillConfig['description'] ?? null,
            target: $skillConfig['target'] ?? null,
            tips: $skillConfig['tips'] ?? [],
            failingCriteriaLabels: $failingCriteriaLabels,
            contextBreakdown: $contextBreakdown,
        );
    }

    private function calculateSkillFailureRate(Collection $scores, array $positiveCriteria, array $negativeCriteria): array
    {
        $total = 0;
        $failures = 0;

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            if (! is_array($scoreData)) {
                continue;
            }

            $hasRelevantCriteria = false;
            $hasFailed = false;

            foreach ($positiveCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($this->isFalsy($scoreData[$criterion])) {
                        $hasFailed = true;
                    }
                }
            }

            foreach ($negativeCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($this->isTruthy($scoreData[$criterion])) {
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
                    if ($this->isFalsy($scoreData[$criterion])) {
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
                    if ($this->isTruthy($scoreData[$criterion])) {
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

    private function findContext(User $user, string $criteria): ?string
    {
        $result = DrillScore::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get()
            ->filter(function ($score) use ($criteria) {
                $scoreData = $score->scores;
                if (! is_array($scoreData) || ! array_key_exists($criteria, $scoreData)) {
                    return false;
                }

                return $scoreData[$criteria] === true || $scoreData[$criteria] === false;
            })
            ->groupBy('drill_phase')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->keys()
            ->first();

        return $result;
    }

    private function mapContextToPracticeMode(?string $drillPhase): ?string
    {
        if (! $drillPhase) {
            return null;
        }

        return config("drill_types.practice_mode_mapping.{$drillPhase}");
    }

    /**
     * Find the drill phase where a skill's criteria fail most often.
     * Used as fallback when no single criterion meets the blind spot threshold.
     */
    private function findContextForSkill(User $user, array $positiveCriteria, array $negativeCriteria): ?string
    {
        $result = DrillScore::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get()
            ->filter(function ($score) use ($positiveCriteria, $negativeCriteria) {
                $scoreData = $score->scores;
                if (! is_array($scoreData)) {
                    return false;
                }

                // Check if this score has any failing criteria for this skill
                foreach ($positiveCriteria as $criterion) {
                    if (array_key_exists($criterion, $scoreData) && $this->isFalsy($scoreData[$criterion])) {
                        return true;
                    }
                }
                foreach ($negativeCriteria as $criterion) {
                    if (array_key_exists($criterion, $scoreData) && $this->isTruthy($scoreData[$criterion])) {
                        return true;
                    }
                }

                return false;
            })
            ->groupBy('drill_phase')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->keys()
            ->first();

        return $result;
    }

    /**
     * Calculate failure rates per drill phase for a skill's criteria.
     * Returns array of [phase => rate] sorted by rate descending.
     */
    private function calculateContextBreakdown(Collection $scores, array $positiveCriteria, array $negativeCriteria): array
    {
        $phaseStats = [];

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            $phase = $score->drill_phase;

            if (! is_array($scoreData) || ! $phase) {
                continue;
            }

            // Check if this score has any relevant criteria for this skill
            $hasRelevantCriteria = false;
            $hasFailed = false;

            foreach ($positiveCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($this->isFalsy($scoreData[$criterion])) {
                        $hasFailed = true;
                    }
                }
            }

            foreach ($negativeCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($this->isTruthy($scoreData[$criterion])) {
                        $hasFailed = true;
                    }
                }
            }

            if ($hasRelevantCriteria) {
                if (! isset($phaseStats[$phase])) {
                    $phaseStats[$phase] = ['total' => 0, 'failures' => 0];
                }
                $phaseStats[$phase]['total']++;
                if ($hasFailed) {
                    $phaseStats[$phase]['failures']++;
                }
            }
        }

        // Calculate rates and format for frontend
        $breakdown = [];
        $practiceModeMapping = config('drill_types.practice_mode_mapping', []);

        foreach ($phaseStats as $phase => $stats) {
            if ($stats['total'] >= 3) { // Need at least 3 samples
                $rate = round($stats['failures'] / $stats['total'], 2);
                $breakdown[] = [
                    'phase' => $phase,
                    'rate' => $rate,
                    'total' => $stats['total'],
                    'practiceMode' => $practiceModeMapping[$phase] ?? null,
                ];
            }
        }

        // Sort by rate descending (worst first)
        usort($breakdown, fn ($a, $b) => $b['rate'] <=> $a['rate']);

        return $breakdown;
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

    /**
     * Find the skill with highest failure rate, regardless of threshold.
     * Used for "Growth Edge" - the area with most room for improvement.
     */
    private function findGrowthEdge(array $skillAnalyses): ?string
    {
        $worst = null;
        $worstRate = 0;

        foreach ($skillAnalyses as $analysis) {
            if ($analysis->currentRate > $worstRate) {
                $worstRate = $analysis->currentRate;
                $worst = $analysis->skill;
            }
        }

        // Only return if there's actually some room for improvement (> 5%)
        return $worstRate > 0.05 ? $worst : null;
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

        // Build lookup of all negative criteria from skill config
        $negativeCriteria = [];
        foreach (config('skills.skill_criteria', []) as $skill) {
            foreach ($skill['negative'] ?? [] as $criterion) {
                $negativeCriteria[$criterion] = true;
            }
        }

        $totalCriteria = 0;
        $totalFailures = 0;

        foreach ($scores as $score) {
            $scoreData = $score->scores;
            if (! is_array($scoreData)) {
                continue;
            }

            foreach ($scoreData as $criterion => $value) {
                // Skip non-boolean-like values (e.g., filler_phrases count)
                if ($value !== true && $value !== false && $value !== 1 && $value !== '1' && $value !== 0 && $value !== '0' && $value !== '') {
                    continue;
                }

                $totalCriteria++;
                $isNegative = isset($negativeCriteria[$criterion]);

                if ($isNegative && $this->isTruthy($value)) {
                    $totalFailures++;
                } elseif (! $isNegative && $this->isFalsy($value)) {
                    $totalFailures++;
                }
            }
        }

        return $totalCriteria > 0 ? $totalFailures / $totalCriteria : 0;
    }

    /**
     * Check if a score value represents a truthy (passed) state.
     * Handles booleans, integers, and strings from the database.
     */
    private function isTruthy(mixed $value): bool
    {
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }

        return false;
    }

    /**
     * Check if a score value represents a falsy (failed) state.
     * Empty strings, null, 0, and false are considered falsy.
     */
    private function isFalsy(mixed $value): bool
    {
        return ! $this->isTruthy($value);
    }
}
