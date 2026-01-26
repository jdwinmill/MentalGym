<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        // Get plan configurations from config file
        $plansConfig = config('plans');

        // Count users per plan
        $userCounts = User::selectRaw('COALESCE(plan, "free") as plan_tier, COUNT(*) as count')
            ->groupBy('plan_tier')
            ->pluck('count', 'plan_tier')
            ->toArray();

        // Build plans array with user counts
        $plans = collect($plansConfig)->map(function ($config, $key) use ($userCounts) {
            return [
                'key' => $key,
                'name' => ucfirst($key),
                'monthly_drills' => $config['monthly_drills'] ?? null,
                'daily_exchanges' => $config['daily_exchanges'] ?? null,
                'max_level' => $config['max_level'],
                'user_count' => $userCounts[$key] ?? 0,
            ];
        })->values()->toArray();

        return Inertia::render('admin/plans/index', [
            'plans' => $plans,
        ]);
    }
}
