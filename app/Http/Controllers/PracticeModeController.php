<?php

namespace App\Http\Controllers;

use App\Models\PracticeMode;
use App\Services\TrainingSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeModeController extends Controller
{
    public function __construct(
        private TrainingSessionService $trainingService,
    ) {}

    /**
     * Display the practice modes index page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Check if user has hit their daily limit (separate from plan access)
        $canTrain = \Illuminate\Support\Facades\Gate::allows('can-train');

        $modes = PracticeMode::query()
            ->active()
            ->ordered()
            ->with('tags')
            ->get()
            ->map(function (PracticeMode $mode) use ($user, $canTrain) {
                // Get user's progress for this mode
                $progress = $mode->userProgress()
                    ->where('user_id', $user->id)
                    ->first();

                // Check for active session
                $activeSession = $this->trainingService->getActiveSession($user, $mode);

                // Check if user's plan allows this mode (separate from daily limit)
                $meetsPlanRequirement = $this->meetsPlanRequirement($user, $mode);

                return [
                    'id' => $mode->id,
                    'slug' => $mode->slug,
                    'name' => $mode->name,
                    'tagline' => $mode->tagline,
                    'icon' => $mode->icon,
                    'required_plan' => $mode->required_plan,
                    'tags' => $mode->tags->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'category' => $tag->category,
                    ]),
                    'progress' => $progress ? [
                        'current_level' => $progress->current_level,
                        'total_exchanges' => $progress->total_exchanges,
                        'total_sessions' => $progress->total_sessions,
                    ] : null,
                    'has_active_session' => $activeSession !== null,
                    'can_access' => $meetsPlanRequirement, // Plan-based access only
                    'can_train' => $canTrain, // Daily limit check
                ];
            });

        return Inertia::render('practice-modes/index', [
            'modes' => $modes,
        ]);
    }

    /**
     * Check if user's plan meets the mode's requirement.
     */
    private function meetsPlanRequirement($user, PracticeMode $mode): bool
    {
        if ($mode->required_plan === null) {
            return true;
        }

        $planHierarchy = ['free' => 0, 'pro' => 1, 'unlimited' => 2];
        $userLevel = $planHierarchy[$user->plan] ?? 0;
        $requiredLevel = $planHierarchy[$mode->required_plan] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Display the training page for a specific mode.
     */
    public function train(Request $request, PracticeMode $practiceMode): Response
    {
        $user = $request->user();

        // Check authorization
        $this->authorize('start', $practiceMode);

        // Get user's progress for this mode
        $progress = $practiceMode->userProgress()
            ->where('user_id', $user->id)
            ->first();

        // Check for active session
        $activeSession = $this->trainingService->getActiveSession($user, $practiceMode);

        return Inertia::render('practice-modes/[slug]/train', [
            'mode' => [
                'id' => $practiceMode->id,
                'slug' => $practiceMode->slug,
                'name' => $practiceMode->name,
                'tagline' => $practiceMode->tagline,
                'icon' => $practiceMode->icon,
                'config' => [
                    'input_character_limit' => $practiceMode->config['input_character_limit'],
                    'reflection_character_limit' => $practiceMode->config['reflection_character_limit'],
                ],
            ],
            'progress' => $progress ? [
                'current_level' => $progress->current_level,
                'total_exchanges' => $progress->total_exchanges,
                'exchanges_at_current_level' => $progress->exchanges_at_current_level,
            ] : [
                'current_level' => 1,
                'total_exchanges' => 0,
                'exchanges_at_current_level' => 0,
            ],
            'has_active_session' => $activeSession !== null,
        ]);
    }
}
