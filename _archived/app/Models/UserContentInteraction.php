<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserContentInteraction model representing engagement with content blocks.
 *
 * @property int $id
 * @property int $user_lesson_attempt_id
 * @property int $lesson_content_block_id
 * @property string $interaction_type
 * @property array|null $interaction_data
 * @property \Illuminate\Support\Carbon $interacted_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class UserContentInteraction extends Model
{
    protected $fillable = [
        'user_lesson_attempt_id',
        'lesson_content_block_id',
        'interaction_type',
        'interaction_data',
        'interacted_at',
    ];

    protected function casts(): array
    {
        return [
            'interaction_data' => 'array',
            'interacted_at' => 'datetime',
        ];
    }

    /**
     * Interaction type constants.
     */
    public const TYPE_AUDIO_PLAYED = 'audio_played';

    public const TYPE_VIDEO_WATCHED = 'video_watched';

    public const TYPE_CONTENT_VIEWED = 'content_viewed';

    /**
     * Get the lesson attempt for this interaction.
     */
    public function lessonAttempt(): BelongsTo
    {
        return $this->belongsTo(UserLessonAttempt::class, 'user_lesson_attempt_id');
    }

    /**
     * Get the content block for this interaction.
     */
    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(LessonContentBlock::class, 'lesson_content_block_id');
    }

    /**
     * Get a specific data field from interaction_data.
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return $this->interaction_data[$key] ?? $default;
    }

    /**
     * Set a specific data field in interaction_data.
     */
    public function setData(string $key, mixed $value): self
    {
        $data = $this->interaction_data ?? [];
        $data[$key] = $value;
        $this->interaction_data = $data;

        return $this;
    }

    /**
     * Get the play count for audio/video content.
     */
    public function getPlayCount(): int
    {
        return $this->getData('play_count', 0);
    }

    /**
     * Increment the play count for audio/video content.
     */
    public function incrementPlayCount(): self
    {
        return $this->setData('play_count', $this->getPlayCount() + 1);
    }

    /**
     * Get the completion percentage for media content.
     */
    public function getCompletionPercentage(): float
    {
        return $this->getData('completion_percentage', 0.0);
    }

    /**
     * Set the completion percentage for media content.
     */
    public function setCompletionPercentage(float $percentage): self
    {
        return $this->setData('completion_percentage', min(100, max(0, $percentage)));
    }

    /**
     * Check if the content was fully completed.
     */
    public function isFullyCompleted(): bool
    {
        return $this->getCompletionPercentage() >= 100;
    }

    /**
     * Check if this is an audio interaction.
     */
    public function isAudioInteraction(): bool
    {
        return $this->interaction_type === self::TYPE_AUDIO_PLAYED;
    }

    /**
     * Check if this is a video interaction.
     */
    public function isVideoInteraction(): bool
    {
        return $this->interaction_type === self::TYPE_VIDEO_WATCHED;
    }
}
