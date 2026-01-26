<?php

namespace App\Services;

use App\Models\Drill;
use App\Models\Insight;
use App\Models\PracticeMode;
use App\Models\PracticeModeRequiredContext;
use App\Models\Principle;
use App\Models\SkillDimension;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PracticeModeImportService
{
    private array $errors = [];

    private array $warnings = [];

    private array $result = [
        'would_create' => [
            'principles' => [],
            'insights' => [],
            'tags' => [],
            'dimensions' => [],
            'practice_mode' => null,
            'drills' => 0,
        ],
        'would_link_existing' => [
            'principles' => [],
            'insights' => [],
            'tags' => [],
            'dimensions' => [],
        ],
    ];

    /**
     * Validate JSON schema structure.
     */
    public function validateSchema(array $data): array
    {
        $this->errors = [];
        $existing = [
            'principles' => [],
            'insights' => [],
            'tags' => [],
            'dimensions' => [],
            'practice_mode' => null,
        ];

        // Practice mode is required
        if (! isset($data['practice_mode']) || ! is_array($data['practice_mode'])) {
            $this->errors[] = 'Missing required "practice_mode" object';

            return ['valid' => false, 'errors' => $this->errors, 'existing' => $existing];
        }

        // Validate practice_mode required fields
        $pm = $data['practice_mode'];
        $requiredPmFields = ['slug', 'name', 'instruction_set'];
        foreach ($requiredPmFields as $field) {
            if (empty($pm[$field])) {
                $this->errors[] = "practice_mode.{$field} is required";
            }
        }

        // Validate slug format
        if (! empty($pm['slug']) && ! preg_match('/^[a-z0-9-]+$/', $pm['slug'])) {
            $this->errors[] = 'practice_mode.slug must be lowercase alphanumeric with hyphens only';
        }

        // Check if practice mode already exists
        if (! empty($pm['slug']) && PracticeMode::where('slug', $pm['slug'])->exists()) {
            $this->errors[] = "Practice mode with slug '{$pm['slug']}' already exists";
        }

        // Validate required_plan
        if (isset($pm['required_plan']) && $pm['required_plan'] !== null && ! in_array($pm['required_plan'], ['pro', 'unlimited'])) {
            $this->errors[] = 'practice_mode.required_plan must be null, "pro", or "unlimited"';
        }

        // Validate config if present
        if (isset($pm['config']) && is_array($pm['config'])) {
            $this->validateConfig($pm['config']);
        }

        // Validate drills
        if (isset($data['drills'])) {
            if (! is_array($data['drills'])) {
                $this->errors[] = '"drills" must be an array';
            } else {
                foreach ($data['drills'] as $i => $drill) {
                    $this->validateDrill($drill, $i);
                }
            }
        }

        // Validate principles
        if (isset($data['principles'])) {
            if (! is_array($data['principles'])) {
                $this->errors[] = '"principles" must be an array';
            } else {
                foreach ($data['principles'] as $i => $principle) {
                    $this->validatePrinciple($principle, $i);
                }
            }
        }

        // Validate insights
        if (isset($data['insights'])) {
            if (! is_array($data['insights'])) {
                $this->errors[] = '"insights" must be an array';
            } else {
                $principleSlugsDefined = array_column($data['principles'] ?? [], 'slug');
                foreach ($data['insights'] as $i => $insight) {
                    $this->validateInsight($insight, $i, $principleSlugsDefined);
                }
            }
        }

        // Validate tags
        if (isset($data['tags'])) {
            if (! is_array($data['tags'])) {
                $this->errors[] = '"tags" must be an array';
            } else {
                foreach ($data['tags'] as $i => $tag) {
                    $this->validateTag($tag, $i);
                }
            }
        }

        // Validate dimensions
        if (isset($data['dimensions'])) {
            if (! is_array($data['dimensions'])) {
                $this->errors[] = '"dimensions" must be an array';
            } else {
                foreach ($data['dimensions'] as $i => $dimension) {
                    $this->validateDimension($dimension, $i);
                }
            }
        }

        // Check what already exists in the database
        if (! empty($pm['slug']) && PracticeMode::where('slug', $pm['slug'])->exists()) {
            $existing['practice_mode'] = $pm['slug'];
        }

        foreach ($data['principles'] ?? [] as $principle) {
            if (! empty($principle['slug']) && Principle::where('slug', $principle['slug'])->exists()) {
                $existing['principles'][] = $principle['slug'];
            }
        }

        foreach ($data['insights'] ?? [] as $insight) {
            if (! empty($insight['slug']) && Insight::where('slug', $insight['slug'])->exists()) {
                $existing['insights'][] = $insight['slug'];
            }
        }

        foreach ($data['tags'] ?? [] as $tag) {
            if (! empty($tag['slug']) && Tag::where('slug', $tag['slug'])->exists()) {
                $existing['tags'][] = $tag['slug'];
            }
        }

        foreach ($data['dimensions'] ?? [] as $dimension) {
            if (! empty($dimension['key']) && SkillDimension::where('key', $dimension['key'])->exists()) {
                $existing['dimensions'][] = $dimension['key'];
            }
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'existing' => $existing,
        ];
    }

    private function validateConfig(array $config): void
    {
        $configRules = [
            'input_character_limit' => [100, 2000],
            'reflection_character_limit' => [50, 500],
            'max_response_tokens' => [200, 2000],
            'max_history_exchanges' => [5, 24],
        ];

        foreach ($configRules as $key => [$min, $max]) {
            if (isset($config[$key])) {
                if (! is_int($config[$key]) || $config[$key] < $min || $config[$key] > $max) {
                    $this->errors[] = "practice_mode.config.{$key} must be an integer between {$min} and {$max}";
                }
            }
        }

        if (isset($config['model']) && ! in_array($config['model'], ['claude-sonnet-4-20250514', 'claude-haiku-4-20250414'])) {
            $this->errors[] = 'practice_mode.config.model must be "claude-sonnet-4-20250514" or "claude-haiku-4-20250414"';
        }
    }

    private function validateDrill(array $drill, int $index): void
    {
        $prefix = "drills[{$index}]";

        $requiredFields = ['name', 'input_type', 'scenario_instruction_set', 'evaluation_instruction_set'];
        foreach ($requiredFields as $field) {
            if (empty($drill[$field])) {
                $this->errors[] = "{$prefix}.{$field} is required";
            }
        }

        if (isset($drill['input_type']) && ! in_array($drill['input_type'], ['text', 'multiple_choice'])) {
            $this->errors[] = "{$prefix}.input_type must be 'text' or 'multiple_choice'";
        }

        if (isset($drill['timer_seconds']) && (! is_int($drill['timer_seconds']) || $drill['timer_seconds'] < 0 || $drill['timer_seconds'] > 600)) {
            $this->errors[] = "{$prefix}.timer_seconds must be an integer between 0 and 600";
        }

        if (isset($drill['insights']) && ! is_array($drill['insights'])) {
            $this->errors[] = "{$prefix}.insights must be an array";
        }
    }

    private function validatePrinciple(array $principle, int $index): void
    {
        $prefix = "principles[{$index}]";

        if (empty($principle['slug'])) {
            $this->errors[] = "{$prefix}.slug is required";
        } elseif (! preg_match('/^[a-z0-9-]+$/', $principle['slug'])) {
            $this->errors[] = "{$prefix}.slug must be lowercase alphanumeric with hyphens only";
        }

        if (empty($principle['name'])) {
            $this->errors[] = "{$prefix}.name is required";
        }
    }

    private function validateInsight(array $insight, int $index, array $principleSlugsDefined): void
    {
        $prefix = "insights[{$index}]";

        if (empty($insight['slug'])) {
            $this->errors[] = "{$prefix}.slug is required";
        } elseif (! preg_match('/^[a-z0-9-]+$/', $insight['slug'])) {
            $this->errors[] = "{$prefix}.slug must be lowercase alphanumeric with hyphens only";
        }

        $requiredFields = ['name', 'principle', 'summary', 'content'];
        foreach ($requiredFields as $field) {
            if (empty($insight[$field])) {
                $this->errors[] = "{$prefix}.{$field} is required";
            }
        }

        // Check principle exists in JSON or DB
        if (! empty($insight['principle'])) {
            $existsInJson = in_array($insight['principle'], $principleSlugsDefined);
            $existsInDb = Principle::where('slug', $insight['principle'])->exists();

            if (! $existsInJson && ! $existsInDb) {
                $this->errors[] = "{$prefix}.principle '{$insight['principle']}' not found in JSON or database";
            }
        }

        if (isset($insight['summary']) && strlen($insight['summary']) > 500) {
            $this->errors[] = "{$prefix}.summary must be 500 characters or less";
        }
    }

    private function validateTag(array $tag, int $index): void
    {
        $prefix = "tags[{$index}]";

        if (empty($tag['slug'])) {
            $this->errors[] = "{$prefix}.slug is required";
        }

        if (empty($tag['name'])) {
            $this->errors[] = "{$prefix}.name is required";
        }

        if (empty($tag['category']) || ! in_array($tag['category'], ['skill', 'context', 'duration', 'role'])) {
            $this->errors[] = "{$prefix}.category must be one of: skill, context, duration, role";
        }
    }

    private function validateDimension(array $dimension, int $index): void
    {
        $prefix = "dimensions[{$index}]";

        if (empty($dimension['key'])) {
            $this->errors[] = "{$prefix}.key is required";
        }

        if (empty($dimension['label'])) {
            $this->errors[] = "{$prefix}.label is required";
        }
    }

    /**
     * Test run - validate and return what would be created/linked.
     */
    public function testRun(array $data): array
    {
        $schemaResult = $this->validateSchema($data);
        if (! $schemaResult['valid']) {
            return [
                'valid' => false,
                'errors' => $schemaResult['errors'],
                'warnings' => [],
                'would_create' => null,
                'would_link_existing' => null,
            ];
        }

        $this->warnings = [];
        $this->result = [
            'would_create' => [
                'principles' => [],
                'insights' => [],
                'tags' => [],
                'dimensions' => [],
                'practice_mode' => $data['practice_mode']['slug'],
                'drills' => count($data['drills'] ?? []),
            ],
            'would_link_existing' => [
                'principles' => [],
                'insights' => [],
                'tags' => [],
                'dimensions' => [],
            ],
        ];

        // Analyze principles
        foreach ($data['principles'] ?? [] as $principle) {
            if (Principle::where('slug', $principle['slug'])->exists()) {
                $this->result['would_link_existing']['principles'][] = $principle['slug'];
            } else {
                $this->result['would_create']['principles'][] = $principle['slug'];
            }
        }

        // Analyze insights
        foreach ($data['insights'] ?? [] as $insight) {
            if (Insight::where('slug', $insight['slug'])->exists()) {
                $this->result['would_link_existing']['insights'][] = $insight['slug'];
            } else {
                $this->result['would_create']['insights'][] = $insight['slug'];
            }
        }

        // Analyze tags (from tags array and practice_mode.tags)
        $tagSlugsToCheck = array_column($data['tags'] ?? [], 'slug');
        $pmTagSlugs = $data['practice_mode']['tags'] ?? [];
        $allTagSlugs = array_unique(array_merge($tagSlugsToCheck, $pmTagSlugs));

        foreach ($allTagSlugs as $slug) {
            if (Tag::where('slug', $slug)->exists()) {
                $this->result['would_link_existing']['tags'][] = $slug;
            } else {
                // Check if it's defined in the tags array
                $inTagsArray = in_array($slug, $tagSlugsToCheck);
                if ($inTagsArray) {
                    $this->result['would_create']['tags'][] = $slug;
                } else {
                    $this->warnings[] = "Tag '{$slug}' referenced but not defined in tags array - will be created with defaults";
                    $this->result['would_create']['tags'][] = $slug;
                }
            }
        }

        // Analyze dimensions
        $dimensionKeysToCheck = array_column($data['dimensions'] ?? [], 'key');
        $drillDimensionKeys = [];
        foreach ($data['drills'] ?? [] as $drill) {
            $drillDimensionKeys = array_merge($drillDimensionKeys, $drill['dimensions'] ?? []);
        }
        $allDimensionKeys = array_unique(array_merge($dimensionKeysToCheck, $drillDimensionKeys));

        foreach ($allDimensionKeys as $key) {
            if (SkillDimension::where('key', $key)->exists()) {
                $this->result['would_link_existing']['dimensions'][] = $key;
            } else {
                $inDimensionsArray = in_array($key, $dimensionKeysToCheck);
                if ($inDimensionsArray) {
                    $this->result['would_create']['dimensions'][] = $key;
                } else {
                    $this->warnings[] = "Dimension '{$key}' referenced but not defined in dimensions array - will be created with defaults";
                    $this->result['would_create']['dimensions'][] = $key;
                }
            }
        }

        // Check drill insight references
        $insightSlugsInJson = array_column($data['insights'] ?? [], 'slug');
        foreach ($data['drills'] ?? [] as $i => $drill) {
            foreach ($drill['insights'] ?? [] as $insightRef) {
                $slug = $insightRef['slug'] ?? null;
                if ($slug) {
                    $inJson = in_array($slug, $insightSlugsInJson);
                    $inDb = Insight::where('slug', $slug)->exists();
                    if (! $inJson && ! $inDb) {
                        $this->warnings[] = "drills[{$i}].insights references '{$slug}' which doesn't exist in JSON or database";
                    }
                }
            }
        }

        return [
            'valid' => true,
            'errors' => [],
            'warnings' => $this->warnings,
            'would_create' => $this->result['would_create'],
            'would_link_existing' => $this->result['would_link_existing'],
        ];
    }

    /**
     * Execute the import.
     */
    public function import(array $data): array
    {
        $testResult = $this->testRun($data);
        if (! $testResult['valid']) {
            return $testResult;
        }

        try {
            DB::beginTransaction();

            // 1. Create principles
            $principleMap = []; // slug => id
            foreach ($data['principles'] ?? [] as $principleData) {
                $existing = Principle::where('slug', $principleData['slug'])->first();
                if ($existing) {
                    $principleMap[$principleData['slug']] = $existing->id;
                } else {
                    $maxPosition = Principle::max('position') ?? 0;
                    $principle = Principle::create([
                        'slug' => $principleData['slug'],
                        'name' => $principleData['name'],
                        'description' => $principleData['description'] ?? null,
                        'icon' => $principleData['icon'] ?? null,
                        'position' => $maxPosition + 1,
                        'is_active' => false,
                        'blog_urls' => $principleData['blog_urls'] ?? [],
                    ]);
                    $principleMap[$principleData['slug']] = $principle->id;
                }
            }

            // 2. Create insights
            $insightMap = []; // slug => id
            foreach ($data['insights'] ?? [] as $insightData) {
                $existing = Insight::where('slug', $insightData['slug'])->first();
                if ($existing) {
                    $insightMap[$insightData['slug']] = $existing->id;
                } else {
                    // Get principle ID
                    $principleId = $principleMap[$insightData['principle']]
                        ?? Principle::where('slug', $insightData['principle'])->value('id');

                    $maxPosition = Insight::where('principle_id', $principleId)->max('position') ?? 0;
                    $insight = Insight::create([
                        'principle_id' => $principleId,
                        'slug' => $insightData['slug'],
                        'name' => $insightData['name'],
                        'summary' => $insightData['summary'],
                        'content' => $insightData['content'],
                        'position' => $maxPosition + 1,
                        'is_active' => false,
                    ]);
                    $insightMap[$insightData['slug']] = $insight->id;
                }
            }

            // 3. Create tags
            $tagMap = []; // slug => id
            $tagSlugsFromArray = [];
            foreach ($data['tags'] ?? [] as $tagData) {
                $tagSlugsFromArray[$tagData['slug']] = $tagData;
            }

            $allTagSlugsNeeded = $data['practice_mode']['tags'] ?? [];
            foreach ($allTagSlugsNeeded as $slug) {
                $existing = Tag::where('slug', $slug)->first();
                if ($existing) {
                    $tagMap[$slug] = $existing->id;
                } else {
                    $tagData = $tagSlugsFromArray[$slug] ?? [
                        'slug' => $slug,
                        'name' => Str::title(str_replace('-', ' ', $slug)),
                        'category' => 'skill',
                    ];
                    $maxOrder = Tag::where('category', $tagData['category'])->max('display_order') ?? 0;
                    $tag = Tag::create([
                        'slug' => $tagData['slug'],
                        'name' => $tagData['name'],
                        'category' => $tagData['category'],
                        'display_order' => $maxOrder + 1,
                        'color' => $tagData['color'] ?? null,
                    ]);
                    $tagMap[$slug] = $tag->id;
                }
            }

            // 4. Create dimensions
            $dimensionKeysFromArray = [];
            foreach ($data['dimensions'] ?? [] as $dimData) {
                $dimensionKeysFromArray[$dimData['key']] = $dimData;
            }

            $allDimensionKeysNeeded = [];
            foreach ($data['drills'] ?? [] as $drill) {
                $allDimensionKeysNeeded = array_merge($allDimensionKeysNeeded, $drill['dimensions'] ?? []);
            }
            $allDimensionKeysNeeded = array_unique($allDimensionKeysNeeded);

            foreach ($allDimensionKeysNeeded as $key) {
                if (! SkillDimension::where('key', $key)->exists()) {
                    $dimData = $dimensionKeysFromArray[$key] ?? [
                        'key' => $key,
                        'label' => Str::title(str_replace('-', ' ', $key)),
                    ];
                    SkillDimension::create([
                        'key' => $dimData['key'],
                        'label' => $dimData['label'],
                        'description' => $dimData['description'] ?? null,
                        'category' => $dimData['category'] ?? 'general',
                        'score_anchors' => $dimData['score_anchors'] ?? null,
                        'improvement_tips' => $dimData['improvement_tips'] ?? null,
                        'active' => false,
                    ]);
                }
            }

            // 5. Create practice mode
            $pm = $data['practice_mode'];
            $maxSortOrder = PracticeMode::max('sort_order') ?? 0;
            $practiceMode = PracticeMode::create([
                'slug' => $pm['slug'],
                'name' => $pm['name'],
                'tagline' => $pm['tagline'] ?? null,
                'description' => $pm['description'] ?? null,
                'icon' => $pm['icon'] ?? null,
                'instruction_set' => $pm['instruction_set'],
                'config' => $pm['config'] ?? null,
                'required_plan' => $pm['required_plan'] ?? null,
                'is_active' => false,
                'sort_order' => $maxSortOrder + 1,
            ]);

            // Sync tags
            $tagIds = array_values(array_filter(array_map(fn ($slug) => $tagMap[$slug] ?? null, $pm['tags'] ?? [])));
            $practiceMode->tags()->sync($tagIds);

            // Sync required context
            foreach ($pm['required_context'] ?? [] as $field) {
                PracticeModeRequiredContext::create([
                    'practice_mode_id' => $practiceMode->id,
                    'profile_field' => $field,
                ]);
            }

            // 6. Create drills
            foreach ($data['drills'] ?? [] as $position => $drillData) {
                $drill = Drill::create([
                    'practice_mode_id' => $practiceMode->id,
                    'name' => $drillData['name'],
                    'position' => $position,
                    'timer_seconds' => $drillData['timer_seconds'] ?? null,
                    'input_type' => $drillData['input_type'],
                    'scenario_instruction_set' => $drillData['scenario_instruction_set'],
                    'evaluation_instruction_set' => $drillData['evaluation_instruction_set'],
                    'dimensions' => $drillData['dimensions'] ?? [],
                    'config' => $drillData['config'] ?? null,
                ]);

                // Attach insights
                foreach ($drillData['insights'] ?? [] as $insightRef) {
                    $slug = $insightRef['slug'] ?? null;
                    $isPrimary = $insightRef['is_primary'] ?? false;

                    $insightId = $insightMap[$slug] ?? Insight::where('slug', $slug)->value('id');
                    if ($insightId) {
                        $drill->insights()->attach($insightId, ['is_primary' => $isPrimary]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import completed successfully',
                'created' => $testResult['would_create'],
                'linked' => $testResult['would_link_existing'],
                'warnings' => $testResult['warnings'],
                'practice_mode_id' => $practiceMode->id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }
}
