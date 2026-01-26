<?php

namespace App\Policies;

use App\Models\PracticeMode;
use App\Models\User;

class PracticeModePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Anyone can browse modes
    }

    public function view(User $user, PracticeMode $mode): bool
    {
        return $mode->is_active || $user->isAdmin();
    }

    public function start(User $user, PracticeMode $mode): bool
    {
        // Mode must be active
        if (! $mode->is_active) {
            return false;
        }

        // Check plan requirement only - daily limits are checked in the service layer
        // This allows the frontend to show appropriate messaging for each case
        return $this->meetsRequiredPlan($user, $mode);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, PracticeMode $mode): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PracticeMode $mode): bool
    {
        return $user->isAdmin();
    }

    private function meetsRequiredPlan(User $user, PracticeMode $mode): bool
    {
        if ($mode->required_plan === null) {
            return true; // Available to all
        }

        $planHierarchy = ['free' => 0, 'pro' => 1, 'unlimited' => 2];
        $userLevel = $planHierarchy[$user->plan] ?? 0;
        $requiredLevel = $planHierarchy[$mode->required_plan] ?? 0;

        return $userLevel >= $requiredLevel;
    }
}
