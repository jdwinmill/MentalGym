<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:bug,idea,other',
            'title' => 'nullable|string|max:100',
            'body' => 'required|string|max:1000',
            'url' => 'required|string|max:500',
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'] ?? 'idea',
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'url' => $validated['url'],
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks! We got it.',
        ]);
    }
}
