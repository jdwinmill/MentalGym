<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActivePlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->denyAccess($request, 'Authentication required');
        }

        // Admins bypass plan checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasAccess()) {
            return $this->denyAccess($request, 'An active subscription is required');
        }

        return $next($request);
    }

    protected function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'requires_subscription' => true,
            ], 403);
        }

        return redirect()
            ->route('plans.index')
            ->with('error', $message);
    }
}
