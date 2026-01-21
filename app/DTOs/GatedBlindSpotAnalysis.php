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
        public int $sessionsUntilInsights,

        // Only populated if unlocked
        public ?array $blindSpots,
        public ?array $improving,
        public ?array $slipping,
        public ?array $stable,
        public ?array $universalPatterns,
        public ?string $biggestGap,
        public ?string $biggestWin,

        public Carbon $analyzedAt,
    ) {}

    public static function locked(BlindSpotAnalysis $analysis, int $minimumSessions): self
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
            sessionsUntilInsights: 0,

            blindSpots: null,
            improving: null,
            slipping: null,
            stable: null,
            universalPatterns: null,
            biggestGap: null,
            biggestWin: null,

            analyzedAt: $analysis->analyzedAt,
        );
    }

    public static function insufficientData(int $totalSessions, int $totalResponses, int $minimumSessions): self
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
            sessionsUntilInsights: max(0, $minimumSessions - $totalSessions),

            blindSpots: null,
            improving: null,
            slipping: null,
            stable: null,
            universalPatterns: null,
            biggestGap: null,
            biggestWin: null,

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
            sessionsUntilInsights: 0,

            blindSpots: $analysis->blindSpots,
            improving: $analysis->improving,
            slipping: $analysis->slipping,
            stable: $analysis->stable,
            universalPatterns: $analysis->universalPatterns,
            biggestGap: $analysis->biggestGap,
            biggestWin: $analysis->biggestWin,

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
            'sessionsUntilInsights' => $this->sessionsUntilInsights,

            'blindSpots' => is_array($this->blindSpots) ? array_map(fn($s) => $s->toArray(), $this->blindSpots) : null,
            'improving' => is_array($this->improving) ? array_map(fn($s) => $s->toArray(), $this->improving) : null,
            'slipping' => is_array($this->slipping) ? array_map(fn($s) => $s->toArray(), $this->slipping) : null,
            'stable' => is_array($this->stable) ? array_map(fn($s) => $s->toArray(), $this->stable) : null,
            'universalPatterns' => is_array($this->universalPatterns) ? array_map(fn($p) => $p->toArray(), $this->universalPatterns) : null,
            'biggestGap' => $this->biggestGap,
            'biggestWin' => $this->biggestWin,

            'analyzedAt' => $this->analyzedAt->toIso8601String(),
        ];
    }
}
