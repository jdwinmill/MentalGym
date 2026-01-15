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

        $tracks = Track::active()
            ->ordered()
            ->with([
                'skillLevels' => fn($q) => $q->orderBy('level_number'),
                'skillLevels.lessons' => fn($q) => $q->active()->orderBy('lesson_number'),
            ])
            ->get()
            ->map(function ($track) use ($user) {
                $enrollment = $user->trackEnrollments()
                    ->where('track_id', $track->id)
                    ->whereIn('status', ['active', 'paused'])
                    ->first();

                // Map skill levels with lesson completion status
                $skillLevels = $track->skillLevels->map(function ($level) use ($user) {
                    $lessons = $level->lessons->map(function ($lesson) use ($user) {
                        return [
                            'id' => $lesson->id,
                            'lesson_number' => $lesson->lesson_number,
                            'title' => $lesson->title,
                            'is_completed' => $lesson->isCompletedBy($user),
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
                    'enrollment' => $enrollment,
                    'skill_levels' => $skillLevels,
                ];
            });

        return Inertia::render('dashboard', [
            'tracks' => $tracks,
        ]);
    }
}
