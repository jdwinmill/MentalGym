<?php

namespace App\Services;

use Anthropic\Client;
use App\DTOs\BlindSpotAnalysis;
use App\DTOs\WeeklyEmailContent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WeeklyEmailContentGenerator
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(config('services.anthropic.api_key'));
    }

    public function generate(User $user, BlindSpotAnalysis $analysis, int $sessionsThisWeek): WeeklyEmailContent
    {
        $prompt = $this->buildPrompt($user, $analysis, $sessionsThisWeek);

        try {
            $response = $this->client->messages->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            $content = $response->content[0]->text ?? '';

            return $this->parseResponse($content);

        } catch (\Exception $e) {
            Log::error('Weekly email content generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackContent($analysis);
        }
    }

    private function buildPrompt(User $user, BlindSpotAnalysis $analysis, int $sessionsThisWeek): string
    {
        $blindSpotsText = $this->formatBlindSpots($analysis->blindSpots);
        $improvingText = $this->formatSkills($analysis->improving);
        $slippingText = $this->formatSkills($analysis->slipping);
        $stableText = $this->formatSkills($analysis->stable);

        return <<<PROMPT
You are writing a weekly training report email for a professional using SharpStack, a mental fitness training platform.

SCORING SYSTEM:
- Skills are scored 1-10 (higher is better)
- Scores ≤4 = blind spot (needs significant work)
- Scores 5-6 = developing (room for growth)
- Scores 7+ = strong (performing well)
- Trends: "improving" (score rising), "slipping" (score declining), "stable" (consistent), "new" (insufficient history)

USER DATA:
- First name: {$user->first_name}
- Sessions this week: {$sessionsThisWeek}
- Total sessions: {$analysis->totalSessions}

ANALYSIS DATA:
- Blind spots (≤4/10): {$blindSpotsText}
- Improving: {$improvingText}
- Slipping: {$slippingText}
- Stable: {$stableText}
- Biggest gap: {$analysis->biggestGap}
- Biggest win: {$analysis->biggestWin}

Generate the following sections. Be direct, specific, and actionable. No fluff. No motivational clichés. Reference their actual scores and sample sizes.

1. SUBJECT_LINE: Short, specific subject line (under 50 chars). Format: "Your week: [insight]"

2. IMPROVING: 1-2 bullet points about what's getting better. Reference the skill, score, and trend. If nothing is improving, return an empty array.

3. NEEDS_WORK: 1-2 bullet points about blind spots or slipping skills. Reference the score (e.g., "averaging 3.2/10 across 8 responses"). Be direct but not harsh.

4. PATTERN_TO_WATCH: 1-2 sentences about their most important pattern. Connect the score to real-world impact. This should be insightful, not just restating data.

5. WEEKLY_FOCUS: One specific, actionable thing they can do this week. Should be concrete (e.g., "Before submitting any response, delete 'I think' and 'maybe'. See what's left."). Not generic advice.

Respond in this exact JSON format only, with no additional text:
{
  "subject_line": "...",
  "improving": ["...", "..."],
  "needs_work": ["...", "..."],
  "pattern_to_watch": "...",
  "weekly_focus": "..."
}
PROMPT;
    }

    private function formatBlindSpots(array $blindSpots): string
    {
        if (empty($blindSpots)) {
            return 'None detected';
        }

        $items = array_map(function ($spot) {
            $score = round($spot->averageScore, 1);
            $samples = $spot->sampleSize;
            $suggestion = $spot->latestSuggestion ?? '';

            $text = "{$spot->label}: {$score}/10 across {$samples} responses";
            if ($suggestion) {
                $text .= " (AI suggestion: {$suggestion})";
            }

            return $text;
        }, $blindSpots);

        return implode('; ', $items);
    }

    private function formatSkills(array $skills): string
    {
        if (empty($skills)) {
            return 'None';
        }

        $items = array_map(function ($skill) {
            $score = round($skill->averageScore, 1);
            $samples = $skill->sampleSize;
            $trend = $skill->trend;

            return "{$skill->label}: {$score}/10 across {$samples} responses, trend: {$trend}";
        }, $skills);

        return implode('; ', $items);
    }

    private function parseResponse(string $response): WeeklyEmailContent
    {
        // Extract JSON from response (handle potential markdown code blocks)
        $jsonMatch = preg_match('/\{[\s\S]*\}/', $response, $matches);

        if (! $jsonMatch) {
            throw new \RuntimeException('No JSON found in response');
        }

        $data = json_decode($matches[0], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in response: '.json_last_error_msg());
        }

        return new WeeklyEmailContent(
            subjectLine: $data['subject_line'] ?? 'Your weekly training report',
            improving: $data['improving'] ?? [],
            needsWork: $data['needs_work'] ?? ['Keep practicing to build more data.'],
            patternToWatch: $data['pattern_to_watch'] ?? 'Continue training to identify patterns.',
            weeklyFocus: $data['weekly_focus'] ?? 'Complete at least 3 training sessions this week.',
        );
    }

    private function getFallbackContent(BlindSpotAnalysis $analysis): WeeklyEmailContent
    {
        $needsWork = [];

        if (! empty($analysis->blindSpots)) {
            $topBlindSpot = $analysis->blindSpots[0];
            $score = round($topBlindSpot->averageScore, 1);
            $samples = $topBlindSpot->sampleSize;
            $needsWork[] = "{$topBlindSpot->label}: averaging {$score}/10 across {$samples} responses - needs focused practice.";
        }

        if (empty($needsWork)) {
            $needsWork[] = 'Continue building your training data for more specific insights.';
        }

        $improving = [];
        if (! empty($analysis->improving)) {
            $topImproving = $analysis->improving[0];
            $score = round($topImproving->averageScore, 1);
            $improving[] = "{$topImproving->label} is trending up (now at {$score}/10).";
        }

        return new WeeklyEmailContent(
            subjectLine: $analysis->biggestGap
                ? "Your week: Focus on {$analysis->biggestGap}"
                : 'Your weekly training report',
            improving: $improving,
            needsWork: $needsWork,
            patternToWatch: 'We need a few more sessions to identify clear patterns.',
            weeklyFocus: 'Complete 3+ training sessions this week to unlock deeper insights.',
        );
    }
}
