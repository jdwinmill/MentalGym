<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ApiMetricsController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        $today = now()->startOfDay();
        $weekAgo = now()->subDays(7)->startOfDay();
        $monthAgo = now()->subDays(30)->startOfDay();

        // Totals
        $todayStats = $this->getStats($today);
        $weekStats = $this->getStats($weekAgo);
        $monthStats = $this->getStats($monthAgo);

        // By mode (last 30 days)
        $byMode = ApiLog::query()
            ->where('created_at', '>=', $monthAgo)
            ->whereNotNull('practice_mode_id')
            ->selectRaw('practice_mode_id,
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cache_read_input_tokens) as total_cache_read_tokens,
                AVG(response_time_ms) as avg_response_time,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as error_count')
            ->groupBy('practice_mode_id')
            ->orderByDesc('request_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->load('practiceMode:id,name,slug');
                return [
                    'practice_mode_id' => $item->practice_mode_id,
                    'mode_name' => $item->practiceMode?->name ?? 'Unknown',
                    'mode_slug' => $item->practiceMode?->slug ?? 'unknown',
                    'request_count' => $item->request_count,
                    'total_input_tokens' => $item->total_input_tokens ?? 0,
                    'total_output_tokens' => $item->total_output_tokens ?? 0,
                    'total_cache_read_tokens' => $item->total_cache_read_tokens ?? 0,
                    'avg_response_time' => round($item->avg_response_time ?? 0),
                    'error_count' => $item->error_count ?? 0,
                ];
            });

        // Recent errors
        $recentErrors = ApiLog::query()
            ->where('success', false)
            ->with(['practiceMode:id,name', 'user:id,name,email'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'created_at' => $log->created_at->toDateTimeString(),
                'mode_name' => $log->practiceMode?->name ?? 'N/A',
                'user_name' => $log->user?->name ?? 'Anonymous',
                'user_email' => $log->user?->email ?? '',
                'error_message' => $log->error_message,
                'model' => $log->model,
            ]);

        // Cache efficiency (last 7 days)
        $cacheStats = ApiLog::query()
            ->where('created_at', '>=', $weekAgo)
            ->where('success', true)
            ->selectRaw('
                SUM(cache_read_input_tokens) as cached_tokens,
                SUM(cache_creation_input_tokens) as cache_write_tokens,
                SUM(input_tokens) as total_input_tokens
            ')
            ->first();

        $cacheHitRate = 0;
        if ($cacheStats && ($cacheStats->total_input_tokens + $cacheStats->cached_tokens) > 0) {
            $cacheHitRate = round(
                ($cacheStats->cached_tokens / ($cacheStats->total_input_tokens + $cacheStats->cached_tokens)) * 100,
                1
            );
        }

        return Inertia::render('admin/api-metrics/index', [
            'stats' => [
                'today' => $todayStats,
                'week' => $weekStats,
                'month' => $monthStats,
            ],
            'byMode' => $byMode,
            'recentErrors' => $recentErrors,
            'cacheStats' => [
                'cached_tokens' => $cacheStats->cached_tokens ?? 0,
                'cache_write_tokens' => $cacheStats->cache_write_tokens ?? 0,
                'total_input_tokens' => $cacheStats->total_input_tokens ?? 0,
                'cache_hit_rate' => $cacheHitRate,
            ],
        ]);
    }

    private function getStats($since): array
    {
        $data = ApiLog::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cache_creation_input_tokens) as total_cache_write_tokens,
                SUM(cache_read_input_tokens) as total_cache_read_tokens,
                AVG(response_time_ms) as avg_response_time,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as error_count
            ')
            ->first();

        // Calculate estimated cost
        $inputCost = (($data->total_input_tokens ?? 0) / 1_000_000) * 3.00;
        $outputCost = (($data->total_output_tokens ?? 0) / 1_000_000) * 15.00;
        $cacheWriteCost = (($data->total_cache_write_tokens ?? 0) / 1_000_000) * 3.75;
        $cacheReadCost = (($data->total_cache_read_tokens ?? 0) / 1_000_000) * 0.30;

        return [
            'request_count' => $data->request_count ?? 0,
            'total_input_tokens' => $data->total_input_tokens ?? 0,
            'total_output_tokens' => $data->total_output_tokens ?? 0,
            'total_cache_read_tokens' => $data->total_cache_read_tokens ?? 0,
            'avg_response_time' => round($data->avg_response_time ?? 0),
            'error_count' => $data->error_count ?? 0,
            'estimated_cost' => round($inputCost + $outputCost + $cacheWriteCost + $cacheReadCost, 4),
        ];
    }
}
