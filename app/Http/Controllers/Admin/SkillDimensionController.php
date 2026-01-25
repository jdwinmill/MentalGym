<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillDimension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SkillDimensionController extends Controller
{
    public function index(): Response
    {
        $dimensions = SkillDimension::ordered()
            ->get()
            ->map(fn ($dim) => [
                'key' => $dim->key,
                'label' => $dim->label,
                'category' => $dim->category,
                'active' => $dim->active,
            ]);

        return Inertia::render('admin/skill-dimensions/index', [
            'dimensions' => $dimensions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/skill-dimensions/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        // Auto-generate key from label if not provided
        $validated['key'] = $validated['key'] ?: Str::slug($validated['label'], '_');

        // Transform score anchor fields into JSON
        $validated['score_anchors'] = [
            'low' => $validated['anchor_low'],
            'mid' => $validated['anchor_mid'],
            'high' => $validated['anchor_high'],
            'exemplary' => $validated['anchor_exemplary'],
        ];

        unset($validated['anchor_low'], $validated['anchor_mid'], $validated['anchor_high'], $validated['anchor_exemplary']);

        SkillDimension::create($validated);

        return redirect()
            ->route('admin.skill-dimensions.index')
            ->with('success', 'Skill dimension created successfully.');
    }

    public function edit(SkillDimension $skillDimension): Response
    {
        $anchors = $skillDimension->score_anchors ?? [];

        return Inertia::render('admin/skill-dimensions/edit', [
            'dimension' => [
                'key' => $skillDimension->key,
                'label' => $skillDimension->label,
                'description' => $skillDimension->description,
                'category' => $skillDimension->category,
                'anchor_low' => $anchors['low'] ?? '',
                'anchor_mid' => $anchors['mid'] ?? '',
                'anchor_high' => $anchors['high'] ?? '',
                'anchor_exemplary' => $anchors['exemplary'] ?? '',
                'active' => $skillDimension->active,
            ],
        ]);
    }

    public function update(Request $request, SkillDimension $skillDimension): RedirectResponse
    {
        $validated = $request->validate($this->validationRules(isEdit: true));

        // Transform score anchor fields into JSON
        $validated['score_anchors'] = [
            'low' => $validated['anchor_low'],
            'mid' => $validated['anchor_mid'],
            'high' => $validated['anchor_high'],
            'exemplary' => $validated['anchor_exemplary'],
        ];

        unset($validated['anchor_low'], $validated['anchor_mid'], $validated['anchor_high'], $validated['anchor_exemplary'], $validated['key']);

        $skillDimension->update($validated);

        return redirect()
            ->route('admin.skill-dimensions.index')
            ->with('success', 'Skill dimension updated successfully.');
    }

    public function destroy(SkillDimension $skillDimension): RedirectResponse
    {
        $skillDimension->delete();

        return redirect()
            ->route('admin.skill-dimensions.index')
            ->with('success', 'Skill dimension deleted successfully.');
    }

    private function validationRules(bool $isEdit = false): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'in:communication,reasoning,resilience,influence,self_awareness'],
            'anchor_low' => ['required', 'string', 'max:500'],
            'anchor_mid' => ['required', 'string', 'max:500'],
            'anchor_high' => ['required', 'string', 'max:500'],
            'anchor_exemplary' => ['required', 'string', 'max:500'],
            'active' => ['boolean'],
        ];

        if (!$isEdit) {
            $rules['key'] = [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('skill_dimensions', 'key'),
            ];
        }

        return $rules;
    }
}
