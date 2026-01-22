<?php

namespace Database\Factories;

use App\Models\PracticeMode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PracticeMode>
 */
class PracticeModeFactory extends Factory
{
    protected $model = PracticeMode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'instruction_set' => 'You are a training assistant at Level {{level}}. Respond with JSON containing a "type" field.',
            'config' => [
                'input_character_limit' => 500,
                'reflection_character_limit' => 200,
                'max_response_tokens' => 800,
                'max_history_exchanges' => 10,
                'model' => 'claude-sonnet-4-20250514',
            ],
            'required_plan' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the mode is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the mode requires a specific plan.
     */
    public function requiresPlan(string $plan): static
    {
        return $this->state(fn (array $attributes) => [
            'required_plan' => $plan,
        ]);
    }
}
