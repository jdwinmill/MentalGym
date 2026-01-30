<?php

namespace App\Services;

use App\Models\UserProfile;

class UserContextFormatter
{
    /**
     * Format selected fields from a UserProfile into an array of "label: value" strings.
     *
     * @param  array<string>  $selectedFields
     * @return array<string>
     */
    public function format(UserProfile $profile, array $selectedFields): array
    {
        $config = config('user_context');
        $formatted = [];

        foreach ($selectedFields as $field) {
            if (! isset($config[$field])) {
                continue;
            }

            $fieldConfig = $config[$field];
            $value = $profile->getAttribute($field);

            $line = $this->formatField($value, $fieldConfig);

            if ($line !== null) {
                $formatted[] = $line;
            }
        }

        return $formatted;
    }

    /**
     * Format selected fields from a UserProfile into a newline-separated string block.
     *
     * @param  array<string>  $selectedFields
     */
    public function formatAsBlock(UserProfile $profile, array $selectedFields): string
    {
        return implode("\n", $this->format($profile, $selectedFields));
    }

    /**
     * Format a single field value according to its config.
     *
     * @param  array<string, string>  $fieldConfig
     */
    private function formatField(mixed $value, array $fieldConfig): ?string
    {
        $type = $fieldConfig['type'];
        $label = $fieldConfig['label'];

        return match ($type) {
            'string' => $this->formatString($value, $label),
            'integer' => $this->formatInteger($value, $label),
            'boolean' => $this->formatBoolean($value, $label),
            'array' => $this->formatArray($value, $label),
            default => null,
        };
    }

    /**
     * Format a string field.
     */
    private function formatString(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return "{$label}: {$value}";
    }

    /**
     * Format an integer field.
     */
    private function formatInteger(mixed $value, string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        return "{$label}: {$value}";
    }

    /**
     * Format a boolean field. Only outputs if true.
     */
    private function formatBoolean(mixed $value, string $label): ?string
    {
        if ($value !== true) {
            return null;
        }

        return "{$label}: yes";
    }

    /**
     * Format an array field as comma-separated values.
     */
    private function formatArray(mixed $value, string $label): ?string
    {
        if ($value === null || ! is_array($value) || empty($value)) {
            return null;
        }

        $list = implode(', ', array_map(fn ($item) => (string) $item, $value));

        return "{$label}: {$list}";
    }
}
