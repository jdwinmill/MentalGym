<?php

namespace App\DTOs;

use Carbon\Carbon;

class BlindSpotAnalysis
{
    public function __construct(
        public bool $hasEnoughData,
        public int $totalSessions,
        public int $totalResponses,
        public array $blindSpots,
        public array $improving,
        public array $stable,
        public array $slipping,
        public array $universalPatterns,
        public ?string $biggestGap,
        public ?string $biggestWin,
        public Carbon $analyzedAt,
    ) {}

    public static function insufficient(int $totalSessions = 0, int $totalResponses = 0): self
    {
        return new self(
            hasEnoughData: false,
            totalSessions: $totalSessions,
            totalResponses: $totalResponses,
            blindSpots: [],
            improving: [],
            stable: [],
            slipping: [],
            universalPatterns: [],
            biggestGap: null,
            biggestWin: null,
            analyzedAt: now(),
        );
    }

    public function toArray(): array
    {
        return [
            'hasEnoughData' => $this->hasEnoughData,
            'totalSessions' => $this->totalSessions,
            'totalResponses' => $this->totalResponses,
            'blindSpots' => array_map(fn ($s) => $s->toArray(), $this->blindSpots),
            'improving' => array_map(fn ($s) => $s->toArray(), $this->improving),
            'stable' => array_map(fn ($s) => $s->toArray(), $this->stable),
            'slipping' => array_map(fn ($s) => $s->toArray(), $this->slipping),
            'universalPatterns' => array_map(fn ($p) => $p->toArray(), $this->universalPatterns),
            'biggestGap' => $this->biggestGap,
            'biggestWin' => $this->biggestWin,
            'analyzedAt' => $this->analyzedAt->toIso8601String(),
        ];
    }

    public function getBlindSpotCount(): int
    {
        return count($this->blindSpots);
    }

    public function hasBlindSpots(): bool
    {
        return count($this->blindSpots) > 0;
    }

    public function hasImprovements(): bool
    {
        return count($this->improving) > 0;
    }

    public function hasRegressions(): bool
    {
        return count($this->slipping) > 0;
    }

    public function getProblematicUniversalPatterns(): array
    {
        return array_filter($this->universalPatterns, fn ($p) => $p->isProblematic());
    }
}
