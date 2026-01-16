<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LessonContentBlock model representing flexible content containers.
 *
 * @property int $id
 * @property int $lesson_id
 * @property string $block_type
 * @property array $content
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class LessonContentBlock extends Model
{
    protected $fillable = [
        'lesson_id',
        'block_type',
        'content',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Block type constants for consistency.
     */
    public const TYPE_PRINCIPLE_TEXT = 'principle_text';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const TYPE_IMAGE = 'image';
    public const TYPE_INSTRUCTION_TEXT = 'instruction_text';

    /**
     * Get the lesson this block belongs to.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get all questions related to this content block.
     */
    public function relatedQuestions(): HasMany
    {
        return $this->hasMany(LessonQuestion::class, 'related_block_id');
    }

    /**
     * Get all user interactions with this content block.
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(UserContentInteraction::class);
    }

    /**
     * Check if this block is of a specific type.
     */
    public function isType(string $type): bool
    {
        return $this->block_type === $type;
    }

    /**
     * Check if this is an audio block.
     */
    public function isAudio(): bool
    {
        return $this->isType(self::TYPE_AUDIO);
    }

    /**
     * Check if this is a video block.
     */
    public function isVideo(): bool
    {
        return $this->isType(self::TYPE_VIDEO);
    }

    /**
     * Check if this is an image block.
     */
    public function isImage(): bool
    {
        return $this->isType(self::TYPE_IMAGE);
    }

    /**
     * Check if this is a text block.
     */
    public function isText(): bool
    {
        return in_array($this->block_type, [
            self::TYPE_PRINCIPLE_TEXT,
            self::TYPE_INSTRUCTION_TEXT,
        ]);
    }

    /**
     * Check if this is a media block (audio, video, or image).
     */
    public function isMedia(): bool
    {
        return in_array($this->block_type, [
            self::TYPE_AUDIO,
            self::TYPE_VIDEO,
            self::TYPE_IMAGE,
        ]);
    }

    /**
     * Get a specific content field.
     */
    public function getContentField(string $key, mixed $default = null): mixed
    {
        return $this->content[$key] ?? $default;
    }

    /**
     * Get the URL for media content blocks.
     */
    public function getMediaUrl(): ?string
    {
        // Audio blocks use 'audio_url' key
        if ($this->isAudio()) {
            return $this->getContentField('audio_url');
        }
        return $this->getContentField('url');
    }

    /**
     * Get the text content for text blocks.
     */
    public function getText(): ?string
    {
        return $this->getContentField('text');
    }
}
