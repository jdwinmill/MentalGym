<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use Illuminate\Http\Request;

class LessonQuestionController extends Controller
{
    public function index(Lesson $lesson)
    {
        $questions = $lesson->questions()
            ->with('answerOptions')
            ->withCount('answerOptions')
            ->orderBy('sort_order')
            ->get();

        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        return view('admin.lesson-questions.index', compact('lesson', 'skillLevel', 'track', 'questions'));
    }

    public function create(Lesson $lesson)
    {
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;
        $nextSortOrder = ($lesson->questions()->max('sort_order') ?? 0) + 1;

        // Get content blocks for relating questions to content
        $contentBlocks = $lesson->contentBlocks()->orderBy('sort_order')->get();

        return view('admin.lesson-questions.create', compact('lesson', 'skillLevel', 'track', 'nextSortOrder', 'contentBlocks'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended',
            'explanation' => 'nullable|string|max:2000',
            'points' => 'required|integer|min:1|max:100',
            'related_block_id' => 'nullable|exists:lesson_content_blocks,id',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $validated['lesson_id'] = $lesson->id;
        $validated['skill_level_id'] = $lesson->skill_level_id;
        $validated['sort_order'] = $validated['sort_order'] ?? (($lesson->questions()->max('sort_order') ?? 0) + 1);

        $question = LessonQuestion::create($validated);

        if ($request->has('save_and_add_answers')) {
            return redirect()->route('admin.lesson-questions.answer-options.create', $question)
                ->with('success', 'Question created. Now add answer options.');
        }

        return redirect()->route('admin.lessons.lesson-questions.index', $lesson)
            ->with('success', 'Question created successfully.');
    }

    public function edit(LessonQuestion $lessonQuestion)
    {
        $lesson = $lessonQuestion->lesson;
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        // Get content blocks for relating questions to content
        $contentBlocks = $lesson->contentBlocks()->orderBy('sort_order')->get();

        // Load answer options
        $answerOptions = $lessonQuestion->answerOptions()->orderBy('sort_order')->get();

        return view('admin.lesson-questions.edit', compact('lessonQuestion', 'lesson', 'skillLevel', 'track', 'contentBlocks', 'answerOptions'));
    }

    public function update(Request $request, LessonQuestion $lessonQuestion)
    {
        $validated = $request->validate([
            'question_text' => 'required|string|max:1000',
            'question_type' => 'required|in:multiple_choice,true_false,open_ended',
            'explanation' => 'nullable|string|max:2000',
            'points' => 'required|integer|min:1|max:100',
            'related_block_id' => 'nullable|exists:lesson_content_blocks,id',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $lessonQuestion->update($validated);

        return redirect()->route('admin.lessons.lesson-questions.index', $lessonQuestion->lesson)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(LessonQuestion $lessonQuestion)
    {
        $lesson = $lessonQuestion->lesson;
        $answerCount = $lessonQuestion->answerOptions()->count();

        $lessonQuestion->delete();

        $message = 'Question deleted successfully.';
        if ($answerCount > 0) {
            $message .= " {$answerCount} answer option(s) were also removed.";
        }

        return redirect()->route('admin.lessons.lesson-questions.index', $lesson)
            ->with('success', $message);
    }
}
