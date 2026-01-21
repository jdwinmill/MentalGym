<?php

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Jobs\SendBlindSpotTeaserEmail;
use App\Models\TrainingSession;
use App\Models\User;

class CheckBlindSpotTeaserTrigger
{
    public function handle(SessionCompleted $event): void
    {
        $user = $event->session->user;

        if ($this->shouldSendTeaser($user)) {
            SendBlindSpotTeaserEmail::dispatch($user);
        }
    }

    private function shouldSendTeaser(User $user): bool
    {
        // Must be free user
        if ($user->hasPaidPlan()) {
            return false;
        }

        // Must have exactly 5 completed sessions (just crossed threshold)
        $sessionCount = TrainingSession::where('user_id', $user->id)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->count();

        if ($sessionCount !== 5) {
            return false;
        }

        // Must not have received teaser already
        if ($user->blindSpotEmails()->where('email_type', 'teaser')->exists()) {
            return false;
        }

        // Must have teaser emails enabled
        if (!$user->wantsEmail('teaser_emails')) {
            return false;
        }

        return true;
    }
}
