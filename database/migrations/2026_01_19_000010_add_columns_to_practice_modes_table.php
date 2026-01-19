<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_modes', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('name');
            $table->json('config')->nullable()->after('instruction_set');
            $table->string('required_plan', 20)->nullable()->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('practice_modes', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'config', 'required_plan']);
        });
    }
};
