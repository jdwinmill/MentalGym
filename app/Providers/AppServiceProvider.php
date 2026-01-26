<?php

namespace App\Providers;

use App\Events\SessionCompleted;
use App\Http\Responses\LogoutResponse;
use App\Listeners\CheckBlindSpotTeaserTrigger;
use App\Listeners\RecordSessionCompletion;
use App\Models\DailyUsage;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\Principle;
use App\Models\Tag;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserModeProgress;
use App\Policies\InsightPolicy;
use App\Policies\PracticeModePolicy;
use App\Policies\PrinciplePolicy;
use App\Policies\TagPolicy;
use App\Policies\TrainingSessionPolicy;
use App\Services\PracticeAIService;
use App\Services\TrainingSessionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PracticeAIService::class);
        $this->app->singleton(TrainingSessionService::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
        $this->registerPolicies();
        $this->registerEvents();
    }

    /**
     * Register event listeners.
     */
    protected function registerEvents(): void
    {
        Event::listen(
            SessionCompleted::class,
            RecordSessionCompletion::class
        );

        Event::listen(
            SessionCompleted::class,
            CheckBlindSpotTeaserTrigger::class
        );
    }

    /**
     * Register authorization gates.
     */
    protected function registerGates(): void
    {
        // User has exchanges remaining (monthly for free, daily for pro)
        Gate::define('can-train', function (User $user) {
            $planConfig = $user->planConfig();

            // Free users have monthly limits
            if (isset($planConfig['monthly_drills'])) {
                $monthlyUsage = DailyUsage::monthlyExchangeCount($user);

                return $monthlyUsage < $planConfig['monthly_drills'];
            }

            // Pro users have daily limits
            $usage = DailyUsage::forUserToday($user);

            return $usage->exchange_count < $planConfig['daily_exchanges'];
        });

        // User can train AND their current level in the mode doesn't exceed plan's max level
        Gate::define('can-train-mode', function (User $user, PracticeMode $mode) {
            if (! Gate::forUser($user)->allows('can-train')) {
                return false;
            }

            $progress = UserModeProgress::where('user_id', $user->id)
                ->where('practice_mode_id', $mode->id)
                ->first();

            $currentLevel = $progress?->current_level ?? 1;
            $maxLevel = $user->planConfig()['max_level'];

            return $currentLevel <= $maxLevel;
        });

        // User's current level is below plan's max level
        Gate::define('can-level-up', function (User $user, int $currentLevel) {
            return $currentLevel < $user->planConfig()['max_level'];
        });

        // Can user access level N?
        Gate::define('access-level', function (User $user, int $level) {
            return $user->planConfig()['max_level'] >= $level;
        });

        // User has admin privileges
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });
    }

    /**
     * Register model policies.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(PracticeMode::class, PracticeModePolicy::class);
        Gate::policy(TrainingSession::class, TrainingSessionPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Principle::class, PrinciplePolicy::class);
        Gate::policy(Insight::class, InsightPolicy::class);
    }
}
