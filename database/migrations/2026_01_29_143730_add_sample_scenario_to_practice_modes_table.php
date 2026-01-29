<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_modes', function (Blueprint $table) {
            $table->text('sample_scenario')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('practice_modes', function (Blueprint $table) {
            $table->dropColumn('sample_scenario');
        });
    }
};
