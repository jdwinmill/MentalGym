<?php

namespace App\DTOs;

use Carbon\Carbon;

class GatedBlindSpotAnalysis
{
    public function __construct(
        // Always visible
        public bool $hasEnoughData,
        public int $totalSessions,
        public int $totalResponses,
        public int $blindSpotCount,
        public bool $hasBlindSpots,

        // Gating info
        public bool $isUnlocked,
        public string $requiredPlan,
        public ?string $gateReason,
        public int $responsesUntilInsights,

        // Only populated if unlocked
        public ?array $blindSpots,
        public ?array $improving,
        public ?array $slipping,
        public ?array $stable,
        public ?string $biggestGap,
        public ?string $biggestWin,
        public ?string $growthEdge,
        public ?array $allSkills,

        public Carbon $analyzedAt,
    ) {}

    public static function locked(BlindSpotAnalysis $analysis, int $minimumResponses): self
    {
        return new self(
            hasEnoughData: $analysis->hasEnoughData,
            totalSessions: $analysis->totalSessions,
            totalResponses: $analysis->totalResponses,
            blindSpotCount: $analysis->getBlindSpotCount(),
            hasBlindSpots: $analysis->hasBlindSpots(),

            isUnlocked: false,
            requiredPlan: 'pro',
            gateReason: 'requires_upgrade',
            responsesUntilInsights: 0,

            blindSpots: null,
            improving: null,
            slipping: null,
            stable: null,
            biggestGap: null,
            biggestWin: null,
            growthEdge: null,
            allSkills: null,

            analyzedAt: $analysis->analyzedAt,
        );
    }

    public static function insufficientData(int $totalSessions, int $totalResponses, int $minimumResponses): self
    {
        return new self(
            hasEnoughData: false,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            blindSpotCount: 0,
            hasBlindSpots: false,

            isUnlocked: false,
            requiredPlan: 'pro',
            gateReason: 'insufficient_data',
            responsesUntilInsights: max(0, $minimumResponses - $totalResponses),

            blindSpots: null,
            improving: null,
            slipping: null,
            stable: null,
            biggestGap: null,
            biggestWin: null,
            growthEdge: null,
            allSkills: null,

            analyzedAt: now(),
        );
    }

    public static function unlocked(BlindSpotAnalysis $analysis): self
    {
        return new self(
            hasEnoughData: $analysis->hasEnoughData,
            totalSessions: $analysis->totalSessions,
            totalResponses: $analysis->totalResponses,
            blindSpotCount: $analysis->getBlindSpotCount(),
            hasBlindSpots: $analysis->hasBlindSpots(),

            isUnlocked: true,
            requiredPlan: 'pro',
            gateReason: null,
            responsesUntilInsights: 0,

            blindSpots: $analysis->blindSpots,
            improving: $analysis->improving,
            slipping: $analysis->slipping,
            stable: $analysis->stable,
            biggestGap: $analysis->biggestGap,
            biggestWin: $analysis->biggestWin,
            growthEdge: $analysis->growthEdge,
            allSkills: $analysis->allSkills,

            analyzedAt: $analysis->analyzedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'hasEnoughData' => $this->hasEnoughData,
            'totalSessions' => $this->totalSessions,
            'totalResponses' => $this->totalResponses,
            'blindSpotCount' => $this->blindSpotCount,
            'hasBlindSpots' => $this->hasBlindSpots,

            'isUnlocked' => $this->isUnlocked,
            'requiredPlan' => $this->requiredPlan,
            'gateReason' => $this->gateReason,
            'responsesUntilInsights' => $this->responsesUntilInsights,

            'blindSpots' => is_array($this->blindSpots) ? array_map(fn ($s) => $s->toArray(), $this->blindSpots) : null,
            'improving' => is_array($this->improving) ? array_map(fn ($s) => $s->toArray(), $this->improving) : null,
            'slipping' => is_array($this->slipping) ? array_map(fn ($s) => $s->toArray(), $this->slipping) : null,
            'stable' => is_array($this->stable) ? array_map(fn ($s) => $s->toArray(), $this->stable) : null,
            'biggestGap' => $this->biggestGap,
            'biggestWin' => $this->biggestWin,
            'growthEdge' => $this->growthEdge,
            'allSkills' => is_array($this->allSkills) ? array_map(fn ($s) => $s->toArray(), $this->allSkills) : null,

            'analyzedAt' => $this->analyzedAt->toIso8601String(),
        ];
    }
}
