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
        Schema::create('skill_dimensions', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->json('score_anchors');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_dimensions');
    }
};
