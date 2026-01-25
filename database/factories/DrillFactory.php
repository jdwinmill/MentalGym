<?php

namespace Database\Factories;

use App\Models\Drill;
use App\Models\PracticeMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Drill>
 */
class DrillFactory extends Factory
{
    protected $model = Drill::class;

    public function definition(): array
    {
        return [
            'practice_mode_id' => PracticeMode::factory(),
            'name' => fake()->words(3, true),
            'scenario_instruction_set' => fake()->paragraphs(2, true),
            'evaluation_instruction_set' => fake()->paragraphs(2, true),
            'position' => fake()->numberBetween(0, 5),
            'timer_seconds' => null,
            'input_type' => 'text',
            'config' => [],
            'dimensions' => ['assertiveness', 'clarity'],
        ];
    }

    public function forMode(PracticeMode $mode): static
    {
        return $this->state(fn (array $attributes) => [
            'practice_mode_id' => $mode->id,
        ]);
    }

    public function withTimer(int $seconds = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'timer_seconds' => $seconds,
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'input_type' => 'multiple_choice',
        ]);
    }

    public function atPosition(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }
}
