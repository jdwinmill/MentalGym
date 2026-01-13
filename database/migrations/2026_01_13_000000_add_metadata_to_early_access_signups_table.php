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
        Schema::table('early_access_signups', function (Blueprint $table) {
            $table->string('referrer')->nullable()->after('selected_topics');
            $table->json('utm_params')->nullable()->after('referrer');
            $table->string('timezone')->nullable()->after('utm_params');
            $table->string('device_type', 20)->nullable()->after('timezone');
            $table->string('locale', 10)->nullable()->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('early_access_signups', function (Blueprint $table) {
            $table->dropColumn(['referrer', 'utm_params', 'timezone', 'device_type', 'locale']);
        });
    }
};
