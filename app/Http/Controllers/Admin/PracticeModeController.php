<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PracticeModeController extends Controller
{
    public function index(): Response
    {
        $modes = PracticeMode::orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($mode) => [
                'id' => $mode->id,
                'name' => $mode->name,
                'slug' => $mode->slug,
                'tagline' => $mode->tagline,
                'is_active' => $mode->is_active,
                'required_plan' => $mode->required_plan,
                'sort_order' => $mode->sort_order,
            ]);

        return Inertia::render('admin/practice-modes/index', [
            'modes' => $modes,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PracticeMode::class);

        // Get insights grouped by principle for the dropdown
        $insightsByPrinciple = Principle::active()
            ->ordered()
            ->with(['insights' => fn ($query) => $query->active()->ordered()])
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
                'insights' => $principle->insights->map(fn ($insight) => [
                    'id' => $insight->id,
                    'name' => $insight->name,
                ]),
            ]);

        return Inertia::render('admin/practice-modes/create', [
            'tagsByCategory' => Tag::ordered()->get()->groupBy('category'),
            'insightsByPrinciple' => $insightsByPrinciple,
            'contextFields' => config('profile.context_fields'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PracticeMode::class);

        $validated = $request->validate($this->validationRules());

        // Auto-generate slug from name if not provided
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        // Build config array from individual fields
        $validated['config'] = $this->buildConfig($request);

        $practiceMode = PracticeMode::create($validated);
        $practiceMode->tags()->sync($request->input('tags', []));

        // Save required context
        $this->syncRequiredContext($practiceMode, $request->input('required_context', []));

        // Save drills
        $this->saveDrills($practiceMode, $request->input('drills', []));

        return redirect()
            ->route('admin.practice-modes.index')
            ->with('success', 'Practice Mode created successfully.');
    }

    public function edit(PracticeMode $practiceMode): Response
    {
        $this->authorize('update', $practiceMode);

        // Get insights grouped by principle for the dropdown
        $insightsByPrinciple = Principle::active()
            ->ordered()
            ->with(['insights' => fn ($query) => $query->active()->ordered()])
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
                'insights' => $principle->insights->map(fn ($insight) => [
                    'id' => $insight->id,
                    'name' => $insight->name,
                ]),
            ]);

        return Inertia::render('admin/practice-modes/edit', [
            'mode' => [
                'id' => $practiceMode->id,
                'name' => $practiceMode->name,
                'slug' => $practiceMode->slug,
                'tagline' => $practiceMode->tagline,
                'description' => $practiceMode->description,
                'instruction_set' => $practiceMode->instruction_set,
                'config' => $practiceMode->config,
                'required_plan' => $practiceMode->required_plan,
                'is_active' => $practiceMode->is_active,
                'sort_order' => $practiceMode->sort_order,
                'drills' => $practiceMode->drills()->orderBy('position')->with('insights')->get()->map(fn ($drill) => [
                    'id' => $drill->id,
                    'name' => $drill->name,
                    'position' => $drill->position,
                    'timer_seconds' => $drill->timer_seconds,
                    'input_type' => $drill->input_type,
                    'scenario_instruction_set' => $drill->scenario_instruction_set,
                    'evaluation_instruction_set' => $drill->evaluation_instruction_set,
                    'primary_insight_id' => $drill->insights->where('pivot.is_primary', true)->first()?->id,
                    'dimensions' => $drill->dimensions ?? [],
                ]),
            ],
            'tagsByCategory' => Tag::ordered()->get()->groupBy('category'),
            'selectedTags' => $practiceMode->tags->pluck('id')->toArray(),
            'insightsByPrinciple' => $insightsByPrinciple,
            'contextFields' => config('profile.context_fields'),
            'selectedContext' => $practiceMode->getRequiredContextFields(),
        ]);
    }

    public function update(Request $request, PracticeMode $practiceMode): RedirectResponse
    {
        $this->authorize('update', $practiceMode);

        $validated = $request->validate($this->validationRules($practiceMode->id));

        // Build config array from individual fields
        $validated['config'] = $this->buildConfig($request);

        $practiceMode->update($validated);
        $practiceMode->tags()->sync($request->input('tags', []));

        // Save required context
        $this->syncRequiredContext($practiceMode, $request->input('required_context', []));

        // Save drills
        $this->saveDrills($practiceMode, $request->input('drills', []));

        return redirect()
            ->route('admin.practice-modes.index')
            ->with('success', 'Practice Mode updated successfully.');
    }

    public function destroy(PracticeMode $practiceMode): RedirectResponse
    {
        $this->authorize('delete', $practiceMode);

        $practiceMode->delete();

        return redirect()
            ->route('admin.practice-modes.index')
            ->with('success', 'Practice Mode deleted successfully.');
    }

    /**
     * Get validation rules for store/update.
     */
    private function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('practice_modes', 'slug')->ignore($id),
            ],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instruction_set' => ['required', 'string'],
            'required_plan' => ['nullable', 'in:pro,unlimited'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            // Config fields validated individually
            'config.input_character_limit' => ['nullable', 'integer', 'min:100', 'max:2000'],
            'config.reflection_character_limit' => ['nullable', 'integer', 'min:50', 'max:500'],
            'config.max_response_tokens' => ['nullable', 'integer', 'min:200', 'max:2000'],
            'config.max_history_exchanges' => ['nullable', 'integer', 'min:5', 'max:24'],
            'config.model' => ['nullable', 'string', 'in:claude-sonnet-4-20250514,claude-haiku-4-20250414'],
            // Tags
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
            // Required context
            'required_context' => ['array'],
            'required_context.*' => ['string', Rule::in(array_keys(config('profile.context_fields')))],
            // Drills
            'drills' => ['array'],
            'drills.*.id' => ['nullable', 'integer'],
            'drills.*.name' => ['required', 'string', 'max:255'],
            'drills.*.position' => ['required', 'integer', 'min:0'],
            'drills.*.timer_seconds' => ['nullable', 'integer', 'min:0', 'max:600'],
            'drills.*.input_type' => ['required', 'in:text,multiple_choice'],
            'drills.*.scenario_instruction_set' => ['required', 'string'],
            'drills.*.evaluation_instruction_set' => ['required', 'string'],
            'drills.*.primary_insight_id' => ['nullable', 'integer', 'exists:insights,id'],
            'drills.*.dimensions' => ['nullable', 'array'],
            'drills.*.dimensions.*' => ['string', 'exists:skill_dimensions,key,active,1'],
        ];
    }

    /**
     * Build config array from request data with defaults.
     */
    private function buildConfig(Request $request): array
    {
        $defaults = [
            'input_character_limit' => 500,
            'reflection_character_limit' => 200,
            'max_response_tokens' => 800,
            'max_history_exchanges' => 10,
            'model' => 'claude-sonnet-4-20250514',
        ];

        $config = $request->input('config', []);

        return [
            'input_character_limit' => $config['input_character_limit'] ?? $defaults['input_character_limit'],
            'reflection_character_limit' => $config['reflection_character_limit'] ?? $defaults['reflection_character_limit'],
            'max_response_tokens' => $config['max_response_tokens'] ?? $defaults['max_response_tokens'],
            'max_history_exchanges' => $config['max_history_exchanges'] ?? $defaults['max_history_exchanges'],
            'model' => $config['model'] ?? $defaults['model'],
        ];
    }

    /**
     * Save drills for a practice mode.
     * Handles create, update, and delete operations.
     */
    private function saveDrills(PracticeMode $practiceMode, array $drills): void
    {
        $existingDrillIds = $practiceMode->drills()->pluck('id')->toArray();
        $submittedDrillIds = [];

        foreach ($drills as $drillData) {
            if (! empty($drillData['id'])) {
                // Update existing drill
                $drill = Drill::find($drillData['id']);
                if ($drill && $drill->practice_mode_id === $practiceMode->id) {
                    $drill->update([
                        'name' => $drillData['name'],
                        'position' => $drillData['position'],
                        'timer_seconds' => $drillData['timer_seconds'],
                        'input_type' => $drillData['input_type'],
                        'scenario_instruction_set' => $drillData['scenario_instruction_set'],
                        'evaluation_instruction_set' => $drillData['evaluation_instruction_set'],
                        'dimensions' => $drillData['dimensions'] ?? [],
                    ]);
                    $submittedDrillIds[] = $drill->id;

                    // Handle primary insight
                    $this->syncPrimaryInsight($drill, $drillData['primary_insight_id'] ?? null);
                }
            } else {
                // Create new drill
                $drill = Drill::create([
                    'practice_mode_id' => $practiceMode->id,
                    'name' => $drillData['name'],
                    'position' => $drillData['position'],
                    'timer_seconds' => $drillData['timer_seconds'],
                    'input_type' => $drillData['input_type'],
                    'scenario_instruction_set' => $drillData['scenario_instruction_set'],
                    'evaluation_instruction_set' => $drillData['evaluation_instruction_set'],
                    'dimensions' => $drillData['dimensions'] ?? [],
                ]);
                $submittedDrillIds[] = $drill->id;

                // Handle primary insight
                $this->syncPrimaryInsight($drill, $drillData['primary_insight_id'] ?? null);
            }
        }

        // Delete drills that were removed
        $drillsToDelete = array_diff($existingDrillIds, $submittedDrillIds);
        if (! empty($drillsToDelete)) {
            Drill::whereIn('id', $drillsToDelete)->delete();
        }
    }

    /**
     * Sync the primary insight for a drill.
     */
    private function syncPrimaryInsight(Drill $drill, ?int $insightId): void
    {
        // Remove existing primary insight
        $drill->insights()->wherePivot('is_primary', true)->detach();

        // Attach new primary insight if provided
        if ($insightId) {
            $insight = Insight::find($insightId);
            if ($insight) {
                $drill->insights()->attach($insightId, ['is_primary' => true]);
            }
        }
    }

    /**
     * Sync required context fields for a practice mode.
     */
    private function syncRequiredContext(PracticeMode $practiceMode, array $fields): void
    {
        // Delete existing
        $practiceMode->requiredContext()->delete();

        // Insert new
        foreach ($fields as $field) {
            PracticeModeRequiredContext::create([
                'practice_mode_id' => $practiceMode->id,
                'profile_field' => $field,
            ]);
        }
    }
}
