<?php

use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\UserContextFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserProfile accessors', function () {
    it('calculates age from birth_year', function () {
        $currentYear = (int) date('Y');
        $profile = new UserProfile(['birth_year' => $currentYear - 35]);

        expect($profile->age)->toBe(35);
    });

    it('returns null age when birth_year is null', function () {
        $profile = new UserProfile(['birth_year' => null]);

        expect($profile->age)->toBeNull();
    });

    it('calculates kids_ages from kid_birth_years sorted ascending', function () {
        $currentYear = (int) date('Y');
        $profile = new UserProfile([
            'kid_birth_years' => [$currentYear - 12, $currentYear - 5, $currentYear - 8],
        ]);

        expect($profile->kids_ages)->toBe([5, 8, 12]);
    });

    it('returns null kids_ages when kid_birth_years is null', function () {
        $profile = new UserProfile(['kid_birth_years' => null]);

        expect($profile->kids_ages)->toBeNull();
    });

    it('returns null kids_ages when kid_birth_years is empty', function () {
        $profile = new UserProfile(['kid_birth_years' => []]);

        expect($profile->kids_ages)->toBeNull();
    });
});

describe('UserContextFormatter', function () {
    it('formats string fields as label: value', function () {
        $profile = new UserProfile(['job_title' => 'Product Manager']);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['job_title']);

        expect($result)->toBe(['Role: Product Manager']);
    });

    it('formats integer fields as label: value', function () {
        $profile = new UserProfile(['years_experience' => 10]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['years_experience']);

        expect($result)->toBe(['Experience: 10']);
    });

    it('formats boolean true as label: yes', function () {
        $profile = new UserProfile(['manages_people' => true]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['manages_people']);

        expect($result)->toBe(['Manages people: yes']);
    });

    it('omits boolean false fields entirely', function () {
        $profile = new UserProfile(['manages_people' => false]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['manages_people']);

        expect($result)->toBe([]);
    });

    it('formats array fields as comma-separated values', function () {
        $profile = new UserProfile([
            'cross_functional_teams' => ['engineering', 'design', 'sales'],
        ]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['cross_functional_teams']);

        expect($result)->toBe(['Cross-functional: engineering, design, sales']);
    });

    it('omits null fields', function () {
        $profile = new UserProfile(['job_title' => null]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['job_title']);

        expect($result)->toBe([]);
    });

    it('omits empty string fields', function () {
        $profile = new UserProfile(['job_title' => '']);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['job_title']);

        expect($result)->toBe([]);
    });

    it('omits empty array fields', function () {
        $profile = new UserProfile(['improvement_areas' => []]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['improvement_areas']);

        expect($result)->toBe([]);
    });

    it('formats age accessor correctly', function () {
        $currentYear = (int) date('Y');
        $profile = new UserProfile(['birth_year' => $currentYear - 34]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['age']);

        expect($result)->toBe(['Age: 34']);
    });

    it('formats kids_ages accessor correctly', function () {
        $currentYear = (int) date('Y');
        $profile = new UserProfile([
            'kid_birth_years' => [$currentYear - 10, $currentYear - 3],
        ]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['kids_ages']);

        expect($result)->toBe(['Kids ages: 3, 10']);
    });

    it('formats multiple fields', function () {
        $currentYear = (int) date('Y');
        $profile = new UserProfile([
            'birth_year' => $currentYear - 40,
            'job_title' => 'Engineering Manager',
            'manages_people' => true,
            'direct_reports' => 5,
        ]);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['age', 'job_title', 'manages_people', 'direct_reports']);

        expect($result)->toBe([
            'Age: 40',
            'Role: Engineering Manager',
            'Manages people: yes',
            'Direct reports: 5',
        ]);
    });

    it('skips unknown fields', function () {
        $profile = new UserProfile(['job_title' => 'PM']);
        $formatter = new UserContextFormatter;

        $result = $formatter->format($profile, ['job_title', 'unknown_field']);

        expect($result)->toBe(['Role: PM']);
    });

    it('formatAsBlock joins with newlines', function () {
        $profile = new UserProfile([
            'job_title' => 'Developer',
            'years_experience' => 5,
        ]);
        $formatter = new UserContextFormatter;

        $result = $formatter->formatAsBlock($profile, ['job_title', 'years_experience']);

        expect($result)->toBe("Role: Developer\nExperience: 5");
    });
});

describe('User getProfileContext', function () {
    it('returns empty string when user has no profile', function () {
        $user = User::factory()->create();

        expect($user->getProfileContext())->toBe('');
    });

    it('wraps formatted context in user tags', function () {
        $user = User::factory()->create();
        $currentYear = (int) date('Y');
        UserProfile::create([
            'user_id' => $user->id,
            'birth_year' => $currentYear - 30,
            'job_title' => 'Designer',
        ]);
        $user->load('profile');

        $result = $user->getProfileContext(['age', 'job_title']);

        expect($result)->toBe("<user>\nAge: 30\nRole: Designer\n</user>");
    });

    it('returns empty string when no fields have values', function () {
        $user = User::factory()->create();
        UserProfile::create([
            'user_id' => $user->id,
        ]);
        $user->load('profile');

        $result = $user->getProfileContext(['job_title', 'industry']);

        expect($result)->toBe('');
    });

    it('uses all config fields when no fields specified', function () {
        $user = User::factory()->create();
        $currentYear = (int) date('Y');
        UserProfile::create([
            'user_id' => $user->id,
            'birth_year' => $currentYear - 28,
            'job_title' => 'Analyst',
            'manages_people' => true,
        ]);
        $user->load('profile');

        $result = $user->getProfileContext();

        expect($result)->toContain('<user>')
            ->and($result)->toContain('</user>')
            ->and($result)->toContain('Age: 28')
            ->and($result)->toContain('Role: Analyst')
            ->and($result)->toContain('Manages people: yes');
    });

    it('filters to only specified fields', function () {
        $user = User::factory()->create();
        $currentYear = (int) date('Y');
        UserProfile::create([
            'user_id' => $user->id,
            'birth_year' => $currentYear - 25,
            'job_title' => 'Engineer',
            'industry' => 'Tech',
        ]);
        $user->load('profile');

        $result = $user->getProfileContext(['job_title']);

        expect($result)->toBe("<user>\nRole: Engineer\n</user>");
        expect($result)->not->toContain('Age');
        expect($result)->not->toContain('Industry');
    });
});

describe('PracticeMode getRequiredContextFields', function () {
    it('returns array of required profile fields', function () {
        $mode = PracticeMode::factory()->create();

        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'job_title',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'career_level',
        ]);

        $fields = $mode->fresh()->getRequiredContextFields();

        expect($fields)->toContain('job_title')
            ->and($fields)->toContain('career_level')
            ->and($fields)->toHaveCount(2);
    });

    it('returns empty array when no required context', function () {
        $mode = PracticeMode::factory()->create();

        expect($mode->getRequiredContextFields())->toBe([]);
    });
});
