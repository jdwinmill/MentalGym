<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use App\Models\Principle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InsightController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Insight::class);

        $insights = Insight::with('principle')
            ->orderBy('principle_id')
            ->ordered()
            ->get()
            ->map(fn ($insight) => [
                'id' => $insight->id,
                'name' => $insight->name,
                'slug' => $insight->slug,
                'principle_name' => $insight->principle->name,
                'position' => $insight->position,
                'is_active' => $insight->is_active,
            ]);

        return Inertia::render('admin/insights/index', [
            'insights' => $insights,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Insight::class);

        $principles = Principle::active()
            ->ordered()
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
            ]);

        return Inertia::render('admin/insights/create', [
            'principles' => $principles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Insight::class);

        $validated = $request->validate($this->validationRules());

        // Auto-generate slug from name if not provided
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        Insight::create($validated);

        return redirect()
            ->route('admin.insights.index')
            ->with('success', 'Insight created successfully.');
    }

    public function edit(Insight $insight): Response
    {
        $this->authorize('update', $insight);

        $principles = Principle::active()
            ->ordered()
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
            ]);

        return Inertia::render('admin/insights/edit', [
            'insight' => [
                'id' => $insight->id,
                'principle_id' => $insight->principle_id,
                'name' => $insight->name,
                'slug' => $insight->slug,
                'summary' => $insight->summary,
                'content' => $insight->content,
                'position' => $insight->position,
                'is_active' => $insight->is_active,
            ],
            'principles' => $principles,
        ]);
    }

    public function update(Request $request, Insight $insight): RedirectResponse
    {
        $this->authorize('update', $insight);

        $validated = $request->validate($this->validationRules($insight->id));

        $insight->update($validated);

        return redirect()
            ->route('admin.insights.index')
            ->with('success', 'Insight updated successfully.');
    }

    public function destroy(Insight $insight): RedirectResponse
    {
        $this->authorize('delete', $insight);

        $insight->delete();

        return redirect()
            ->route('admin.insights.index')
            ->with('success', 'Insight deleted successfully.');
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'principle_id' => ['required', 'exists:principles,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('insights', 'slug')->ignore($id),
            ],
            'summary' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'position' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
