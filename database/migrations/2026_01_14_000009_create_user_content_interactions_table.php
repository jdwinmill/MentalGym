<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_content_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_lesson_attempt_id')
                ->constrained('user_lesson_attempts')
                ->cascadeOnDelete();
            $table->foreignId('lesson_content_block_id')
                ->constrained('lesson_content_blocks')
                ->cascadeOnDelete();
            $table->string('interaction_type');
            $table->json('interaction_data')->nullable();
            $table->timestamp('interacted_at');
            $table->timestamps();

            $table->index('interaction_type');
            $table->index('interacted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_content_interactions');
    }
};
