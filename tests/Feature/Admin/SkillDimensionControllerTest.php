<?php

use App\Models\SkillDimension;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->user = User::factory()->create(['is_admin' => false]);
});

describe('GET /admin/skill-dimensions', function () {
    it('requires admin access', function () {
        $response = $this->actingAs($this->user)->get('/admin/skill-dimensions');

        $response->assertRedirect('/');
    });

    it('allows admin to view index', function () {
        SkillDimension::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/skill-dimensions');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/skill-dimensions/index')
            ->has('dimensions', 3)
        );
    });
});

describe('GET /admin/skill-dimensions/create', function () {
    it('requires admin access', function () {
        $response = $this->actingAs($this->user)->get('/admin/skill-dimensions/create');

        $response->assertRedirect('/');
    });

    it('allows admin to view create form', function () {
        $response = $this->actingAs($this->admin)->get('/admin/skill-dimensions/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/skill-dimensions/create')
        );
    });
});

describe('POST /admin/skill-dimensions', function () {
    it('requires admin access', function () {
        $response = $this->actingAs($this->user)->post('/admin/skill-dimensions', [
            'key' => 'test_key',
            'label' => 'Test Label',
        ]);

        $response->assertRedirect('/');
    });

    it('creates a new skill dimension', function () {
        $response = $this->actingAs($this->admin)->post('/admin/skill-dimensions', [
            'key' => 'test_dimension',
            'label' => 'Test Dimension',
            'description' => 'A test description',
            'category' => 'communication',
            'anchor_low' => 'Low performance',
            'anchor_mid' => 'Medium performance',
            'anchor_high' => 'High performance',
            'anchor_exemplary' => 'Exemplary performance',
            'active' => true,
        ]);

        $response->assertRedirect('/admin/skill-dimensions');

        $this->assertDatabaseHas('skill_dimensions', [
            'key' => 'test_dimension',
            'label' => 'Test Dimension',
            'category' => 'communication',
            'active' => true,
        ]);

        $dimension = SkillDimension::find('test_dimension');
        expect($dimension->score_anchors)->toBe([
            'low' => 'Low performance',
            'mid' => 'Medium performance',
            'high' => 'High performance',
            'exemplary' => 'Exemplary performance',
        ]);
    });

    it('auto-generates key from label if not provided', function () {
        $response = $this->actingAs($this->admin)->post('/admin/skill-dimensions', [
            'key' => '',
            'label' => 'Auto Generated Key',
            'anchor_low' => 'Low',
            'anchor_mid' => 'Mid',
            'anchor_high' => 'High',
            'anchor_exemplary' => 'Exemplary',
        ]);

        $response->assertRedirect('/admin/skill-dimensions');

        $this->assertDatabaseHas('skill_dimensions', [
            'key' => 'auto_generated_key',
            'label' => 'Auto Generated Key',
        ]);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->admin)->post('/admin/skill-dimensions', [
            'key' => 'test',
            'label' => '',
        ]);

        $response->assertSessionHasErrors(['label', 'anchor_low', 'anchor_mid', 'anchor_high', 'anchor_exemplary']);
    });

    it('validates category is valid', function () {
        $response = $this->actingAs($this->admin)->post('/admin/skill-dimensions', [
            'label' => 'Test',
            'category' => 'invalid_category',
            'anchor_low' => 'Low',
            'anchor_mid' => 'Mid',
            'anchor_high' => 'High',
            'anchor_exemplary' => 'Exemplary',
        ]);

        $response->assertSessionHasErrors(['category']);
    });

    it('validates key uniqueness', function () {
        SkillDimension::factory()->create(['key' => 'existing_key']);

        $response = $this->actingAs($this->admin)->post('/admin/skill-dimensions', [
            'key' => 'existing_key',
            'label' => 'Test',
            'anchor_low' => 'Low',
            'anchor_mid' => 'Mid',
            'anchor_high' => 'High',
            'anchor_exemplary' => 'Exemplary',
        ]);

        $response->assertSessionHasErrors(['key']);
    });
});

describe('GET /admin/skill-dimensions/{key}/edit', function () {
    it('requires admin access', function () {
        $dimension = SkillDimension::factory()->create();

        $response = $this->actingAs($this->user)->get("/admin/skill-dimensions/{$dimension->key}/edit");

        $response->assertRedirect('/');
    });

    it('allows admin to view edit form', function () {
        $dimension = SkillDimension::factory()->create([
            'key' => 'test_dim',
            'label' => 'Test Dimension',
            'score_anchors' => [
                'low' => 'Low',
                'mid' => 'Mid',
                'high' => 'High',
                'exemplary' => 'Exemplary',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/skill-dimensions/{$dimension->key}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/skill-dimensions/edit')
            ->has('dimension')
            ->where('dimension.key', 'test_dim')
            ->where('dimension.anchor_low', 'Low')
        );
    });
});

describe('PUT /admin/skill-dimensions/{key}', function () {
    it('requires admin access', function () {
        $dimension = SkillDimension::factory()->create();

        $response = $this->actingAs($this->user)->put("/admin/skill-dimensions/{$dimension->key}", [
            'label' => 'Updated',
        ]);

        $response->assertRedirect('/');
    });

    it('updates a skill dimension', function () {
        $dimension = SkillDimension::factory()->create([
            'key' => 'test_dim',
            'label' => 'Original Label',
            'category' => 'communication',
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/skill-dimensions/{$dimension->key}", [
            'label' => 'Updated Label',
            'description' => 'Updated description',
            'category' => 'reasoning',
            'anchor_low' => 'New Low',
            'anchor_mid' => 'New Mid',
            'anchor_high' => 'New High',
            'anchor_exemplary' => 'New Exemplary',
            'active' => false,
        ]);

        $response->assertRedirect('/admin/skill-dimensions');

        $dimension->refresh();
        expect($dimension->label)->toBe('Updated Label');
        expect($dimension->category)->toBe('reasoning');
        expect($dimension->active)->toBeFalse();
        expect($dimension->score_anchors['low'])->toBe('New Low');
    });
});

describe('DELETE /admin/skill-dimensions/{key}', function () {
    it('requires admin access', function () {
        $dimension = SkillDimension::factory()->create();

        $response = $this->actingAs($this->user)->delete("/admin/skill-dimensions/{$dimension->key}");

        $response->assertRedirect('/');
    });

    it('deletes a skill dimension', function () {
        $dimension = SkillDimension::factory()->create(['key' => 'to_delete']);

        $response = $this->actingAs($this->admin)->delete("/admin/skill-dimensions/{$dimension->key}");

        $response->assertRedirect('/admin/skill-dimensions');
        $this->assertDatabaseMissing('skill_dimensions', ['key' => 'to_delete']);
    });
});
