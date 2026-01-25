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
        Schema::create('blind_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drill_id')->constrained()->cascadeOnDelete();
            $table->string('dimension_key');
            $table->unsignedTinyInteger('score');
            $table->text('scenario');
            $table->text('user_response');
            $table->text('feedback');
            $table->timestamp('created_at')->nullable();

            $table->foreign('dimension_key')->references('key')->on('skill_dimensions')->cascadeOnDelete();
            $table->index(['user_id', 'dimension_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blind_spots');
    }
};
