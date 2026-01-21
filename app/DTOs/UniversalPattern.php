<?php

namespace App\DTOs;

class UniversalPattern
{
    public function __construct(
        public string $criteria,
        public float $rate,
        public int $count,
        public int $total,
        public string $trend,
    ) {}

    public function toArray(): array
    {
        return [
            'criteria' => $this->criteria,
            'rate' => $this->rate,
            'count' => $this->count,
            'total' => $this->total,
            'trend' => $this->trend,
        ];
    }

    public function isProblematic(): bool
    {
        return $this->rate >= 0.6;
    }
}
