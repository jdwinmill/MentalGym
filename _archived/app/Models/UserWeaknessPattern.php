<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserWeaknessPattern model representing recurring mistake patterns.
 *
 * @property int $id
 * @property int $user_id
 * @property int $track_id
 * @property int $skill_level_id
 * @property string $pattern_tag
 * @property int $occurrence_count
 * @property \Illuminate\Support\Carbon $first_detected_at
 * @property \Illuminate\Support\Carbon $last_detected_at
 * @property int|null $severity_score
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserWeaknessPattern extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'skill_level_id',
        'pattern_tag',
        'occurrence_count',
        'first_detected_at',
        'last_detected_at',
        'severity_score',
    ];

    protected function casts(): array
    {
        return [
            'occurrence_count' => 'integer',
            'first_detected_at' => 'datetime',
            'last_detected_at' => 'datetime',
            'severity_score' => 'integer',
        ];
    }

    /**
     * Severity thresholds.
     */
    public const SEVERITY_LOW = 1;
    public const SEVERITY_MEDIUM = 2;
    public const SEVERITY_HIGH = 3;
    public const SEVERITY_CRITICAL = 4;

    /**
     * Get the user for this pattern.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the track for this pattern.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the skill level for this pattern.
     */
    public function skillLevel(): BelongsTo
    {
        return $this->belongsTo(SkillLevel::class);
    }

    /**
     * Record a new occurrence of this pattern.
     */
    public function recordOccurrence(): self
    {
        $this->occurrence_count++;
        $this->last_detected_at = now();
        $this->updateSeverityScore();
        $this->save();

        return $this;
    }

    /**
     * Update the severity score based on occurrence count and frequency.
     */
    public function updateSeverityScore(): self
    {
        $daysSinceFirst = max(1, $this->first_detected_at->diffInDays(now()));
        $frequencyRate = $this->occurrence_count / $daysSinceFirst;

        if ($this->occurrence_count >= 10 || $frequencyRate >= 2) {
            $this->severity_score = self::SEVERITY_CRITICAL;
        } elseif ($this->occurrence_count >= 5 || $frequencyRate >= 1) {
            $this->severity_score = self::SEVERITY_HIGH;
        } elseif ($this->occurrence_count >= 3 || $frequencyRate >= 0.5) {
            $this->severity_score = self::SEVERITY_MEDIUM;
        } else {
            $this->severity_score = self::SEVERITY_LOW;
        }

        return $this;
    }

    /**
     * Check if this is a critical weakness.
     */
    public function isCritical(): bool
    {
        return $this->severity_score === self::SEVERITY_CRITICAL;
    }

    /**
     * Check if this is a high-severity weakness.
     */
    public function isHighSeverity(): bool
    {
        return $this->severity_score >= self::SEVERITY_HIGH;
    }

    /**
     * Get the severity label.
     */
    public function getSeverityLabel(): string
    {
        return match ($this->severity_score) {
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_LOW => 'Low',
            default => 'Unknown',
        };
    }

    /**
     * Get days since first detection.
     */
    public function getDaysSinceFirstDetection(): int
    {
        return $this->first_detected_at->diffInDays(now());
    }

    /**
     * Get days since last detection.
     */
    public function getDaysSinceLastDetection(): int
    {
        return $this->last_detected_at->diffInDays(now());
    }

    /**
     * Check if this pattern was recently detected (within last 7 days).
     */
    public function isRecent(): bool
    {
        return $this->getDaysSinceLastDetection() <= 7;
    }

    /**
     * Find or create a weakness pattern for the given parameters.
     */
    public static function findOrCreatePattern(
        int $userId,
        int $trackId,
        int $skillLevelId,
        string $patternTag
    ): self {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'track_id' => $trackId,
                'skill_level_id' => $skillLevelId,
                'pattern_tag' => $patternTag,
            ],
            [
                'occurrence_count' => 0,
                'first_detected_at' => now(),
                'last_detected_at' => now(),
                'severity_score' => self::SEVERITY_LOW,
            ]
        );
    }

    /**
     * Scope to get patterns by severity.
     */
    public function scopeBySeverity($query, int $severity)
    {
        return $query->where('severity_score', $severity);
    }

    /**
     * Scope to get critical patterns.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity_score', self::SEVERITY_CRITICAL);
    }

    /**
     * Scope to get recent patterns.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('last_detected_at', '>=', now()->subDays($days));
    }
}
