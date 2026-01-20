<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\DailyUsage;
use App\Models\TrainingSession;
use App\Models\UserModeProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsageController extends Controller
{
    /**
     * Show the user's usage statistics page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $planConfig = $user->planConfig();

        // Today's usage (exchanges from DailyUsage, sessions from TrainingSession)
        $todayUsage = DailyUsage::forUserToday($user);
        $todaySessions = TrainingSession::where('user_id', $user->id)
            ->whereDate('started_at', today())
            ->count();

        // This week's usage (last 7 days)
        $weekStart = now()->subDays(6)->startOfDay();
        $weeklyExchanges = DailyUsage::where('user_id', $user->id)
            ->where('date', '>=', $weekStart)
            ->sum('exchange_count');
        $weeklySessions = TrainingSession::where('user_id', $user->id)
            ->where('started_at', '>=', $weekStart)
            ->count();

        // All-time stats from TrainingSession for accuracy
        $allTimeStats = TrainingSession::where('user_id', $user->id)
            ->selectRaw('COUNT(*) as sessions, SUM(exchange_count) as exchanges')
            ->first();

        // Progress by practice mode
        $modeProgress = UserModeProgress::where('user_id', $user->id)
            ->with('practiceMode')
            ->orderByDesc('last_trained_at')
            ->get()
            ->filter(fn ($progress) => $progress->practiceMode !== null)
            ->map(fn ($progress) => [
                'mode' => $progress->practiceMode->title,
                'slug' => $progress->practiceMode->slug,
                'level' => $progress->current_level,
                'exchanges' => $progress->total_exchanges,
                'sessions' => $progress->total_sessions,
                'last_trained_at' => $progress->last_trained_at?->diffForHumans(),
            ])
            ->values();

        return Inertia::render('settings/usage', [
            'usage' => [
                'today' => [
                    'exchanges' => $todayUsage->exchange_count,
                    'sessions' => $todaySessions,
                ],
                'weekly' => [
                    'exchanges' => $weeklyExchanges,
                    'sessions' => $weeklySessions,
                ],
                'allTime' => [
                    'exchanges' => (int) ($allTimeStats->exchanges ?? 0),
                    'sessions' => (int) ($allTimeStats->sessions ?? 0),
                ],
                'modeProgress' => $modeProgress,
            ],
            'limits' => [
                'daily_exchanges' => $planConfig['daily_exchanges'],
                'max_level' => $planConfig['max_level'],
            ],
            'streak' => $user->streak?->current_streak ?? 0,
        ]);
    }
}
