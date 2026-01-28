<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeMode extends Model
{
    use HasFactory;

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

    public function requiredContext(): HasMany
    {
        return $this->hasMany(PracticeModeRequiredContext::class);
    }

    public function getRequiredContextFields(): array
    {
        return $this->requiredContext->pluck('profile_field')->toArray();
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserModeProgress::class);
    }

    public function drills(): HasMany
    {
        return $this->hasMany(Drill::class)->orderBy('position');
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

    /**
     * Inject user profile context into a prompt string.
     * Replaces {{field_name}} placeholders with actual values from user's profile.
     */
    public function injectUserContext(string $prompt, ?UserProfile $profile): string
    {
        if (! $profile) {
            return $prompt;
        }

        $requiredFields = $this->getRequiredContextFields();

        foreach ($requiredFields as $field) {
            $value = $profile->getContextValue($field);
            $prompt = str_replace("{{{$field}}}", $value, $prompt);
        }

        return $prompt;
    }

    /**
     * Get instruction set with both level and user context injected.
     */
    public function getInstructionSetWithContext(int $level, ?UserProfile $profile): string
    {
        $prompt = $this->getInstructionSetForLevel($level);

        return $this->injectUserContext($prompt, $profile);
    }

    /**
     * Get the required profile fields that are missing for a user.
     *
     * @return array<string> List of missing field names
     */
    public function getMissingRequiredFields(?UserProfile $profile): array
    {
        $requiredFields = $this->getRequiredContextFields();

        if (empty($requiredFields)) {
            return [];
        }

        if (! $profile) {
            return $requiredFields;
        }

        $missing = [];
        foreach ($requiredFields as $field) {
            $value = $profile->getAttribute($field);

            // Consider a field missing only if null or empty string
            // Empty arrays are valid (user chose not to select any options)
            if ($value === null || $value === '') {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    /**
     * Check if a user has all required profile fields filled.
     */
    public function hasRequiredContext(?UserProfile $profile): bool
    {
        return empty($this->getMissingRequiredFields($profile));
    }
}
