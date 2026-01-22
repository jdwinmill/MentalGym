<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SkillLevel model representing a progressive difficulty level within a track.
 *
 * @property int $id
 * @property int $track_id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property int $level_number
 * @property float $pass_threshold
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SkillLevel extends Model
{
    protected $fillable = [
        'track_id',
        'slug',
        'name',
        'description',
        'level_number',
        'pass_threshold',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'pass_threshold' => 'decimal:2',
        ];
    }

    /**
     * Get the track this skill level belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get all lessons for this skill level.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('lesson_number');
    }

    /**
     * Get all questions for this skill level.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class);
    }

    /**
     * Get all enrollments currently at this skill level.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UserTrackEnrollment::class, 'current_skill_level_id');
    }

    /**
     * Get all weakness patterns for this skill level.
     */
    public function weaknessPatterns(): HasMany
    {
        return $this->hasMany(UserWeaknessPattern::class);
    }

    /**
     * Get the next skill level in the track.
     */
    public function getNextLevel(): ?SkillLevel
    {
        return self::where('track_id', $this->track_id)
            ->where('level_number', $this->level_number + 1)
            ->first();
    }

    /**
     * Get the previous skill level in the track.
     */
    public function getPreviousLevel(): ?SkillLevel
    {
        return self::where('track_id', $this->track_id)
            ->where('level_number', $this->level_number - 1)
            ->first();
    }

    /**
     * Check if this is the first level in the track.
     */
    public function isFirstLevel(): bool
    {
        return $this->level_number === 1;
    }

    /**
     * Check if this is the last level in the track.
     */
    public function isLastLevel(): bool
    {
        return ! $this->getNextLevel();
    }

    /**
     * Get the pass threshold as a percentage.
     */
    public function getPassThresholdPercentage(): float
    {
        return $this->pass_threshold * 100;
    }

    /**
     * Check if a score meets the pass threshold.
     */
    public function meetsPassThreshold(float $score): bool
    {
        return $score >= $this->pass_threshold;
    }
}
