<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_mode_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['practice_mode_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_mode_tag');
    }
};
