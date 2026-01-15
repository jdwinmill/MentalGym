<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Track model representing a top-level training program.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string|null $pitch
 * @property int $duration_weeks
 * @property int $sessions_per_week
 * @property int $session_duration_minutes
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Track extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'pitch',
        'duration_weeks',
        'sessions_per_week',
        'session_duration_minutes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'duration_weeks' => 'integer',
            'sessions_per_week' => 'integer',
            'session_duration_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the skill levels for this track.
     */
    public function skillLevels(): HasMany
    {
        return $this->hasMany(SkillLevel::class)->orderBy('level_number');
    }

    /**
     * Get all lessons for this track.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get all user enrollments for this track.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UserTrackEnrollment::class);
    }

    /**
     * Get all weakness patterns for this track.
     */
    public function weaknessPatterns(): HasMany
    {
        return $this->hasMany(UserWeaknessPattern::class);
    }

    /**
     * Calculate the user's progress percentage through this track.
     */
    public function getProgressPercentage(int $userId): float
    {
        $totalLessons = $this->lessons()->where('is_active', true)->count();

        if ($totalLessons === 0) {
            return 0.0;
        }

        $completedLessons = UserLessonAttempt::where('user_id', $userId)
            ->whereIn('lesson_id', $this->lessons()->pluck('id'))
            ->whereNotNull('completed_at')
            ->distinct('lesson_id')
            ->count('lesson_id');

        return round(($completedLessons / $totalLessons) * 100, 2);
    }

    /**
     * Check if a user has access to this track.
     */
    public function isAccessibleBy(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->enrollments()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'paused'])
            ->exists();
    }

    /**
     * Get the total number of sessions in the track.
     */
    public function getTotalSessions(): int
    {
        return $this->duration_weeks * $this->sessions_per_week;
    }

    /**
     * Get the first skill level of this track.
     */
    public function getFirstSkillLevel(): ?SkillLevel
    {
        return $this->skillLevels()->orderBy('level_number')->first();
    }

    /**
     * Scope to get only active tracks.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
