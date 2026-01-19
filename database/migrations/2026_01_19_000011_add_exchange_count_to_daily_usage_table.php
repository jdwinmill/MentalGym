<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_usage', function (Blueprint $table) {
            $table->unsignedInteger('exchange_count')->default(0)->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('daily_usage', function (Blueprint $table) {
            $table->dropColumn('exchange_count');
        });
    }
};
