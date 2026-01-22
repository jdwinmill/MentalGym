<?php

namespace App\Jobs;

use App\Mail\BlindSpotTeaserEmail;
use App\Models\BlindSpotEmail;
use App\Models\User;
use App\Services\BlindSpotAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBlindSpotTeaserEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function handle(BlindSpotAnalyzer $analyzer): void
    {
        // Double-check eligibility (in case of race conditions)
        if (! $this->isEligible()) {
            Log::debug('Teaser email skipped - user no longer eligible', [
                'user_id' => $this->user->id,
            ]);

            return;
        }

        // Run analysis
        $analysis = $analyzer->analyze($this->user);

        // Only send if there are actual blind spots
        if (! $analysis->hasBlindSpots()) {
            Log::debug('Teaser email skipped - no blind spots found', [
                'user_id' => $this->user->id,
            ]);

            return;
        }

        $blindSpotCount = $analysis->getBlindSpotCount();
        $subjectLine = $this->getSubjectLine($blindSpotCount);

        // Send email
        Mail::to($this->user)->send(
            new BlindSpotTeaserEmail(
                $this->user,
                $blindSpotCount,
                $analysis->totalResponses
            )
        );

        // Record send
        BlindSpotEmail::create([
            'user_id' => $this->user->id,
            'email_type' => 'teaser',
            'week_number' => now()->isoWeek(),
            'year' => now()->year,
            'analysis_snapshot' => [
                'blind_spot_count' => $blindSpotCount,
                'total_sessions' => $analysis->totalSessions,
                'total_responses' => $analysis->totalResponses,
            ],
            'subject_line' => $subjectLine,
            'sent_at' => now(),
        ]);

        Log::info('Sent blind spot teaser email', [
            'user_id' => $this->user->id,
            'blind_spot_count' => $blindSpotCount,
            'subject' => $subjectLine,
        ]);
    }

    private function isEligible(): bool
    {
        return ! $this->user->hasPaidPlan()
            && ! $this->user->blindSpotEmails()->where('email_type', 'teaser')->exists();
    }

    private function getSubjectLine(int $count): string
    {
        if ($count === 1) {
            return 'We found a pattern in your training';
        }

        return "We found {$count} patterns in your training";
    }
}
