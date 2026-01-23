<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_completed_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->integer('drills_completed');
            $table->integer('total_duration_seconds');
            $table->json('scores')->nullable(); // [{drill_id, drill_name, score}, ...]
            $table->timestamp('completed_at');
            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'completed_at']);
            $table->index(['practice_mode_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_completed_events');
    }
};
