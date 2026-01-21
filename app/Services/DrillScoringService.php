<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\DrillScore;
use App\Models\PracticeMode;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DrillScoringService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(config('services.anthropic.api_key'));
    }

    public function scoreResponse(
        User $user,
        TrainingSession $session,
        string $drillType,
        string $drillPhase,
        string $userResponse,
        bool $isIteration = false
    ): ?DrillScore {
        $wordCount = str_word_count($userResponse);

        $scores = $this->callScoringApi($drillType, $userResponse, $wordCount);

        if ($scores === null) {
            return null;
        }

        return DrillScore::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'practice_mode_id' => $session->practice_mode_id,
            'drill_type' => $drillType,
            'drill_phase' => $drillPhase,
            'is_iteration' => $isIteration,
            'scores' => $scores,
            'user_response' => $userResponse,
            'word_count' => $wordCount,
            'response_time_seconds' => null,
        ]);
    }

    public function getUniversalCriteria(): array
    {
        return config('drill_types.universal_criteria', []);
    }

    public function getCriteriaForDrillType(string $drillType): array
    {
        return config("drill_types.drill_criteria.{$drillType}", []);
    }

    public function getDrillTypeFromPhase(string $drillPhase): ?string
    {
        return config("drill_types.phase_mapping.{$drillPhase}");
    }

    private function callScoringApi(string $drillType, string $userResponse, int $wordCount): ?array
    {
        $prompt = $this->buildScoringPrompt($drillType, $userResponse, $wordCount);

        try {
            $response = $this->client->messages->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 500,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            $content = $response->content[0]->text ?? '';

            return $this->parseScores($content, $drillType);

        } catch (\Exception $e) {
            Log::error('Drill scoring API call failed', [
                'drill_type' => $drillType,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildScoringPrompt(string $drillType, string $userResponse, int $wordCount): string
    {
        $universalCriteria = $this->getUniversalCriteria();
        $drillCriteria = $this->getCriteriaForDrillType($drillType);

        $universalList = $this->formatCriteriaList($universalCriteria);
        $drillList = $this->formatCriteriaList($drillCriteria);

        $universalFields = $this->formatJsonFields($universalCriteria);
        $drillFields = $this->formatJsonFields($drillCriteria);

        return <<<PROMPT
You are evaluating a training response. Score it against the criteria below.

DRILL TYPE: {$drillType}
USER RESPONSE: {$userResponse}
WORD COUNT: {$wordCount}

UNIVERSAL CRITERIA (evaluate all):
{$universalList}

DRILL-SPECIFIC CRITERIA:
{$drillList}

Respond with JSON only. Boolean values as true/false, counts as integers. No explanation.

{
{$universalFields}
{$drillFields}
}
PROMPT;
    }

    private function formatCriteriaList(array $criteria): string
    {
        $lines = [];
        foreach ($criteria as $key => $config) {
            $lines[] = "- {$key}: {$config['description']}";
        }
        return implode("\n", $lines);
    }

    private function formatJsonFields(array $criteria): string
    {
        $fields = [];
        foreach ($criteria as $key => $config) {
            $type = $config['type'] === 'boolean' ? 'boolean' : 'integer';
            $fields[] = "  \"{$key}\": {$type}";
        }
        return implode(",\n", $fields);
    }

    private function parseScores(string $aiResponse, string $drillType): ?array
    {
        // Extract JSON from response (handle markdown code blocks)
        $json = $aiResponse;
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $aiResponse, $matches)) {
            $json = $matches[1];
        }

        // Try to extract JSON object if there's extra text
        if (preg_match('/\{[\s\S]*\}/', $json, $matches)) {
            $json = $matches[0];
        }

        $scores = json_decode($json, true);

        if (!is_array($scores)) {
            Log::warning('Failed to parse drill scoring response', [
                'drill_type' => $drillType,
                'response' => $aiResponse,
            ]);
            return null;
        }

        // Validate and sanitize scores
        $validatedScores = [];
        $allCriteria = array_merge(
            $this->getUniversalCriteria(),
            $this->getCriteriaForDrillType($drillType)
        );

        foreach ($allCriteria as $key => $config) {
            if (isset($scores[$key])) {
                if ($config['type'] === 'boolean') {
                    $validatedScores[$key] = (bool) $scores[$key];
                } else {
                    $validatedScores[$key] = (int) $scores[$key];
                }
            }
        }

        return $validatedScores;
    }
}
