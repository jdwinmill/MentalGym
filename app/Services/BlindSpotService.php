<?php

namespace App\Services;

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\DimensionAnalysis;
use App\DTOs\GatedBlindSpotAnalysis;
use App\Models\BlindSpot;
use App\Models\SkillDimension;
use App\Models\TrainingSession;
use App\Models\User;

class BlindSpotService
{
    private int $minimumSessions = 5;

    private int $recentDays = 7;

    private int $baselineDays = 30;

    public function getAnalysis(User $user): GatedBlindSpotAnalysis
    {
        $totalSessions = $this->getSessionCount($user);
        $totalResponses = $this->getResponseCount($user);

        if (! $this->hasEnoughData($user)) {
            return GatedBlindSpotAnalysis::insufficientData(
                $totalSessions,
                $totalResponses,
                $this->minimumSessions
            );
        }

        $analysis = $this->analyze($user);

        if (! $this->hasProAccess($user)) {
            return GatedBlindSpotAnalysis::locked($analysis, $this->minimumSessions);
        }

        return GatedBlindSpotAnalysis::unlocked($analysis);
    }

    public function analyze(User $user): BlindSpotAnalysis
    {
        $totalSessions = $this->getSessionCount($user);
        $totalResponses = $this->getResponseCount($user);

        if ($totalResponses < 3) {
            return BlindSpotAnalysis::insufficient($totalSessions, $totalResponses);
        }

        $dimensionAnalyses = $this->analyzeDimensions($user);

        $blindSpots = array_filter($dimensionAnalyses, fn ($d) => $d->isBlindSpot());
        $improving = array_filter($dimensionAnalyses, fn ($d) => $d->isImproving() && ! $d->isBlindSpot());
        $slipping = array_filter($dimensionAnalyses, fn ($d) => $d->isSlipping());
        $stable = array_filter($dimensionAnalyses, fn ($d) => $d->isStable() && ! $d->isBlindSpot());

        // Sort by score (lowest first for blind spots)
        usort($blindSpots, fn ($a, $b) => $a->averageScore <=> $b->averageScore);

        $biggestGap = ! empty($blindSpots) ? $blindSpots[0]->dimensionKey : null;
        $biggestWin = $this->findBiggestWin($dimensionAnalyses);
        $growthEdge = $this->findGrowthEdge($dimensionAnalyses);

        return new BlindSpotAnalysis(
            hasEnoughData: true,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            blindSpots: array_values($blindSpots),
            improving: array_values($improving),
            stable: array_values($stable),
            slipping: array_values($slipping),
            biggestGap: $biggestGap,
            biggestWin: $biggestWin,
            analyzedAt: now(),
            growthEdge: $growthEdge,
            allSkills: array_values($dimensionAnalyses),
        );
    }

    /**
     * Analyze all dimensions for a user.
     *
     * @return DimensionAnalysis[]
     */
    private function analyzeDimensions(User $user): array
    {
        // Get all blind spot records for this user
        $allSpots = BlindSpot::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get();

        // Group by dimension
        $allByDimension = $allSpots->groupBy('dimension_key');

        // Load dimension metadata
        $dimensions = SkillDimension::active()->get()->keyBy('key');

        $analyses = [];

        foreach ($allByDimension as $dimensionKey => $spots) {
            if ($spots->count() < 3) {
                continue; // Need minimum sample size
            }

            $dimension = $dimensions->get($dimensionKey);
            if (! $dimension) {
                continue;
            }

            $allAvg = $spots->avg('score');
            $trend = $this->calculateTrend($spots);

            // Get latest suggestion for this dimension
            $latestSuggestion = $spots
                ->sortByDesc('created_at')
                ->whereNotNull('suggestion')
                ->first()
                ?->suggestion;

            $analyses[] = new DimensionAnalysis(
                dimensionKey: $dimensionKey,
                label: $dimension->label,
                category: $dimension->category,
                averageScore: $allAvg,
                sampleSize: $spots->count(),
                trend: $trend,
                latestSuggestion: $latestSuggestion,
                description: $dimension->description,
            );
        }

        return $analyses;
    }

    /**
     * Calculate trend for a dimension.
     *
     * @param  \Illuminate\Support\Collection  $spots  All spots for this dimension, sorted by created_at
     */
    private function calculateTrend($spots): string
    {
        // Need at least 4 samples to calculate a meaningful trend
        if ($spots->count() < 4) {
            return 'new';
        }

        // Sort by date ascending
        $sorted = $spots->sortBy('created_at')->values();

        // Check if we have data older than 7 days
        $oldestSpot = $sorted->first();
        $hasOlderBaseline = $oldestSpot->created_at < now()->subDays($this->recentDays);

        if ($hasOlderBaseline) {
            // Use the standard approach: compare last 7 days to older data
            $recentSpots = $sorted->filter(fn ($s) => $s->created_at >= now()->subDays($this->recentDays));
            $baselineSpots = $sorted->filter(fn ($s) => $s->created_at < now()->subDays($this->recentDays));

            if ($recentSpots->isEmpty() || $baselineSpots->isEmpty()) {
                return 'new';
            }

            $recentAvg = $recentSpots->avg('score');
            $baselineAvg = $baselineSpots->avg('score');
        } else {
            // All data is recent - split in half temporally
            $midpoint = (int) floor($sorted->count() / 2);
            $baselineSpots = $sorted->take($midpoint);
            $recentSpots = $sorted->skip($midpoint);

            if ($baselineSpots->isEmpty() || $recentSpots->isEmpty()) {
                return 'new';
            }

            $recentAvg = $recentSpots->avg('score');
            $baselineAvg = $baselineSpots->avg('score');
        }

        $delta = $recentAvg - $baselineAvg;

        if ($delta >= 1.0) {
            return 'improving';
        }

        if ($delta <= -1.0) {
            return 'slipping';
        }

        return 'stable';
    }

    private function findBiggestWin(array $analyses): ?string
    {
        // Find the dimension with highest score or most improvement
        $improving = array_filter($analyses, fn ($d) => $d->isImproving());

        if (! empty($improving)) {
            usort($improving, fn ($a, $b) => $b->averageScore <=> $a->averageScore);

            return $improving[0]->dimensionKey;
        }

        // Fallback to highest scoring dimension
        $sorted = $analyses;
        usort($sorted, fn ($a, $b) => $b->averageScore <=> $a->averageScore);

        return ! empty($sorted) && $sorted[0]->averageScore >= 6
            ? $sorted[0]->dimensionKey
            : null;
    }

    private function findGrowthEdge(array $analyses): ?string
    {
        // Find dimension with most room for improvement (lowest score that isn't a blind spot)
        $sorted = $analyses;
        usort($sorted, fn ($a, $b) => $a->averageScore <=> $b->averageScore);

        foreach ($sorted as $analysis) {
            if (! $analysis->isBlindSpot() && $analysis->averageScore < 7) {
                return $analysis->dimensionKey;
            }
        }

        return null;
    }

    public function hasEnoughData(User $user): bool
    {
        return $this->getSessionCount($user) >= $this->minimumSessions;
    }

    public function canAccessFullInsights(User $user): bool
    {
        return $this->hasProAccess($user) && $this->hasEnoughData($user);
    }

    public function shouldShowTeaser(User $user): bool
    {
        if ($this->hasProAccess($user)) {
            return false;
        }

        if (! $this->hasEnoughData($user)) {
            return false;
        }

        $analysis = $this->analyze($user);

        return $analysis->hasBlindSpots();
    }

    public function getTeaserData(User $user): ?array
    {
        if (! $this->shouldShowTeaser($user)) {
            return null;
        }

        $analysis = $this->analyze($user);

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
        $hasEnoughData = $this->hasEnoughData($user);
        $hasProAccess = $this->hasProAccess($user);
        $totalSessions = $this->getSessionCount($user);

        $blindSpotCount = 0;
        if ($hasEnoughData) {
            $analysis = $this->analyze($user);
            $blindSpotCount = $analysis->getBlindSpotCount();
        }

        return [
            'hasEnoughData' => $hasEnoughData,
            'hasProAccess' => $hasProAccess,
            'canAccessFullInsights' => $hasEnoughData && $hasProAccess,
            'showTeaser' => $this->shouldShowTeaser($user),
            'totalSessions' => $totalSessions,
            'minimumSessions' => $this->minimumSessions,
            'sessionsUntilInsights' => max(0, $this->minimumSessions - $totalSessions),
            'blindSpotCount' => $blindSpotCount,
        ];
    }

    public function getHistoricalTrends(User $user, int $weeks = 8): array
    {
        $trends = [];
        $now = now();

        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $spots = BlindSpot::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->get();

            if ($spots->isEmpty()) {
                $trends[] = [
                    'week' => $weekStart->format('M j'),
                    'data' => null,
                    'sessions' => 0,
                    'responses' => 0,
                ];

                continue;
            }

            // Calculate average score per dimension for this week
            $dimensionScores = $spots->groupBy('dimension_key')
                ->map(fn ($group) => round($group->avg('score'), 1))
                ->toArray();

            $trends[] = [
                'week' => $weekStart->format('M j'),
                'data' => $dimensionScores,
                'sessions' => $spots->pluck('drill_id')->unique()->count(),
                'responses' => $spots->count(),
            ];
        }

        return $trends;
    }

    private function getSessionCount(User $user): int
    {
        return TrainingSession::where('user_id', $user->id)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->count();
    }

    private function getResponseCount(User $user): int
    {
        return BlindSpot::where('user_id', $user->id)->count();
    }

    public function getMinimumSessions(): int
    {
        return $this->minimumSessions;
    }
}
