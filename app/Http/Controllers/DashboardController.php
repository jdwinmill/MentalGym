<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get cooldown info once for all tracks
        $cooldownInfo = $user->getTrackSwitchCooldownInfo();

        $tracks = Track::active()
            ->ordered()
            ->with([
                'skillLevels' => fn($q) => $q->orderBy('level_number'),
                'skillLevels.lessons' => fn($q) => $q->active()->orderBy('lesson_number'),
            ])
            ->get()
            ->map(function ($track) use ($user, $cooldownInfo) {
                $enrollment = $user->trackEnrollments()
                    ->where('track_id', $track->id)
                    ->whereIn('status', ['active', 'paused'])
                    ->first();

                // Check if this track can be activated
                $canActivate = true;
                $activationBlockedReason = null;

                if ($enrollment && $enrollment->isActive()) {
                    // Already active, can't re-activate
                    $canActivate = false;
                } elseif (!$cooldownInfo['can_switch'] && (!$enrollment || !$enrollment->isActive())) {
                    // Cooldown applies and this isn't the current track
                    $canActivate = false;
                    $daysRemaining = (int) ceil($cooldownInfo['days_remaining']);
                    $activationBlockedReason = "Cooldown active. {$daysRemaining} day(s) remaining.";
                } else {
                    // Check enrollment limits
                    $enrollmentCheck = $user->canEnrollInTrack($track);
                    if (!$enrollmentCheck['allowed']) {
                        $canActivate = false;
                        $activationBlockedReason = $enrollmentCheck['reason'];
                    }
                }

                // Get max lessons limit (0 = unlimited)
                $maxLessons = $user->capabilityValue('max_track_lessons') ?? 0;
                $lessonCount = 0;

                // Map skill levels with lesson completion status
                $skillLevels = $track->skillLevels->map(function ($level) use ($user, $maxLessons, &$lessonCount) {
                    $lessons = $level->lessons->map(function ($lesson) use ($user, $maxLessons, &$lessonCount) {
                        $lessonCount++;
                        $isLocked = $maxLessons > 0 && $lessonCount > $maxLessons;

                        return [
                            'id' => $lesson->id,
                            'lesson_number' => $lesson->lesson_number,
                            'title' => $lesson->title,
                            'is_completed' => $lesson->isCompletedBy($user),
                            'is_locked' => $isLocked,
                        ];
                    });

                    return [
                        'id' => $level->id,
                        'track_id' => $level->track_id,
                        'slug' => $level->slug,
                        'name' => $level->name,
                        'description' => $level->description,
                        'level_number' => $level->level_number,
                        'pass_threshold' => (float) $level->pass_threshold,
                        'lessons' => $lessons,
                    ];
                });

                return [
                    'id' => $track->id,
                    'slug' => $track->slug,
                    'name' => $track->name,
                    'description' => $track->description,
                    'pitch' => $track->pitch,
                    'duration_weeks' => $track->duration_weeks,
                    'sessions_per_week' => $track->sessions_per_week,
                    'session_duration_minutes' => $track->session_duration_minutes,
                    'is_active' => $track->is_active,
                    'is_enrolled' => $enrollment !== null,
                    'is_activated' => $enrollment?->isActive() ?? false,
                    'enrollment' => $enrollment ? [
                        'id' => $enrollment->id,
                        'status' => $enrollment->status,
                        'enrolled_at' => $enrollment->enrolled_at?->toDateString(),
                        'activated_at' => $enrollment->activated_at?->toDateString(),
                    ] : null,
                    'can_activate' => $canActivate,
                    'activation_blocked_reason' => $activationBlockedReason,
                    'skill_levels' => $skillLevels,
                ];
            });

        return Inertia::render('dashboard', [
            'tracks' => $tracks,
            'cooldownInfo' => $cooldownInfo,
        ]);
    }
}
