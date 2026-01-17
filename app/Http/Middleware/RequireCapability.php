<?php

namespace App\Http\Middleware;

use App\Services\CapabilityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCapability
{
    public function __construct(
        protected CapabilityService $capabilityService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->denyAccess($request, 'Authentication required');
        }

        // Admins bypass capability checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasCapability($capability)) {
            $upgradeInfo = $this->capabilityService->upgradeNeededFor($user, $capability);

            return $this->denyAccess($request, "Upgrade required for this feature", $upgradeInfo);
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
