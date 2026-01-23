<?php

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Models\SessionCompletedEvent;
use App\Models\UserModeProgress;

class RecordSessionCompletion
{
    public function handle(SessionCompleted $event): void
    {
        // 1. Record the completion event for analytics
        $this->recordEvent($event);

        // 2. Update user progress and check level up
        $this->updateUserProgress($event);
    }

    private function recordEvent(SessionCompleted $event): void
    {
        SessionCompletedEvent::create([
            'user_id' => $event->user->id,
            'practice_mode_id' => $event->session->practice_mode_id,
            'training_session_id' => $event->session->id,
            'drills_completed' => $event->drillsCompleted,
            'total_duration_seconds' => $event->totalDurationSeconds,
            'scores' => $event->scores,
            'completed_at' => now(),
        ]);
    }

    private function updateUserProgress(SessionCompleted $event): void
    {
        $progress = UserModeProgress::firstOrCreate(
            [
                'user_id' => $event->user->id,
                'practice_mode_id' => $event->session->practice_mode_id,
            ],
            [
                'current_level' => 1,
                'total_sessions' => 0,
                'total_drills_completed' => 0,
                'sessions_at_current_level' => 0,
            ]
        );

        $progress->increment('total_sessions');
        $progress->increment('total_drills_completed', $event->drillsCompleted);
        $progress->increment('sessions_at_current_level');
        $progress->total_time_seconds += $event->totalDurationSeconds;
        $progress->last_session_at = now();
        $progress->last_trained_at = now();

        // Level up check: every 3 sessions = level up (config-driven)
        $sessionsToLevelUp = config('mentalgym.sessions_to_level_up', 3);
        if ($progress->sessions_at_current_level >= $sessionsToLevelUp && $progress->current_level < 5) {
            $progress->current_level++;
            $progress->sessions_at_current_level = 0;
        }

        $progress->save();
    }
}
