<?php

namespace App\DTOs;

class DimensionAnalysis
{
    public function __construct(
        public string $dimensionKey,
        public string $label,
        public string $category,
        public float $averageScore,
        public int $sampleSize,
        public string $trend,
        public ?string $latestSuggestion = null,
        public ?string $description = null,
    ) {}

    public function toArray(): array
    {
        return [
            'skill' => $this->dimensionKey,
            'name' => $this->label,
            'category' => $this->category,
            'description' => $this->description,
            'averageScore' => round($this->averageScore, 1),
            'scoreLevel' => $this->getScoreLevel(),
            'sampleSize' => $this->sampleSize,
            'trend' => $this->trend,
            'suggestion' => $this->latestSuggestion,
        ];
    }

    /**
     * A dimension is a blind spot if average score is 4 or below.
     */
    public function isBlindSpot(): bool
    {
        return $this->averageScore <= 4.0;
    }

    public function isImproving(): bool
    {
        return $this->trend === 'improving';
    }

    public function isSlipping(): bool
    {
        return $this->trend === 'slipping';
    }

    public function isStable(): bool
    {
        return $this->trend === 'stable';
    }

    /**
     * Score level for display (low, mid, high).
     */
    public function getScoreLevel(): string
    {
        if ($this->averageScore <= 4) {
            return 'low';
        }
        if ($this->averageScore <= 6) {
            return 'mid';
        }
        return 'high';
    }
}
