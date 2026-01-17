<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'essentials', 'all_access', etc.
            $table->string('name'); // Marketing name: 'Essentials', 'All Access'
            $table->text('description')->nullable();
            $table->string('tagline')->nullable(); // Short marketing tagline
            $table->decimal('price', 8, 2); // Monthly price
            $table->string('billing_interval')->default('monthly'); // monthly, yearly, lifetime
            $table->decimal('yearly_price', 8, 2)->nullable(); // Optional yearly price
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Highlight this plan
            $table->json('metadata')->nullable(); // Flexible additional data
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
