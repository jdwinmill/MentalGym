<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use App\Models\Principle;
use Illuminate\Http\JsonResponse;

class PrinciplesApiController extends Controller
{
    /**
     * List all active principles with their nested insights.
     * GET /api/principles
     */
    public function index(): JsonResponse
    {
        $principles = Principle::active()
            ->ordered()
            ->with(['insights' => function ($query) {
                $query->active()->ordered();
            }])
            ->get()
            ->map(fn ($principle) => [
                'id' => $principle->id,
                'name' => $principle->name,
                'slug' => $principle->slug,
                'description' => $principle->description,
                'icon' => $principle->icon,
                'blog_urls' => $principle->blog_urls,
                'insights' => $principle->insights->map(fn ($insight) => [
                    'id' => $insight->id,
                    'name' => $insight->name,
                    'slug' => $insight->slug,
                    'summary' => $insight->summary,
                ]),
            ]);

        return response()->json([
            'success' => true,
            'data' => $principles,
        ]);
    }

    /**
     * Get a single insight by slug with full content and linked drills.
     * GET /api/insights/{slug}
     */
    public function showInsight(string $slug): JsonResponse
    {
        $insight = Insight::where('slug', $slug)
            ->active()
            ->with(['principle', 'drills' => function ($query) {
                $query->with('practiceMode');
            }])
            ->first();

        if (! $insight) {
            return response()->json([
                'success' => false,
                'message' => 'Insight not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
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
