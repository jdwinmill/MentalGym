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
        Schema::create('user_weakness_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->foreignId('skill_level_id')->constrained('skill_levels')->cascadeOnDelete();
            $table->string('pattern_tag');
            $table->integer('occurrence_count')->default(1);
            $table->timestamp('first_detected_at');
            $table->timestamp('last_detected_at');
            $table->integer('severity_score')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'track_id', 'skill_level_id', 'pattern_tag'], 'user_weakness_patterns_unique');
            $table->index('pattern_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_weakness_patterns');
    }
};
