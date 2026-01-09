<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'text',
        'principle',
        'intent_tag',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
