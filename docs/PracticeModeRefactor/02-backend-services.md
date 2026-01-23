# Phase 2: Backend Services

## Tasks

1. Add `generateScenario()` method to PracticeAIService
2. Add `evaluateResponse()` method to PracticeAIService
3. Update `TrainingSessionService` to use new drill-based flow
4. Add new API endpoints
5. Keep old methods temporarily for backwards compat

---

## PracticeAIService

### Two Focused Methods

```php
class PracticeAIService
{
    /**
     * Generate a scenario for a specific drill
     * Uses: Global + Mode + Drill.scenario_instruction_set
     */
    public function generateScenario(Drill $drill, User $user): array
    {
        $systemPrompt = $this->buildSystemPrompt(
            $drill->practiceMode,
            $drill->scenario_instruction_set
        );

        $userPrompt = $this->buildGeneratePrompt($drill, $user);

        $response = retry(3, function () use ($systemPrompt, $userPrompt) {
            return $this->callClaude($systemPrompt, $userPrompt);
        }, 1000);

        return $this->parseScenarioResponse($response);
    }

    /**
     * Evaluate user's response and provide feedback
     * Uses: Global + Mode + Drill.evaluation_instruction_set
     */
    public function evaluateResponse(
        Drill $drill,
        string $scenario,
        string $task,
        string $userResponse,
        User $user
    ): array {
        $systemPrompt = $this->buildSystemPrompt(
            $drill->practiceMode,
            $drill->evaluation_instruction_set
        );

        $userPrompt = $this->buildEvaluatePrompt(
            $scenario, $task, $userResponse, $user
        );

        $response = retry(3, function () use ($systemPrompt, $userPrompt) {
            return $this->callClaude($systemPrompt, $userPrompt);
        }, 1000);

        return $this->parseFeedbackResponse($response);
    }

    /**
     * Assemble system prompt from hierarchy: Global + Mode + Drill instruction
     */
    private function buildSystemPrompt(PracticeMode $mode, string $drillInstruction): string
    {
        $global = config('mentalgym.main_instruction_set');

        return <<<PROMPT
        {$global}

        ---

        MODE: {$mode->name}
        {$mode->instruction_set}

        ---

        DRILL INSTRUCTIONS:
        {$drillInstruction}
        PROMPT;
    }

    private function buildGeneratePrompt(Drill $drill, User $user): string
    {
        $level = $user->modeProgress($drill->practice_mode_id)?->current_level ?? 1;

        return <<<PROMPT
        User level: {$level}

        Generate a scenario and task for this drill.

        Respond with JSON:
        {
            "scenario": "...",
            "task": "..."
        }
        PROMPT;
    }

    private function buildEvaluatePrompt(
        string $scenario,
        string $task,
        string $userResponse,
        User $user
    ): string {
        return <<<PROMPT
        SCENARIO:
        "{$scenario}"

        TASK:
        "{$task}"

        USER RESPONSE:
        "{$userResponse}"

        Evaluate this response.

        Respond with JSON:
        {
            "feedback": "...",
            "score": 0-100
        }
        PROMPT;
    }

    private function parseScenarioResponse(string $response): array
    {
        $data = json_decode($response, true);

        return [
            'scenario' => $data['scenario'] ?? '',
            'task' => $data['task'] ?? '',
            'options' => $data['options'] ?? null,
            'correct_option' => $data['correct_option'] ?? null,
        ];
    }

    private function parseFeedbackResponse(string $response): array
    {
        $data = json_decode($response, true);

        return [
            'feedback' => $data['feedback'] ?? '',
            'score' => (int) ($data['score'] ?? 0),
        ];
    }
}
```

---

## TrainingSessionService

### New Drill-Based Flow

```php
class TrainingSessionService
{
    public function __construct(
        private PracticeAIService $aiService
    ) {}

    /**
     * Start a new session
     */
    public function startSession(PracticeMode $mode, User $user): array
    {
        // Check for existing active session
        $existingSession = TrainingSession::where('user_id', $user->id)
            ->where('practice_mode_id', $mode->id)
            ->where('status', 'active')
            ->first();

        if ($existingSession) {
            return $this->resumeSession($existingSession);
        }

        // Create new session
        $session = TrainingSession::create([
            'user_id' => $user->id,
            'practice_mode_id' => $mode->id,
            'status' => 'active',
            'drill_index' => 0,
            'phase' => 'scenario',
            'started_at' => now(),
        ]);

        // Get first drill and generate scenario
        $drill = $mode->drills()->first();
        $scenarioData = $this->aiService->generateScenario($drill, $user);

        // Store scenario for later evaluation
        $session->update([
            'current_scenario' => $scenarioData['scenario'],
            'current_task' => $scenarioData['task'],
            'current_options' => $scenarioData['options'],
            'current_correct_option' => $scenarioData['correct_option'],
            'phase' => 'responding',
        ]);

        return [
            'session' => $session->fresh(),
            'drill' => $drill,
            'card' => [
                'type' => 'scenario',
                'content' => $scenarioData['scenario'],
                'task' => $scenarioData['task'],
                'options' => $scenarioData['options'],
            ],
            'progress' => [
                'current' => 1,
                'total' => $mode->drills()->count(),
            ],
        ];
    }

    /**
     * Resume an existing session
     */
    public function resumeSession(TrainingSession $session): array
    {
        $drill = $session->practiceMode->drills()
            ->where('position', $session->drill_index)
            ->first();

        $card = match ($session->phase) {
            'responding' => [
                'type' => 'scenario',
                'content' => $session->current_scenario,
                'task' => $session->current_task,
                'options' => $session->current_options,
            ],
            'feedback' => [
                'type' => 'feedback',
                'content' => $session->current_feedback ?? '',
                'score' => $session->current_score ?? 0,
            ],
            default => null,
        };

        return [
            'session' => $session,
            'drill' => $drill,
            'card' => $card,
            'progress' => [
                'current' => $session->drill_index + 1,
                'total' => $session->practiceMode->drills()->count(),
            ],
        ];
    }

    /**
     * Submit response to current drill
     */
    public function submitResponse(TrainingSession $session, string $response, User $user): array
    {
        $drill = $session->practiceMode->drills()
            ->where('position', $session->drill_index)
            ->first();

        // Evaluate response
        $feedbackData = $this->aiService->evaluateResponse(
            $drill,
            $session->current_scenario,
            $session->current_task,
            $response,
            $user
        );

        // Append score to drill_scores
        $scores = $session->drill_scores ?? [];
        $scores[] = [
            'drill_id' => $drill->id,
            'drill_name' => $drill->name,
            'score' => $feedbackData['score'],
        ];

        $session->update([
            'phase' => 'feedback',
            'drill_scores' => $scores,
        ]);

        return [
            'session' => $session->fresh(),
            'card' => [
                'type' => 'feedback',
                'content' => $feedbackData['feedback'],
                'score' => $feedbackData['score'],
            ],
        ];
    }

    /**
     * Continue to next drill
     */
    public function continueToNextDrill(TrainingSession $session, User $user): array
    {
        $nextIndex = $session->drill_index + 1;
        $totalDrills = $session->practiceMode->drills()->count();

        // Check if session is complete
        if ($nextIndex >= $totalDrills) {
            return $this->completeSession($session, $user);
        }

        // Get next drill
        $drill = $session->practiceMode->drills()
            ->where('position', $nextIndex)
            ->first();

        // Generate next scenario
        $scenarioData = $this->aiService->generateScenario($drill, $user);

        $session->update([
            'drill_index' => $nextIndex,
            'phase' => 'responding',
            'current_scenario' => $scenarioData['scenario'],
            'current_task' => $scenarioData['task'],
            'current_options' => $scenarioData['options'],
            'current_correct_option' => $scenarioData['correct_option'],
        ]);

        return [
            'session' => $session->fresh(),
            'drill' => $drill,
            'card' => [
                'type' => 'scenario',
                'content' => $scenarioData['scenario'],
                'task' => $scenarioData['task'],
                'options' => $scenarioData['options'],
            ],
            'progress' => [
                'current' => $nextIndex + 1,
                'total' => $totalDrills,
            ],
        ];
    }

    /**
     * Complete the session
     */
    public function completeSession(TrainingSession $session, User $user): array
    {
        $session->update([
            'phase' => 'complete',
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // Dispatch completion event (handled in Phase 3)
        event(new SessionCompleted(
            user: $user,
            session: $session,
            drillsCompleted: $session->drill_index + 1,
            totalDurationSeconds: $session->started_at->diffInSeconds(now()),
            scores: $session->drill_scores ?? []
        ));

        return [
            'session' => $session->fresh(),
            'complete' => true,
            'stats' => [
                'drills_completed' => $session->drill_index + 1,
                'total_duration_seconds' => $session->started_at->diffInSeconds(now()),
            ],
        ];
    }
}
```

---

## API Endpoints

### Controller

```php
// app/Http/Controllers/Api/TrainingApiController.php

class TrainingApiController extends Controller
{
    public function __construct(
        private TrainingSessionService $sessionService
    ) {}

    /**
     * Start a new session
     * POST /api/training/start/{mode_slug}
     */
    public function start(string $modeSlug): JsonResponse
    {
        $mode = PracticeMode::where('slug', $modeSlug)->firstOrFail();
        $user = auth()->user();

        $result = $this->sessionService->startSession($mode, $user);

        return response()->json($result);
    }

    /**
     * Get current session state (for resume)
     * GET /api/training/session/{session}
     */
    public function show(TrainingSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $result = $this->sessionService->resumeSession($session);

        return response()->json($result);
    }

    /**
     * Submit response to current drill
     * POST /api/training/respond/{session}
     */
    public function respond(TrainingSession $session, Request $request): JsonResponse
    {
        $this->authorize('update', $session);

        $validated = $request->validate([
            'response' => 'required|string|max:5000',
        ]);

        $result = $this->sessionService->submitResponse(
            $session,
            $validated['response'],
            auth()->user()
        );

        return response()->json($result);
    }

    /**
     * Continue to next drill
     * POST /api/training/continue/{session}
     */
    public function continue(TrainingSession $session): JsonResponse
    {
        $this->authorize('update', $session);

        $result = $this->sessionService->continueToNextDrill(
            $session,
            auth()->user()
        );

        return response()->json($result);
    }
}
```

### Routes

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('training')->group(function () {
        Route::post('/start/{mode_slug}', [TrainingApiController::class, 'start']);
        Route::get('/session/{session}', [TrainingApiController::class, 'show']);
        Route::post('/respond/{session}', [TrainingApiController::class, 'respond']);
        Route::post('/continue/{session}', [TrainingApiController::class, 'continue']);
    });
});
```

---

## Multiple Choice Handling

For drills with `input_type: 'multiple_choice'`:

### Scenario Generation

```php
// In buildGeneratePrompt for MC drills
if ($drill->input_type === 'multiple_choice') {
    return <<<PROMPT
    User level: {$level}

    Generate a scenario with multiple choice options.

    Respond with JSON:
    {
        "scenario": "...",
        "task": "...",
        "options": ["Option A", "Option B", "Option C", "Option D"],
        "correct_option": 0
    }
    PROMPT;
}
```

### Response Evaluation

```php
// MC responses are just the option index
$userResponse = '0';  // User selected first option

// Evaluation prompt includes correct answer
$evaluatePrompt = <<<PROMPT
User selected option index: {$userResponse}
Correct option index: {$session->current_correct_option}

If correct: Score 100, explain why correct.
If incorrect: Score 0, explain correct answer.
PROMPT;
```

---

## Error Handling

```php
public function generateScenario(Drill $drill, User $user): array
{
    try {
        return retry(3, function () use ($drill, $user) {
            return $this->doGenerateScenario($drill, $user);
        }, 1000);
    } catch (\Exception $e) {
        Log::error('Failed to generate scenario', [
            'drill_id' => $drill->id,
            'user_id' => $user->id,
            'error' => $e->getMessage(),
        ]);

        throw new \RuntimeException('Failed to generate scenario. Please try again.');
    }
}
```

---

## Files to Modify

- `app/Services/PracticeAIService.php` - new focused methods
- `app/Services/TrainingSessionService.php` - drill-based flow
- `app/Http/Controllers/Api/TrainingApiController.php` - new endpoints
- `routes/api.php` - new routes
