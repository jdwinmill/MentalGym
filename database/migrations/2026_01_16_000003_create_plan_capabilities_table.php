<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('capability_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable(); // Override default value for this plan
            $table->timestamps();

            $table->unique(['plan_id', 'capability_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_capabilities');
    }
};
