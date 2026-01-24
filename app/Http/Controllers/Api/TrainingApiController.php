<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Services\TrainingSessionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingApiController extends Controller
{
    public function __construct(
        private TrainingSessionService $trainingService,
    ) {}

    /**
     * Start or resume a training session.
     */
    public function start(Request $request, PracticeMode $practiceMode): JsonResponse
    {
        try {
            $result = $this->trainingService->startSession(
                $request->user(),
                $practiceMode
            );

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $result['session']->id,
                    'exchange_count' => $result['session']->exchange_count,
                    'started_at' => $result['session']->started_at,
                ],
                'messages' => $result['messages'] ?? [],
                'card' => $result['card'] ?? null,
                'resumed' => $result['resumed'],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Continue a session with user input.
     */
    public function continue(Request $request, TrainingSession $session): JsonResponse
    {
        $request->validate([
            'input' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $result = $this->trainingService->continueSession(
                $session,
                $request->input('input')
            );

            // Check for limit reached error
            if (isset($result['error']) && $result['error'] === 'limit_reached') {
                return response()->json([
                    'success' => false,
                    'error' => 'limit_reached',
                    'message' => $result['message'],
                ], 429);
            }

            return response()->json([
                'success' => true,
                'card' => $result['card'],
                'session' => [
                    'id' => $result['session']->id,
                    'exchange_count' => $result['session']->exchange_count,
                ],
                'progress' => $result['progress'],
                'levelUp' => $result['levelUp'] ?? null,
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * End a training session.
     */
    public function end(Request $request, TrainingSession $session): JsonResponse
    {
        try {
            $this->trainingService->endSession($session);

            return response()->json([
                'success' => true,
                'message' => 'Session ended successfully.',
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    // =========================================================================
    // NEW DRILL-BASED ENDPOINTS (Phase 2 Refactor)
    // =========================================================================

    /**
     * Start a new drill-based session.
     * POST /api/training/v2/start/{mode_slug}
     */
    public function startDrill(Request $request, string $modeSlug): JsonResponse
    {
        $mode = PracticeMode::where('slug', $modeSlug)->firstOrFail();

        try {
            $result = $this->trainingService->startDrillSession(
                $request->user(),
                $mode
            );

            // Check for limit reached error
            if (isset($result['error']) && $result['error'] === 'limit_reached') {
                return response()->json([
                    'success' => false,
                    'error' => 'limit_reached',
                    'plan' => $result['plan'],
                ], 429);
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $result['session']->id,
                    'drill_index' => $result['session']->drill_index,
                    'phase' => $result['session']->phase,
                ],
                'drill' => $result['drill'] ? [
                    'id' => $result['drill']->id,
                    'name' => $result['drill']->name,
                    'timer_seconds' => $result['drill']->timer_seconds,
                    'input_type' => $result['drill']->input_type,
                ] : null,
                'card' => $result['card'],
                'progress' => $result['progress'],
                'resumed' => $result['resumed'] ?? false,
                'primary_insight' => $result['primary_insight'] ?? null,
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => $e->getMessage(),
            ], 403);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current drill session state (for resume).
     * GET /api/training/v2/session/{session}
     */
    public function showDrill(TrainingSession $session): JsonResponse
    {
        // Check ownership
        if ($session->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'This session does not belong to you.',
            ], 403);
        }

        $result = $this->trainingService->resumeDrillSession($session);

        return response()->json([
            'success' => true,
            'session' => [
                'id' => $result['session']->id,
                'drill_index' => $result['session']->drill_index,
                'phase' => $result['session']->phase,
            ],
            'drill' => $result['drill'] ? [
                'id' => $result['drill']->id,
                'name' => $result['drill']->name,
                'timer_seconds' => $result['drill']->timer_seconds,
                'input_type' => $result['drill']->input_type,
            ] : null,
            'card' => $result['card'],
            'progress' => $result['progress'],
        ]);
    }

    /**
     * Submit response to current drill.
     * POST /api/training/v2/respond/{session}
     */
    public function respondDrill(Request $request, TrainingSession $session): JsonResponse
    {
        // Check ownership
        if ($session->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'This session does not belong to you.',
            ], 403);
        }

        $request->validate([
            'response' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $result = $this->trainingService->submitDrillResponse(
                $session,
                $request->input('response'),
                $request->user()
            );

            // Check for limit reached error
            if (isset($result['error']) && $result['error'] === 'limit_reached') {
                return response()->json([
                    'success' => false,
                    'error' => 'limit_reached',
                    'plan' => $result['plan'],
                ], 429);
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $result['session']->id,
                    'drill_index' => $result['session']->drill_index,
                    'phase' => $result['session']->phase,
                ],
                'card' => $result['card'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Continue to next drill.
     * POST /api/training/v2/continue/{session}
     */
    public function continueDrill(Request $request, TrainingSession $session): JsonResponse
    {
        // Check ownership
        if ($session->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'This session does not belong to you.',
            ], 403);
        }

        try {
            $result = $this->trainingService->continueToNextDrill(
                $session,
                $request->user()
            );

            // Check for limit reached error
            if (isset($result['error']) && $result['error'] === 'limit_reached') {
                return response()->json([
                    'success' => false,
                    'error' => 'limit_reached',
                    'plan' => $result['plan'],
                ], 429);
            }

            // Check if session is complete
            if ($result['complete'] ?? false) {
                return response()->json([
                    'success' => true,
                    'complete' => true,
                    'session' => [
                        'id' => $result['session']->id,
                        'drill_index' => $result['session']->drill_index,
                        'phase' => $result['session']->phase,
                    ],
                    'stats' => $result['stats'],
                ]);
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $result['session']->id,
                    'drill_index' => $result['session']->drill_index,
                    'phase' => $result['session']->phase,
                ],
                'drill' => $result['drill'] ? [
                    'id' => $result['drill']->id,
                    'name' => $result['drill']->name,
                    'timer_seconds' => $result['drill']->timer_seconds,
                    'input_type' => $result['drill']->input_type,
                ] : null,
                'card' => $result['card'],
                'progress' => $result['progress'],
                'primary_insight' => $result['primary_insight'] ?? null,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
