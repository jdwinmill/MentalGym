<?php

namespace App\Services;

use App\DTOs\GatedBlindSpotAnalysis;
use App\Models\DrillScore;
use App\Models\User;
use Illuminate\Support\Collection;

class BlindSpotService
{
    public function __construct(
        private BlindSpotAnalyzer $analyzer
    ) {}

    public function getAnalysis(User $user): GatedBlindSpotAnalysis
    {
        $minimumSessions = $this->analyzer->getMinimumSessions();

        if (! $this->analyzer->hasEnoughData($user)) {
            $analysis = $this->analyzer->analyze($user);

            return GatedBlindSpotAnalysis::insufficientData(
                $analysis->totalSessions,
                $analysis->totalResponses,
                $minimumSessions
            );
        }

        $analysis = $this->analyzer->analyze($user);

        if (! $this->hasProAccess($user)) {
            return GatedBlindSpotAnalysis::locked($analysis, $minimumSessions);
        }

        return GatedBlindSpotAnalysis::unlocked($analysis);
    }

    public function canAccessFullInsights(User $user): bool
    {
        return $this->hasProAccess($user) && $this->analyzer->hasEnoughData($user);
    }

    public function shouldShowTeaser(User $user): bool
    {
        if ($this->hasProAccess($user)) {
            return false;
        }

        if (! $this->analyzer->hasEnoughData($user)) {
            return false;
        }

        $analysis = $this->analyzer->analyze($user);

        return $analysis->hasBlindSpots();
    }

    public function getTeaserData(User $user): ?array
    {
        if (! $this->shouldShowTeaser($user)) {
            return null;
        }

        $analysis = $this->analyzer->analyze($user);

        return [
            'blindSpotCount' => $analysis->getBlindSpotCount(),
            'hasImprovements' => $analysis->hasImprovements(),
            'hasRegressions' => $analysis->hasRegressions(),
            'totalSessions' => $analysis->totalSessions,
        ];
    }

    public function hasProAccess(User $user): bool
    {
        return $user->hasPaidPlan();
    }

    public function getStatus(User $user): array
    {
        $hasEnoughData = $this->analyzer->hasEnoughData($user);
        $hasProAccess = $this->hasProAccess($user);
        $minimumSessions = $this->analyzer->getMinimumSessions();

        $analysis = $this->analyzer->analyze($user);

        return [
            'hasEnoughData' => $hasEnoughData,
            'hasProAccess' => $hasProAccess,
            'canAccessFullInsights' => $hasEnoughData && $hasProAccess,
            'showTeaser' => $this->shouldShowTeaser($user),
            'totalSessions' => $analysis->totalSessions,
            'minimumSessions' => $minimumSessions,
            'sessionsUntilInsights' => max(0, $minimumSessions - $analysis->totalSessions),
            'blindSpotCount' => $hasEnoughData ? $analysis->getBlindSpotCount() : 0,
        ];
    }

    public function getHistoricalTrends(User $user, int $weeks = 8): array
    {
        $trends = [];
        $now = now();
        $skills = config('skills.skill_criteria', []);

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $scores = DrillScore::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->get();

            if ($scores->isEmpty()) {
                $trends[] = [
                    'week' => $weekStart->format('M j'),
                    'data' => null,
                    'sessions' => 0,
                    'responses' => 0,
                ];

                continue;
            }

            $skillRates = [];
            foreach ($skills as $skill => $criteria) {
                $rate = $this->calculateSkillFailureRate(
                    $scores,
                    $criteria['positive'] ?? [],
                    $criteria['negative'] ?? []
                );
                if ($rate !== null) {
                    $skillRates[$skill] = $rate;
                }
            }

            $trends[] = [
                'week' => $weekStart->format('M j'),
                'data' => empty($skillRates) ? null : $skillRates,
                'sessions' => $scores->pluck('training_session_id')->unique()->count(),
                'responses' => $scores->count(),
            ];
        }

        return $trends;
    }

    /**
     * Calculate the failure rate for a skill based on its positive and negative criteria.
     */
    private function calculateSkillFailureRate(Collection $scores, array $positiveCriteria, array $negativeCriteria): ?float
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

            // Check positive criteria (should be truthy; falsy = failure)
            foreach ($positiveCriteria as $criterion) {
                if (array_key_exists($criterion, $scoreData)) {
                    $hasRelevantCriteria = true;
                    if ($this->isFalsy($scoreData[$criterion])) {
                        $hasFailed = true;
                    }
                }
            }

            // Check negative criteria (should be falsy; truthy = failure)
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

        if ($total < 3) { // Need at least 3 responses for a meaningful rate
            return null;
        }

        return round($failures / $total, 2);
    }

    /**
     * Check if a score value represents a truthy (passed) state.
     */
    private function isTruthy(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * Check if a score value represents a falsy (failed) state.
     */
    private function isFalsy(mixed $value): bool
    {
        return ! $this->isTruthy($value);
    }
}
