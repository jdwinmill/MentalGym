<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrackController extends Controller
{
    public function index()
    {
        $tracks = Track::withCount('skillLevels')
            ->ordered()
            ->paginate(20);

        return view('admin.tracks.index', compact('tracks'));
    }

    public function create()
    {
        $nextSortOrder = (Track::max('sort_order') ?? 0) + 1;

        return view('admin.tracks.create', compact('nextSortOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:tracks,slug',
            'pitch' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_weeks' => 'required|integer|min:1',
            'sessions_per_week' => 'required|integer|min:1',
            'session_duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? (Track::max('sort_order') ?? 0) + 1;

        Track::create($validated);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track created successfully.');
    }

    public function edit(Track $track)
    {
        return view('admin.tracks.edit', compact('track'));
    }

    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['required', 'string', 'max:100', Rule::unique('tracks', 'slug')->ignore($track->id)],
            'pitch' => 'required|string|max:255',
            'description' => 'required|string',
            'duration_weeks' => 'required|integer|min:1',
            'sessions_per_week' => 'required|integer|min:1',
            'session_duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $track->update($validated);

        return redirect()->route('admin.tracks.index')
            ->with('success', 'Track updated successfully.');
    }

    public function destroy(Track $track)
    {
        $skillCount = $track->skillLevels()->count();

        $track->delete();

        return redirect()->route('admin.tracks.index')
            ->with('success', "Track deleted successfully." . ($skillCount > 0 ? " {$skillCount} skill level(s) were also removed." : ''));
    }
}
