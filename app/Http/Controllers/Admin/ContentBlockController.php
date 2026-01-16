<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentBlockController extends Controller
{
    public function index(Lesson $lesson)
    {
        $contentBlocks = $lesson->contentBlocks()
            ->orderBy('sort_order')
            ->get();

        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        return view('admin.content-blocks.index', compact('lesson', 'skillLevel', 'track', 'contentBlocks'));
    }

    public function create(Lesson $lesson)
    {
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;
        $nextSortOrder = ($lesson->contentBlocks()->max('sort_order') ?? 0) + 1;

        return view('admin.content-blocks.create', compact('lesson', 'skillLevel', 'track', 'nextSortOrder'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'block_type' => 'required|in:audio,video,principle_text,instruction_text,image',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        // Type-specific validation
        $contentRules = $this->getContentRulesForType($request->block_type);
        $contentValidated = $request->validate($contentRules);

        // Build content JSON based on type
        $content = $this->buildContentFromRequest($request, $request->block_type);

        LessonContentBlock::create([
            'lesson_id' => $lesson->id,
            'block_type' => $validated['block_type'],
            'content' => $content,
            'sort_order' => $validated['sort_order'] ?? (($lesson->contentBlocks()->max('sort_order') ?? 0) + 1),
        ]);

        return redirect()->route('admin.lessons.content-blocks.index', $lesson)
            ->with('success', 'Content block created successfully.');
    }

    public function edit(LessonContentBlock $contentBlock)
    {
        $lesson = $contentBlock->lesson;
        $skillLevel = $lesson->skillLevel;
        $track = $skillLevel->track;

        return view('admin.content-blocks.edit', compact('contentBlock', 'lesson', 'skillLevel', 'track'));
    }

    public function update(Request $request, LessonContentBlock $contentBlock)
    {
        $validated = $request->validate([
            'sort_order' => 'nullable|integer|min:1',
        ]);

        // Type-specific validation (type cannot change)
        $contentRules = $this->getContentRulesForType($contentBlock->block_type);
        $request->validate($contentRules);

        // Build content JSON based on type
        $content = $this->buildContentFromRequest($request, $contentBlock->block_type);

        $contentBlock->update([
            'content' => $content,
            'sort_order' => $validated['sort_order'] ?? $contentBlock->sort_order,
        ]);

        return redirect()->route('admin.lessons.content-blocks.index', $contentBlock->lesson)
            ->with('success', 'Content block updated successfully.');
    }

    public function destroy(LessonContentBlock $contentBlock)
    {
        $lesson = $contentBlock->lesson;

        // Delete associated file if it exists
        if ($contentBlock->isMedia()) {
            $url = $contentBlock->getMediaUrl();
            if ($url && str_starts_with($url, '/storage/')) {
                $path = str_replace('/storage/', '', $url);
                Storage::disk('public')->delete($path);
            }
        }

        $contentBlock->delete();

        return redirect()->route('admin.lessons.content-blocks.index', $lesson)
            ->with('success', 'Content block deleted successfully.');
    }

    private function getContentRulesForType(string $type): array
    {
        return match ($type) {
            'audio' => [
                'audio_url' => 'required|string|max:500',
                'duration_seconds' => 'nullable|integer|min:1',
                'transcript' => 'nullable|string|max:50000',
                'title' => 'nullable|string|max:255',
            ],
            'video' => [
                'url' => 'required|string|max:500',
                'duration_seconds' => 'nullable|integer|min:1',
                'thumbnail_url' => 'nullable|string|max:500',
                'description' => 'nullable|string|max:255',
            ],
            'principle_text', 'instruction_text' => [
                'text' => 'required|string|max:50000',
                'format' => 'nullable|in:plain,markdown,html',
            ],
            'image' => [
                'url' => 'required|string|max:500',
                'alt_text' => 'required|string|max:255',
                'caption' => 'nullable|string|max:500',
            ],
            default => [],
        };
    }

    private function buildContentFromRequest(Request $request, string $type): array
    {
        return match ($type) {
            'audio' => [
                'audio_url' => $request->input('audio_url'),
                'duration_seconds' => $request->input('duration_seconds'),
                'transcript' => $request->input('transcript'),
                'title' => $request->input('title'),
            ],
            'video' => [
                'url' => $request->input('url'),
                'duration_seconds' => $request->input('duration_seconds'),
                'thumbnail_url' => $request->input('thumbnail_url'),
                'description' => $request->input('description'),
            ],
            'principle_text', 'instruction_text' => [
                'text' => $request->input('text'),
                'format' => $request->input('format', 'markdown'),
            ],
            'image' => [
                'url' => $request->input('url'),
                'alt_text' => $request->input('alt_text'),
                'caption' => $request->input('caption'),
            ],
            default => [],
        };
    }
}
