<?php

namespace App\Providers;

use App\Models\DailyUsage;
use App\Models\PracticeMode;
use App\Models\Tag;
use App\Models\TrainingSession;
use App\Models\User;
use App\Models\UserModeProgress;
use App\Policies\PracticeModePolicy;
use App\Policies\TagPolicy;
use App\Policies\TrainingSessionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
        $this->registerPolicies();
    }

    /**
     * Register authorization gates.
     */
    protected function registerGates(): void
    {
        // User has exchanges remaining today
        Gate::define('can-train', function (User $user) {
            $usage = DailyUsage::forUserToday($user);
            $limit = $user->planConfig()['daily_exchanges'];
            return $usage->exchange_count < $limit;
        });

        // User can train AND their current level in the mode doesn't exceed plan's max level
        Gate::define('can-train-mode', function (User $user, PracticeMode $mode) {
            if (! Gate::allows('can-train')) {
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
    }
}
