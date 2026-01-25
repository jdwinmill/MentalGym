<?php

namespace Database\Factories;

use App\Models\BlindSpot;
use App\Models\Drill;
use App\Models\SkillDimension;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlindSpot>
 */
class BlindSpotFactory extends Factory
{
    protected $model = BlindSpot::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'drill_id' => Drill::factory(),
            'dimension_key' => fn () => $this->getOrCreateDimension()->key,
            'score' => fake()->numberBetween(1, 10),
            'suggestion' => null,
            'created_at' => now(),
        ];
    }

    /**
     * Get an existing dimension or create one.
     */
    private function getOrCreateDimension(): SkillDimension
    {
        $dimension = SkillDimension::inRandomOrder()->first();

        if (! $dimension) {
            $dimension = SkillDimension::factory()->create();
        }

        return $dimension;
    }

    /**
     * Create a blind spot with low scores (indicating a blind spot).
     */
    public function withLowScores(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(1, 4),
            'suggestion' => 'Consider practicing this skill more frequently.',
        ]);
    }

    /**
     * Create a blind spot with good scores.
     */
    public function withGoodScores(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(7, 10),
            'suggestion' => null,
        ]);
    }

    /**
     * Create a blind spot with mid-range scores.
     */
    public function withMidScores(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(5, 6),
            'suggestion' => 'Good progress, keep working on this area.',
        ]);
    }

    /**
     * Associate with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Associate with a specific drill.
     */
    public function forDrill(Drill $drill): static
    {
        return $this->state(fn (array $attributes) => [
            'drill_id' => $drill->id,
        ]);
    }

    /**
     * Set a specific dimension key (creates the dimension if it doesn't exist).
     */
    public function forDimension(string $dimensionKey): static
    {
        return $this->state(function (array $attributes) use ($dimensionKey) {
            // Ensure the dimension exists
            $dimension = SkillDimension::where('key', $dimensionKey)->first();
            if (! $dimension) {
                $dimension = SkillDimension::factory()->create(['key' => $dimensionKey]);
            }

            return [
                'dimension_key' => $dimensionKey,
            ];
        });
    }

    /**
     * Create with a specific date.
     */
    public function createdAt(\DateTimeInterface $date): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $date,
        ]);
    }
}
