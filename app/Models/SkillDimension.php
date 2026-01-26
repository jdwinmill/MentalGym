<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillDimension extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'label',
        'description',
        'category',
        'score_anchors',
        'improvement_tips',
        'active',
    ];

    protected $casts = [
        'score_anchors' => 'array',
        'improvement_tips' => 'array',
        'active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function blindSpots(): HasMany
    {
        return $this->hasMany(BlindSpot::class, 'dimension_key', 'key');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserSkillDimensionProgress::class, 'dimension_key', 'key');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('label');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the appropriate improvement tip based on score.
     * Score 1-4 = low, 5-6 = mid, 7-10 = high
     */
    public function getTipForScore(int $score): ?string
    {
        $tips = $this->improvement_tips;

        if (empty($tips)) {
            return null;
        }

        if ($score <= 4) {
            return $tips['low'] ?? null;
        }

        if ($score <= 6) {
            return $tips['mid'] ?? null;
        }

        return $tips['high'] ?? null;
    }

    /**
     * Get the score level label (low, mid, high) for a score.
     */
    public static function getScoreLevel(int $score): string
    {
        if ($score <= 4) {
            return 'low';
        }

        if ($score <= 6) {
            return 'mid';
        }

        return 'high';
    }
}
