<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'title',
        'text',
        'prompt',
        'task',
        'principle',
        'intent_tag',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
