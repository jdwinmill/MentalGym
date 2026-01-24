<?php

namespace App\Policies;

use App\Models\Principle;
use App\Models\User;

class PrinciplePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Principle $principle): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Principle $principle): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Principle $principle): bool
    {
        return $user->isAdmin();
    }
}
