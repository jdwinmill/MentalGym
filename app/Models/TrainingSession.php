<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'user_id',
        'practice_mode_id',
        'level_at_start',
        'exchange_count',
        'started_at',
        'ended_at',
        'duration_seconds',
        'status',
        // New drill-based fields
        'drill_index',
        'phase',
        'current_scenario',
        'current_task',
        'current_options',
        'current_correct_option',
        'drill_scores',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        // New drill-based fields
        'drill_index' => 'integer',
        'current_options' => 'array',
        'current_correct_option' => 'integer',
        'drill_scores' => 'array',
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

    public function messages(): HasMany
    {
        return $this->hasMany(SessionMessage::class)->orderBy('sequence');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if the session is still active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * End the session and calculate duration.
     */
    public function complete(): void
    {
        $this->ended_at = now();
        $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Mark the session as abandoned.
     */
    public function abandon(): void
    {
        $this->ended_at = now();
        $this->duration_seconds = $this->started_at->diffInSeconds($this->ended_at);
        $this->status = self::STATUS_ABANDONED;
        $this->save();
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        if (! $this->duration_seconds) {
            return '-';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        return "{$minutes}m {$seconds}s";
    }

    /**
     * Get the current drill based on drill_index.
     */
    public function getCurrentDrill(): ?Drill
    {
        return $this->practiceMode->drills()
            ->where('position', $this->drill_index)
            ->first();
    }

    /**
     * Check if all drills are completed.
     */
    public function allDrillsCompleted(): bool
    {
        $totalDrills = $this->practiceMode->drills()->count();

        return $this->drill_index >= $totalDrills;
    }

    /**
     * Append a score to drill_scores.
     */
    public function appendDrillScore(int $drillId, string $drillName, int $score): void
    {
        $scores = $this->drill_scores ?? [];
        $scores[] = [
            'drill_id' => $drillId,
            'drill_name' => $drillName,
            'score' => $score,
        ];
        $this->drill_scores = $scores;
        $this->save();
    }
}
