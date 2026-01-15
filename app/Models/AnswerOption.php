<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * AnswerOption model representing multiple choice options for questions.
 *
 * @property int $id
 * @property int $question_id
 * @property string $option_text
 * @property bool $is_correct
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AnswerOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the question this option belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id');
    }

    /**
     * Get the feedback for this answer option.
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(AnswerFeedback::class, 'answer_option_id');
    }

    /**
     * Get all user answers that selected this option.
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'answer_option_id');
    }

    /**
     * Get how many times this option has been selected.
     */
    public function getSelectionCount(): int
    {
        return $this->userAnswers()->count();
    }

    /**
     * Get the selection rate as a percentage of all answers to the question.
     */
    public function getSelectionRate(): float
    {
        $totalAnswers = $this->question->userAnswers()->count();

        if ($totalAnswers === 0) {
            return 0.0;
        }

        return round(($this->getSelectionCount() / $totalAnswers) * 100, 2);
    }
}
