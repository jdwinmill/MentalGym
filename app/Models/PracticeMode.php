<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeMode extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'description',
        'instruction_set',
        'config',
        'required_plan',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserModeProgress::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ─────────────────────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the config with defaults merged in.
     */
    public function getConfigAttribute($value): array
    {
        $defaults = [
            'input_character_limit' => 500,
            'reflection_character_limit' => 200,
            'max_response_tokens' => 800,
            'max_history_exchanges' => 10,
            'model' => 'claude-sonnet-4-20250514',
        ];

        $config = is_string($value) ? json_decode($value, true) : ($value ?? []);

        return array_merge($defaults, $config);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the instruction set with level placeholder replaced.
     */
    public function getInstructionSetForLevel(int $level): string
    {
        return str_replace('{{level}}', (string) $level, $this->instruction_set);
    }
}
