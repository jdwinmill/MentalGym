<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('practice_mode_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('training_session_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('cache_creation_input_tokens')->default(0);
            $table->integer('cache_read_input_tokens')->default(0);
            $table->string('model', 50);
            $table->integer('response_time_ms')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at');

            $table->index('user_id');
            $table->index('practice_mode_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
