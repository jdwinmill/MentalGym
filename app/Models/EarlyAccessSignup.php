<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyAccessSignup extends Model
{
    protected $fillable = [
        'email',
        'selected_topics',
        'referrer',
        'utm_params',
        'timezone',
        'device_type',
        'locale',
    ];

    protected $casts = [
        'selected_topics' => 'array',
        'utm_params' => 'array',
    ];
}
