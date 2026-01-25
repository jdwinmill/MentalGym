<?php

use App\Models\SkillDimension;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/skill-dimensions', function () {
    it('requires authentication', function () {
        $response = $this->getJson('/api/skill-dimensions');

        $response->assertUnauthorized();
    });

    it('returns active skill dimensions', function () {
        $user = User::factory()->create();

        SkillDimension::factory()->create([
            'key' => 'assertiveness',
            'label' => 'Assertiveness',
            'category' => 'communication',
            'active' => true,
        ]);

        SkillDimension::factory()->create([
            'key' => 'logical_structure',
            'label' => 'Logical Structure',
            'category' => 'reasoning',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/skill-dimensions');

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment([
            'key' => 'assertiveness',
            'label' => 'Assertiveness',
            'category' => 'communication',
        ]);
    });

    it('excludes inactive dimensions', function () {
        $user = User::factory()->create();

        SkillDimension::factory()->create([
            'key' => 'active_dim',
            'label' => 'Active Dimension',
            'active' => true,
        ]);

        SkillDimension::factory()->create([
            'key' => 'inactive_dim',
            'label' => 'Inactive Dimension',
            'active' => false,
        ]);

        $response = $this->actingAs($user)->getJson('/api/skill-dimensions');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['key' => 'active_dim']);
        $response->assertJsonMissing(['key' => 'inactive_dim']);
    });

    it('returns dimensions ordered by category and label', function () {
        $user = User::factory()->create();

        SkillDimension::factory()->create([
            'key' => 'zebra',
            'label' => 'Zebra Skill',
            'category' => 'reasoning',
        ]);

        SkillDimension::factory()->create([
            'key' => 'alpha',
            'label' => 'Alpha Skill',
            'category' => 'communication',
        ]);

        SkillDimension::factory()->create([
            'key' => 'beta',
            'label' => 'Beta Skill',
            'category' => 'communication',
        ]);

        $response = $this->actingAs($user)->getJson('/api/skill-dimensions');

        $response->assertOk();
        $data = $response->json();

        // Communication comes before reasoning alphabetically
        expect($data[0]['category'])->toBe('communication');
        expect($data[0]['key'])->toBe('alpha');
        expect($data[1]['key'])->toBe('beta');
        expect($data[2]['category'])->toBe('reasoning');
    });

    it('only returns key, label, and category fields', function () {
        $user = User::factory()->create();

        SkillDimension::factory()->create([
            'key' => 'test_dim',
            'label' => 'Test Dimension',
            'category' => 'communication',
            'description' => 'This should not be returned',
            'score_anchors' => ['low' => 'test'],
        ]);

        $response = $this->actingAs($user)->getJson('/api/skill-dimensions');

        $response->assertOk();
        $dimension = $response->json()[0];

        expect($dimension)->toHaveKeys(['key', 'label', 'category']);
        expect($dimension)->not->toHaveKey('description');
        expect($dimension)->not->toHaveKey('score_anchors');
        expect($dimension)->not->toHaveKey('active');
    });
});
