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
        $isAdmin = Gate::allows('admin');

        // Build limits based on plan type (monthly for free, daily for pro)
        $hasMonthlyLimit = isset($planConfig['monthly_drills']);
        if ($hasMonthlyLimit) {
            $used = DailyUsage::monthlyExchangeCount($user);
            $limit = $planConfig['monthly_drills'];
            $limits = [
                'type' => 'monthly',
                'limit' => $limit,
                'used' => $used,
                'remaining' => max(0, $limit - $used),
                'max_level' => $planConfig['max_level'],
            ];
        } else {
            $usage = DailyUsage::forUserToday($user);
            $used = $usage->exchange_count;
            $limit = $planConfig['daily_exchanges'];
            $limits = [
                'type' => 'daily',
                'limit' => $limit,
                'used' => $used,
                'remaining' => max(0, $limit - $used),
                'max_level' => $planConfig['max_level'],
            ];
        }

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'can' => [
                'train' => Gate::allows('can-train'),
                'admin' => $isAdmin,
            ],
            'limits' => $limits,
            'plan' => $user->plan,
        ];
    }
}
