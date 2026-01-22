<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AnswerFeedback model representing personalized feedback for answer choices.
 *
 * @property int $id
 * @property int $question_id
 * @property int|null $answer_option_id
 * @property string $feedback_text
 * @property string|null $pattern_tag
 * @property string|null $severity
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AnswerFeedback extends Model
{
    protected $table = 'answer_feedback';

    protected $fillable = [
        'question_id',
        'answer_option_id',
        'feedback_text',
        'pattern_tag',
        'severity',
    ];

    /**
     * Severity level constants.
     */
    public const SEVERITY_CRITICAL_MISS = 'critical_miss';

    public const SEVERITY_MINOR_MISS = 'minor_miss';

    public const SEVERITY_CORRECT = 'correct';

    /**
     * Get the question this feedback belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id');
    }

    /**
     * Get the answer option this feedback is for.
     */
    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class, 'answer_option_id');
    }

    /**
     * Check if this feedback indicates a critical miss.
     */
    public function isCriticalMiss(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL_MISS;
    }

    /**
     * Check if this feedback indicates a minor miss.
     */
    public function isMinorMiss(): bool
    {
        return $this->severity === self::SEVERITY_MINOR_MISS;
    }

    /**
     * Check if this feedback is for a correct answer.
     */
    public function isCorrect(): bool
    {
        return $this->severity === self::SEVERITY_CORRECT;
    }

    /**
     * Check if this feedback has a pattern tag.
     */
    public function hasPatternTag(): bool
    {
        return ! empty($this->pattern_tag);
    }
}
