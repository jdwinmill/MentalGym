<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MentalGymController extends Controller
{
    public function getRandomQuestion(Request $request)
    {
        $sessionId = $request->header('X-Session-Id')
            ?? $request->cookie('session_id')
            ?? Str::uuid()->toString();

        $recentQuestionIds = $request->session()->get('recent_questions', []);

        $question = Question::where('active', true)
            ->whereNotIn('id', array_slice($recentQuestionIds, 0, 10))
            ->inRandomOrder()
            ->first();

        if (! $question) {
            $request->session()->forget('recent_questions');
            $question = Question::where('active', true)
                ->inRandomOrder()
                ->first();
        }

        if ($question) {
            $recentQuestionIds = array_merge([$question->id], $recentQuestionIds);
            $request->session()->put('recent_questions', array_slice($recentQuestionIds, 0, 10));
        }

        return response()->json([
            'question' => $question,
            'session_id' => $sessionId,
        ]);
    }

    public function submitResponse(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'response_text' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback_text' => 'nullable|string',
            'anonymous_session_id' => 'required|string',
        ]);

        $response = Response::create($validated);

        return response()->json([
            'message' => 'Response saved successfully',
            'response' => $response,
        ], 201);
    }
}
