<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LessonQuestion model representing assessment questions tied to lessons.
 *
 * @property int $id
 * @property int $lesson_id
 * @property int $skill_level_id
 * @property int|null $related_block_id
 * @property string $question_text
 * @property string $question_type
 * @property string|null $correct_answer
 * @property string|null $explanation
 * @property int $points
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LessonQuestion extends Model
{
    protected $table = 'lesson_questions';

    protected $fillable = [
        'lesson_id',
        'skill_level_id',
        'related_block_id',
        'question_text',
        'question_type',
        'correct_answer',
        'explanation',
        'points',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Question type constants.
     */
    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_TRUE_FALSE = 'true_false';
    public const TYPE_OPEN_ENDED = 'open_ended';

    /**
     * Get the lesson this question belongs to.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the skill level this question is associated with.
     */
    public function skillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class);
    }

    /**
     * Get the related content block if any.
     */
    public function relatedBlock(): BelongsTo
    {
        return $this->belongsTo(LessonContentBlock::class, 'related_block_id');
    }

    /**
     * Get all answer options for this question.
     */
    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class, 'question_id')->orderBy('sort_order');
    }

    /**
     * Get all feedback for this question.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(AnswerFeedback::class, 'question_id');
    }

    /**
     * Get all user answers for this question.
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'question_id');
    }

    /**
     * Get the correct answer option(s) for multiple choice questions.
     */
    public function getCorrectAnswerOptions()
    {
        return $this->answerOptions()->where('is_correct', true)->get();
    }

    /**
     * Check if this is a multiple choice question.
     */
    public function isMultipleChoice(): bool
    {
        return $this->question_type === self::TYPE_MULTIPLE_CHOICE;
    }

    /**
     * Check if this is a true/false question.
     */
    public function isTrueFalse(): bool
    {
        return $this->question_type === self::TYPE_TRUE_FALSE;
    }

    /**
     * Check if this is an open-ended question.
     */
    public function isOpenEnded(): bool
    {
        return $this->question_type === self::TYPE_OPEN_ENDED;
    }

    /**
     * Check if an answer is correct.
     */
    public function isAnswerCorrect(int $answerOptionId): bool
    {
        return $this->answerOptions()
            ->where('id', $answerOptionId)
            ->where('is_correct', true)
            ->exists();
    }

    /**
     * Get the accuracy rate for this question across all users.
     */
    public function getAccuracyRate(): float
    {
        $totalAnswers = $this->userAnswers()->count();

        if ($totalAnswers === 0) {
            return 0.0;
        }

        $correctAnswers = $this->userAnswers()->where('is_correct', true)->count();

        return round(($correctAnswers / $totalAnswers) * 100, 2);
    }

    /**
     * Get feedback for a specific answer option.
     */
    public function getFeedbackForOption(int $answerOptionId): ?AnswerFeedback
    {
        return $this->feedback()->where('answer_option_id', $answerOptionId)->first();
    }
}
