<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * UserLessonAttempt model representing individual lesson completion records.
 *
 * @property int $id
 * @property int $user_id
 * @property int $lesson_id
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $total_questions
 * @property int|null $correct_answers
 * @property float|null $accuracy_percentage
 * @property int|null $duration_seconds
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserLessonAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'started_at',
        'completed_at',
        'total_questions',
        'correct_answers',
        'accuracy_percentage',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_questions' => 'integer',
            'correct_answers' => 'integer',
            'accuracy_percentage' => 'decimal:2',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the user for this attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lesson for this attempt.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get all content interactions for this attempt.
     */
    public function contentInteractions(): HasMany
    {
        return $this->hasMany(UserContentInteraction::class);
    }

    /**
     * Get all answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }

    /**
     * Calculate and update the accuracy percentage based on answers.
     */
    public function calculateAccuracy(): float
    {
        $totalQuestions = $this->answers()->count();

        if ($totalQuestions === 0) {
            return 0.0;
        }

        $correctAnswers = $this->answers()->where('is_correct', true)->count();
        $accuracy = round(($correctAnswers / $totalQuestions) * 100, 2);

        $this->total_questions = $totalQuestions;
        $this->correct_answers = $correctAnswers;
        $this->accuracy_percentage = $accuracy;
        $this->save();

        return $accuracy;
    }

    /**
     * Check if this attempt is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Check if this attempt is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->completed_at === null;
    }

    /**
     * Mark the attempt as completed.
     */
    public function markCompleted(): bool
    {
        $this->completed_at = now();
        $this->duration_seconds = $this->started_at->diffInSeconds(now());
        $this->calculateAccuracy();

        return $this->save();
    }

    /**
     * Get the duration in a human-readable format.
     */
    public function getDurationFormatted(): string
    {
        if (! $this->duration_seconds) {
            return '0:00';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Check if the user passed this lesson based on the skill level threshold.
     */
    public function passed(): bool
    {
        if (! $this->isCompleted() || $this->accuracy_percentage === null) {
            return false;
        }

        $skillLevel = $this->lesson->skillLevel;

        return $skillLevel->meetsPassThreshold($this->accuracy_percentage / 100);
    }

    /**
     * Get questions that were answered incorrectly.
     */
    public function getIncorrectAnswers()
    {
        return $this->answers()->where('is_correct', false)->with('question')->get();
    }

    /**
     * Scope to get completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope to get in-progress attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }
}
