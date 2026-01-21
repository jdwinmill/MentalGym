<?php

namespace App\Events;

use App\Models\TrainingSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TrainingSession $session
    ) {}
}
