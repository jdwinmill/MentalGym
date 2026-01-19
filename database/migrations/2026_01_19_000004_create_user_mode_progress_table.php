<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_mode_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('current_level')->default(1); // 1-5
            $table->unsignedInteger('total_sessions')->default(0);
            $table->unsignedInteger('total_time_seconds')->default(0);
            $table->timestamp('last_session_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'practice_mode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_mode_progress');
    }
};
