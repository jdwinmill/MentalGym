<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drill extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_mode_id',
        'name',
        'scenario_instruction_set',
        'evaluation_instruction_set',
        'position',
        'timer_seconds',
        'input_type',
        'config',
        'dimensions',
    ];

    protected $casts = [
        'config' => 'array',
        'dimensions' => 'array',
        'timer_seconds' => 'integer',
        'position' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }

    public function insights(): BelongsToMany
    {
        return $this->belongsToMany(Insight::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function userSkillProgress(): HasMany
    {
        return $this->hasMany(UserSkillDimensionProgress::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if this is a multiple choice drill.
     */
    public function isMultipleChoice(): bool
    {
        return $this->input_type === 'multiple_choice';
    }

    /**
     * Check if this drill has a timer.
     */
    public function hasTimer(): bool
    {
        return $this->timer_seconds !== null;
    }

    /**
     * Get the primary insight for this drill.
     */
    public function getPrimaryInsight(): ?Insight
    {
        return $this->insights()
            ->wherePivot('is_primary', true)
            ->with('principle')
            ->first();
    }
}
