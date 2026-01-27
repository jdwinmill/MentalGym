<?php

namespace App\DTOs;

class PrimaryBlindSpot
{
    public function __construct(
        public string $dimensionKey,
        public string $label,
        public string $category,
        public ?string $description,
        public float $averageScore,
        public int $occurrences,
        public int $sessionsWithDimension,
        public string $trend,
        public array $recentSuggestions,
        public ?array $recommendedDrill,
    ) {}

    public function toArray(): array
    {
        return [
            'dimensionKey' => $this->dimensionKey,
            'label' => $this->label,
            'category' => $this->category,
            'description' => $this->description,
            'averageScore' => round($this->averageScore, 1),
            'occurrences' => $this->occurrences,
            'sessionsWithDimension' => $this->sessionsWithDimension,
            'trend' => $this->trend,
            'recentSuggestions' => $this->recentSuggestions,
            'recommendedDrill' => $this->recommendedDrill,
        ];
    }
}
