<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // user, assistant
            $table->text('content'); // The message content
            $table->json('metadata')->nullable(); // For storing parsed JSON response types, etc.
            $table->unsignedInteger('sequence')->default(0); // Order in conversation
            $table->timestamps();

            $table->index(['training_session_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_messages');
    }
};
