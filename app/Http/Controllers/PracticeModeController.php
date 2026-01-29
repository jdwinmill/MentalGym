<?php

namespace App\Http\Controllers;

use App\Models\BlindSpot;
use App\Models\PracticeMode;
use App\Models\SkillDimension;
use App\Models\TrainingSession;
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
     * Display the detail page for a specific practice mode.
     */
    public function show(Request $request, PracticeMode $practiceMode): Response
    {
        $user = $request->user();

        // Get drills for this mode
        $drills = $practiceMode->drills()
            ->orderBy('position')
            ->get()
            ->map(fn ($drill) => [
                'id' => $drill->id,
                'name' => $drill->name,
                'timer_seconds' => $drill->timer_seconds,
                'input_type' => $drill->input_type,
                'position' => $drill->position,
            ]);

        // Calculate estimated minutes from drill timers (default 60s for untimed)
        $totalSeconds = $practiceMode->drills->sum(function ($drill) {
            return $drill->timer_seconds ?? 60;
        });
        $estimatedMinutes = (int) ceil($totalSeconds / 60);

        // Get user's progress for this mode
        $progress = $practiceMode->userProgress()
            ->where('user_id', $user->id)
            ->first();

        // Get completed drill count (max drill_index from completed sessions)
        $completedDrillCount = TrainingSession::where('user_id', $user->id)
            ->where('practice_mode_id', $practiceMode->id)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->max('drill_index');
        $completedDrillCount = $completedDrillCount !== null ? $completedDrillCount + 1 : 0;

        // Get drill IDs for this mode
        $drillIds = $practiceMode->drills->pluck('id')->toArray();

        // Get all unique dimensions for this mode's drills
        $modeDimensionKeys = $practiceMode->drills
            ->pluck('dimensions')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // Get dimension labels
        $modeDimensions = SkillDimension::whereIn('key', $modeDimensionKeys)
            ->get()
            ->map(fn ($dim) => [
                'key' => $dim->key,
                'label' => $dim->label,
            ])
            ->values()
            ->toArray();

        // Get user patterns (BlindSpot data filtered by mode's drill IDs)
        $userPatterns = null;
        $hasPatternHistory = false;

        if (count($drillIds) > 0) {
            $blindSpots = BlindSpot::where('user_id', $user->id)
                ->whereIn('drill_id', $drillIds)
                ->get();

            $hasPatternHistory = $blindSpots->isNotEmpty();

            if ($hasPatternHistory) {
                // Group by dimension_key with avg scores
                $groupedPatterns = $blindSpots->groupBy('dimension_key')->map(function ($spots, $dimensionKey) {
                    $avgScore = round($spots->avg('score'), 1);
                    $count = $spots->count();

                    // Get dimension label
                    $dimension = SkillDimension::find($dimensionKey);
                    $label = $dimension?->label ?? $dimensionKey;

                    // Categorize based on score
                    $category = match (true) {
                        $avgScore >= 7 => 'strength',
                        $avgScore >= 4 => 'tendency',
                        default => 'improve',
                    };

                    return [
                        'dimension_key' => $dimensionKey,
                        'label' => $label,
                        'avg_score' => $avgScore,
                        'count' => $count,
                        'category' => $category,
                    ];
                })->values()->toArray();

                if (count($groupedPatterns) > 0) {
                    $userPatterns = [
                        'patterns' => $groupedPatterns,
                    ];
                }
            }
        }

        // Check for active session
        $hasActiveSession = $this->trainingService->getActiveSession($user, $practiceMode) !== null;

        // Check if user's plan allows this mode
        $canAccess = $this->meetsPlanRequirement($user, $practiceMode);

        return Inertia::render('practice/[slug]/show', [
            'mode' => [
                'id' => $practiceMode->id,
                'slug' => $practiceMode->slug,
                'name' => $practiceMode->name,
                'tagline' => $practiceMode->tagline,
                'description' => $practiceMode->description,
                'sample_scenario' => $practiceMode->sample_scenario,
                'icon' => $practiceMode->icon,
                'required_plan' => $practiceMode->required_plan,
            ],
            'drills' => $drills,
            'estimatedMinutes' => $estimatedMinutes,
            'progress' => $progress ? [
                'current_level' => $progress->current_level,
                'total_drills_completed' => $progress->total_drills_completed,
                'total_sessions' => $progress->total_sessions,
                'last_session_at' => $progress->last_session_at?->toISOString(),
            ] : null,
            'completedDrillCount' => $completedDrillCount,
            'userPatterns' => $userPatterns,
            'modeDimensions' => $modeDimensions,
            'hasPatternHistory' => $hasPatternHistory,
            'userPlan' => $user->plan,
            'isFirstTime' => $progress === null,
            'hasActiveSession' => $hasActiveSession,
            'canAccess' => $canAccess,
        ]);
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
