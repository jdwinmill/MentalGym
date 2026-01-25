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
        Schema::table('skill_dimensions', function (Blueprint $table) {
            $table->json('improvement_tips')->nullable()->after('score_anchors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skill_dimensions', function (Blueprint $table) {
            $table->dropColumn('improvement_tips');
        });
    }
};
