<?php

namespace App\Events;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public TrainingSession $session,
        public int $drillsCompleted = 0,
        public int $totalDurationSeconds = 0,
        public array $scores = []
    ) {}
}
