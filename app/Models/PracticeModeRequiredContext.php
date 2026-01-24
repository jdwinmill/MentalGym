<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeModeRequiredContext extends Model
{
    protected $table = 'practice_mode_required_context';

    protected $fillable = [
        'practice_mode_id',
        'profile_field',
    ];

    public function practiceMode(): BelongsTo
    {
        return $this->belongsTo(PracticeMode::class);
    }
}
