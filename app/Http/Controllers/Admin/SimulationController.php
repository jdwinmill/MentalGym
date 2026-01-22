<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PracticeMode;
use App\Services\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function __construct(
        private SimulationService $simulationService
    ) {}

    public function simulate(Request $request, PracticeMode $practiceMode): JsonResponse
    {
        $this->authorize('update', $practiceMode);

        $validated = $request->validate([
            'interaction_count' => ['required', 'integer', 'min:5', 'max:25'],
            'user_type' => ['required', 'string', 'in:cooperative,terse,verbose,confused,adversarial'],
        ]);

        try {
            $result = $this->simulationService->runSimulation(
                $practiceMode,
                $validated['interaction_count'],
                $validated['user_type']
            );

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Simulation failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
