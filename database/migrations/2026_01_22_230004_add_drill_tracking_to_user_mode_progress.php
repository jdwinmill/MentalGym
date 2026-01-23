<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_mode_progress', function (Blueprint $table) {
            $table->unsignedInteger('total_drills_completed')->default(0)->after('total_exchanges');
            $table->unsignedInteger('sessions_at_current_level')->default(0)->after('exchanges_at_current_level');
        });
    }

    public function down(): void
    {
        Schema::table('user_mode_progress', function (Blueprint $table) {
            $table->dropColumn(['total_drills_completed', 'sessions_at_current_level']);
        });
    }
};
