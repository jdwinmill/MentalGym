<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Tag::class);

        $tagsByCategory = Tag::withCount('practiceModes')
            ->ordered()
            ->get()
            ->groupBy('category')
            ->map(fn($tags) => $tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'category' => $tag->category,
                'display_order' => $tag->display_order,
                'usage_count' => $tag->practice_modes_count,
            ]));

        return Inertia::render('admin/tags/index', [
            'tagsByCategory' => $tagsByCategory,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Tag::class);

        return Inertia::render('admin/tags/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tag::class);

        $validated = $request->validate($this->validationRules());

        // Auto-generate slug from name if not provided
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        Tag::create($validated);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag): Response
    {
        $this->authorize('update', $tag);

        return Inertia::render('admin/tags/edit', [
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'category' => $tag->category,
                'display_order' => $tag->display_order,
            ],
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);

        $validated = $request->validate($this->validationRules($tag->id));

        $tag->update($validated);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    private function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('tags', 'slug')->ignore($id),
            ],
            'category' => ['required', 'in:skill,context,duration,role'],
            'display_order' => ['integer', 'min:0'],
        ];
    }
}
