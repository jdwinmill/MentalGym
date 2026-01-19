<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
     * Get the capabilities required to access this track.
     */
    public function capabilityRequirements(): BelongsToMany
    {
        return $this->belongsToMany(Capability::class, 'track_capability_requirements')
            ->withPivot('required_value')
            ->withTimestamps();
    }

    /**
     * Require a capability for this track.
     */
    public function requireCapability(string|Capability $capability, mixed $value = null): void
    {
        $capabilityModel = $capability instanceof Capability
            ? $capability
            : Capability::where('key', $capability)->firstOrFail();

        $this->capabilityRequirements()->syncWithoutDetaching([
            $capabilityModel->id => ['required_value' => $value],
        ]);
    }

    /**
     * Remove a capability requirement from this track.
     */
    public function removeCapabilityRequirement(string|Capability $capability): void
    {
        $capabilityModel = $capability instanceof Capability
            ? $capability
            : Capability::where('key', $capability)->first();

        if ($capabilityModel) {
            $this->capabilityRequirements()->detach($capabilityModel->id);
        }
    }

    /**
     * Check if a user can access this track based on capabilities.
     */
    public function userCanAccess(User $user): bool
    {
        return $user->canAccessTrack($this);
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
