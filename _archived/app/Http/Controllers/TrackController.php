<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Activate a track for the user.
     */
    public function activate(Request $request, Track $track)
    {
        $user = $request->user();

        // Check if track is active
        if (! $track->is_active) {
            return back()->with('error', 'This track is not available.');
        }

        // Check cooldown
        $cooldownCheck = $user->canSwitchTrack($track);
        if (! $cooldownCheck['allowed']) {
            return back()->with('error', $cooldownCheck['reason']);
        }

        // Check enrollment limits
        $enrollmentCheck = $user->canEnrollInTrack($track);
        if (! $enrollmentCheck['allowed']) {
            return back()->with('error', $enrollmentCheck['reason']);
        }

        try {
            $enrollment = $user->activateTrack($track);

            return back()->with('success', "You've activated {$track->name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Pause a track enrollment.
     */
    public function pause(Request $request, Track $track)
    {
        $user = $request->user();

        $enrollment = $user->trackEnrollments()
            ->where('track_id', $track->id)
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'You are not enrolled in this track.');
        }

        $enrollment->pause();

        return back()->with('success', "{$track->name} has been paused.");
    }

    /**
     * Get activation eligibility info (for API/JSON responses).
     */
    public function checkActivation(Request $request, Track $track)
    {
        $user = $request->user();

        $cooldownCheck = $user->canSwitchTrack($track);
        $enrollmentCheck = $user->canEnrollInTrack($track);

        $existingEnrollment = $user->trackEnrollments()
            ->where('track_id', $track->id)
            ->first();

        return response()->json([
            'track_id' => $track->id,
            'track_name' => $track->name,
            'is_enrolled' => $existingEnrollment !== null,
            'is_active' => $existingEnrollment?->isActive() ?? false,
            'cooldown_check' => $cooldownCheck,
            'enrollment_check' => $enrollmentCheck,
            'can_activate' => $cooldownCheck['allowed'] && $enrollmentCheck['allowed'],
        ]);
    }
}
