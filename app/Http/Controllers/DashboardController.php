<?php

namespace App\Http\Controllers;

use App\Models\PracticeMode;
use App\Services\TrainingSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private TrainingSessionService $trainingService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $modes = PracticeMode::query()
            ->active()
            ->ordered()
            ->with('tags')
            ->get()
            ->map(function (PracticeMode $mode) use ($user) {
                $progress = $mode->userProgress()
                    ->where('user_id', $user->id)
                    ->first();

                $activeSession = $this->trainingService->getActiveSession($user, $mode);

                $canAccess = $user->can('start', $mode);

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
                        'color' => $tag->color,
                    ]),
                    'progress' => $progress ? [
                        'current_level' => $progress->current_level,
                        'total_exchanges' => $progress->total_exchanges,
                        'total_sessions' => $progress->total_sessions,
                    ] : null,
                    'has_active_session' => $activeSession !== null,
                    'can_access' => $canAccess,
                ];
            });

        return Inertia::render('dashboard', [
            'modes' => $modes,
            'hasAccess' => $user->hasAccess(),
            'subscriptionStatus' => $user->getSubscriptionStatus(),
        ]);
    }
}
