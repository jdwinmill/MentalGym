<?php

namespace App\DTOs;

class WeeklyEmailContent
{
    public function __construct(
        public string $subjectLine,
        public array $improving,
        public array $needsWork,
        public string $patternToWatch,
        public string $weeklyFocus,
    ) {}

    public function hasImprovements(): bool
    {
        return !empty($this->improving);
    }

    public function toArray(): array
    {
        return [
            'subjectLine' => $this->subjectLine,
            'improving' => $this->improving,
            'needsWork' => $this->needsWork,
            'patternToWatch' => $this->patternToWatch,
            'weeklyFocus' => $this->weeklyFocus,
        ];
    }
}
