<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class UserSkillDimensionProgress extends Model
{
    protected $table = 'user_skill_dimension_progress';

    protected $fillable = [
        'user_id',
        'drill_id',
        'dimension_key',
        'current_level',
    ];

    protected $casts = [
        'current_level' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drill(): BelongsTo
    {
        return $this->belongsTo(Drill::class);
    }

    public function skillDimension(): BelongsTo
    {
        return $this->belongsTo(SkillDimension::class, 'dimension_key', 'key');
    }

    // ─────────────────────────────────────────────────────────────
    // Static Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get aggregated dimension levels for a user across all drills.
     *
     * @return Collection<string, float> dimension_key => average_level
     */
    public static function getAggregatedLevels(int $userId): Collection
    {
        return static::where('user_id', $userId)
            ->selectRaw('dimension_key, AVG(current_level) as overall_level')
            ->groupBy('dimension_key')
            ->pluck('overall_level', 'dimension_key');
    }

    /**
     * Get or create a progress record for a user/drill/dimension combination.
     */
    public static function getOrCreate(int $userId, int $drillId, string $dimensionKey): static
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'drill_id' => $drillId,
                'dimension_key' => $dimensionKey,
            ],
            ['current_level' => 1]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Instance Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Update the level, clamping to valid range (1-10).
     */
    public function setLevel(int $level): void
    {
        $this->current_level = max(1, min(10, $level));
        $this->save();
    }
}
