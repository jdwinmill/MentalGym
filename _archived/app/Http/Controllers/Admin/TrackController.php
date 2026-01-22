<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerOption;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use App\Models\LessonQuestion;
use App\Models\SkillLevel;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->with('success', 'Track deleted successfully.'.($skillCount > 0 ? " {$skillCount} skill level(s) were also removed." : ''));
    }

    public function bulkImportForm()
    {
        return view('admin.tracks.bulk-import');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        try {
            $data = json_decode($request->json_data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return back()->withInput()->withErrors(['json_data' => 'Invalid JSON: '.$e->getMessage()]);
        }

        // Validate required track fields
        if (empty($data['name']) || empty($data['slug'])) {
            return back()->withInput()->withErrors(['json_data' => 'Track must have name and slug fields.']);
        }

        // Check if track slug already exists
        if (Track::where('slug', $data['slug'])->exists()) {
            return back()->withInput()->withErrors(['json_data' => "A track with slug '{$data['slug']}' already exists."]);
        }

        // Handle dry run mode
        if ($request->boolean('dry_run')) {
            return $this->dryRunSummary($data);
        }

        $stats = [
            'skill_levels' => 0,
            'lessons' => 0,
            'content_blocks' => 0,
            'questions' => 0,
            'answer_options' => 0,
        ];

        DB::beginTransaction();

        try {
            // Create the track
            $track = Track::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? '',
                'pitch' => $data['pitch'] ?? '',
                'duration_weeks' => $data['duration_weeks'] ?? 4,
                'sessions_per_week' => $data['sessions_per_week'] ?? 3,
                'session_duration_minutes' => $data['session_duration_minutes'] ?? 10,
                'is_active' => $data['is_active'] ?? false,
                'sort_order' => $data['sort_order'] ?? (Track::max('sort_order') ?? 0) + 1,
            ]);

            // Process skill levels
            foreach ($data['skill_levels'] ?? [] as $skillData) {
                $skillLevel = SkillLevel::create([
                    'track_id' => $track->id,
                    'name' => $skillData['name'],
                    'slug' => $skillData['slug'],
                    'description' => $skillData['description'] ?? '',
                    'level_number' => $skillData['level_number'] ?? 1,
                    'pass_threshold' => $skillData['pass_threshold'] ?? 0.8,
                ]);
                $stats['skill_levels']++;

                // Process lessons
                foreach ($skillData['lessons'] ?? [] as $lessonData) {
                    $lesson = Lesson::create([
                        'track_id' => $track->id,
                        'skill_level_id' => $skillLevel->id,
                        'title' => $lessonData['title'],
                        'lesson_number' => $lessonData['lesson_number'] ?? 1,
                        'estimated_duration_minutes' => $lessonData['estimated_duration_minutes'] ?? 5,
                        'learning_objectives' => $lessonData['learning_objectives'] ?? [],
                        'is_active' => $lessonData['is_active'] ?? false,
                    ]);
                    $stats['lessons']++;

                    // Process content blocks
                    foreach ($lessonData['content_blocks'] ?? [] as $blockData) {
                        LessonContentBlock::create([
                            'lesson_id' => $lesson->id,
                            'block_type' => $blockData['block_type'],
                            'content' => $blockData['content'] ?? [],
                            'sort_order' => $blockData['sort_order'] ?? 1,
                        ]);
                        $stats['content_blocks']++;
                    }

                    // Process questions
                    foreach ($lessonData['questions'] ?? [] as $questionData) {
                        $question = LessonQuestion::create([
                            'lesson_id' => $lesson->id,
                            'skill_level_id' => $skillLevel->id,
                            'question_text' => $questionData['question_text'],
                            'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                            'explanation' => $questionData['explanation'] ?? null,
                            'points' => $questionData['points'] ?? 10,
                            'sort_order' => $questionData['sort_order'] ?? 1,
                        ]);
                        $stats['questions']++;

                        // Process answer options
                        foreach ($questionData['answer_options'] ?? [] as $answerData) {
                            AnswerOption::create([
                                'question_id' => $question->id,
                                'option_text' => $answerData['option_text'],
                                'is_correct' => $answerData['is_correct'] ?? false,
                                'sort_order' => $answerData['sort_order'] ?? 1,
                            ]);
                            $stats['answer_options']++;
                        }
                    }
                }
            }

            DB::commit();

            $message = "Track '{$track->name}' imported successfully! ";
            $message .= "Created: {$stats['skill_levels']} skill levels, ";
            $message .= "{$stats['lessons']} lessons, ";
            $message .= "{$stats['content_blocks']} content blocks, ";
            $message .= "{$stats['questions']} questions, ";
            $message .= "{$stats['answer_options']} answer options.";

            return redirect()->route('admin.tracks.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['json_data' => 'Import failed: '.$e->getMessage()]);
        }
    }

    private function dryRunSummary(array $data): \Illuminate\Http\RedirectResponse
    {
        $counts = [
            'skill_levels' => 0,
            'lessons' => 0,
            'content_blocks' => 0,
            'questions' => 0,
            'answer_options' => 0,
        ];

        $details = [];

        foreach ($data['skill_levels'] ?? [] as $skillData) {
            $counts['skill_levels']++;

            $skillDetail = [
                'name' => $skillData['name'] ?? 'Unnamed Skill',
                'level_number' => $skillData['level_number'] ?? 1,
                'lessons' => [],
            ];

            foreach ($skillData['lessons'] ?? [] as $lessonData) {
                $counts['lessons']++;

                $contentBlockCount = count($lessonData['content_blocks'] ?? []);
                $questionCount = count($lessonData['questions'] ?? []);

                $counts['content_blocks'] += $contentBlockCount;
                $counts['questions'] += $questionCount;

                foreach ($lessonData['questions'] ?? [] as $questionData) {
                    $counts['answer_options'] += count($questionData['answer_options'] ?? []);
                }

                $skillDetail['lessons'][] = [
                    'title' => $lessonData['title'] ?? 'Unnamed Lesson',
                    'lesson_number' => $lessonData['lesson_number'] ?? 1,
                    'content_blocks' => $contentBlockCount,
                    'questions' => $questionCount,
                ];
            }

            $details[] = $skillDetail;
        }

        $summary = [
            'track' => [
                'name' => $data['name'],
                'slug' => $data['slug'],
            ],
            'counts' => $counts,
            'details' => $details,
        ];

        return back()->withInput()->with('dry_run_summary', $summary);
    }
}
