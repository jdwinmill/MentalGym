<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlindSpot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'drill_id',
        'dimension_key',
        'score',
        'suggestion',
        'created_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'created_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drill(): BelongsTo
    {
        return $this->belongsTo(Drill::class);
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(SkillDimension::class, 'dimension_key', 'key');
    }
}
