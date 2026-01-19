<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionMessage extends Model
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'training_session_id',
        'role',
        'content',
        'parsed_type',
        'metadata',
        'sequence',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if message is from user.
     */
    public function isFromUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if message is from assistant.
     */
    public function isFromAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Get the message type from metadata (for assistant messages).
     */
    public function getType(): ?string
    {
        return $this->metadata['type'] ?? null;
    }
}
