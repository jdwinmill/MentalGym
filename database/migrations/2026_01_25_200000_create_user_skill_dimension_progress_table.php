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
        Schema::create('user_skill_dimension_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drill_id')->constrained()->cascadeOnDelete();
            $table->string('dimension_key');
            $table->unsignedTinyInteger('current_level')->default(1);
            $table->timestamps();

            $table->foreign('dimension_key')
                ->references('key')
                ->on('skill_dimensions')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'drill_id', 'dimension_key'], 'user_drill_dimension_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skill_dimension_progress');
    }
};
