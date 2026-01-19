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
}
