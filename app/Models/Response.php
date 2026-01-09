<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = [
        'question_id',
        'response_text',
        'rating',
        'feedback_text',
        'anonymous_session_id',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
