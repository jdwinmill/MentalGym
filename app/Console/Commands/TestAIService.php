<?php

namespace App\Console\Commands;

use App\Models\PracticeMode;
use App\Services\PracticeAIService;
use Illuminate\Console\Command;

class TestAIService extends Command
{
    protected $signature = 'ai:test {mode_slug} {--level=1}';

    protected $description = 'Test AI service with a practice mode';

    public function handle(PracticeAIService $service): int
    {
        $mode = PracticeMode::where('slug', $this->argument('mode_slug'))->first();

        if (! $mode) {
            $this->error("Practice mode not found: {$this->argument('mode_slug')}");
            $this->newLine();
            $this->info('Available modes:');
            PracticeMode::all()->each(fn ($m) => $this->line("  - {$m->slug}"));

            return Command::FAILURE;
        }

        $level = (int) $this->option('level');

        $this->info("Testing {$mode->name} at Level {$level}");
        $this->newLine();

        $this->info('Getting first response...');
        $response = $service->getFirstResponse($mode, $level);

        $this->info("Type: {$response['type']}");
        $this->newLine();

        if (isset($response['content'])) {
            $this->line('Content:');
            $this->line($response['content']);
        } else {
            $this->line('Response: '.json_encode($response, JSON_PRETTY_PRINT));
        }

        $this->newLine();
        $this->info('Check api_logs table for logged metrics.');

        return Command::SUCCESS;
    }
}
