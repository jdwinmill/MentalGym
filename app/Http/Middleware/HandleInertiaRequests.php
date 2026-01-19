<?php

namespace App\Http\Middleware;

use App\Models\DailyUsage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();
        $authData = $this->buildAuthData($user);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => $authData,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Build the auth data array with permissions and limits.
     */
    protected function buildAuthData($user): array
    {
        if (! $user) {
            return [
                'user' => null,
                'isAdmin' => false,
                'can' => [
                    'train' => false,
                    'admin' => false,
                ],
                'limits' => null,
                'plan' => null,
            ];
        }

        $planConfig = $user->planConfig();
        $usage = DailyUsage::forUserToday($user);
        $exchangesUsed = $usage->exchange_count;

        $isAdmin = Gate::allows('admin');

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'can' => [
                'train' => Gate::allows('can-train'),
                'admin' => $isAdmin,
            ],
            'limits' => [
                'daily_exchanges' => $planConfig['daily_exchanges'],
                'exchanges_used' => $exchangesUsed,
                'exchanges_remaining' => max(0, $planConfig['daily_exchanges'] - $exchangesUsed),
                'max_level' => $planConfig['max_level'],
            ],
            'plan' => $user->plan,
        ];
    }
}
