<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_capability_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained()->cascadeOnDelete();
            $table->text('required_value')->nullable(); // Minimum value needed (for integers)
            $table->timestamps();

            $table->unique(['track_id', 'capability_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_capability_requirements');
    }
};
