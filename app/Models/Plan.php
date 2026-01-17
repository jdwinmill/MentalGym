<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'tagline',
        'price',
        'billing_interval',
        'yearly_price',
        'sort_order',
        'is_active',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function capabilities(): BelongsToMany
    {
        return $this->belongsToMany(Capability::class, 'plan_capabilities')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Capability Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if this plan has a specific capability.
     * For boolean capabilities, this also checks if the value is truthy.
     */
    public function hasCapability(string $key): bool
    {
        $capability = $this->capabilities()
            ->where('capabilities.key', $key)
            ->where('capabilities.is_active', true)
            ->first();

        if (!$capability) {
            return false;
        }

        // For boolean capabilities, check if the value is truthy
        if ($capability->value_type === Capability::TYPE_BOOLEAN) {
            $value = $capability->pivot->value ?? $capability->default_value;
            return $capability->castValue($value);
        }

        // For non-boolean capabilities, just having it is enough
        return true;
    }

    /**
     * Get the value of a capability for this plan.
     * Returns the plan-specific override if set, otherwise the default value.
     */
    public function getCapabilityValue(string $key): mixed
    {
        $capability = $this->capabilities()
            ->where('capabilities.key', $key)
            ->where('capabilities.is_active', true)
            ->first();

        if (!$capability) {
            return null;
        }

        // Use pivot value if set, otherwise use default
        $rawValue = $capability->pivot->value ?? $capability->default_value;

        return $capability->castValue($rawValue);
    }

    /**
     * Assign a capability to this plan with an optional custom value.
     */
    public function assignCapability(string|Capability $capability, mixed $value = null): void
    {
        $capabilityModel = $capability instanceof Capability
            ? $capability
            : Capability::where('key', $capability)->firstOrFail();

        $this->capabilities()->syncWithoutDetaching([
            $capabilityModel->id => ['value' => $value],
        ]);
    }

    /**
     * Remove a capability from this plan.
     */
    public function removeCapability(string|Capability $capability): void
    {
        $capabilityModel = $capability instanceof Capability
            ? $capability
            : Capability::where('key', $capability)->first();

        if ($capabilityModel) {
            $this->capabilities()->detach($capabilityModel->id);
        }
    }

    /**
     * Get all capabilities with their values as a keyed array.
     */
    public function getCapabilitiesArray(): array
    {
        $result = [];

        foreach ($this->capabilities()->where('is_active', true)->get() as $capability) {
            $rawValue = $capability->pivot->value ?? $capability->default_value;
            $result[$capability->key] = $capability->castValue($rawValue);
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────
    // Price Formatting
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the formatted monthly price.
     */
    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get the formatted yearly price.
     */
    public function getFormattedYearlyPrice(): ?string
    {
        return $this->yearly_price
            ? '$' . number_format($this->yearly_price, 2)
            : null;
    }

    /**
     * Calculate monthly savings when paying yearly.
     */
    public function getYearlySavings(): ?float
    {
        if (!$this->yearly_price) {
            return null;
        }

        $yearlyEquivalent = $this->price * 12;
        return $yearlyEquivalent - $this->yearly_price;
    }

    /**
     * Get the effective monthly price when paying yearly.
     */
    public function getEffectiveMonthlyPrice(): float
    {
        return $this->yearly_price
            ? $this->yearly_price / 12
            : $this->price;
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ─────────────────────────────────────────────────────────────
    // Static Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Find a plan by its key.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * Get all active plans ordered for display.
     */
    public static function getActivePlans()
    {
        return static::active()->ordered()->with('capabilities')->get();
    }
}
