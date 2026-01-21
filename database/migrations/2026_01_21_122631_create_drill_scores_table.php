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
        Schema::create('drill_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('practice_mode_id')->constrained()->onDelete('cascade');
            $table->string('drill_type', 50);
            $table->string('drill_phase', 100);
            $table->boolean('is_iteration')->default(false);
            $table->json('scores');
            $table->text('user_response');
            $table->unsignedSmallInteger('word_count');
            $table->unsignedSmallInteger('response_time_seconds')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'drill_type']);
            $table->index(['user_id', 'practice_mode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drill_scores');
    }
};
