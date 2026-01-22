<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserAnswer model representing individual question responses.
 *
 * @property int $id
 * @property int $user_lesson_attempt_id
 * @property int $question_id
 * @property int|null $answer_option_id
 * @property string|null $answer_text
 * @property bool $is_correct
 * @property int|null $time_to_answer_seconds
 * @property \Illuminate\Support\Carbon $answered_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserAnswer extends Model
{
    protected $fillable = [
        'user_lesson_attempt_id',
        'question_id',
        'answer_option_id',
        'answer_text',
        'is_correct',
        'time_to_answer_seconds',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'time_to_answer_seconds' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * Get the lesson attempt for this answer.
     */
    public function lessonAttempt(): BelongsTo
    {
        return $this->belongsTo(UserLessonAttempt::class, 'user_lesson_attempt_id');
    }

    /**
     * Get the question for this answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id');
    }

    /**
     * Get the selected answer option if any.
     */
    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class, 'answer_option_id');
    }

    /**
     * Get the feedback for this answer.
     */
    public function getFeedback(): ?AnswerFeedback
    {
        if (! $this->answer_option_id) {
            return null;
        }

        return $this->question->getFeedbackForOption($this->answer_option_id);
    }

    /**
     * Get the pattern tag from the feedback if any.
     */
    public function getPatternTag(): ?string
    {
        $feedback = $this->getFeedback();

        return $feedback?->pattern_tag;
    }

    /**
     * Check if this answer has a pattern tag.
     */
    public function hasPatternTag(): bool
    {
        return $this->getPatternTag() !== null;
    }

    /**
     * Get time to answer in a human-readable format.
     */
    public function getTimeFormatted(): string
    {
        if (! $this->time_to_answer_seconds) {
            return '0s';
        }

        if ($this->time_to_answer_seconds < 60) {
            return $this->time_to_answer_seconds.'s';
        }

        $minutes = floor($this->time_to_answer_seconds / 60);
        $seconds = $this->time_to_answer_seconds % 60;

        return sprintf('%dm %ds', $minutes, $seconds);
    }

    /**
     * Check if this was a quick answer (under 5 seconds).
     */
    public function wasQuickAnswer(): bool
    {
        return $this->time_to_answer_seconds !== null && $this->time_to_answer_seconds < 5;
    }

    /**
     * Check if this was a slow answer (over 30 seconds).
     */
    public function wasSlowAnswer(): bool
    {
        return $this->time_to_answer_seconds !== null && $this->time_to_answer_seconds > 30;
    }

    /**
     * Scope to get correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }
}
