<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
        'plan',
        'is_admin',
        'trial_ends_at',
        'email_preferences',
    ];

    public function isAdmin(): bool
    {
        return $this->is_admin || $this->role === 'admin';
    }

    // ─────────────────────────────────────────────────────────────
    // Plan Config (Simple String-based Plan)
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the plan configuration for the user's current plan tier.
     */
    public function planConfig(): array
    {
        return config("plans.{$this->plan}", config('plans.free'));
    }

    /**
     * Get the user's plan tier name.
     */
    public function getPlanTier(): string
    {
        return $this->plan ?? 'free';
    }

    // ─────────────────────────────────────────────────────────────
    // Access Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Check if user has access to the app.
     * Access is granted if: admin, on valid trial, or has a paid plan.
     */
    public function hasAccess(): bool
    {
        // Admins always have access
        if ($this->isAdmin()) {
            return true;
        }

        // Check if on valid trial
        if ($this->isOnTrial()) {
            return true;
        }

        // Check if has a paid plan
        return $this->hasPaidPlan();
    }

    /**
     * Check if the user has a paid plan (pro or unlimited).
     */
    public function hasPaidPlan(): bool
    {
        return in_array($this->plan, ['pro', 'unlimited']);
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
     * Get the user's subscription status for display.
     */
    public function getSubscriptionStatus(): string
    {
        if ($this->isOnTrial()) {
            $days = $this->trialDaysRemaining();
            return "Trial ({$days} days left)";
        }

        if ($this->hasPaidPlan()) {
            return ucfirst($this->plan);
        }

        return 'Free';
    }

    // ─────────────────────────────────────────────────────────────
    // Plan Management Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Set the user's plan tier.
     */
    public function setPlan(string $plan): void
    {
        $this->plan = $plan;
        $this->save();
    }

    /**
     * Start a trial period.
     */
    public function startTrial(int $durationDays = 14): void
    {
        $this->trial_ends_at = now()->addDays($durationDays);
        $this->save();
    }

    /**
     * Extend the trial period.
     */
    public function extendTrial(int $days): void
    {
        $currentEnd = $this->trial_ends_at ?? now();
        $newEnd = $currentEnd->isFuture()
            ? $currentEnd->addDays($days)
            : now()->addDays($days);

        $this->trial_ends_at = $newEnd;
        $this->save();
    }

    // ─────────────────────────────────────────────────────────────
    // Training & Practice Mode Relationships
    // ─────────────────────────────────────────────────────────────

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    public function modeProgress(): HasMany
    {
        return $this->hasMany(UserModeProgress::class);
    }

    public function streak(): HasOne
    {
        return $this->hasOne(UserStreak::class);
    }

    public function dailyUsage(): HasMany
    {
        return $this->hasMany(DailyUsage::class);
    }

    /**
     * Get or create progress for a specific practice mode.
     */
    public function getProgressForMode(PracticeMode $mode): UserModeProgress
    {
        return $this->modeProgress()->firstOrCreate(
            ['practice_mode_id' => $mode->id],
            ['current_level' => 1, 'total_sessions' => 0, 'total_time_seconds' => 0]
        );
    }

    /**
     * Get progress for a specific practice mode (without creating).
     */
    public function progressInMode(PracticeMode $mode): ?UserModeProgress
    {
        return $this->modeProgress()
            ->where('practice_mode_id', $mode->id)
            ->first();
    }

    /**
     * Get or create the user's streak record.
     */
    public function getOrCreateStreak(): UserStreak
    {
        return $this->streak ?? UserStreak::create([
            'user_id' => $this->id,
            'current_streak' => 0,
            'longest_streak' => 0,
        ]);
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
            'trial_ends_at' => 'datetime',
            'is_admin' => 'boolean',
            'email_preferences' => 'array',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Email Preferences
    // ─────────────────────────────────────────────────────────────

    public function blindSpotEmails(): HasMany
    {
        return $this->hasMany(BlindSpotEmail::class);
    }

    /**
     * Check if a specific email type is enabled.
     */
    public function wantsEmail(string $type): bool
    {
        $preferences = $this->email_preferences ?? [];

        // Default to true if not explicitly set to false
        return ($preferences[$type] ?? true) !== false;
    }

    /**
     * Get the user's first name for personalization.
     */
    public function getFirstNameAttribute(): string
    {
        $nameParts = explode(' ', $this->name);
        return $nameParts[0] ?? $this->name;
    }
}
