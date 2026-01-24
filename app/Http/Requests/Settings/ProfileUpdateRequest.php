<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            // Demographics
            'birth_year' => ['nullable', 'integer', 'min:1920', 'max:2015'],
            'gender' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:20'],

            // Career Context
            'job_title' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'string', Rule::in(array_keys(config('profile.company_sizes')))],
            'career_level' => ['nullable', 'string', Rule::in(array_keys(config('profile.career_levels')))],
            'years_in_role' => ['nullable', 'integer', 'min:0', 'max:50'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],

            // Team & Reporting Structure
            'manages_people' => ['boolean'],
            'direct_reports' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'reports_to_role' => ['nullable', 'string', 'max:100'],
            'team_composition' => ['nullable', 'string', Rule::in(array_keys(config('profile.team_compositions')))],

            // Work Environment
            'collaboration_style' => ['nullable', 'string', Rule::in(array_keys(config('profile.collaboration_styles')))],
            'cross_functional_teams' => ['nullable', 'array'],
            'cross_functional_teams.*' => ['string', Rule::in(array_keys(config('profile.cross_functional_options')))],
            'communication_tools' => ['nullable', 'array'],
            'communication_tools.*' => ['string', 'max:50'],

            // Professional Goals
            'improvement_areas' => ['nullable', 'array'],
            'improvement_areas.*' => ['string', Rule::in(array_keys(config('profile.improvement_areas')))],
            'upcoming_challenges' => ['nullable', 'array'],
            'upcoming_challenges.*' => ['string', Rule::in(array_keys(config('profile.challenges')))],
        ];
    }
}
