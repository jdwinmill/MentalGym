<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlindSpotEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_type',
        'week_number',
        'year',
        'analysis_snapshot',
        'subject_line',
        'sent_at',
        'opened_at',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis_snapshot' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWeeklyReport($query)
    {
        return $query->where('email_type', 'weekly_report');
    }

    public function scopeForWeek($query, int $weekNumber, int $year)
    {
        return $query->where('week_number', $weekNumber)->where('year', $year);
    }
}
