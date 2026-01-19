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
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_lesson_attempt_id')
                ->constrained('user_lesson_attempts')
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('lesson_questions')
                ->cascadeOnDelete();
            $table->foreignId('answer_option_id')
                ->nullable()
                ->constrained('answer_options')
                ->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct');
            $table->integer('time_to_answer_seconds')->nullable();
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->index('is_correct');
            $table->index('answered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};
