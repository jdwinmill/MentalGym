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
        // Drop the old drill_scores table (no longer used)
        Schema::dropIfExists('drill_scores');

        // Remove redundant columns from blind_spots
        // These are duplicated across all dimension rows for a single drill
        Schema::table('blind_spots', function (Blueprint $table) {
            $table->dropColumn(['scenario', 'user_response', 'feedback']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate drill_scores table
        Schema::create('drill_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->string('drill_phase')->nullable();
            $table->json('scores');
            $table->boolean('is_iteration')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // Re-add columns to blind_spots
        Schema::table('blind_spots', function (Blueprint $table) {
            $table->text('scenario')->nullable();
            $table->text('user_response')->nullable();
            $table->text('feedback')->nullable();
        });
    }
};
