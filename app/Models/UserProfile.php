<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        // Demographics
        'birth_year',
        'gender',
        'zip_code',
        // Career Context
        'job_title',
        'industry',
        'company_size',
        'career_level',
        'years_in_role',
        'years_experience',
        // Team & Reporting Structure
        'manages_people',
        'direct_reports',
        'reports_to_role',
        'team_composition',
        // Work Environment
        'collaboration_style',
        'cross_functional_teams',
        'communication_tools',
        // Professional Goals
        'improvement_areas',
        'upcoming_challenges',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'years_in_role' => 'integer',
            'years_experience' => 'integer',
            'manages_people' => 'boolean',
            'direct_reports' => 'integer',
            'cross_functional_teams' => 'array',
            'communication_tools' => 'array',
            'improvement_areas' => 'array',
            'upcoming_challenges' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a formatted context value for prompt injection.
     * Handles arrays, booleans, and config lookups for human-readable values.
     */
    public function getContextValue(string $field): string
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return 'not specified';
        }

        // Handle arrays (JSON fields)
        if (is_array($value)) {
            if (empty($value)) {
                return 'none';
            }

            // Look up human-readable labels from config
            $configKey = match ($field) {
                'cross_functional_teams' => 'cross_functional_options',
                'improvement_areas' => 'improvement_areas',
                'upcoming_challenges' => 'challenges',
                'communication_tools' => null, // No config lookup, use raw values
                default => null,
            };

            if ($configKey && config("profile.{$configKey}")) {
                $labels = config("profile.{$configKey}");
                $value = array_map(fn ($v) => $labels[$v] ?? $v, $value);
            }

            return implode(', ', $value);
        }

        // Handle booleans
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        // Look up human-readable labels for enum-like fields
        $configKey = match ($field) {
            'company_size' => 'company_sizes',
            'career_level' => 'career_levels',
            'team_composition' => 'team_compositions',
            'collaboration_style' => 'collaboration_styles',
            default => null,
        };

        if ($configKey && config("profile.{$configKey}.{$value}")) {
            return config("profile.{$configKey}.{$value}");
        }

        return (string) $value;
    }
}
