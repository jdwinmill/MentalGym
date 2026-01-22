<?php

namespace App\Http\Controllers;

use App\Http\Requests\EarlyAccessSignupRequest;
use App\Models\EarlyAccessSignup;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('landing');
    }

    public function store(EarlyAccessSignupRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $selectedTopics = $validated['selected_topics'] ?? [];

        // Collect UTM params if present
        $utmParams = array_filter([
            'source' => $request->input('utm_source'),
            'medium' => $request->input('utm_medium'),
            'campaign' => $request->input('utm_campaign'),
            'term' => $request->input('utm_term'),
            'content' => $request->input('utm_content'),
        ]);

        EarlyAccessSignup::create([
            'email' => $validated['email'],
            'selected_topics' => $selectedTopics,
            'referrer' => $request->input('referrer'),
            'utm_params' => ! empty($utmParams) ? $utmParams : null,
            'timezone' => $request->input('timezone'),
            'device_type' => $request->input('device_type'),
            'locale' => $request->input('locale'),
        ]);

        $topicLabels = [
            'critical-thinking' => 'Critical Thinking',
            'active-listening' => 'Active Listening',
            'first-principles' => 'First Principles Thinking',
            'strategic-thinking' => 'Strategic Thinking',
            'clear-communication' => 'Clear Communication',
            'thinking-under-pressure' => 'Thinking Under Pressure',
        ];

        $selectedLabels = array_map(
            fn ($topic) => $topicLabels[$topic] ?? $topic,
            $selectedTopics
        );

        $message = count($selectedTopics) > 0
            ? "You're in! We'll notify you when your selected training is ready."
            : "You're in! We'll notify you when we launch.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'selected_topics' => $selectedLabels,
        ]);
    }
}
