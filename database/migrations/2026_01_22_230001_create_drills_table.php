<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('scenario_instruction_set');
            $table->text('evaluation_instruction_set');
            $table->integer('position')->default(0);
            $table->integer('timer_seconds')->nullable();
            $table->string('input_type')->default('text'); // 'text', 'multiple_choice'
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index(['practice_mode_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drills');
    }
};
