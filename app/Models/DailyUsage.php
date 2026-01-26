<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyUsage extends Model
{
    protected $table = 'daily_usage';

    protected $fillable = [
        'user_id',
        'date',
        'exchange_count',
        'sessions_count',
        'total_time_seconds',
        'messages_count',
    ];

    protected $casts = [
        'date' => 'date',
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
     * Get or create today's usage record for a user.
     */
    public static function forUserToday(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id, 'date' => today()],
            ['exchange_count' => 0, 'sessions_count' => 0, 'total_time_seconds' => 0, 'messages_count' => 0]
        );
    }

    /**
     * Get total exchanges for a user this month.
     */
    public static function monthlyExchangeCount(User $user): int
    {
        return (int) static::where('user_id', $user->id)
            ->where('date', '>=', now()->startOfMonth())
            ->sum('exchange_count');
    }

    /**
     * Record activity for today using atomic increments to prevent race conditions.
     */
    public static function recordActivity(int $userId, int $timeSeconds = 0, int $messages = 0, bool $newSession = false): self
    {
        $usage = static::firstOrCreate(
            ['user_id' => $userId, 'date' => today()],
            ['exchange_count' => 0, 'sessions_count' => 0, 'total_time_seconds' => 0, 'messages_count' => 0]
        );

        $increments = [];

        if ($newSession) {
            $increments['sessions_count'] = 1;
        }

        if ($timeSeconds > 0) {
            $increments['total_time_seconds'] = $timeSeconds;
        }

        if ($messages > 0) {
            $increments['messages_count'] = $messages;
        }

        if (! empty($increments)) {
            static::where('id', $usage->id)->increment(
                array_key_first($increments),
                $increments[array_key_first($increments)],
                count($increments) > 1 ? array_slice($increments, 1) : []
            );
            $usage->refresh();
        }

        return $usage;
    }

    /**
     * Get formatted time.
     */
    public function getFormattedTime(): string
    {
        $hours = floor($this->total_time_seconds / 3600);
        $minutes = floor(($this->total_time_seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
