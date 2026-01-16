<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\SkillLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonController extends Controller
{
    public function index(SkillLevel $skillLevel)
    {
        $lessons = $skillLevel->lessons()
            ->withCount(['contentBlocks', 'questions'])
            ->orderBy('lesson_number')
            ->paginate(20);

        $track = $skillLevel->track;

        return view('admin.lessons.index', compact('skillLevel', 'track', 'lessons'));
    }

    public function create(SkillLevel $skillLevel)
    {
        $track = $skillLevel->track;
        $nextLessonNumber = ($skillLevel->lessons()->max('lesson_number') ?? 0) + 1;

        return view('admin.lessons.create', compact('skillLevel', 'track', 'nextLessonNumber'));
    }

    public function store(Request $request, SkillLevel $skillLevel)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'learning_objectives' => 'required|array|min:1|max:5',
            'learning_objectives.*' => 'required|string|max:255',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'lesson_number' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Filter out empty objectives
        $validated['learning_objectives'] = array_values(array_filter($validated['learning_objectives'], fn($obj) => trim($obj) !== ''));

        if (empty($validated['learning_objectives'])) {
            return back()->withErrors(['learning_objectives' => 'At least one learning objective is required.'])->withInput();
        }

        $validated['skill_level_id'] = $skillLevel->id;
        $validated['track_id'] = $skillLevel->track_id;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['lesson_number'] = $validated['lesson_number'] ?? (($skillLevel->lessons()->max('lesson_number') ?? 0) + 1);

        Lesson::create($validated);

        return redirect()->route('admin.skill-levels.lessons.index', $skillLevel)
            ->with('success', 'Lesson created successfully.');
    }

    public function edit(Lesson $lesson)
    {
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        return view('admin.lessons.edit', compact('lesson', 'skillLevel', 'track'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'learning_objectives' => 'required|array|min:1|max:5',
            'learning_objectives.*' => 'required|string|max:255',
            'estimated_duration_minutes' => 'required|integer|min:1',
            'lesson_number' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Filter out empty objectives
        $validated['learning_objectives'] = array_values(array_filter($validated['learning_objectives'], fn($obj) => trim($obj) !== ''));

        if (empty($validated['learning_objectives'])) {
            return back()->withErrors(['learning_objectives' => 'At least one learning objective is required.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');

        $lesson->update($validated);

        return redirect()->route('admin.skill-levels.lessons.index', $lesson->skillLevel)
            ->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Lesson $lesson)
    {
        $skillLevel = $lesson->skillLevel;
        $contentCount = $lesson->contentBlocks()->count();
        $questionCount = $lesson->questions()->count();

        $lesson->delete();

        $message = "Lesson deleted successfully.";
        if ($contentCount > 0 || $questionCount > 0) {
            $message .= " {$contentCount} content block(s) and {$questionCount} question(s) were also removed.";
        }

        return redirect()->route('admin.skill-levels.lessons.index', $skillLevel)
            ->with('success', $message);
    }
}
