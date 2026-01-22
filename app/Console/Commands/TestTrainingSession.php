<?php

namespace App\Console\Commands;

use App\Models\PracticeMode;
use App\Models\User;
use App\Services\TrainingSessionService;
use Illuminate\Console\Command;

class TestTrainingSession extends Command
{
    protected $signature = 'training:test {user_id} {mode_slug}';

    protected $description = 'Test training session flow';

    public function handle(TrainingSessionService $service): int
    {
        $user = User::findOrFail($this->argument('user_id'));
        $mode = PracticeMode::where('slug', $this->argument('mode_slug'))->firstOrFail();

        $this->info("Testing training session for {$user->name} in {$mode->name}");
        $this->newLine();

        // Start session
        $this->info('Starting session...');
        try {
            $result = $service->startSession($user, $mode);

            if ($result['resumed']) {
                $this->warn("Resumed existing session: #{$result['session']->id}");
                $lastMessage = $result['messages']->last();
                $this->info("Last message type: {$lastMessage['type']}");
            } else {
                $this->info("New session created: #{$result['session']->id}");
                $this->info("First card type: {$result['card']['type']}");
                $this->line('Content: '.substr($result['card']['content'] ?? '', 0, 100).'...');
            }
        } catch (\Exception $e) {
            $this->error("Failed to start: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->newLine();

        // Continue session
        $this->info('Continuing session with test input...');
        try {
            $result = $service->continueSession(
                $result['session'],
                'I would address the situation directly and have a conversation with them.'
            );

            if (isset($result['error'])) {
                $this->error("Error: {$result['error']} - {$result['message']}");

                return Command::FAILURE;
            }

            $this->info("Response card type: {$result['card']['type']}");

            if (isset($result['levelUp'])) {
                if ($result['levelUp']['type'] === 'level_up') {
                    $this->warn("Level up! New level: {$result['levelUp']['new_level']}");
                } else {
                    $this->warn("Level cap reached: {$result['levelUp']['message']}");
                }
            }

            $this->info("Progress: Level {$result['progress']['current_level']}, ".
                "{$result['progress']['exchanges_at_current_level']} exchanges, ".
                ($result['progress']['exchanges_to_next_level'] ?? 'max').' to next level');
        } catch (\Exception $e) {
            $this->error("Failed to continue: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->newLine();

        // End session
        $this->info('Ending session...');
        try {
            $service->endSession($result['session']);
            $this->info('Session ended successfully');
        } catch (\Exception $e) {
            $this->error("Failed to end: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
