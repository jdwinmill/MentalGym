<?php

namespace Database\Factories;

use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrainingSession>
 */
class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $endedAt = (clone $startedAt)->modify('+'.fake()->numberBetween(5, 30).' minutes');

        return [
            'user_id' => User::factory(),
            'practice_mode_id' => PracticeMode::factory(),
            'level_at_start' => fake()->numberBetween(1, 5),
            'exchange_count' => fake()->numberBetween(3, 15),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $startedAt->diff($endedAt)->s + ($startedAt->diff($endedAt)->i * 60),
            'status' => TrainingSession::STATUS_COMPLETED,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'ended_at' => null,
            'duration_seconds' => null,
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingSession::STATUS_COMPLETED,
        ]);
    }

    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TrainingSession::STATUS_ABANDONED,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forMode(PracticeMode $mode): static
    {
        return $this->state(fn (array $attributes) => [
            'practice_mode_id' => $mode->id,
        ]);
    }
}
