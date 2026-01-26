<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks (works for both MySQL and SQLite)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        Schema::dropIfExists('answer_feedback');
        Schema::dropIfExists('answer_options');
        Schema::dropIfExists('user_answers');
        Schema::dropIfExists('track_capability_requirements');
        Schema::dropIfExists('plan_capabilities');
        Schema::dropIfExists('capabilities');
        Schema::dropIfExists('drill_insight');
        Schema::dropIfExists('user_lesson_attempts');
        Schema::dropIfExists('lesson_content_blocks');
        Schema::dropIfExists('lesson_questions');
        Schema::dropIfExists('lessons');

        // Drop plan_id foreign key from users before dropping plans table
        if (Schema::hasColumn('users', 'plan_id')) {
            Schema::table('users', function ($table) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn(['plan_id', 'plan_started_at', 'plan_expires_at', 'subscription_status']);
            });
        }

        Schema::dropIfExists('plans');
        Schema::dropIfExists('practice_mode_tag');
        Schema::dropIfExists('question_tag');
        Schema::dropIfExists('skill_levels');
        Schema::dropIfExists('user_track_enrollments');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('user_content_interactions');
        Schema::dropIfExists('user_weakness_patterns');

        // Re-enable foreign key checks
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
