<?php

namespace App\Http\Controllers;

use App\Models\AnswerFeedback;
use App\Models\Lesson;
use App\Models\UserAnswer;
use App\Models\UserContentInteraction;
use App\Models\UserLessonAttempt;
use App\Models\UserWeaknessPattern;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    /**
     * Display the lesson flow page.
     * Access is controlled by HasActiveSubscription middleware.
     */
    public function show(Lesson $lesson): Response
    {
        $lesson->load([
            'track',
            'skillLevel',
            'contentBlocks' => fn($q) => $q->orderBy('sort_order'),
            'questions' => fn($q) => $q->orderBy('sort_order'),
            'questions.answerOptions' => fn($q) => $q->orderBy('sort_order'),
        ]);

        // Load feedback separately for each answer option
        foreach ($lesson->questions as $question) {
            foreach ($question->answerOptions as $option) {
                $option->feedback = AnswerFeedback::where('question_id', $question->id)
                    ->where('answer_option_id', $option->id)
                    ->first();
            }
        }

        // Convert relative audio URLs to internal streaming URLs
        foreach ($lesson->contentBlocks as $block) {
            if ($block->block_type === 'audio') {
                $content = $block->content;
                if (isset($content['audio_url']) && $content['audio_url']) {
                    // Use internal route to stream audio from S3
                    $content['url'] = route('media.stream', ['path' => $content['audio_url']]);
                }
                $block->content = $content;
            }
        }

        return Inertia::render('lessons/show', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Start a new lesson attempt.
     * Access is controlled by HasActiveSubscription middleware.
     */
    public function startAttempt(Lesson $lesson): JsonResponse
    {
        $attempt = UserLessonAttempt::create([
            'user_id' => auth()->id(),
            'lesson_id' => $lesson->id,
            'started_at' => now(),
        ]);

        return response()->json(['attempt' => $attempt]);
    }

    /**
     * Record audio/content interaction.
     */
    public function recordInteraction(Request $request, UserLessonAttempt $attempt): JsonResponse
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content_block_id' => 'required|exists:lesson_content_blocks,id',
            'interaction_type' => 'required|string',
            'interaction_data' => 'nullable|array',
        ]);

        $interaction = UserContentInteraction::updateOrCreate(
            [
                'user_lesson_attempt_id' => $attempt->id,
                'lesson_content_block_id' => $validated['content_block_id'],
            ],
            [
                'interaction_type' => $validated['interaction_type'],
                'interaction_data' => $validated['interaction_data'] ?? [],
                'interacted_at' => now(),
            ]
        );

        return response()->json(['interaction' => $interaction]);
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(Request $request, UserLessonAttempt $attempt): JsonResponse
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:lesson_questions,id',
            'answer_option_id' => 'required|exists:answer_options,id',
            'time_to_answer_seconds' => 'nullable|integer|min:0',
        ]);

        $question = $attempt->lesson->questions()->findOrFail($validated['question_id']);
        $answerOption = $question->answerOptions()->findOrFail($validated['answer_option_id']);
        $isCorrect = $answerOption->is_correct;

        $userAnswer = UserAnswer::create([
            'user_lesson_attempt_id' => $attempt->id,
            'question_id' => $validated['question_id'],
            'answer_option_id' => $validated['answer_option_id'],
            'is_correct' => $isCorrect,
            'time_to_answer_seconds' => $validated['time_to_answer_seconds'],
            'answered_at' => now(),
        ]);

        // Get feedback for this answer option
        $feedback = AnswerFeedback::where('question_id', $validated['question_id'])
            ->where('answer_option_id', $validated['answer_option_id'])
            ->first();

        // Track weakness pattern if incorrect and has pattern tag
        if (!$isCorrect && $feedback && $feedback->pattern_tag) {
            $this->recordWeaknessPattern($attempt, $feedback->pattern_tag);
        }

        // Get the correct answer option ID
        $correctOptionId = $question->answerOptions()->where('is_correct', true)->first()?->id;

        return response()->json([
            'answer' => $userAnswer,
            'is_correct' => $isCorrect,
            'feedback' => $feedback,
            'correct_option_id' => $correctOptionId,
        ]);
    }

    /**
     * Complete a lesson attempt.
     */
    public function completeAttempt(UserLessonAttempt $attempt): JsonResponse
    {
        // Verify the attempt belongs to the current user
        if ($attempt->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attempt->markCompleted();

        $lesson = $attempt->lesson->load([
            'contentBlocks' => fn($q) => $q->where('block_type', 'audio'),
        ]);

        // Get transcript from audio block
        $audioBlock = $lesson->contentBlocks->first();
        $transcript = $audioBlock?->getContentField('transcript');

        // Get weakness patterns for this user in this track/level
        $weaknessPatterns = UserWeaknessPattern::where('user_id', $attempt->user_id)
            ->where('track_id', $lesson->track_id)
            ->where('skill_level_id', $lesson->skill_level_id)
            ->orderByDesc('occurrence_count')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'pattern_tag' => $p->pattern_tag,
                'occurrence_count' => $p->occurrence_count,
                'severity_label' => $p->getSeverityLabel(),
            ]);

        return response()->json([
            'attempt' => $attempt->fresh(),
            'score' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'accuracy_percentage' => (float) $attempt->accuracy_percentage,
            'passed' => $attempt->passed(),
            'weakness_patterns' => $weaknessPatterns,
            'transcript' => $transcript,
        ]);
    }

    /**
     * Record a weakness pattern occurrence.
     */
    private function recordWeaknessPattern(UserLessonAttempt $attempt, string $patternTag): void
    {
        $lesson = $attempt->lesson;

        $pattern = UserWeaknessPattern::findOrCreatePattern(
            $attempt->user_id,
            $lesson->track_id,
            $lesson->skill_level_id,
            $patternTag
        );

        $pattern->recordOccurrence();
    }

    /**
     * Stream audio file from S3.
     */
    public function streamAudio(string $path): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Validate the path is an audio file
        if (!preg_match('/\.(mp3|wav|ogg|m4a)$/i', $path)) {
            abort(404);
        }

        // Check if file exists
        if (!Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
        ];

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? 'audio/mpeg';
        $size = Storage::disk('s3')->size($path);

        return response()->stream(
            function () use ($path) {
                $stream = Storage::disk('s3')->readStream($path);
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $size,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'public, max-age=3600',
            ]
        );
    }
}
