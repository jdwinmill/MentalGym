<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->string('category', 20)->default('skill')->after('slug');
            $table->unsignedInteger('display_order')->default(0)->after('category');
        });

        // Add index on category for grouped queries
        Schema::table('tags', function (Blueprint $table) {
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'display_order']);
        });
    }
};
