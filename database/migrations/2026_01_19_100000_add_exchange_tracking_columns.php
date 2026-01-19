<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add exchange_count to training_sessions
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->unsignedInteger('exchange_count')->default(0)->after('level_at_start');
        });

        // Add exchange tracking columns to user_mode_progress
        Schema::table('user_mode_progress', function (Blueprint $table) {
            $table->unsignedInteger('total_exchanges')->default(0)->after('current_level');
            $table->unsignedInteger('exchanges_at_current_level')->default(0)->after('total_exchanges');
            $table->timestamp('last_trained_at')->nullable()->after('last_session_at');
        });

        // Add parsed_type to session_messages for quick card type lookup
        Schema::table('session_messages', function (Blueprint $table) {
            $table->string('parsed_type', 50)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('exchange_count');
        });

        Schema::table('user_mode_progress', function (Blueprint $table) {
            $table->dropColumn(['total_exchanges', 'exchanges_at_current_level', 'last_trained_at']);
        });

        Schema::table('session_messages', function (Blueprint $table) {
            $table->dropColumn('parsed_type');
        });
    }
};
