<?php

namespace Database\Factories;

use App\Models\DrillScore;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DrillScore>
 */
class DrillScoreFactory extends Factory
{
    protected $model = DrillScore::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'training_session_id' => TrainingSession::factory(),
            'practice_mode_id' => PracticeMode::factory(),
            'drill_type' => fake()->randomElement([
                'compression',
                'executive_communication',
                'problem_solving',
                'story_compression',
                'opener',
            ]),
            'drill_phase' => fake()->randomElement([
                'Compression',
                'Executive Communication',
                'Problem-Solving',
                'Story Compression',
                'The Opener',
            ]),
            'is_iteration' => false,
            'scores' => [
                'hedging' => fake()->boolean(30),
                'filler_phrases' => fake()->numberBetween(0, 5),
                'word_limit_met' => fake()->boolean(70),
                'apology_detected' => fake()->boolean(20),
                'ran_long' => fake()->boolean(25),
                'too_short' => fake()->boolean(15),
            ],
            'user_response' => fake()->paragraph(),
            'word_count' => fake()->numberBetween(10, 150),
            'response_time_seconds' => fake()->numberBetween(30, 300),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forSession(TrainingSession $session): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $session->user_id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $session->practice_mode_id,
        ]);
    }

    public function withHedging(bool $hedging = true): static
    {
        return $this->state(function (array $attributes) use ($hedging) {
            $scores = $attributes['scores'] ?? [];
            $scores['hedging'] = $hedging;

            return ['scores' => $scores];
        });
    }

    public function withAuthorityIssues(): static
    {
        return $this->state(function (array $attributes) {
            $scores = $attributes['scores'] ?? [];
            $scores['hedging'] = true;
            $scores['declarative_sentences'] = false;
            $scores['authority_tone'] = false;
            $scores['clear_position'] = false;

            return [
                'scores' => $scores,
                'drill_type' => 'executive_communication',
                'drill_phase' => 'Executive Communication',
            ];
        });
    }

    public function withClarityIssues(): static
    {
        return $this->state(function (array $attributes) {
            $scores = $attributes['scores'] ?? [];
            $scores['core_point_captured'] = false;
            $scores['jargon_removed'] = false;
            $scores['clarity'] = false;

            return [
                'scores' => $scores,
                'drill_type' => 'compression',
                'drill_phase' => 'Compression',
            ];
        });
    }

    public function withStructureIssues(): static
    {
        return $this->state(function (array $attributes) {
            $scores = $attributes['scores'] ?? [];
            $scores['star_structure'] = false;
            $scores['logical_flow'] = false;
            $scores['structure_followed'] = false;

            return [
                'scores' => $scores,
                'drill_type' => 'story_compression',
                'drill_phase' => 'Story Compression',
            ];
        });
    }

    public function withGoodScores(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'scores' => [
                    'hedging' => false,
                    'filler_phrases' => 0,
                    'word_limit_met' => true,
                    'apology_detected' => false,
                    'ran_long' => false,
                    'too_short' => false,
                    'core_point_captured' => true,
                    'clarity' => true,
                    'declarative_sentences' => true,
                    'authority_tone' => true,
                    'clear_position' => true,
                ],
            ];
        });
    }

    public function iteration(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_iteration' => true,
        ]);
    }

    public function createdDaysAgo(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays($days),
            'updated_at' => now()->subDays($days),
        ]);
    }
}
