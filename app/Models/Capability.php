<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Capability extends Model
{
    // Value type constants
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_JSON = 'json';

    // Category constants
    public const CATEGORY_ACCESS = 'access';
    public const CATEGORY_LIMITS = 'limits';
    public const CATEGORY_FEATURES = 'features';
    public const CATEGORY_SUPPORT = 'support';

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'value_type',
        'default_value',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_capabilities')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function trackRequirements(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_capability_requirements')
            ->withPivot('required_value')
            ->withTimestamps();
    }

    // ─────────────────────────────────────────────────────────────
    // Value Casting
    // ─────────────────────────────────────────────────────────────

    /**
     * Cast a raw value to the appropriate type.
     */
    public function castValue(mixed $value): mixed
    {
        if ($value === null) {
            return $this->getDefaultTypedValue();
        }

        return match ($this->value_type) {
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_JSON => is_array($value) ? $value : json_decode($value, true),
            default => (string) $value,
        };
    }

    /**
     * Get the default value for this capability's type.
     */
    public function getDefaultTypedValue(): mixed
    {
        if ($this->default_value !== null) {
            return $this->castValue($this->default_value);
        }

        return match ($this->value_type) {
            self::TYPE_BOOLEAN => false,
            self::TYPE_INTEGER => 0,
            self::TYPE_JSON => [],
            default => '',
        };
    }

    /**
     * Validate that a value is appropriate for this capability type.
     */
    public function validateValue(mixed $value): bool
    {
        return match ($this->value_type) {
            self::TYPE_BOOLEAN => is_bool($value) || in_array($value, ['true', 'false', '1', '0', 1, 0], true),
            self::TYPE_INTEGER => is_numeric($value),
            self::TYPE_JSON => is_array($value) || $this->isValidJson($value),
            default => is_string($value) || is_null($value),
        };
    }

    private function isValidJson(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    // ─────────────────────────────────────────────────────────────
    // Display Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get a human-readable representation of a value.
     */
    public function formatValue(mixed $value): string
    {
        $castValue = $this->castValue($value);

        return match ($this->value_type) {
            self::TYPE_BOOLEAN => $castValue ? 'Yes' : 'No',
            self::TYPE_INTEGER => number_format($castValue),
            self::TYPE_JSON => json_encode($castValue, JSON_PRETTY_PRINT),
            default => (string) $castValue,
        };
    }

    /**
     * Get the category display name.
     */
    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_ACCESS => 'Access',
            self::CATEGORY_LIMITS => 'Limits',
            self::CATEGORY_FEATURES => 'Features',
            self::CATEGORY_SUPPORT => 'Support',
            default => ucfirst($this->category ?? 'General'),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBoolean($query)
    {
        return $query->where('value_type', self::TYPE_BOOLEAN);
    }

    public function scopeInteger($query)
    {
        return $query->where('value_type', self::TYPE_INTEGER);
    }

    // ─────────────────────────────────────────────────────────────
    // Static Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Find a capability by its key.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * Get all active capabilities grouped by category.
     */
    public static function getGroupedByCategory(): array
    {
        return static::active()
            ->ordered()
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    /**
     * Get available value types.
     */
    public static function getValueTypes(): array
    {
        return [
            self::TYPE_BOOLEAN => 'Boolean (Yes/No)',
            self::TYPE_INTEGER => 'Integer (Number)',
            self::TYPE_STRING => 'String (Text)',
            self::TYPE_JSON => 'JSON (Complex Data)',
        ];
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_ACCESS => 'Access',
            self::CATEGORY_LIMITS => 'Limits',
            self::CATEGORY_FEATURES => 'Features',
            self::CATEGORY_SUPPORT => 'Support',
        ];
    }
}
