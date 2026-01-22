<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use App\Models\User;
use App\Services\DrillScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScoreDrillResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public int $userId,
        public int $trainingSessionId,
        public int $practiceModeId,
        public string $drillType,
        public string $drillPhase,
        public string $userResponse,
        public bool $isIteration = false
    ) {}

    public function handle(DrillScoringService $service): void
    {
        $user = User::find($this->userId);
        $session = TrainingSession::find($this->trainingSessionId);

        if (! $user || ! $session) {
            Log::warning('ScoreDrillResponse: User or session not found', [
                'user_id' => $this->userId,
                'session_id' => $this->trainingSessionId,
            ]);

            return;
        }

        $score = $service->scoreResponse(
            $user,
            $session,
            $this->drillType,
            $this->drillPhase,
            $this->userResponse,
            $this->isIteration
        );

        if ($score) {
            Log::info('Drill response scored', [
                'drill_score_id' => $score->id,
                'drill_type' => $this->drillType,
                'user_id' => $this->userId,
            ]);
        }
    }

    public function tags(): array
    {
        return [
            'drill-scoring',
            'user:'.$this->userId,
            'drill:'.$this->drillType,
        ];
    }
}
