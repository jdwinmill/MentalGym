<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStreak extends Model
{
    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Record activity and update streak.
     */
    public function recordActivity(): void
    {
        $today = now()->toDateString();

        // Already recorded today
        if ($this->last_activity_date?->toDateString() === $today) {
            return;
        }

        $yesterday = now()->subDay()->toDateString();

        // Continue streak if activity was yesterday
        if ($this->last_activity_date?->toDateString() === $yesterday) {
            $this->current_streak++;
        } else {
            // Streak broken, start new
            $this->current_streak = 1;
        }

        // Update longest streak if needed
        if ($this->current_streak > $this->longest_streak) {
            $this->longest_streak = $this->current_streak;
        }

        $this->last_activity_date = $today;
        $this->save();
    }

    /**
     * Check if streak is active (activity today or yesterday).
     */
    public function isActive(): bool
    {
        if (!$this->last_activity_date) {
            return false;
        }

        $daysSinceActivity = $this->last_activity_date->diffInDays(now());

        return $daysSinceActivity <= 1;
    }

    /**
     * Get the effective current streak (0 if broken).
     */
    public function getEffectiveStreak(): int
    {
        if (!$this->isActive()) {
            return 0;
        }

        return $this->current_streak;
    }
}
