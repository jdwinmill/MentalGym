<?php

namespace App\DTOs;

class BlindSpotPageData
{
    public function __construct(
        public bool $hasEnoughData,
        public int $totalSessions,
        public int $totalResponses,
        public int $responsesRemaining,
        public bool $isUnlocked,
        public ?string $gateReason,
        public ?PrimaryBlindSpot $primaryBlindSpot,
    ) {}

    public static function insufficientData(int $totalSessions, int $totalResponses, int $requiredResponses): self
    {
        return new self(
            hasEnoughData: false,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            responsesRemaining: max(0, $requiredResponses - $totalResponses),
            isUnlocked: false,
            gateReason: 'insufficient_data',
            primaryBlindSpot: null,
        );
    }

    public static function locked(int $totalSessions, int $totalResponses, int $requiredResponses): self
    {
        return new self(
            hasEnoughData: true,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            responsesRemaining: 0,
            isUnlocked: false,
            gateReason: 'requires_pro',
            primaryBlindSpot: null,
        );
    }

    public static function unlocked(int $totalSessions, int $totalResponses, ?PrimaryBlindSpot $primaryBlindSpot): self
    {
        return new self(
            hasEnoughData: true,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            responsesRemaining: 0,
            isUnlocked: true,
            gateReason: null,
            primaryBlindSpot: $primaryBlindSpot,
        );
    }

    public function toArray(): array
    {
        return [
            'hasEnoughData' => $this->hasEnoughData,
            'totalSessions' => $this->totalSessions,
            'totalResponses' => $this->totalResponses,
            'responsesRemaining' => $this->responsesRemaining,
            'isUnlocked' => $this->isUnlocked,
            'gateReason' => $this->gateReason,
            'primaryBlindSpot' => $this->primaryBlindSpot?->toArray(),
        ];
    }
}
