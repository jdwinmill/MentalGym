<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drill_insight', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insight_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['drill_id', 'insight_id']);
            $table->index(['drill_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drill_insight');
    }
};
