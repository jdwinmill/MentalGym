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

            $trends[] = [
                'week' => $weekStart->format('M j'),
                'data' => [
                    'authority' => $this->calculateFailureRate($scores, 'hedging'),
                    'brevity' => $this->calculateFailureRate($scores, 'ran_long'),
                    'structure' => $this->calculateFailureRate($scores, 'structure_followed', invert: true),
                    'composure' => $this->calculateFailureRate($scores, 'calm_tone', invert: true),
                    'directness' => $this->calculateFailureRate($scores, 'direct_opening', invert: true),
                ],
                'sessions' => $scores->pluck('training_session_id')->unique()->count(),
                'responses' => $scores->count(),
            ];
        }

        return $trends;
    }

    private function calculateFailureRate(Collection $scores, string $criteria, bool $invert = false): ?float
    {
        $relevant = $scores->filter(fn ($s) => isset($s->scores[$criteria]));

        if ($relevant->isEmpty()) {
            return null;
        }

        $failures = $relevant->filter(fn ($s) => $invert
            ? $s->scores[$criteria] === false
            : $s->scores[$criteria] === true
        )->count();

        return round($failures / $relevant->count(), 2);
    }
}
