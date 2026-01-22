<?php

namespace App\Console\Commands;

use Database\Seeders\TrackContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

/**
 * ImportTrackContent - Artisan command to import AI-generated track content.
 *
 * This command provides a user-friendly interface for importing structured
 * JSON content into the database using the TrackContentSeeder.
 *
 * @example
 * php artisan track:import storage/seeds/active-listening.json
 * php artisan track:import storage/seeds/active-listening.json --dry-run
 * php artisan track:import storage/seeds/active-listening.json --validate-only
 */
class ImportTrackContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:import
                            {file : Path to the JSON file to import}
                            {--dry-run : Validate and preview without importing}
                            {--validate-only : Only validate the JSON structure}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import AI-generated track content from a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        // Resolve relative paths
        if (! str_starts_with($filePath, '/')) {
            $filePath = base_path($filePath);
        }

        $this->info("Processing: {$filePath}");
        $this->newLine();

        // Check if file exists
        if (! File::exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return Command::FAILURE;
        }

        // Read and parse JSON
        try {
            $content = File::get($filePath);
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error("Invalid JSON: {$e->getMessage()}");

            return Command::FAILURE;
        }

        // Validate structure
        try {
            $this->validateStructure($data);
            $this->success('JSON structure is valid');
        } catch (InvalidArgumentException $e) {
            $this->error("Validation failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        // Preview the content
        $this->previewContent($data);

        // If validate-only, stop here
        if ($this->option('validate-only')) {
            $this->info('Validation complete. Use --dry-run or remove --validate-only to proceed.');

            return Command::SUCCESS;
        }

        // If dry-run, show what would be created
        if ($this->option('dry-run')) {
            $this->showDryRunSummary($data);
            $this->info('Dry run complete. Remove --dry-run to actually import.');

            return Command::SUCCESS;
        }

        // Confirm import
        if (! $this->option('force')) {
            if (! $this->confirm('Do you want to import this track content?')) {
                $this->info('Import cancelled.');

                return Command::SUCCESS;
            }
        }

        // Run the seeder
        try {
            $seeder = new TrackContentSeeder;
            $seeder->setCommand($this);
            $track = $seeder->seedFromArray($data);

            $this->newLine();
            $this->success("Track '{$track->name}' imported successfully!");
            $this->info("Track ID: {$track->id}");
            $this->info("Track Slug: {$track->slug}");

            return Command::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error("Import failed: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Validate the JSON structure.
     */
    protected function validateStructure(array $data): void
    {
        // Required top-level keys
        if (! isset($data['track'])) {
            throw new InvalidArgumentException('Missing required "track" key');
        }

        // Required track fields
        $requiredTrackFields = ['slug', 'name', 'description'];
        foreach ($requiredTrackFields as $field) {
            if (empty($data['track'][$field])) {
                throw new InvalidArgumentException("Missing required track field: {$field}");
            }
        }

        // Validate slug format
        if (! preg_match('/^[a-z0-9-]+$/', $data['track']['slug'])) {
            throw new InvalidArgumentException(
                'Track slug must contain only lowercase letters, numbers, and hyphens'
            );
        }

        // Validate skill levels
        if (isset($data['skill_levels'])) {
            $slugs = [];
            $levelNumbers = [];

            foreach ($data['skill_levels'] as $index => $level) {
                if (empty($level['slug'])) {
                    throw new InvalidArgumentException("Skill level at index {$index} is missing 'slug'");
                }
                if (empty($level['name'])) {
                    throw new InvalidArgumentException("Skill level at index {$index} is missing 'name'");
                }
                if (! isset($level['level_number'])) {
                    throw new InvalidArgumentException("Skill level at index {$index} is missing 'level_number'");
                }

                // Check for duplicates
                if (in_array($level['slug'], $slugs)) {
                    throw new InvalidArgumentException("Duplicate skill level slug: {$level['slug']}");
                }
                if (in_array($level['level_number'], $levelNumbers)) {
                    throw new InvalidArgumentException("Duplicate level_number: {$level['level_number']}");
                }

                $slugs[] = $level['slug'];
                $levelNumbers[] = $level['level_number'];
            }
        }

        // Validate lessons
        if (isset($data['lessons'])) {
            $skillLevelSlugs = array_column($data['skill_levels'] ?? [], 'slug');

            foreach ($data['lessons'] as $index => $lesson) {
                if (! isset($lesson['lesson_number'])) {
                    throw new InvalidArgumentException("Lesson at index {$index} is missing 'lesson_number'");
                }
                if (empty($lesson['title'])) {
                    throw new InvalidArgumentException("Lesson at index {$index} is missing 'title'");
                }
                if (empty($lesson['skill_level_slug'])) {
                    throw new InvalidArgumentException("Lesson at index {$index} is missing 'skill_level_slug'");
                }

                // Check skill level reference
                if (! in_array($lesson['skill_level_slug'], $skillLevelSlugs)) {
                    throw new InvalidArgumentException(
                        "Lesson at index {$index} references unknown skill level: {$lesson['skill_level_slug']}"
                    );
                }

                // Validate content blocks
                if (isset($lesson['content_blocks'])) {
                    $this->validateContentBlocks($lesson['content_blocks'], $index);
                }

                // Validate questions
                if (isset($lesson['questions'])) {
                    $this->validateQuestions($lesson['questions'], $lesson['content_blocks'] ?? [], $index, $skillLevelSlugs);
                }
            }
        }
    }

    /**
     * Validate content blocks within a lesson.
     */
    protected function validateContentBlocks(array $blocks, int $lessonIndex): void
    {
        $sortOrders = [];

        foreach ($blocks as $blockIndex => $block) {
            if (empty($block['block_type'])) {
                throw new InvalidArgumentException(
                    "Content block at lesson {$lessonIndex}, block {$blockIndex} is missing 'block_type'"
                );
            }
            if (! isset($block['content'])) {
                throw new InvalidArgumentException(
                    "Content block at lesson {$lessonIndex}, block {$blockIndex} is missing 'content'"
                );
            }

            // Check for duplicate sort_orders
            $sortOrder = $block['sort_order'] ?? $blockIndex;
            if (in_array($sortOrder, $sortOrders)) {
                throw new InvalidArgumentException(
                    "Duplicate sort_order {$sortOrder} in lesson {$lessonIndex} content blocks"
                );
            }
            $sortOrders[] = $sortOrder;
        }
    }

    /**
     * Validate questions within a lesson.
     */
    protected function validateQuestions(array $questions, array $contentBlocks, int $lessonIndex, array $skillLevelSlugs): void
    {
        $blockSortOrders = array_column($contentBlocks, 'sort_order');

        foreach ($questions as $qIndex => $question) {
            if (empty($question['question_text'])) {
                throw new InvalidArgumentException(
                    "Question at lesson {$lessonIndex}, question {$qIndex} is missing 'question_text'"
                );
            }
            if (empty($question['skill_level_slug'])) {
                throw new InvalidArgumentException(
                    "Question at lesson {$lessonIndex}, question {$qIndex} is missing 'skill_level_slug'"
                );
            }

            // Check skill level reference
            if (! in_array($question['skill_level_slug'], $skillLevelSlugs)) {
                throw new InvalidArgumentException(
                    "Question at lesson {$lessonIndex}, question {$qIndex} references unknown skill level: {$question['skill_level_slug']}"
                );
            }

            // Check related block reference if provided
            if (isset($question['related_block_sort_order']) && $question['related_block_sort_order'] !== null) {
                if (! in_array($question['related_block_sort_order'], $blockSortOrders)) {
                    throw new InvalidArgumentException(
                        "Question at lesson {$lessonIndex}, question {$qIndex} references unknown content block sort_order: {$question['related_block_sort_order']}"
                    );
                }
            }

            // Validate answer options
            if (isset($question['answer_options'])) {
                $this->validateAnswerOptions($question['answer_options'], $lessonIndex, $qIndex);
            }
        }
    }

    /**
     * Validate answer options within a question.
     */
    protected function validateAnswerOptions(array $options, int $lessonIndex, int $questionIndex): void
    {
        $hasCorrect = false;

        foreach ($options as $optIndex => $option) {
            if (empty($option['option_text'])) {
                throw new InvalidArgumentException(
                    "Answer option at lesson {$lessonIndex}, question {$questionIndex}, option {$optIndex} is missing 'option_text'"
                );
            }

            if (! empty($option['is_correct'])) {
                $hasCorrect = true;
            }
        }

        if (! $hasCorrect) {
            $this->warn("Warning: Question at lesson {$lessonIndex}, question {$questionIndex} has no correct answer marked");
        }
    }

    /**
     * Preview the content structure.
     */
    protected function previewContent(array $data): void
    {
        $this->newLine();
        $this->info('Content Preview:');
        $this->line(str_repeat('-', 50));

        // Track info
        $track = $data['track'];
        $this->info("Track: {$track['name']}");
        $this->line("  Slug: {$track['slug']}");
        $this->line("  Duration: {$track['duration_weeks']} weeks, {$track['sessions_per_week']} sessions/week");

        // Skill levels
        $skillLevels = $data['skill_levels'] ?? [];
        $this->info('Skill Levels: '.count($skillLevels));
        foreach ($skillLevels as $level) {
            $this->line("  {$level['level_number']}. {$level['name']} ({$level['slug']})");
        }

        // Lessons summary
        $lessons = $data['lessons'] ?? [];
        $this->info('Lessons: '.count($lessons));

        $totalBlocks = 0;
        $totalQuestions = 0;
        $totalOptions = 0;

        foreach ($lessons as $lesson) {
            $totalBlocks += count($lesson['content_blocks'] ?? []);
            $questions = $lesson['questions'] ?? [];
            $totalQuestions += count($questions);
            foreach ($questions as $q) {
                $totalOptions += count($q['answer_options'] ?? []);
            }
        }

        $this->line("  Total Content Blocks: {$totalBlocks}");
        $this->line("  Total Questions: {$totalQuestions}");
        $this->line("  Total Answer Options: {$totalOptions}");

        $this->line(str_repeat('-', 50));
        $this->newLine();
    }

    /**
     * Show a summary of what would be created in a dry run.
     */
    protected function showDryRunSummary(array $data): void
    {
        $this->newLine();
        $this->info('Dry Run Summary - Records that would be created:');
        $this->line(str_repeat('-', 50));

        $lessons = $data['lessons'] ?? [];
        $totalBlocks = 0;
        $totalQuestions = 0;
        $totalOptions = 0;
        $totalFeedback = 0;

        foreach ($lessons as $lesson) {
            $totalBlocks += count($lesson['content_blocks'] ?? []);
            $questions = $lesson['questions'] ?? [];
            $totalQuestions += count($questions);
            foreach ($questions as $q) {
                $options = $q['answer_options'] ?? [];
                $totalOptions += count($options);
                foreach ($options as $opt) {
                    if (! empty($opt['feedback'])) {
                        $totalFeedback++;
                    }
                }
            }
        }

        $this->line('  1 Track');
        $this->line('  '.count($data['skill_levels'] ?? []).' Skill Levels');
        $this->line('  '.count($lessons).' Lessons');
        $this->line("  {$totalBlocks} Content Blocks");
        $this->line("  {$totalQuestions} Questions");
        $this->line("  {$totalOptions} Answer Options");
        $this->line("  {$totalFeedback} Answer Feedback entries");

        $total = 1 + count($data['skill_levels'] ?? []) + count($lessons) +
                 $totalBlocks + $totalQuestions + $totalOptions + $totalFeedback;

        $this->newLine();
        $this->info("Total records: {$total}");
        $this->line(str_repeat('-', 50));
    }

    /**
     * Output a success message.
     */
    protected function success(string $message): void
    {
        $this->line("<fg=green>✓</> {$message}");
    }
}
