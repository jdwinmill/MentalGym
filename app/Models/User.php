<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    // Subscription status constants
    public const STATUS_NONE = 'none';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plan_id',
        'plan_started_at',
        'plan_expires_at',
        'trial_ends_at',
        'subscription_status',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ─────────────────────────────────────────────────────────────
    // Plan Relationship
    // ─────────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Capability Checking Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if user has a specific capability through their plan.
     */
    public function hasCapability(string $key): bool
    {
        if (!$this->hasActivePlan()) {
            return false;
        }

        return $this->plan->hasCapability($key);
    }

    /**
     * Get the value of a capability for this user's plan.
     */
    public function capabilityValue(string $key): mixed
    {
        if (!$this->hasActivePlan()) {
            return null;
        }

        return $this->plan->getCapabilityValue($key);
    }

    /**
     * Check if user can access a specific track based on capability requirements.
     */
    public function canAccessTrack(Track $track): bool
    {
        // Admins can access everything
        if ($this->isAdmin()) {
            return true;
        }

        // Check if track has any capability requirements
        $requirements = $track->capabilityRequirements;

        if ($requirements->isEmpty()) {
            return true; // No requirements = publicly accessible
        }

        if (!$this->hasActivePlan()) {
            return false;
        }

        foreach ($requirements as $capability) {
            $requiredValue = $capability->pivot->required_value;
            $userValue = $this->capabilityValue($capability->key);

            // If user doesn't have this capability at all
            if ($userValue === null) {
                return false;
            }

            // For boolean capabilities, just check if they have it
            if ($capability->value_type === Capability::TYPE_BOOLEAN) {
                if (!$userValue) {
                    return false;
                }
                continue;
            }

            // For integer capabilities, check if user meets the minimum
            if ($capability->value_type === Capability::TYPE_INTEGER && $requiredValue !== null) {
                if ($userValue < (int) $requiredValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get capabilities the user is missing to access a track.
     */
    public function getMissingCapabilitiesForTrack(Track $track): array
    {
        $missing = [];
        $requirements = $track->capabilityRequirements;

        foreach ($requirements as $capability) {
            $userValue = $this->capabilityValue($capability->key);
            $requiredValue = $capability->pivot->required_value;

            if ($userValue === null) {
                $missing[] = [
                    'capability' => $capability,
                    'required_value' => $requiredValue,
                    'user_value' => null,
                ];
                continue;
            }

            if ($capability->value_type === Capability::TYPE_BOOLEAN && !$userValue) {
                $missing[] = [
                    'capability' => $capability,
                    'required_value' => true,
                    'user_value' => false,
                ];
            }

            if ($capability->value_type === Capability::TYPE_INTEGER && $requiredValue !== null) {
                if ($userValue < (int) $requiredValue) {
                    $missing[] = [
                        'capability' => $capability,
                        'required_value' => $requiredValue,
                        'user_value' => $userValue,
                    ];
                }
            }
        }

        return $missing;
    }

    // ─────────────────────────────────────────────────────────────
    // Plan Status Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if the user has an active (non-expired) plan.
     */
    public function hasActivePlan(): bool
    {
        if (!$this->plan_id) {
            return false;
        }

        // Check if on trial
        if ($this->isOnTrial()) {
            return true;
        }

        // Check subscription status
        if (!in_array($this->subscription_status, [self::STATUS_ACTIVE, self::STATUS_TRIAL])) {
            return false;
        }

        // Check expiration
        return !$this->isPlanExpired();
    }

    /**
     * Check if user's plan has expired.
     */
    public function isPlanExpired(): bool
    {
        if (!$this->plan_expires_at) {
            return false; // No expiration set = not expired
        }

        return $this->plan_expires_at->isPast();
    }

    /**
     * Check if user is currently on trial.
     */
    public function isOnTrial(): bool
    {
        if (!$this->trial_ends_at) {
            return false;
        }

        return $this->trial_ends_at->isFuture();
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->isOnTrial()) {
            return null;
        }

        return (int) now()->diffInDays($this->trial_ends_at);
    }

    /**
     * Get days remaining in subscription.
     */
    public function subscriptionDaysRemaining(): ?int
    {
        if (!$this->plan_expires_at || $this->isPlanExpired()) {
            return null;
        }

        return (int) now()->diffInDays($this->plan_expires_at);
    }

    // ─────────────────────────────────────────────────────────────
    // Plan Management Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Assign a plan to this user.
     */
    public function assignPlan(Plan $plan, ?int $durationDays = null): void
    {
        $this->plan_id = $plan->id;
        $this->plan_started_at = now();
        $this->subscription_status = self::STATUS_ACTIVE;

        if ($durationDays) {
            $this->plan_expires_at = now()->addDays($durationDays);
        } else {
            $this->plan_expires_at = null;
        }

        $this->save();
    }

    /**
     * Start a trial for a plan.
     */
    public function startTrial(Plan $plan, int $durationDays = 14): void
    {
        $this->plan_id = $plan->id;
        $this->plan_started_at = now();
        $this->trial_ends_at = now()->addDays($durationDays);
        $this->subscription_status = self::STATUS_TRIAL;
        $this->save();
    }

    /**
     * Cancel the current subscription.
     */
    public function cancelSubscription(): void
    {
        $this->subscription_status = self::STATUS_CANCELLED;
        $this->save();
    }

    /**
     * Remove the plan from this user.
     */
    public function removePlan(): void
    {
        $this->plan_id = null;
        $this->plan_started_at = null;
        $this->plan_expires_at = null;
        $this->trial_ends_at = null;
        $this->subscription_status = self::STATUS_NONE;
        $this->save();
    }

    /**
     * Get all track enrollments for this user.
     */
    public function trackEnrollments(): HasMany
    {
        return $this->hasMany(UserTrackEnrollment::class);
    }

    /**
     * Get all lesson attempts for this user.
     */
    public function lessonAttempts(): HasMany
    {
        return $this->hasMany(UserLessonAttempt::class);
    }

    /**
     * Get all weakness patterns for this user.
     */
    public function weaknessPatterns(): HasMany
    {
        return $this->hasMany(UserWeaknessPattern::class);
    }

    /**
     * Get active track enrollments.
     */
    public function activeEnrollments(): HasMany
    {
        return $this->trackEnrollments()->where('status', 'active');
    }

    /**
     * Check if the user is enrolled in a specific track.
     */
    public function isEnrolledIn(Track $track): bool
    {
        return $this->trackEnrollments()
            ->where('track_id', $track->id)
            ->whereIn('status', ['active', 'paused'])
            ->exists();
    }

    /**
     * Enroll the user in a track.
     */
    /**
     * Get count of active track enrollments.
     */
    public function activeTrackCount(): int
    {
        return $this->trackEnrollments()->where('status', 'active')->count();
    }

    /**
     * Check if user can enroll in another track based on max_active_tracks capability.
     */
    public function canEnrollInTrack(Track $track): array
    {
        // Already enrolled? Allow (will just return existing)
        if ($this->isEnrolledIn($track)) {
            return ['allowed' => true];
        }

        // Admins bypass limits
        if ($this->isAdmin()) {
            return ['allowed' => true];
        }

        // No plan = 1 track allowed by default
        $maxTracks = $this->capabilityValue('max_active_tracks') ?? 1;
        $currentCount = $this->activeTrackCount();

        if ($currentCount >= $maxTracks) {
            return [
                'allowed' => false,
                'reason' => "Your plan allows {$maxTracks} active track(s). You currently have {$currentCount}.",
                'current_count' => $currentCount,
                'max_allowed' => $maxTracks,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Enroll the user in a track.
     *
     * @throws \Exception if user has reached max active tracks limit
     */
    public function enrollInTrack(Track $track): UserTrackEnrollment
    {
        $check = $this->canEnrollInTrack($track);

        if (!$check['allowed']) {
            throw new \Exception($check['reason']);
        }

        return UserTrackEnrollment::firstOrCreate(
            [
                'user_id' => $this->id,
                'track_id' => $track->id,
            ],
            [
                'current_skill_level_id' => $track->getFirstSkillLevel()?->id,
                'enrolled_at' => now(),
                'status' => 'active',
            ]
        );
    }

    /**
     * Get the most recently activated track enrollment.
     */
    public function getLastActivatedEnrollment(): ?UserTrackEnrollment
    {
        return $this->trackEnrollments()
            ->whereNotNull('activated_at')
            ->orderByDesc('activated_at')
            ->first();
    }

    /**
     * Check if user can switch/activate a different track based on cooldown.
     */
    public function canSwitchTrack(Track $newTrack): array
    {
        // Admins bypass cooldown
        if ($this->isAdmin()) {
            return ['allowed' => true];
        }

        // Check if already enrolled and active in this track
        $existingEnrollment = $this->trackEnrollments()
            ->where('track_id', $newTrack->id)
            ->first();

        if ($existingEnrollment && $existingEnrollment->isActive()) {
            return ['allowed' => true, 'already_active' => true];
        }

        // Get cooldown requirement
        $cooldownDays = $this->capabilityValue('track_switch_cooldown') ?? 0;

        // No cooldown required
        if ($cooldownDays === 0) {
            return ['allowed' => true];
        }

        // Check last activation
        $lastActivation = $this->getLastActivatedEnrollment();

        if (!$lastActivation || !$lastActivation->activated_at) {
            return ['allowed' => true]; // First activation, no cooldown
        }

        $daysSinceSwitch = (int) $lastActivation->activated_at->diffInDays(now());

        if ($daysSinceSwitch >= $cooldownDays) {
            return ['allowed' => true];
        }

        $daysRemaining = (int) ceil($cooldownDays - $daysSinceSwitch);

        return [
            'allowed' => false,
            'reason' => "You can switch tracks in {$daysRemaining} day(s).",
            'days_remaining' => $daysRemaining,
            'cooldown_ends_at' => $lastActivation->activated_at->addDays($cooldownDays)->toDateString(),
            'last_switch' => $lastActivation->activated_at->toDateString(),
        ];
    }

    /**
     * Activate a track (enroll if needed, set as active, handle cooldown).
     *
     * @throws \Exception if cooldown not met or enrollment limit reached
     */
    public function activateTrack(Track $track): UserTrackEnrollment
    {
        // Check cooldown
        $cooldownCheck = $this->canSwitchTrack($track);
        if (!$cooldownCheck['allowed']) {
            throw new \Exception($cooldownCheck['reason']);
        }

        // Get or create enrollment
        $enrollment = $this->trackEnrollments()
            ->where('track_id', $track->id)
            ->first();

        if (!$enrollment) {
            $enrollment = UserTrackEnrollment::create([
                'user_id' => $this->id,
                'track_id' => $track->id,
                'current_skill_level_id' => $track->getFirstSkillLevel()?->id,
                'enrolled_at' => now(),
                'status' => UserTrackEnrollment::STATUS_ACTIVE,
            ]);
        }

        // If user can only have 1 active track, pause all other active enrollments
        $maxActiveTracks = $this->capabilityValue('max_active_tracks') ?? 1;
        if ($maxActiveTracks === 1) {
            $this->trackEnrollments()
                ->where('status', 'active')
                ->where('track_id', '!=', $track->id)
                ->each(fn($e) => $e->pause());
        }

        // Activate (updates activated_at)
        $enrollment->activate();

        return $enrollment;
    }

    /**
     * Get track switch cooldown info for UI display.
     */
    public function getTrackSwitchCooldownInfo(): array
    {
        $cooldownDays = $this->capabilityValue('track_switch_cooldown') ?? 0;
        $lastActivation = $this->getLastActivatedEnrollment();

        if ($cooldownDays === 0) {
            return [
                'has_cooldown' => false,
                'can_switch' => true,
            ];
        }

        if (!$lastActivation || !$lastActivation->activated_at) {
            return [
                'has_cooldown' => true,
                'cooldown_days' => $cooldownDays,
                'can_switch' => true,
            ];
        }

        $daysSinceSwitch = (int) $lastActivation->activated_at->diffInDays(now());
        $canSwitch = $daysSinceSwitch >= $cooldownDays;
        $daysRemaining = (int) max(0, ceil($cooldownDays - $daysSinceSwitch));

        return [
            'has_cooldown' => true,
            'cooldown_days' => $cooldownDays,
            'can_switch' => $canSwitch,
            'days_remaining' => $daysRemaining,
            'last_switch' => $lastActivation->activated_at->toDateString(),
            'cooldown_ends_at' => $lastActivation->activated_at->addDays($cooldownDays)->toDateString(),
            'current_track_id' => $lastActivation->track_id,
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'plan_started_at' => 'datetime',
            'plan_expires_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }
}
