<?php

namespace App\Services;

use App\DTOs\BlindSpotAnalysis;
use App\DTOs\BlindSpotPageData;
use App\DTOs\DimensionAnalysis;
use App\DTOs\GatedBlindSpotAnalysis;
use App\DTOs\PrimaryBlindSpot;
use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\SkillDimension;
use App\Models\TrainingSession;
use App\Models\User;

class BlindSpotService
{
    private int $minimumSessions = 5;

    private int $minimumOccurrences = 3;

    private int $recentDays = 7;

    private int $baselineDays = 30;

    // =========================================================================
    // NEW PAGE DATA METHOD (Primary Blind Spot focused)
    // =========================================================================

    public function getPageData(User $user): BlindSpotPageData
    {
        $totalSessions = $this->getSessionCount($user);
        $totalResponses = $this->getResponseCount($user);

        if (! $this->hasEnoughData($user)) {
            return BlindSpotPageData::insufficientData(
                $totalSessions,
                $totalResponses,
                $this->minimumSessions
            );
        }

        if (! $this->hasProAccess($user)) {
            return BlindSpotPageData::locked(
                $totalSessions,
                $totalResponses,
                $this->minimumSessions
            );
        }

        $primaryBlindSpot = $this->findPrimaryBlindSpot($user);

        return BlindSpotPageData::unlocked(
            $totalSessions,
            $totalResponses,
            $primaryBlindSpot
        );
    }

    /**
     * Find the ONE primary blind spot for a user.
     *
     * Logic:
     * 1. Group by dimension_key, calculate avg score, count occurrences
     * 2. Filter to dimensions with 3+ occurrences
     * 3. Calculate trend (compare recent vs older scores)
     * 4. Sort by avg score ascending, deprioritize improving trends
     * 5. Take the top result
     */
    public function findPrimaryBlindSpot(User $user): ?PrimaryBlindSpot
    {
        $allSpots = BlindSpot::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($allSpots->isEmpty()) {
            return null;
        }

        $byDimension = $allSpots->groupBy('dimension_key');
        $dimensions = SkillDimension::active()->get()->keyBy('key');

        $candidates = [];

        foreach ($byDimension as $dimensionKey => $spots) {
            if ($spots->count() < $this->minimumOccurrences) {
                continue;
            }

            $dimension = $dimensions->get($dimensionKey);
            if (! $dimension) {
                continue;
            }

            $avgScore = $spots->avg('score');
            $trend = $this->calculateTrend($spots);

            // Count unique sessions (via drill_id as proxy)
            $sessionsWithDimension = $spots->pluck('drill_id')->unique()->count();

            $candidates[] = [
                'dimensionKey' => $dimensionKey,
                'dimension' => $dimension,
                'avgScore' => $avgScore,
                'occurrences' => $spots->count(),
                'sessionsWithDimension' => $sessionsWithDimension,
                'trend' => $trend,
                'spots' => $spots,
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        // Sort: lowest avg score first, but deprioritize improving trends
        usort($candidates, function ($a, $b) {
            // Improving trends get pushed down
            $aIsImproving = $a['trend'] === 'improving';
            $bIsImproving = $b['trend'] === 'improving';

            if ($aIsImproving && ! $bIsImproving) {
                return 1;
            }
            if (! $aIsImproving && $bIsImproving) {
                return -1;
            }

            // Otherwise sort by average score (lowest first)
            return $a['avgScore'] <=> $b['avgScore'];
        });

        $winner = $candidates[0];

        // Get recent suggestions as evidence (deduplicated, with drill context)
        $recentSuggestions = $winner['spots']
            ->whereNotNull('suggestion')
            ->sortByDesc('created_at')
            ->unique('suggestion')
            ->take(3)
            ->map(function ($spot) {
                $spot->load('drill');

                return [
                    'drillName' => $spot->drill?->name,
                    'suggestion' => $spot->suggestion,
                ];
            })
            ->values()
            ->toArray();

        // Find a recommended drill that targets this dimension
        $recommendedDrill = $this->findRecommendedDrill($winner['dimensionKey']);

        return new PrimaryBlindSpot(
            dimensionKey: $winner['dimensionKey'],
            label: $winner['dimension']->label,
            category: $winner['dimension']->category,
            description: $winner['dimension']->description,
            averageScore: $winner['avgScore'],
            occurrences: $winner['occurrences'],
            sessionsWithDimension: $winner['sessionsWithDimension'],
            trend: $winner['trend'],
            recentSuggestions: $recentSuggestions,
            recommendedDrill: $recommendedDrill,
        );
    }

    /**
     * Find a drill that targets the given dimension.
     */
    private function findRecommendedDrill(string $dimensionKey): ?array
    {
        $drill = Drill::whereJsonContains('dimensions', $dimensionKey)
            ->with('practiceMode:id,name,slug')
            ->first();

        if (! $drill) {
            return null;
        }

        return [
            'id' => $drill->id,
            'name' => $drill->name,
            'practiceMode' => [
                'id' => $drill->practiceMode->id,
                'name' => $drill->practiceMode->name,
                'slug' => $drill->practiceMode->slug,
            ],
        ];
    }

    // =========================================================================
    // LEGACY METHODS (for API, emails, etc.)
    // =========================================================================

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
     * @return DimensionAnalysis[]
     */
    private function analyzeDimensions(User $user): array
    {
        $allSpots = BlindSpot::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($this->baselineDays))
            ->get();

        $allByDimension = $allSpots->groupBy('dimension_key');
        $dimensions = SkillDimension::active()->get()->keyBy('key');

        $analyses = [];

        foreach ($allByDimension as $dimensionKey => $spots) {
            if ($spots->count() < 3) {
                continue;
            }

            $dimension = $dimensions->get($dimensionKey);
            if (! $dimension) {
                continue;
            }

            $allAvg = $spots->avg('score');
            $trend = $this->calculateTrend($spots);

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

    private function calculateTrend($spots): string
    {
        if ($spots->count() < 4) {
            return 'new';
        }

        $sorted = $spots->sortBy('created_at')->values();

        $oldestSpot = $sorted->first();
        $hasOlderBaseline = $oldestSpot->created_at < now()->subDays($this->recentDays);

        if ($hasOlderBaseline) {
            $recentSpots = $sorted->filter(fn ($s) => $s->created_at >= now()->subDays($this->recentDays));
            $baselineSpots = $sorted->filter(fn ($s) => $s->created_at < now()->subDays($this->recentDays));

            if ($recentSpots->isEmpty() || $baselineSpots->isEmpty()) {
                return 'new';
            }

            $recentAvg = $recentSpots->avg('score');
            $baselineAvg = $baselineSpots->avg('score');
        } else {
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
        $improving = array_filter($analyses, fn ($d) => $d->isImproving());

        if (! empty($improving)) {
            usort($improving, fn ($a, $b) => $b->averageScore <=> $a->averageScore);

            return $improving[0]->dimensionKey;
        }

        $sorted = $analyses;
        usort($sorted, fn ($a, $b) => $b->averageScore <=> $a->averageScore);

        return ! empty($sorted) && $sorted[0]->averageScore >= 6
            ? $sorted[0]->dimensionKey
            : null;
    }

    private function findGrowthEdge(array $analyses): ?string
    {
        $sorted = $analyses;
        usort($sorted, fn ($a, $b) => $a->averageScore <=> $b->averageScore);

        foreach ($sorted as $analysis) {
            if (! $analysis->isBlindSpot() && $analysis->averageScore < 7) {
                return $analysis->dimensionKey;
            }
        }

        return null;
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

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    public function hasEnoughData(User $user): bool
    {
        return $this->getSessionCount($user) >= $this->minimumSessions;
    }

    public function canAccessFullInsights(User $user): bool
    {
        return $this->hasProAccess($user) && $this->hasEnoughData($user);
    }

    public function hasProAccess(User $user): bool
    {
        return $user->hasPaidPlan();
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
