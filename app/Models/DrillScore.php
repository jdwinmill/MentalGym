<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'training_session_id',
        'practice_mode_id',
        'drill_type',
        'drill_phase',
        'is_iteration',
        'scores',
        'user_response',
        'word_count',
        'response_time_seconds',
    ];

    protected $casts = [
        'scores' => 'array',
        'is_iteration' => 'boolean',
        'word_count' => 'integer',
        'response_time_seconds' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDrillType($query, string $drillType)
    {
        return $query->where('drill_type', $drillType);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
