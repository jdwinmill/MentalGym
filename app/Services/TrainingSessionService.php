<?php

namespace App\Services;

use App\Events\SessionCompleted;
use App\Jobs\ScoreDrillResponse;
use App\Models\DailyUsage;
use App\Models\PracticeMode;
use App\Models\SessionMessage;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserModeProgress;
use App\Models\UserStreak;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class TrainingSessionService
{
    public function __construct(
        private PracticeAIService $aiService,
    ) {}

    /**
     * Start a new training session
     */
    public function startSession(User $user, PracticeMode $mode): array
    {
        // Authorization check (covers: mode active, required_plan, can-train-mode gate)
        if ($user->cannot('start', $mode)) {
            throw new AuthorizationException('Cannot start training in this mode.');
        }

        // Check for existing active session
        $existingSession = $this->getActiveSession($user, $mode);
        if ($existingSession) {
            // Return existing session instead of creating new one
            return [
                'session' => $existingSession,
                'messages' => $this->getSessionHistory($existingSession),
                'resumed' => true,
            ];
        }

        // Get or create progress record
        $progress = $this->getOrCreateProgress($user, $mode);

        // Create new session
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'level_at_start' => $progress->current_level,
            'exchange_count' => 0,
            'started_at' => now(),
            'status' => TrainingSession::STATUS_ACTIVE,
        ]);

        // Get first AI response
        $aiResponse = $this->aiService->getFirstResponse(
            $mode,
            $progress->current_level,
            $user,
            $session
        );

        // Store AI message
        $this->storeMessage($session, 'assistant', json_encode($aiResponse), $aiResponse['type']);

        return [
            'session' => $session->fresh(),
            'card' => $aiResponse,
            'resumed' => false,
        ];
    }

    /**
     * Continue an existing session with user input
     */
    public function continueSession(TrainingSession $session, string $userInput): array
    {
        $user = $session->user;
        $mode = $session->practiceMode;

        // Authorization check (covers: ownership, session not ended, can-train-mode gate)
        if ($user->cannot('continue', $session)) {
            // Determine specific reason for better error handling
            if (! Gate::forUser($user)->allows('can-train')) {
                return [
                    'error' => 'limit_reached',
                    'message' => "You've reached your daily exchange limit.",
                ];
            }

            throw new AuthorizationException('Cannot continue this session.');
        }

        // Get progress record
        $progress = $this->getOrCreateProgress($user, $mode);

        // Get the previous card to know what drill the user was responding to
        $previousCard = $this->getLastAssistantCard($session);

        // Store user message
        $this->storeMessage($session, 'user', $userInput);

        // Dispatch async scoring job if this was a drill response
        $this->dispatchDrillScoring($session, $userInput, $previousCard);

        // Get AI response (message history built internally by PracticeAIService)
        $aiResponse = $this->aiService->getResponse(
            $mode,
            $progress->current_level,
            $session,
            $userInput,
            $user
        );

        // Store AI message
        $this->storeMessage($session, 'assistant', json_encode($aiResponse), $aiResponse['type']);

        // Record exchange and check for level up
        $levelUpCard = $this->recordExchange($session, $progress);

        // Update streak
        $this->updateStreak($user);

        // Build response
        $response = [
            'card' => $aiResponse,
            'session' => $session->fresh(),
            'progress' => [
                'current_level' => $progress->current_level,
                'exchanges_at_current_level' => $progress->exchanges_at_current_level,
                'exchanges_to_next_level' => $this->getExchangesToNextLevel($progress),
            ],
        ];

        // Include level up card if leveled up
        if ($levelUpCard) {
            $response['levelUp'] = $levelUpCard;
        }

        return $response;
    }

    /**
     * End a training session
     */
    public function endSession(TrainingSession $session): bool
    {
        $user = auth()->user();

        if ($user->cannot('end', $session)) {
            throw new AuthorizationException('Cannot end this session.');
        }

        $session->update([
            'ended_at' => now(),
            'status' => TrainingSession::STATUS_COMPLETED,
            'duration_seconds' => $session->started_at->diffInSeconds(now()),
        ]);

        // Dispatch session completed event for blind spot teaser emails
        SessionCompleted::dispatch($session);

        return true;
    }

    /**
     * Get active (non-ended) session for user in a mode
     */
    public function getActiveSession(User $user, PracticeMode $mode): ?TrainingSession
    {
        return TrainingSession::where('user_id', $user->id)
            ->where('practice_mode_id', $mode->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }

    /**
     * Get session message history for display
     */
    public function getSessionHistory(TrainingSession $session): Collection
    {
        return $session->messages()
            ->orderBy('created_at', 'asc')
            ->orderBy('sequence', 'asc')
            ->get()
            ->map(function (SessionMessage $message) {
                $data = [
                    'id' => $message->id,
                    'role' => $message->role,
                    'created_at' => $message->created_at,
                ];

                if ($message->role === 'assistant') {
                    $data['card'] = json_decode($message->content, true);
                    $data['type'] = $message->parsed_type ?? $message->getType();
                } else {
                    $data['content'] = $message->content;
                }

                return $data;
            });
    }

    /**
     * Get or create user's progress record for a mode
     */
    private function getOrCreateProgress(User $user, PracticeMode $mode): UserModeProgress
    {
        return UserModeProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'practice_mode_id' => $mode->id,
            ],
            [
                'current_level' => 1,
                'total_exchanges' => 0,
                'exchanges_at_current_level' => 0,
                'total_sessions' => 0,
                'total_time_seconds' => 0,
            ]
        );
    }

    /**
     * Increment exchange counts and check for level up
     */
    private function recordExchange(TrainingSession $session, UserModeProgress $progress): ?array
    {
        // Increment session count
        $session->increment('exchange_count');

        // Increment progress counts
        $progress->increment('total_exchanges');
        $progress->increment('exchanges_at_current_level');
        $progress->update(['last_trained_at' => now()]);

        // Increment daily usage
        $this->incrementDailyUsage($session->user);

        // Check for level up
        return $this->checkAndProcessLevelUp($session->user, $progress->fresh());
    }

    /**
     * Update daily usage count
     */
    private function incrementDailyUsage(User $user): void
    {
        DailyUsage::forUserToday($user)->increment('exchange_count');
    }

    /**
     * Update user streak
     */
    private function updateStreak(User $user): void
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity_date' => null,
            ]
        );

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Already trained today - no update needed
        if ($streak->last_activity_date?->toDateString() === $today) {
            return;
        }

        if ($streak->last_activity_date?->toDateString() === $yesterday) {
            // Streak continues
            $streak->current_streak++;
        } else {
            // Streak broken or first time
            $streak->current_streak = 1;
        }

        if ($streak->current_streak > $streak->longest_streak) {
            $streak->longest_streak = $streak->current_streak;
        }

        $streak->last_activity_date = $today;
        $streak->save();
    }

    /**
     * Check if user should level up and process it
     */
    private function checkAndProcessLevelUp(User $user, UserModeProgress $progress): ?array
    {
        $threshold = config("levels.thresholds.{$progress->current_level}");

        // At max level or threshold not reached
        if ($threshold === null || $progress->exchanges_at_current_level < $threshold) {
            return null;
        }

        // Check if user's plan allows level up
        if (Gate::forUser($user)->denies('can-level-up', $progress->current_level)) {
            // User hit plan's level cap - don't level up
            return [
                'type' => 'level_cap',
                'current_level' => $progress->current_level,
                'message' => 'Upgrade your plan to unlock higher levels.',
            ];
        }

        // Process level up
        $newLevel = $progress->current_level + 1;

        $progress->update([
            'current_level' => $newLevel,
            'exchanges_at_current_level' => 0,
        ]);

        return [
            'type' => 'level_up',
            'new_level' => $newLevel,
            'message' => $this->getLevelUpMessage($newLevel),
        ];
    }

    /**
     * Get level up message for a level
     */
    private function getLevelUpMessage(int $newLevel): string
    {
        return config("levels.messages.{$newLevel}", "You've reached Level {$newLevel}.");
    }

    /**
     * Get exchanges needed to reach next level
     */
    private function getExchangesToNextLevel(UserModeProgress $progress): ?int
    {
        $threshold = config("levels.thresholds.{$progress->current_level}");

        if ($threshold === null) {
            return null; // At max level
        }

        return max(0, $threshold - $progress->exchanges_at_current_level);
    }

    /**
     * Store a message in session history
     */
    private function storeMessage(
        TrainingSession $session,
        string $role,
        string $content,
        ?string $parsedType = null
    ): SessionMessage {
        // Get next sequence number
        $nextSequence = $session->messages()->max('sequence') + 1;

        return SessionMessage::create([
            'training_session_id' => $session->id,
            'role' => $role,
            'content' => $content,
            'parsed_type' => $parsedType,
            'sequence' => $nextSequence,
            'created_at' => now(),
        ]);
    }

    /**
     * Get the last assistant card from session history
     */
    private function getLastAssistantCard(TrainingSession $session): ?array
    {
        $lastAssistantMessage = $session->messages()
            ->where('role', 'assistant')
            ->orderBy('sequence', 'desc')
            ->first();

        if (! $lastAssistantMessage) {
            return null;
        }

        return json_decode($lastAssistantMessage->content, true);
    }

    /**
     * Dispatch drill scoring job if applicable
     */
    private function dispatchDrillScoring(
        TrainingSession $session,
        string $userInput,
        ?array $previousCard
    ): void {
        // Skip if no previous card or no drill_phase
        if (! $previousCard || empty($previousCard['drill_phase'])) {
            return;
        }

        $drillPhase = $previousCard['drill_phase'];

        // Map drill_phase to drill_type using config
        $drillType = config("drill_types.phase_mapping.{$drillPhase}");

        // Skip non-scorable phases (like reflections)
        if ($drillType === null) {
            return;
        }

        // Skip if card type is not a prompt (we only score prompt responses)
        if (! in_array($previousCard['type'] ?? '', ['prompt', 'scenario'])) {
            return;
        }

        ScoreDrillResponse::dispatch(
            $session->user_id,
            $session->id,
            $session->practice_mode_id,
            $drillType,
            $drillPhase,
            $userInput,
            $previousCard['is_iteration'] ?? false
        );
    }
}
