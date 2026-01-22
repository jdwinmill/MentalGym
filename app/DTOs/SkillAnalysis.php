<?php

namespace App\DTOs;

class SkillAnalysis
{
    public function __construct(
        public string $skill,
        public string $trend,
        public float $currentRate,
        public float $baselineRate,
        public int $sampleSize,
        public ?string $primaryIssue,
        public ?string $context,
        public array $failingCriteria,
        public ?string $practiceMode = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $target = null,
        public array $tips = [],
        public array $failingCriteriaLabels = [],
        public array $contextBreakdown = [],
    ) {}

    public function toArray(): array
    {
        return [
            'skill' => $this->skill,
            'trend' => $this->trend,
            'currentRate' => $this->currentRate,
            'baselineRate' => $this->baselineRate,
            'sampleSize' => $this->sampleSize,
            'primaryIssue' => $this->primaryIssue,
            'context' => $this->context,
            'failingCriteria' => $this->failingCriteria,
            'practiceMode' => $this->practiceMode,
            'name' => $this->name,
            'description' => $this->description,
            'target' => $this->target,
            'tips' => $this->tips,
            'failingCriteriaLabels' => $this->failingCriteriaLabels,
            'contextBreakdown' => $this->contextBreakdown,
        ];
    }

    public function isBlindSpot(): bool
    {
        return $this->currentRate >= 0.6;
    }

    public function isImproving(): bool
    {
        return $this->trend === 'improving';
    }

    public function isSlipping(): bool
    {
        return $this->trend === 'slipping';
    }

    public function isStuck(): bool
    {
        return $this->trend === 'stuck';
    }
}
