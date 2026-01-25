<?php

namespace Database\Factories;

use App\Models\SkillDimension;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillDimensionFactory extends Factory
{
    protected $model = SkillDimension::class;

    public function definition(): array
    {
        $key = fake()->unique()->slug(2, '_');

        return [
            'key' => str_replace('-', '_', $key),
            'label' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['communication', 'reasoning', 'resilience', 'influence', 'self_awareness']),
            'score_anchors' => [
                'low' => fake()->sentence(),
                'mid' => fake()->sentence(),
                'high' => fake()->sentence(),
                'exemplary' => fake()->sentence(),
            ],
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function communication(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'communication',
        ]);
    }

    public function reasoning(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'reasoning',
        ]);
    }
}
