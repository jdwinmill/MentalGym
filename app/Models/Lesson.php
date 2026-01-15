<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Lesson model representing an individual learning session.
 *
 * @property int $id
 * @property int $track_id
 * @property int $skill_level_id
 * @property int $lesson_number
 * @property string $title
 * @property array|null $learning_objectives
 * @property int|null $estimated_duration_minutes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Lesson extends Model
{
    protected $fillable = [
        'track_id',
        'skill_level_id',
        'lesson_number',
        'title',
        'learning_objectives',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'learning_objectives' => 'array',
            'lesson_number' => 'integer',
            'estimated_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the track this lesson belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the skill level this lesson belongs to.
     */
    public function skillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class);
    }

    /**
     * Get all content blocks for this lesson.
     */
    public function contentBlocks(): HasMany
    {
        return $this->hasMany(LessonContentBlock::class)->orderBy('sort_order');
    }

    /**
     * Get all questions for this lesson.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class)->orderBy('sort_order');
    }

    /**
     * Get all user attempts for this lesson.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(UserLessonAttempt::class);
    }

    /**
     * Get content blocks filtered by type.
     */
    public function getContentBlocksByType(string $type): Collection
    {
        return $this->contentBlocks()->where('block_type', $type)->get();
    }

    /**
     * Get the total number of questions for this lesson.
     */
    public function getTotalQuestions(): int
    {
        return $this->questions()->count();
    }

    /**
     * Get the total points available in this lesson.
     */
    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
    }

    /**
     * Get the next lesson in the track/skill level.
     */
    public function getNextLesson(): ?Lesson
    {
        return self::where('skill_level_id', $this->skill_level_id)
            ->where('lesson_number', $this->lesson_number + 1)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the previous lesson in the track/skill level.
     */
    public function getPreviousLesson(): ?Lesson
    {
        return self::where('skill_level_id', $this->skill_level_id)
            ->where('lesson_number', $this->lesson_number - 1)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if a user has completed this lesson.
     */
    public function isCompletedBy(User $user): bool
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    /**
     * Get the best accuracy achieved by a user on this lesson.
     */
    public function getBestAccuracy(int $userId): ?float
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->max('accuracy_percentage');
    }

    /**
     * Scope to get only active lessons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by lesson number.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('lesson_number');
    }
}
