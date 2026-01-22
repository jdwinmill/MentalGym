<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BlindSpotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlindSpotController extends Controller
{
    public function __construct(
        private BlindSpotService $blindSpotService
    ) {}

    /**
     * Get the gated blind spot analysis for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $analysis = $this->blindSpotService->getAnalysis($request->user());

        return response()->json([
            'success' => true,
            'analysis' => $analysis->toArray(),
        ]);
    }

    /**
     * Get lightweight teaser data for free users.
     */
    public function teaser(Request $request): JsonResponse
    {
        $user = $request->user();
        $showTeaser = $this->blindSpotService->shouldShowTeaser($user);

        if (! $showTeaser) {
            return response()->json([
                'success' => true,
                'showTeaser' => false,
                'teaser' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'showTeaser' => true,
            'teaser' => $this->blindSpotService->getTeaserData($user),
        ]);
    }

    /**
     * Get quick status check for blind spots feature.
     */
    public function status(Request $request): JsonResponse
    {
        $status = $this->blindSpotService->getStatus($request->user());

        return response()->json([
            'success' => true,
            'status' => $status,
        ]);
    }
}
