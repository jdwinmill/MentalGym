<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load('profile');

        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'profile' => $user->profile,
            'profileOptions' => [
                'companySizes' => config('profile.company_sizes'),
                'careerLevels' => config('profile.career_levels'),
                'teamCompositions' => config('profile.team_compositions'),
                'collaborationStyles' => config('profile.collaboration_styles'),
                'crossFunctionalOptions' => config('profile.cross_functional_options'),
                'improvementAreas' => config('profile.improvement_areas'),
                'challenges' => config('profile.challenges'),
            ],
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Extract user fields
        $userFields = ['name', 'email'];
        $userData = array_intersect_key($validated, array_flip($userFields));

        // Fill and check email change
        $user->fill($userData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Extract profile fields
        $profileFields = [
            'birth_year',
            'gender',
            'zip_code',
            'job_title',
            'industry',
            'company_size',
            'career_level',
            'years_in_role',
            'years_experience',
            'manages_people',
            'direct_reports',
            'reports_to_role',
            'team_composition',
            'collaboration_style',
            'cross_functional_teams',
            'communication_tools',
            'improvement_areas',
            'upcoming_challenges',
        ];
        $profileData = array_intersect_key($validated, array_flip($profileFields));

        // Only update profile if there are profile fields in the request
        if (! empty($profileData)) {
            $profile = $user->getOrCreateProfile();
            $profile->fill($profileData);
            $profile->save();
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
