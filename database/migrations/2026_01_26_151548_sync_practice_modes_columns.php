<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_modes', function (Blueprint $table) {
            if (! Schema::hasColumn('practice_modes', 'tagline')) {
                $table->string('tagline')->nullable()->after('name');
            }
            if (! Schema::hasColumn('practice_modes', 'config')) {
                $table->json('config')->nullable()->after('instruction_set');
            }
            if (! Schema::hasColumn('practice_modes', 'required_plan')) {
                $table->string('required_plan', 20)->nullable()->after('config');
            }
        });
    }

    public function down(): void
    {
        // No-op: columns may have existed before this migration
    }
};
