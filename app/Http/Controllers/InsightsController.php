<?php

namespace App\Http\Controllers;

use App\Models\Insight;
use App\Models\Principle;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    /**
     * Display the playbook - a blog-style feed of all insights.
     */
    public function index(): Response
    {
        $user = auth()->user();

        // Get all principles for filtering
        $principles = Principle::active()
            ->ordered()
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
                'slug' => $principle->slug,
            ]);

        // Get all insights as a flat list for the blog feed
        $insights = Insight::active()
            ->with(['principle', 'drills' => function ($q) {
                $q->wherePivot('is_primary', true)->with('practiceMode');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($insight) use ($user) {
                $drill = $insight->drills->first();
                $drillData = null;

                if ($drill) {
                    $mode = $drill->practiceMode;
                    $canAccess = $mode->is_active && ($user ? $this->userCanAccessMode($user, $mode) : true);

                    $drillData = [
                        'id' => $drill->id,
                        'name' => $drill->name,
                        'practice_mode_slug' => $mode->slug,
                        'can_access' => $canAccess,
                        'required_plan' => $mode->required_plan,
                    ];
                }

                return [
                    'id' => $insight->id,
                    'name' => $insight->name,
                    'slug' => $insight->slug,
                    'summary' => $insight->summary,
                    'content' => $insight->content,
                    'read_time' => max(1, ceil(str_word_count($insight->content) / 200)),
                    'principle' => [
                        'id' => $insight->principle->id,
                        'name' => $insight->principle->name,
                        'slug' => $insight->principle->slug,
                    ],
                    'drill' => $drillData,
                    'created_at' => $insight->created_at->format('M j, Y'),
                ];
            });

        return Inertia::render('playbook/index', [
            'principles' => $principles,
            'insights' => $insights,
        ]);
    }

    /**
     * Display a single insight detail page.
     */
    public function show(Insight $insight): Response
    {
        // Ensure insight is active
        if (! $insight->is_active) {
            abort(404);
        }

        $insight->load(['principle', 'drills' => function ($query) {
            $query->with('practiceMode');
        }]);

        $user = auth()->user();

        return Inertia::render('playbook/show', [
            'insight' => [
                'id' => $insight->id,
                'name' => $insight->name,
                'slug' => $insight->slug,
                'summary' => $insight->summary,
                'content' => $insight->content,
                'principle' => [
                    'id' => $insight->principle->id,
                    'name' => $insight->principle->name,
                    'slug' => $insight->principle->slug,
                ],
                'drills' => $insight->drills->map(function ($drill) use ($user) {
                    $mode = $drill->practiceMode;
                    $canAccess = $mode->is_active && ($user ? $this->userCanAccessMode($user, $mode) : true);

                    return [
                        'id' => $drill->id,
                        'name' => $drill->name,
                        'is_primary' => $drill->pivot->is_primary,
                        'practice_mode' => [
                            'id' => $mode->id,
                            'name' => $mode->name,
                            'slug' => $mode->slug,
                            'is_active' => $mode->is_active,
                            'required_plan' => $mode->required_plan,
                            'can_access' => $canAccess,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /**
     * Check if user's plan meets the mode's requirement.
     */
    private function userCanAccessMode($user, $mode): bool
    {
        if ($mode->required_plan === null) {
            return true;
        }

        $planHierarchy = ['free' => 0, 'pro' => 1, 'unlimited' => 2];
        $userLevel = $planHierarchy[$user->plan] ?? 0;
        $requiredLevel = $planHierarchy[$mode->required_plan] ?? 0;

        return $userLevel >= $requiredLevel;
    }
}
