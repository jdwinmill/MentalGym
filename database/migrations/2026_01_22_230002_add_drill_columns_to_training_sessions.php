<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            // Simplified state tracking
            $table->integer('drill_index')->default(0)->after('status');
            $table->string('phase')->default('scenario')->after('drill_index'); // 'scenario', 'responding', 'feedback', 'complete'

            // Current drill state (for resume on refresh)
            $table->text('current_scenario')->nullable()->after('phase');
            $table->text('current_task')->nullable()->after('current_scenario');
            $table->json('current_options')->nullable()->after('current_task'); // For MC drills
            $table->integer('current_correct_option')->nullable()->after('current_options');

            // Score accumulation
            $table->json('drill_scores')->nullable()->after('current_correct_option'); // [{drill_id, drill_name, score}, ...]
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'drill_index',
                'phase',
                'current_scenario',
                'current_task',
                'current_options',
                'current_correct_option',
                'drill_scores',
            ]);
        });
    }
};
