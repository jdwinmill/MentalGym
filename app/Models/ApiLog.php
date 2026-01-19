<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'practice_mode_id',
        'training_session_id',
        'input_tokens',
        'output_tokens',
        'cache_creation_input_tokens',
        'cache_read_input_tokens',
        'model',
        'response_time_ms',
        'success',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'created_at' => 'datetime',
    ];

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

    /**
     * Calculate estimated cost based on Claude Sonnet pricing
     * Input: $3.00/1M, Output: $15.00/1M, Cache write: $3.75/1M, Cache read: $0.30/1M
     */
    public function getEstimatedCostAttribute(): float
    {
        $inputCost = ($this->input_tokens / 1_000_000) * 3.00;
        $outputCost = ($this->output_tokens / 1_000_000) * 15.00;
        $cacheWriteCost = ($this->cache_creation_input_tokens / 1_000_000) * 3.75;
        $cacheReadCost = ($this->cache_read_input_tokens / 1_000_000) * 0.30;

        return $inputCost + $outputCost + $cacheWriteCost + $cacheReadCost;
    }
}
