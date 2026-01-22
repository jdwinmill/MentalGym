<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('tags')->latest()->paginate(20);

        return view('admin.questions.index', compact('questions'));
    }

    public function create()
    {
        $allTags = Tag::orderBy('name')->pluck('name');

        return view('admin.questions.create', compact('allTags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'text' => 'nullable|string',
            'prompt' => 'nullable|string',
            'task' => 'nullable|string',
            'principle' => 'nullable|string',
            'intent_tag' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);
        unset($validated['tags']);

        $question = Question::create($validated);

        $this->syncTags($question, $request->input('tags'));

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question created successfully.');
    }

    public function edit(Question $question)
    {
        $question->load('tags');
        $allTags = Tag::orderBy('name')->pluck('name');

        return view('admin.questions.edit', compact('question', 'allTags'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'text' => 'nullable|string',
            'prompt' => 'nullable|string',
            'task' => 'nullable|string',
            'principle' => 'nullable|string',
            'intent_tag' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);
        unset($validated['tags']);

        $question->update($validated);

        $this->syncTags($question, $request->input('tags'));

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()->route('admin.questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    private function syncTags(Question $question, ?string $tagsInput): void
    {
        if (empty($tagsInput)) {
            $question->tags()->detach();

            return;
        }

        $tagNames = array_filter(array_map('trim', explode(',', $tagsInput)));
        $tagIds = [];

        foreach ($tagNames as $name) {
            $tag = Tag::firstOrCreate(['name' => strtolower($name)]);
            $tagIds[] = $tag->id;
        }

        $question->tags()->sync($tagIds);
    }
}
