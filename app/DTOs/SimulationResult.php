<?php

namespace App\DTOs;

class SimulationResult
{
    public function __construct(
        public array $transcript,
        public array $issues,
        public string $improvedInstructionSet,
        public int $exchangeCount,
    ) {}

    public function toArray(): array
    {
        return [
            'transcript' => $this->transcript,
            'issues' => $this->issues,
            'improved_instruction_set' => $this->improvedInstructionSet,
            'exchange_count' => $this->exchangeCount,
        ];
    }
}
