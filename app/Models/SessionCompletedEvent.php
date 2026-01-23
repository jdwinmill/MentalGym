<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionCompletedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'practice_mode_id',
        'training_session_id',
        'drills_completed',
        'total_duration_seconds',
        'scores',
        'completed_at',
    ];

    protected $casts = [
        'scores' => 'array',
        'completed_at' => 'datetime',
        'drills_completed' => 'integer',
        'total_duration_seconds' => 'integer',
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

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMode($query, int $practiceModeId)
    {
        return $query->where('practice_mode_id', $practiceModeId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('completed_at', '>=', now()->subDays($days));
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the average score across all drills in this session.
     */
    public function getAverageScore(): ?float
    {
        if (empty($this->scores)) {
            return null;
        }

        $totalScore = array_sum(array_column($this->scores, 'score'));

        return $totalScore / count($this->scores);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        $minutes = floor($this->total_duration_seconds / 60);
        $seconds = $this->total_duration_seconds % 60;

        return "{$minutes}m {$seconds}s";
    }
}
