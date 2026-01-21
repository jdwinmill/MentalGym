<?php

namespace App\Http\Controllers;

use App\Services\BlindSpotService;
use Inertia\Inertia;
use Inertia\Response;

class BlindSpotDashboardController extends Controller
{
    public function __construct(
        private BlindSpotService $service
    ) {}

    public function index(): Response
    {
        $user = auth()->user();
        $analysis = $this->service->getAnalysis($user);

        // Get historical data for charts (Pro only)
        $history = null;
        if ($analysis->isUnlocked) {
            $history = $this->service->getHistoricalTrends($user, weeks: 8);
        }

        return Inertia::render('blind-spots/index', [
            'analysis' => $analysis->toArray(),
            'history' => $history,
            'isPro' => $user->hasPaidPlan(),
        ]);
    }
}
