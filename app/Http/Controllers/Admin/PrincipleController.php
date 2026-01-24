<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Principle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PrincipleController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Principle::class);

        $principles = Principle::withCount('insights')
            ->ordered()
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
                'slug' => $principle->slug,
                'icon' => $principle->icon,
                'position' => $principle->position,
                'is_active' => $principle->is_active,
                'insights_count' => $principle->insights_count,
            ]);

        return Inertia::render('admin/principles/index', [
            'principles' => $principles,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Principle::class);

        return Inertia::render('admin/principles/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Principle::class);

        $validated = $request->validate($this->validationRules());

        // Auto-generate slug from name if not provided
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        // Process blog_urls
        $validated['blog_urls'] = $this->processBlogUrls($request->input('blog_urls', []));

        Principle::create($validated);

        return redirect()
            ->route('admin.principles.index')
            ->with('success', 'Principle created successfully.');
    }

    public function edit(Principle $principle): Response
    {
        $this->authorize('update', $principle);

        return Inertia::render('admin/principles/edit', [
            'principle' => [
                'id' => $principle->id,
                'name' => $principle->name,
                'slug' => $principle->slug,
                'description' => $principle->description,
                'icon' => $principle->icon,
                'position' => $principle->position,
                'is_active' => $principle->is_active,
                'blog_urls' => $principle->blog_urls ?? [],
            ],
        ]);
    }

    public function update(Request $request, Principle $principle): RedirectResponse
    {
        $this->authorize('update', $principle);

        $validated = $request->validate($this->validationRules($principle->id));

        // Process blog_urls
        $validated['blog_urls'] = $this->processBlogUrls($request->input('blog_urls', []));

        $principle->update($validated);

        return redirect()
            ->route('admin.principles.index')
            ->with('success', 'Principle updated successfully.');
    }

    public function destroy(Principle $principle): RedirectResponse
    {
        $this->authorize('delete', $principle);

        $principle->delete();

        return redirect()
            ->route('admin.principles.index')
            ->with('success', 'Principle deleted successfully.');
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('principles', 'slug')->ignore($id),
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'position' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'blog_urls' => ['array'],
            'blog_urls.*.title' => ['required_with:blog_urls.*.url', 'string', 'max:255'],
            'blog_urls.*.url' => ['required_with:blog_urls.*.title', 'url', 'max:500'],
        ];
    }

    private function processBlogUrls(array $blogUrls): ?array
    {
        $filtered = array_filter($blogUrls, function ($item) {
            return ! empty($item['title']) && ! empty($item['url']);
        });

        return count($filtered) > 0 ? array_values($filtered) : null;
    }
}
