<?php

namespace Database\Seeders;

use App\Models\AnswerFeedback;
use App\Models\AnswerOption;
use App\Models\Lesson;
use App\Models\LessonContentBlock;
use App\Models\LessonQuestion;
use App\Models\SkillLevel;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * TrackContentSeeder - Seeds complete track content from structured JSON.
 *
 * This seeder accepts AI-generated or manually crafted JSON content and creates
 * all related database records in the correct order with proper relationships.
 *
 * Processing order:
 * 1. Track
 * 2. Skill Levels (with track relationship)
 * 3. Lessons (with track and skill_level relationships)
 * 4. Content Blocks (with lesson relationship)
 * 5. Questions (with lesson, skill_level, and related_block relationships)
 * 6. Answer Options (with question relationship)
 * 7. Answer Feedback (with question and answer_option relationships)
 *
 * @example
 * $seeder = new TrackContentSeeder();
 * $seeder->seedFromFile(storage_path('seeds/active-listening.json'));
 * @example
 * $seeder = new TrackContentSeeder();
 * $seeder->seedFromArray($jsonDecodedData);
 */
class TrackContentSeeder extends Seeder
{
    /**
     * Counters for seeded records.
     */
    protected array $counts = [
        'tracks' => 0,
        'skill_levels' => 0,
        'lessons' => 0,
        'content_blocks' => 0,
        'questions' => 0,
        'answer_options' => 0,
        'answer_feedback' => 0,
    ];

    /**
     * Lookup maps for slug -> id resolution.
     */
    protected array $skillLevelMap = [];

    protected array $contentBlockMap = [];

    /**
     * Whether to output progress messages.
     */
    protected bool $verbose = true;

    /**
     * The created track instance.
     */
    protected ?Track $track = null;

    /**
     * Run the database seeds.
     *
     * This method can be called via artisan with a file argument:
     * php artisan db:seed --class=TrackContentSeeder
     *
     * For file-based seeding, use the ImportTrackContent command instead.
     */
    public function run(): void
    {
        // Default behavior: seed from the sample file if it exists
        $sampleFile = storage_path('seeds/active-listening-sample.json');

        if (file_exists($sampleFile)) {
            $this->seedFromFile($sampleFile);
        } else {
            $this->info('No seed file found. Use ImportTrackContent command or call seedFromFile() directly.');
        }
    }

    /**
     * Seed content from a JSON file.
     *
     * @param  string  $filePath  Absolute path to the JSON file
     *
     * @throws InvalidArgumentException If file doesn't exist or is invalid JSON
     * @throws RuntimeException If seeding fails
     */
    public function seedFromFile(string $filePath): Track
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("Seed file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }

        return $this->seedFromArray($data);
    }

    /**
     * Seed content from a decoded JSON array.
     *
     * @param  array  $data  The decoded JSON data
     *
     * @throws InvalidArgumentException If required data is missing
     * @throws RuntimeException If seeding fails
     */
    public function seedFromArray(array $data): Track
    {
        $this->resetState();
        $this->validateStructure($data);

        DB::beginTransaction();

        try {
            // 1. Create or update the track
            $this->track = $this->createTrack($data['track']);

            // 2. Create skill levels
            $this->createSkillLevels($data['skill_levels'] ?? []);

            // 3. Create lessons with content blocks and questions
            $this->createLessons($data['lessons'] ?? []);

            DB::commit();

            $this->printSummary();

            return $this->track;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Seeding failed: {$e->getMessage()}");
            Log::error('TrackContentSeeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new RuntimeException("Failed to seed track content: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Reset internal state for a fresh seeding run.
     */
    protected function resetState(): void
    {
        $this->counts = array_fill_keys(array_keys($this->counts), 0);
        $this->skillLevelMap = [];
        $this->contentBlockMap = [];
        $this->track = null;
    }

    /**
     * Validate the top-level structure of the input data.
     *
     * @param  array  $data  The input data to validate
     *
     * @throws InvalidArgumentException If validation fails
     */
    protected function validateStructure(array $data): void
    {
        if (! isset($data['track'])) {
            throw new InvalidArgumentException('Missing required "track" key in JSON structure');
        }

        $requiredTrackFields = ['slug', 'name', 'description'];
        foreach ($requiredTrackFields as $field) {
            if (empty($data['track'][$field])) {
                throw new InvalidArgumentException("Missing required track field: {$field}");
            }
        }

        // Validate skill levels if present
        if (isset($data['skill_levels'])) {
            foreach ($data['skill_levels'] as $index => $level) {
                if (empty($level['slug']) || empty($level['name']) || ! isset($level['level_number'])) {
                    throw new InvalidArgumentException(
                        "Skill level at index {$index} is missing required fields (slug, name, level_number)"
                    );
                }
            }
        }

        // Validate lessons if present
        if (isset($data['lessons'])) {
            foreach ($data['lessons'] as $index => $lesson) {
                if (! isset($lesson['lesson_number']) || empty($lesson['title']) || empty($lesson['skill_level_slug'])) {
                    throw new InvalidArgumentException(
                        "Lesson at index {$index} is missing required fields (lesson_number, title, skill_level_slug)"
                    );
                }
            }
        }
    }

    /**
     * Create or update the track record.
     *
     * @param  array  $trackData  Track data from JSON
     * @return Track The created or updated track
     */
    protected function createTrack(array $trackData): Track
    {
        $existingTrack = Track::where('slug', $trackData['slug'])->first();

        if ($existingTrack) {
            $this->info("Track '{$trackData['slug']}' already exists. Updating...");

            // Delete existing related data for clean re-seeding
            $existingTrack->skillLevels()->delete();
            $existingTrack->lessons()->delete();

            $existingTrack->update([
                'name' => $trackData['name'],
                'description' => $trackData['description'],
                'pitch' => $trackData['pitch'] ?? null,
                'duration_weeks' => $trackData['duration_weeks'] ?? 8,
                'sessions_per_week' => $trackData['sessions_per_week'] ?? 5,
                'session_duration_minutes' => $trackData['session_duration_minutes'] ?? 10,
                'is_active' => $trackData['is_active'] ?? true,
                'sort_order' => $trackData['sort_order'] ?? 0,
            ]);

            $track = $existingTrack->fresh();
        } else {
            $track = Track::create([
                'slug' => $trackData['slug'],
                'name' => $trackData['name'],
                'description' => $trackData['description'],
                'pitch' => $trackData['pitch'] ?? null,
                'duration_weeks' => $trackData['duration_weeks'] ?? 8,
                'sessions_per_week' => $trackData['sessions_per_week'] ?? 5,
                'session_duration_minutes' => $trackData['session_duration_minutes'] ?? 10,
                'is_active' => $trackData['is_active'] ?? true,
                'sort_order' => $trackData['sort_order'] ?? 0,
            ]);

            $this->counts['tracks']++;
        }

        $this->success("Track: {$track->name}");

        return $track;
    }

    /**
     * Create skill levels for the track.
     *
     * @param  array  $skillLevelsData  Array of skill level data
     */
    protected function createSkillLevels(array $skillLevelsData): void
    {
        foreach ($skillLevelsData as $levelData) {
            $skillLevel = SkillLevel::create([
                'track_id' => $this->track->id,
                'slug' => $levelData['slug'],
                'name' => $levelData['name'],
                'description' => $levelData['description'] ?? null,
                'level_number' => $levelData['level_number'],
                'pass_threshold' => $levelData['pass_threshold'] ?? 0.80,
            ]);

            // Store in lookup map for later reference
            $this->skillLevelMap[$levelData['slug']] = $skillLevel->id;
            $this->counts['skill_levels']++;

            $this->info("  Skill Level {$levelData['level_number']}: {$skillLevel->name}");
        }
    }

    /**
     * Create lessons with their content blocks and questions.
     *
     * @param  array  $lessonsData  Array of lesson data
     */
    protected function createLessons(array $lessonsData): void
    {
        foreach ($lessonsData as $lessonData) {
            $skillLevelId = $this->resolveSkillLevelId($lessonData['skill_level_slug']);

            $lesson = Lesson::create([
                'track_id' => $this->track->id,
                'skill_level_id' => $skillLevelId,
                'lesson_number' => $lessonData['lesson_number'],
                'title' => $lessonData['title'],
                'learning_objectives' => $lessonData['learning_objectives'] ?? null,
                'estimated_duration_minutes' => $lessonData['estimated_duration_minutes'] ?? null,
                'is_active' => $lessonData['is_active'] ?? true,
            ]);

            $this->counts['lessons']++;
            $this->info("  Lesson {$lessonData['lesson_number']}: {$lesson->title}");

            // Reset content block map for this lesson
            $this->contentBlockMap = [];

            // Create content blocks
            if (! empty($lessonData['content_blocks'])) {
                $this->createContentBlocks($lesson, $lessonData['content_blocks']);
            }

            // Create questions
            if (! empty($lessonData['questions'])) {
                $this->createQuestions($lesson, $lessonData['questions']);
            }
        }
    }

    /**
     * Create content blocks for a lesson.
     *
     * @param  Lesson  $lesson  The parent lesson
     * @param  array  $blocksData  Array of content block data
     */
    protected function createContentBlocks(Lesson $lesson, array $blocksData): void
    {
        foreach ($blocksData as $blockData) {
            $block = LessonContentBlock::create([
                'lesson_id' => $lesson->id,
                'block_type' => $blockData['block_type'],
                'content' => $blockData['content'],
                'sort_order' => $blockData['sort_order'] ?? 0,
            ]);

            // Store in lookup map for question reference
            $this->contentBlockMap[$blockData['sort_order']] = $block->id;
            $this->counts['content_blocks']++;

            $this->info("    Block {$blockData['sort_order']}: {$blockData['block_type']}");
        }
    }

    /**
     * Create questions for a lesson.
     *
     * @param  Lesson  $lesson  The parent lesson
     * @param  array  $questionsData  Array of question data
     */
    protected function createQuestions(Lesson $lesson, array $questionsData): void
    {
        foreach ($questionsData as $questionData) {
            $skillLevelId = $this->resolveSkillLevelId($questionData['skill_level_slug']);
            $relatedBlockId = $this->resolveRelatedBlockId($questionData['related_block_sort_order'] ?? null);

            $question = LessonQuestion::create([
                'lesson_id' => $lesson->id,
                'skill_level_id' => $skillLevelId,
                'related_block_id' => $relatedBlockId,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'] ?? 'multiple_choice',
                'correct_answer' => $questionData['correct_answer'] ?? null,
                'explanation' => $questionData['explanation'] ?? null,
                'points' => $questionData['points'] ?? 1,
                'sort_order' => $questionData['sort_order'] ?? 0,
            ]);

            $this->counts['questions']++;
            $this->info("    Question {$questionData['sort_order']}: ".substr($questionData['question_text'], 0, 50).'...');

            // Create answer options with feedback
            if (! empty($questionData['answer_options'])) {
                $this->createAnswerOptions($question, $questionData['answer_options']);
            }
        }
    }

    /**
     * Create answer options and their feedback for a question.
     *
     * @param  LessonQuestion  $question  The parent question
     * @param  array  $optionsData  Array of answer option data
     */
    protected function createAnswerOptions(LessonQuestion $question, array $optionsData): void
    {
        foreach ($optionsData as $optionData) {
            $option = AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'],
                'is_correct' => $optionData['is_correct'] ?? false,
                'sort_order' => $optionData['sort_order'] ?? 0,
            ]);

            $this->counts['answer_options']++;

            // Create feedback if provided
            if (! empty($optionData['feedback'])) {
                $this->createAnswerFeedback($question, $option, $optionData['feedback']);
            }
        }
    }

    /**
     * Create feedback for an answer option.
     *
     * @param  LessonQuestion  $question  The parent question
     * @param  AnswerOption  $option  The answer option
     * @param  array  $feedbackData  Feedback data
     */
    protected function createAnswerFeedback(LessonQuestion $question, AnswerOption $option, array $feedbackData): void
    {
        AnswerFeedback::create([
            'question_id' => $question->id,
            'answer_option_id' => $option->id,
            'feedback_text' => $feedbackData['feedback_text'],
            'pattern_tag' => $feedbackData['pattern_tag'] ?? null,
            'severity' => $feedbackData['severity'] ?? null,
        ]);

        $this->counts['answer_feedback']++;
    }

    /**
     * Resolve a skill level slug to its ID.
     *
     * @param  string  $slug  The skill level slug
     * @return int The skill level ID
     *
     * @throws InvalidArgumentException If the slug is not found
     */
    protected function resolveSkillLevelId(string $slug): int
    {
        if (! isset($this->skillLevelMap[$slug])) {
            throw new InvalidArgumentException("Unknown skill level slug: {$slug}");
        }

        return $this->skillLevelMap[$slug];
    }

    /**
     * Resolve a content block sort_order to its ID within the current lesson.
     *
     * @param  int|null  $sortOrder  The content block sort_order
     * @return int|null The content block ID or null
     */
    protected function resolveRelatedBlockId(?int $sortOrder): ?int
    {
        if ($sortOrder === null) {
            return null;
        }

        return $this->contentBlockMap[$sortOrder] ?? null;
    }

    /**
     * Print a summary of the seeding operation.
     */
    protected function printSummary(): void
    {
        $this->command->newLine();
        $this->success('Seeding Complete!');
        $this->command->newLine();

        $this->info('Summary:');
        $this->info("  Tracks:         {$this->counts['tracks']}");
        $this->info("  Skill Levels:   {$this->counts['skill_levels']}");
        $this->info("  Lessons:        {$this->counts['lessons']}");
        $this->info("  Content Blocks: {$this->counts['content_blocks']}");
        $this->info("  Questions:      {$this->counts['questions']}");
        $this->info("  Answer Options: {$this->counts['answer_options']}");
        $this->info("  Answer Feedback:{$this->counts['answer_feedback']}");

        $total = array_sum($this->counts);
        $this->command->newLine();
        $this->success("Total records created: {$total}");
    }

    /**
     * Output an info message.
     */
    protected function info(string $message): void
    {
        if ($this->verbose && isset($this->command)) {
            $this->command->info($message);
        }
    }

    /**
     * Output a success message.
     */
    protected function success(string $message): void
    {
        if ($this->verbose && isset($this->command)) {
            $this->command->info("<fg=green>✓</> {$message}");
        }
    }

    /**
     * Output an error message.
     */
    protected function error(string $message): void
    {
        if (isset($this->command)) {
            $this->command->error($message);
        }
    }

    /**
     * Set verbose mode.
     */
    public function setVerbose(bool $verbose): self
    {
        $this->verbose = $verbose;

        return $this;
    }

    /**
     * Get the counts of seeded records.
     */
    public function getCounts(): array
    {
        return $this->counts;
    }

    /**
     * Get the created track.
     */
    public function getTrack(): ?Track
    {
        return $this->track;
    }
}
