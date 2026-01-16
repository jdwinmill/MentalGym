<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SkillLevel;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillLevelController extends Controller
{
    public function index(Track $track)
    {
        $skillLevels = $track->skillLevels()
            ->withCount('lessons')
            ->orderBy('level_number')
            ->paginate(20);

        return view('admin.skill-levels.index', compact('track', 'skillLevels'));
    }

    public function create(Track $track)
    {
        $nextLevelNumber = ($track->skillLevels()->max('level_number') ?? 0) + 1;
        $existingLevels = $track->skillLevels()->pluck('level_number')->toArray();

        return view('admin.skill-levels.create', compact('track', 'nextLevelNumber', 'existingLevels'));
    }

    public function store(Request $request, Track $track)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('skill_levels')->where(function ($query) use ($track) {
                    return $query->where('track_id', $track->id);
                }),
            ],
            'level_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('skill_levels')->where(function ($query) use ($track) {
                    return $query->where('track_id', $track->id);
                }),
            ],
            'description' => 'required|string',
            'pass_threshold' => 'required|numeric|min:0|max:1',
        ]);

        $validated['track_id'] = $track->id;

        SkillLevel::create($validated);

        return redirect()->route('admin.tracks.skill-levels.index', $track)
            ->with('success', 'Skill level created successfully.');
    }

    public function edit(SkillLevel $skillLevel)
    {
        $track = $skillLevel->track;
        $existingLevels = $track->skillLevels()
            ->where('id', '!=', $skillLevel->id)
            ->pluck('level_number')
            ->toArray();

        return view('admin.skill-levels.edit', compact('skillLevel', 'track', 'existingLevels'));
    }

    public function update(Request $request, SkillLevel $skillLevel)
    {
        $track = $skillLevel->track;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('skill_levels')->where(function ($query) use ($track) {
                    return $query->where('track_id', $track->id);
                })->ignore($skillLevel->id),
            ],
            'level_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('skill_levels')->where(function ($query) use ($track) {
                    return $query->where('track_id', $track->id);
                })->ignore($skillLevel->id),
            ],
            'description' => 'required|string',
            'pass_threshold' => 'required|numeric|min:0|max:1',
        ]);

        $skillLevel->update($validated);

        return redirect()->route('admin.tracks.skill-levels.index', $track)
            ->with('success', 'Skill level updated successfully.');
    }

    public function destroy(SkillLevel $skillLevel)
    {
        $track = $skillLevel->track;
        $lessonCount = $skillLevel->lessons()->count();

        $skillLevel->delete();

        return redirect()->route('admin.tracks.skill-levels.index', $track)
            ->with('success', "Skill level deleted successfully." . ($lessonCount > 0 ? " {$lessonCount} lesson(s) were also removed." : ''));
    }
}
