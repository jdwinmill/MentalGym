<?php

namespace App\Services;

use App\Models\Capability;
use App\Models\Plan;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Collection;

class CapabilityService
{
    /**
     * Check if a user needs to upgrade to perform an action.
     * Returns null if user can perform action, or upgrade info if they can't.
     */
    public function upgradeNeededFor(User $user, string $action): ?array
    {
        // Map actions to required capabilities
        $capabilityMap = [
            'switch_track' => 'track_switching',
            'access_mastery' => 'mastery_content',
            'use_ai_analytics' => 'ai_analytics',
            'multiple_tracks' => 'multiple_active_tracks',
            'priority_support' => 'priority_support',
        ];

        $requiredCapability = $capabilityMap[$action] ?? $action;

        if ($user->hasCapability($requiredCapability)) {
            return null;
        }

        // Find plans that have this capability
        $plansWithCapability = $this->getPlansWithCapability($requiredCapability);

        if ($plansWithCapability->isEmpty()) {
            return null; // Capability doesn't exist
        }

        return [
            'required_capability' => $requiredCapability,
            'current_plan' => $user->plan?->key,
            'upgrade_options' => $plansWithCapability->map(fn ($plan) => [
                'key' => $plan->key,
                'name' => $plan->name,
                'price' => $plan->price,
                'formatted_price' => $plan->getFormattedPrice(),
            ])->values()->toArray(),
        ];
    }

    /**
     * Get all restrictions for a user based on their plan.
     */
    public function getUserRestrictions(User $user): array
    {
        $restrictions = [];

        if (!$user->hasActivePlan()) {
            return [
                'has_plan' => false,
                'message' => 'No active subscription',
                'restricted_capabilities' => Capability::active()->pluck('key')->toArray(),
            ];
        }

        $allCapabilities = Capability::active()->get();
        $userCapabilities = $user->plan->getCapabilitiesArray();

        foreach ($allCapabilities as $capability) {
            $hasCapability = array_key_exists($capability->key, $userCapabilities);
            $value = $userCapabilities[$capability->key] ?? null;

            if (!$hasCapability) {
                $restrictions[] = [
                    'capability' => $capability->key,
                    'name' => $capability->name,
                    'type' => 'missing',
                    'message' => "Your plan does not include {$capability->name}",
                ];
            } elseif ($capability->value_type === Capability::TYPE_INTEGER && $value !== null) {
                $restrictions[] = [
                    'capability' => $capability->key,
                    'name' => $capability->name,
                    'type' => 'limited',
                    'limit' => $value,
                    'message' => "{$capability->name} limited to {$value}",
                ];
            }
        }

        return [
            'has_plan' => true,
            'plan' => $user->plan->key,
            'restrictions' => $restrictions,
        ];
    }

    /**
     * Get a feature comparison matrix for all active plans.
     */
    public function getPlanFeatureMatrix(): array
    {
        $plans = Plan::active()->ordered()->with('capabilities')->get();
        $capabilities = Capability::active()->ordered()->get();

        $matrix = [
            'plans' => [],
            'capabilities' => [],
            'matrix' => [],
        ];

        // Build plan info
        foreach ($plans as $plan) {
            $matrix['plans'][$plan->key] = [
                'key' => $plan->key,
                'name' => $plan->name,
                'price' => $plan->price,
                'formatted_price' => $plan->getFormattedPrice(),
                'tagline' => $plan->tagline,
                'is_featured' => $plan->is_featured,
            ];
        }

        // Build capability info
        foreach ($capabilities as $capability) {
            $matrix['capabilities'][$capability->key] = [
                'key' => $capability->key,
                'name' => $capability->name,
                'description' => $capability->description,
                'category' => $capability->category,
                'value_type' => $capability->value_type,
            ];
        }

        // Build feature matrix
        foreach ($capabilities as $capability) {
            $matrix['matrix'][$capability->key] = [];

            foreach ($plans as $plan) {
                $hasCapability = $plan->hasCapability($capability->key);
                $value = $plan->getCapabilityValue($capability->key);

                $matrix['matrix'][$capability->key][$plan->key] = [
                    'has' => $hasCapability,
                    'value' => $value,
                    'display' => $hasCapability ? $capability->formatValue($value) : '-',
                ];
            }
        }

        return $matrix;
    }

    /**
     * Get plans that have a specific capability.
     */
    public function getPlansWithCapability(string $capabilityKey): Collection
    {
        return Plan::active()
            ->ordered()
            ->whereHas('capabilities', function ($query) use ($capabilityKey) {
                $query->where('key', $capabilityKey)
                    ->where('is_active', true);
            })
            ->get();
    }

    /**
     * Calculate upgrade savings/benefits when moving from one plan to another.
     */
    public function getUpgradeBenefits(Plan $fromPlan, Plan $toPlan): array
    {
        $fromCapabilities = $fromPlan->getCapabilitiesArray();
        $toCapabilities = $toPlan->getCapabilitiesArray();

        $newCapabilities = [];
        $improvedCapabilities = [];

        foreach ($toCapabilities as $key => $value) {
            if (!array_key_exists($key, $fromCapabilities)) {
                $capability = Capability::findByKey($key);
                $newCapabilities[] = [
                    'key' => $key,
                    'name' => $capability?->name ?? $key,
                    'value' => $value,
                ];
            } elseif ($fromCapabilities[$key] !== $value) {
                $capability = Capability::findByKey($key);
                $improvedCapabilities[] = [
                    'key' => $key,
                    'name' => $capability?->name ?? $key,
                    'from' => $fromCapabilities[$key],
                    'to' => $value,
                ];
            }
        }

        return [
            'price_difference' => $toPlan->price - $fromPlan->price,
            'new_capabilities' => $newCapabilities,
            'improved_capabilities' => $improvedCapabilities,
        ];
    }

    /**
     * Get the recommended plan for accessing a specific track.
     */
    public function getRecommendedPlanForTrack(Track $track): ?Plan
    {
        $requirements = $track->capabilityRequirements;

        if ($requirements->isEmpty()) {
            return null; // No requirements, any plan works
        }

        $requiredCapabilityKeys = $requirements->pluck('key')->toArray();

        // Find the cheapest plan that has all required capabilities
        return Plan::active()
            ->ordered()
            ->get()
            ->filter(function ($plan) use ($requiredCapabilityKeys) {
                foreach ($requiredCapabilityKeys as $key) {
                    if (!$plan->hasCapability($key)) {
                        return false;
                    }
                }
                return true;
            })
            ->sortBy('price')
            ->first();
    }

    /**
     * Get tracks accessible by a specific plan.
     */
    public function getAccessibleTracks(Plan $plan): Collection
    {
        $planCapabilities = $plan->getCapabilitiesArray();

        return Track::active()
            ->ordered()
            ->with('capabilityRequirements')
            ->get()
            ->filter(function ($track) use ($plan, $planCapabilities) {
                foreach ($track->capabilityRequirements as $requirement) {
                    $hasCapability = array_key_exists($requirement->key, $planCapabilities);

                    if (!$hasCapability) {
                        return false;
                    }

                    $userValue = $planCapabilities[$requirement->key];
                    $requiredValue = $requirement->pivot->required_value;

                    if ($requirement->value_type === Capability::TYPE_BOOLEAN && !$userValue) {
                        return false;
                    }

                    if ($requirement->value_type === Capability::TYPE_INTEGER && $requiredValue !== null) {
                        if ($userValue < (int) $requiredValue) {
                            return false;
                        }
                    }
                }

                return true;
            });
    }

    /**
     * Check if user can perform track switching (respecting cooldown).
     */
    public function canSwitchTrack(User $user): array
    {
        if (!$user->hasCapability('track_switching')) {
            return [
                'can_switch' => false,
                'reason' => 'Your plan does not include track switching',
                'upgrade_needed' => true,
            ];
        }

        $cooldownDays = $user->capabilityValue('track_switch_cooldown') ?? 0;

        if ($cooldownDays === 0) {
            return ['can_switch' => true];
        }

        // Check last track switch
        $lastSwitch = $user->trackEnrollments()
            ->latest('enrolled_at')
            ->first();

        if (!$lastSwitch) {
            return ['can_switch' => true];
        }

        $daysSinceSwitch = $lastSwitch->enrolled_at->diffInDays(now());

        if ($daysSinceSwitch >= $cooldownDays) {
            return ['can_switch' => true];
        }

        $daysRemaining = $cooldownDays - $daysSinceSwitch;

        return [
            'can_switch' => false,
            'reason' => "Track switching cooldown active. {$daysRemaining} days remaining.",
            'days_remaining' => $daysRemaining,
            'cooldown_ends_at' => $lastSwitch->enrolled_at->addDays($cooldownDays),
            'upgrade_needed' => false,
        ];
    }

    /**
     * Check if user can have multiple active tracks.
     */
    public function canHaveMultipleTracks(User $user): array
    {
        if ($user->hasCapability('multiple_active_tracks')) {
            return ['allowed' => true];
        }

        $activeCount = $user->trackEnrollments()
            ->where('status', 'active')
            ->count();

        return [
            'allowed' => $activeCount === 0,
            'current_count' => $activeCount,
            'reason' => $activeCount > 0
                ? 'Your plan only allows one active track. Complete or pause your current track first.'
                : null,
        ];
    }
}
