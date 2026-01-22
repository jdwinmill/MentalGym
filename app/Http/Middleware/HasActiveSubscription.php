<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * Check if user has access (valid trial or active plan).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Please log in to access this content.',
                ], 401);
            }

            return redirect()->route('login');
        }

        if (! $user->hasAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription required',
                    'message' => 'Your trial has expired. Please subscribe to access lessons.',
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Your trial has expired. Please subscribe to access lessons.');
        }

        return $next($request);
    }
}
