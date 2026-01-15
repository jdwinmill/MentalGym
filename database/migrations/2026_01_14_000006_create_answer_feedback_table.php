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
        Schema::create('answer_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('lesson_questions')->cascadeOnDelete();
            $table->foreignId('answer_option_id')
                ->nullable()
                ->constrained('answer_options')
                ->nullOnDelete();
            $table->text('feedback_text');
            $table->string('pattern_tag')->nullable();
            $table->string('severity')->nullable();
            $table->timestamps();

            $table->index('pattern_tag');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_feedback');
    }
};
