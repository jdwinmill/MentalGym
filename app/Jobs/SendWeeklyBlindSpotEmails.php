<?php

namespace App\Jobs;

use App\Mail\WeeklyBlindSpotReport;
use App\Models\BlindSpotEmail;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\ArticleRecommender;
use App\Services\BlindSpotAnalyzer;
use App\Services\WeeklyEmailContentGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyBlindSpotEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        BlindSpotAnalyzer $analyzer,
        WeeklyEmailContentGenerator $contentGenerator,
        ArticleRecommender $articleRecommender,
    ): void {
        $weekNumber = now()->isoWeek();
        $year = now()->year;

        $users = $this->getEligibleUsers($weekNumber, $year);

        Log::info('Starting weekly blind spot email job', [
            'week' => $weekNumber,
            'year' => $year,
            'eligible_users' => $users->count(),
        ]);

        foreach ($users as $user) {
            try {
                $this->sendEmailToUser(
                    $user,
                    $analyzer,
                    $contentGenerator,
                    $articleRecommender,
                    $weekNumber,
                    $year
                );
            } catch (\Exception $e) {
                Log::error('Failed to send weekly blind spot email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                report($e);
            }
        }

        Log::info('Completed weekly blind spot email job', [
            'week' => $weekNumber,
            'year' => $year,
        ]);
    }

    private function getEligibleUsers(int $weekNumber, int $year)
    {
        return User::query()
            // Has Pro or Unlimited subscription
            ->whereIn('plan', ['pro', 'unlimited'])
            // Has sessions in last 7 days
            ->whereHas('trainingSessions', function ($query) {
                $query->where('status', TrainingSession::STATUS_COMPLETED)
                    ->where('created_at', '>=', now()->subDays(7));
            })
            // Hasn't received email this week
            ->whereDoesntHave('blindSpotEmails', function ($query) use ($weekNumber, $year) {
                $query->where('email_type', 'weekly_report')
                    ->where('week_number', $weekNumber)
                    ->where('year', $year);
            })
            // Has weekly emails enabled (null means default to true)
            ->where(function ($query) {
                $query->whereNull('email_preferences')
                    ->orWhereJsonDoesntContain('email_preferences->weekly_report', false);
            })
            ->get();
    }

    private function sendEmailToUser(
        User $user,
        BlindSpotAnalyzer $analyzer,
        WeeklyEmailContentGenerator $contentGenerator,
        ArticleRecommender $articleRecommender,
        int $weekNumber,
        int $year,
    ): void {
        // Check if user has enough data
        if (! $analyzer->hasEnoughData($user)) {
            Log::debug('Skipping user - insufficient data', ['user_id' => $user->id]);

            return;
        }

        // Run analysis
        $analysis = $analyzer->analyze($user);

        // Skip if no meaningful data
        if (! $analysis->hasBlindSpots() && empty($analysis->improving) && empty($analysis->slipping)) {
            Log::debug('Skipping user - no meaningful patterns', ['user_id' => $user->id]);

            return;
        }

        // Count sessions this week
        $sessionsThisWeek = TrainingSession::where('user_id', $user->id)
            ->where('status', TrainingSession::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Generate personalized content
        $content = $contentGenerator->generate($user, $analysis, $sessionsThisWeek);

        // Get recommended article based on biggest gap
        $article = $articleRecommender->recommend($analysis->biggestGap);

        // Send email
        Mail::to($user)->send(
            new WeeklyBlindSpotReport($user, $content, $sessionsThisWeek, $article)
        );

        // Record send
        BlindSpotEmail::create([
            'user_id' => $user->id,
            'email_type' => 'weekly_report',
            'week_number' => $weekNumber,
            'year' => $year,
            'analysis_snapshot' => $analysis->toArray(),
            'subject_line' => $content->subjectLine,
            'sent_at' => now(),
        ]);

        Log::info('Sent weekly blind spot email', [
            'user_id' => $user->id,
            'subject' => $content->subjectLine,
        ]);
    }
}
