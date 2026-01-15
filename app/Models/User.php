<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
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
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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
    public function enrollInTrack(Track $track): UserTrackEnrollment
    {
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
        ];
    }
}
