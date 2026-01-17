<?php

namespace App\Http\Middleware;

use App\Models\Track;
use App\Services\CapabilityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrackAccess
{
    public function __construct(
        protected CapabilityService $capabilityService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->denyAccess($request, 'Authentication required');
        }

        // Admins can access all tracks
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Get the track from the route
        $track = $request->route('track');

        if (!$track instanceof Track) {
            // Try to find track by slug or ID
            $trackParam = $request->route('track');
            $track = is_numeric($trackParam)
                ? Track::find($trackParam)
                : Track::where('slug', $trackParam)->first();
        }

        if (!$track) {
            return $next($request); // Let the controller handle 404
        }

        if (!$user->canAccessTrack($track)) {
            $missingCapabilities = $user->getMissingCapabilitiesForTrack($track);
            $recommendedPlan = $this->capabilityService->getRecommendedPlanForTrack($track);

            return $this->denyAccess($request, 'Upgrade required to access this track', [
                'missing_capabilities' => $missingCapabilities,
                'recommended_plan' => $recommendedPlan ? [
                    'key' => $recommendedPlan->key,
                    'name' => $recommendedPlan->name,
                    'price' => $recommendedPlan->getFormattedPrice(),
                ] : null,
            ]);
        }

        return $next($request);
    }

    protected function denyAccess(Request $request, string $message, ?array $upgradeInfo = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'upgrade_info' => $upgradeInfo,
            ], 403);
        }

        return redirect()
            ->route('plans.index')
            ->with('error', $message)
            ->with('upgrade_info', $upgradeInfo);
    }
}
