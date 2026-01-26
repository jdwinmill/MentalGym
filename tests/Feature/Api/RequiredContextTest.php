<?php

use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/training/v2/check-context/{mode_slug}', function () {
    it('returns has_required_context true when no fields are required', function () {
        $user = User::factory()->create();
        $mode = PracticeMode::factory()->create(['slug' => 'test-mode']);

        $response = $this->actingAs($user)
            ->getJson("/api/training/v2/check-context/{$mode->slug}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'has_required_context' => true,
                'missing_fields' => [],
            ]);
    });

    it('returns has_required_context true when user has all required fields', function () {
        $user = User::factory()->create();
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'job_title' => 'Engineer',
            'career_level' => 'senior',
        ]);

        $mode = PracticeMode::factory()->create(['slug' => 'test-mode']);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'job_title',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'career_level',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/training/v2/check-context/{$mode->slug}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'has_required_context' => true,
                'missing_fields' => [],
            ]);
    });

    it('returns missing fields when user lacks required context', function () {
        $user = User::factory()->create();
        // User has no profile

        $mode = PracticeMode::factory()->create(['slug' => 'test-mode']);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'job_title',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'career_level',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/training/v2/check-context/{$mode->slug}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'has_required_context' => false,
            ]);

        expect($response->json('missing_fields'))->toHaveCount(2);

        // Check that field metadata is included
        $fields = collect($response->json('missing_fields'));
        $jobTitleField = $fields->firstWhere('key', 'job_title');
        expect($jobTitleField)->not->toBeNull();
        expect($jobTitleField['label'])->toBe('Job Title');
        expect($jobTitleField['type'])->toBe('text');

        $careerLevelField = $fields->firstWhere('key', 'career_level');
        expect($careerLevelField)->not->toBeNull();
        expect($careerLevelField['type'])->toBe('select');
        expect($careerLevelField['options'])->not->toBeEmpty();
    });

    it('returns missing fields when user has partial profile', function () {
        $user = User::factory()->create();
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'job_title' => 'Engineer',
            'career_level' => null, // Missing
        ]);

        $mode = PracticeMode::factory()->create(['slug' => 'test-mode']);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'job_title',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'career_level',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/training/v2/check-context/{$mode->slug}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'has_required_context' => false,
            ]);

        expect($response->json('missing_fields'))->toHaveCount(1);
        expect($response->json('missing_fields.0.key'))->toBe('career_level');
    });
});

describe('POST /api/training/v2/update-profile', function () {
    it('updates user profile with provided fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/training/v2/update-profile', [
                'job_title' => 'Senior Engineer',
                'career_level' => 'senior',
                'manages_people' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);

        $user->refresh();
        expect($user->profile)->not->toBeNull();
        expect($user->profile->job_title)->toBe('Senior Engineer');
        expect($user->profile->career_level)->toBe('senior');
        expect($user->profile->manages_people)->toBeTrue();
    });

    it('rejects request with no valid profile fields', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/training/v2/update-profile', [
                'invalid_field' => 'value',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'No valid profile fields provided.',
            ]);
    });

    it('updates existing profile without overwriting other fields', function () {
        $user = User::factory()->create();
        $profile = UserProfile::create([
            'user_id' => $user->id,
            'job_title' => 'Engineer',
            'industry' => 'Technology',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/training/v2/update-profile', [
                'career_level' => 'mid',
            ]);

        $response->assertOk();

        $profile->refresh();
        expect($profile->job_title)->toBe('Engineer');
        expect($profile->industry)->toBe('Technology');
        expect($profile->career_level)->toBe('mid');
    });
});
