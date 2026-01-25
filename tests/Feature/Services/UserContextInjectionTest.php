<?php

use App\Models\Drill;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PracticeMode user context injection', function () {
    beforeEach(function () {
        $this->mode = PracticeMode::factory()->create([
            'slug' => 'test-mode',
            'name' => 'Test Mode',
            'instruction_set' => 'You are coaching a {{career_level}} professional who works as a {{job_title}}. They {{manages_people}}.',
        ]);

        // Add required context fields
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $this->mode->id,
            'profile_field' => 'career_level',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $this->mode->id,
            'profile_field' => 'job_title',
        ]);
        PracticeModeRequiredContext::create([
            'practice_mode_id' => $this->mode->id,
            'profile_field' => 'manages_people',
        ]);
    });

    it('injects user context values into instruction set', function () {
        $profile = new UserProfile([
            'career_level' => 'senior',
            'job_title' => 'Product Manager',
            'manages_people' => true,
        ]);

        $result = $this->mode->injectUserContext($this->mode->instruction_set, $profile);

        expect($result)->toContain('Senior')
            ->and($result)->toContain('Product Manager')
            ->and($result)->toContain('yes')
            ->and($result)->not->toContain('{{career_level}}')
            ->and($result)->not->toContain('{{job_title}}')
            ->and($result)->not->toContain('{{manages_people}}');
    });

    it('replaces missing profile values with "not specified"', function () {
        $profile = new UserProfile([
            'career_level' => 'mid',
            // job_title is missing
            // manages_people is missing
        ]);

        $result = $this->mode->injectUserContext($this->mode->instruction_set, $profile);

        expect($result)->toContain('Mid-Level')
            ->and($result)->toContain('not specified');
    });

    it('returns original prompt when profile is null', function () {
        $result = $this->mode->injectUserContext($this->mode->instruction_set, null);

        expect($result)->toBe($this->mode->instruction_set);
    });

    it('combines level and user context injection', function () {
        $profile = new UserProfile([
            'career_level' => 'executive',
            'job_title' => 'VP of Engineering',
            'manages_people' => true,
        ]);

        // Update mode to include level placeholder
        $this->mode->instruction_set = 'Level {{level}}: Coaching a {{career_level}} {{job_title}}.';
        $this->mode->save();

        $result = $this->mode->getInstructionSetWithContext(5, $profile);

        expect($result)->toContain('Level 5')
            ->and($result)->toContain('Executive')
            ->and($result)->toContain('VP of Engineering')
            ->and($result)->not->toContain('{{level}}')
            ->and($result)->not->toContain('{{career_level}}');
    });
});

describe('UserProfile getContextValue', function () {
    it('returns human-readable labels for career_level', function () {
        $profile = new UserProfile(['career_level' => 'senior']);
        expect($profile->getContextValue('career_level'))->toBe('Senior');

        $profile = new UserProfile(['career_level' => 'executive']);
        expect($profile->getContextValue('career_level'))->toBe('Executive');

        $profile = new UserProfile(['career_level' => 'mid']);
        expect($profile->getContextValue('career_level'))->toBe('Mid-Level');
    });

    it('returns human-readable labels for company_size', function () {
        $profile = new UserProfile(['company_size' => 'startup']);
        expect($profile->getContextValue('company_size'))->toBe('Startup (1-50)');

        $profile = new UserProfile(['company_size' => 'enterprise']);
        expect($profile->getContextValue('company_size'))->toBe('Enterprise (500+)');
    });

    it('returns yes/no for boolean fields', function () {
        $profile = new UserProfile(['manages_people' => true]);
        expect($profile->getContextValue('manages_people'))->toBe('yes');

        $profile = new UserProfile(['manages_people' => false]);
        expect($profile->getContextValue('manages_people'))->toBe('no');
    });

    it('returns comma-separated list for array fields', function () {
        $profile = new UserProfile([
            'cross_functional_teams' => ['engineering', 'product', 'design'],
        ]);

        $result = $profile->getContextValue('cross_functional_teams');

        expect($result)->toContain('Engineering')
            ->and($result)->toContain('Product')
            ->and($result)->toContain('Design');
    });

    it('returns "not specified" for null values', function () {
        $profile = new UserProfile([]);

        expect($profile->getContextValue('job_title'))->toBe('not specified');
        expect($profile->getContextValue('career_level'))->toBe('not specified');
    });

    it('returns "none" for empty arrays', function () {
        $profile = new UserProfile([
            'cross_functional_teams' => [],
        ]);

        expect($profile->getContextValue('cross_functional_teams'))->toBe('none');
    });

    it('returns raw string values for fields without config lookup', function () {
        $profile = new UserProfile([
            'job_title' => 'Senior Software Engineer',
            'industry' => 'Technology',
        ]);

        expect($profile->getContextValue('job_title'))->toBe('Senior Software Engineer');
        expect($profile->getContextValue('industry'))->toBe('Technology');
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

describe('Drill instruction set context injection', function () {
    it('injects user context into drill scenario instruction set', function () {
        $mode = PracticeMode::factory()->create([
            'instruction_set' => 'Mode for {{career_level}} professionals.',
        ]);

        PracticeModeRequiredContext::create([
            'practice_mode_id' => $mode->id,
            'profile_field' => 'career_level',
        ]);

        $drillInstruction = 'Generate scenario for a {{career_level}} worker.';

        $profile = new UserProfile(['career_level' => 'senior']);

        // Test mode injection
        $modeResult = $mode->injectUserContext($mode->instruction_set, $profile);
        expect($modeResult)->toContain('Senior');

        // Test drill instruction injection
        $drillResult = $mode->injectUserContext($drillInstruction, $profile);
        expect($drillResult)->toContain('Senior')
            ->and($drillResult)->not->toContain('{{career_level}}');
    });
});
