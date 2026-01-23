<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModeProgress extends Model
{
    protected $table = 'user_mode_progress';

    protected $fillable = [
        'user_id',
        'practice_mode_id',
        'current_level',
        'total_exchanges',
        'total_drills_completed',
        'exchanges_at_current_level',
        'sessions_at_current_level',
        'total_sessions',
        'total_time_seconds',
        'last_session_at',
        'last_trained_at',
    ];

    protected $casts = [
        'last_session_at' => 'datetime',
        'last_trained_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Increment the session count and update last session time.
     */
    public function recordSession(int $durationSeconds): void
    {
        $this->total_sessions++;
        $this->total_time_seconds += $durationSeconds;
        $this->last_session_at = now();
        $this->save();
    }

    /**
     * Level up if not at max level.
     */
    public function levelUp(): bool
    {
        if ($this->current_level >= 5) {
            return false;
        }

        $this->current_level++;
        $this->save();

        return true;
    }

    /**
     * Get formatted total time.
     */
    public function getFormattedTotalTime(): string
    {
        $hours = floor($this->total_time_seconds / 3600);
        $minutes = floor(($this->total_time_seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
