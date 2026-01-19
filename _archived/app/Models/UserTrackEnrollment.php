<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserTrackEnrollment model representing user access and progress in tracks.
 *
 * @property int $id
 * @property int $user_id
 * @property int $track_id
 * @property int|null $current_skill_level_id
 * @property \Illuminate\Support\Carbon $enrolled_at
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserTrackEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'current_skill_level_id',
        'enrolled_at',
        'activated_at',
        'completed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ABANDONED = 'abandoned';

    /**
     * Get the user for this enrollment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the track for this enrollment.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the current skill level for this enrollment.
     */
    public function currentSkillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class, 'current_skill_level_id');
    }

    /**
     * Check if the user can advance to the next level based on recent performance.
     */
    public function canAdvanceLevel(): bool
    {
        if (!$this->current_skill_level_id) {
            return false;
        }

        $currentLevel = $this->currentSkillLevel;
        if (!$currentLevel || $currentLevel->isLastLevel()) {
            return false;
        }

        $recentAccuracy = $this->getRecentAccuracy(5);

        return $currentLevel->meetsPassThreshold($recentAccuracy);
    }

    /**
     * Get the recent accuracy percentage from the last N completed lessons.
     */
    public function getRecentAccuracy(int $lessonCount = 5): float
    {
        $lessonIds = $this->track
            ->lessons()
            ->where('skill_level_id', $this->current_skill_level_id)
            ->pluck('id');

        $attempts = UserLessonAttempt::where('user_id', $this->user_id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('completed_at')
            ->whereNotNull('accuracy_percentage')
            ->orderByDesc('completed_at')
            ->limit($lessonCount)
            ->get();

        if ($attempts->isEmpty()) {
            return 0.0;
        }

        return round($attempts->avg('accuracy_percentage') / 100, 2);
    }

    /**
     * Advance to the next skill level.
     */
    public function advanceToNextLevel(): bool
    {
        $currentLevel = $this->currentSkillLevel;
        if (!$currentLevel) {
            return false;
        }

        $nextLevel = $currentLevel->getNextLevel();
        if (!$nextLevel) {
            return false;
        }

        $this->current_skill_level_id = $nextLevel->id;
        return $this->save();
    }

    /**
     * Mark the enrollment as completed.
     */
    public function markCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Check if the enrollment is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get the progress percentage through the track.
     */
    public function getProgressPercentage(): float
    {
        return $this->track->getProgressPercentage($this->user_id);
    }

    /**
     * Scope to get active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Activate this enrollment (set as current active track).
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        $this->activated_at = now();
        return $this->save();
    }

    /**
     * Pause this enrollment.
     */
    public function pause(): bool
    {
        $this->status = self::STATUS_PAUSED;
        return $this->save();
    }

    /**
     * Check if this track is currently activated (active and has activated_at).
     */
    public function isActivated(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->activated_at !== null;
    }

    /**
     * Get days since activation.
     */
    public function daysSinceActivation(): ?int
    {
        if (!$this->activated_at) {
            return null;
        }

        return (int) $this->activated_at->diffInDays(now());
    }
}
