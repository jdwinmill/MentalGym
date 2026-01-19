<?php

namespace App\Policies;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class TrainingSessionPolicy
{
    public function view(User $user, TrainingSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function continue(User $user, TrainingSession $session): bool
    {
        if ($user->id !== $session->user_id) {
            return false;
        }

        if ($session->ended_at !== null) {
            return false;
        }

        return Gate::forUser($user)->allows('can-train-mode', $session->practiceMode);
    }

    public function resume(User $user, TrainingSession $session): bool
    {
        return $this->continue($user, $session);
    }

    public function end(User $user, TrainingSession $session): bool
    {
        return $user->id === $session->user_id && $session->ended_at === null;
    }
}
