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
            ->map(fn ($insight) => [
                'id' => $insight->id,
                'name' => $insight->name,
                'slug' => $insight->slug,
                'summary' => $insight->summary,
                'content' => $insight->content,
                'read_time' => max(1, ceil(str_word_count($insight->content) / 200)), // ~200 words per minute
                'principle' => [
                    'id' => $insight->principle->id,
                    'name' => $insight->principle->name,
                    'slug' => $insight->principle->slug,
                ],
                'drill' => $insight->drills->first() ? [
                    'id' => $insight->drills->first()->id,
                    'name' => $insight->drills->first()->name,
                    'practice_mode_slug' => $insight->drills->first()->practiceMode->slug,
                ] : null,
                'created_at' => $insight->created_at->format('M j, Y'),
            ]);

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
                'drills' => $insight->drills->map(fn ($drill) => [
                    'id' => $drill->id,
                    'name' => $drill->name,
                    'is_primary' => $drill->pivot->is_primary,
                    'practice_mode' => [
                        'id' => $drill->practiceMode->id,
                        'name' => $drill->practiceMode->name,
                        'slug' => $drill->practiceMode->slug,
                    ],
                ]),
            ],
        ]);
    }
}
