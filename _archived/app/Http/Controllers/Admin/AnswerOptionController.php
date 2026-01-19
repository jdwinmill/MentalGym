<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerOption;
use App\Models\LessonQuestion;
use Illuminate\Http\Request;

class AnswerOptionController extends Controller
{
    public function index(LessonQuestion $lessonQuestion)
    {
        $answerOptions = $lessonQuestion->answerOptions()
            ->orderBy('sort_order')
            ->get();

        $lesson = $lessonQuestion->lesson;
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        // Check if any correct answer exists
        $hasCorrectAnswer = $answerOptions->contains('is_correct', true);

        return view('admin.answer-options.index', compact('lessonQuestion', 'lesson', 'skillLevel', 'track', 'answerOptions', 'hasCorrectAnswer'));
    }

    public function create(LessonQuestion $lessonQuestion)
    {
        $lesson = $lessonQuestion->lesson;
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;
        $nextSortOrder = ($lessonQuestion->answerOptions()->max('sort_order') ?? 0) + 1;

        return view('admin.answer-options.create', compact('lessonQuestion', 'lesson', 'skillLevel', 'track', 'nextSortOrder'));
    }

    public function store(Request $request, LessonQuestion $lessonQuestion)
    {
        $validated = $request->validate([
            'option_text' => 'required|string|max:500',
            'is_correct' => 'boolean',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $validated['question_id'] = $lessonQuestion->id;
        $validated['is_correct'] = $request->boolean('is_correct');
        $validated['sort_order'] = $validated['sort_order'] ?? (($lessonQuestion->answerOptions()->max('sort_order') ?? 0) + 1);

        AnswerOption::create($validated);

        if ($request->has('save_and_add_another')) {
            return redirect()->route('admin.lesson-questions.answer-options.create', $lessonQuestion)
                ->with('success', 'Answer option created. Add another.');
        }

        return redirect()->route('admin.lesson-questions.answer-options.index', $lessonQuestion)
            ->with('success', 'Answer option created successfully.');
    }

    public function edit(AnswerOption $answerOption)
    {
        $lessonQuestion = $answerOption->question;
        $lesson = $lessonQuestion->lesson;
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        return view('admin.answer-options.edit', compact('answerOption', 'lessonQuestion', 'lesson', 'skillLevel', 'track'));
    }

    public function update(Request $request, AnswerOption $answerOption)
    {
        $validated = $request->validate([
            'option_text' => 'required|string|max:500',
            'is_correct' => 'boolean',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $validated['is_correct'] = $request->boolean('is_correct');

        $answerOption->update($validated);

        return redirect()->route('admin.lesson-questions.answer-options.index', $answerOption->question)
            ->with('success', 'Answer option updated successfully.');
    }

    public function destroy(AnswerOption $answerOption)
    {
        $lessonQuestion = $answerOption->question;

        // Warn if deleting the only correct answer
        $isOnlyCorrect = $answerOption->is_correct &&
            $lessonQuestion->answerOptions()->where('is_correct', true)->count() === 1;

        $answerOption->delete();

        $message = 'Answer option deleted successfully.';
        if ($isOnlyCorrect) {
            $message .= ' Warning: This question now has no correct answers.';
        }

        return redirect()->route('admin.lesson-questions.answer-options.index', $lessonQuestion)
            ->with($isOnlyCorrect ? 'error' : 'success', $message);
    }
}
