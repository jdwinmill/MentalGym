<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        // Family
        'has_kids',
        'kid_birth_years',
        'has_partner',
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
            'has_kids' => 'boolean',
            'kid_birth_years' => 'array',
            'has_partner' => 'boolean',
        ];
    }

    /**
     * Get the user's age derived from birth_year.
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function (): ?int {
                if ($this->birth_year === null) {
                    return null;
                }

                return (int) date('Y') - $this->birth_year;
            },
        );
    }

    /**
     * Get the ages of the user's kids derived from kid_birth_years.
     */
    protected function kidsAges(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                if ($this->kid_birth_years === null || empty($this->kid_birth_years)) {
                    return null;
                }

                $currentYear = (int) date('Y');
                $ages = array_map(fn ($year) => $currentYear - $year, $this->kid_birth_years);
                sort($ages);

                return $ages;
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
