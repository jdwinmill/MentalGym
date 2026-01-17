<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'track_switching', 'ai_analytics', etc.
            $table->string('name'); // Human-readable: 'Track Switching'
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // 'access', 'limits', 'features', 'support'
            $table->string('value_type')->default('boolean'); // boolean, integer, string, json
            $table->text('default_value')->nullable(); // Default when capability exists
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capabilities');
    }
};
