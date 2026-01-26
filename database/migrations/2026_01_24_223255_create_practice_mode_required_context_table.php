<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('practice_mode_required_context')) {
            Schema::create('practice_mode_required_context', function (Blueprint $table) {
                $table->id();
                $table->foreignId('practice_mode_id')->constrained()->cascadeOnDelete();
                $table->string('profile_field', 50);
                $table->timestamps();

                $table->unique(['practice_mode_id', 'profile_field'], 'pm_required_context_unique');
            });
        } else {
            // Table exists but index might be missing (from failed previous migration)
            // Try to add the index, ignore if it already exists
            try {
                Schema::table('practice_mode_required_context', function (Blueprint $table) {
                    $table->unique(['practice_mode_id', 'profile_field'], 'pm_required_context_unique');
                });
            } catch (\Exception $e) {
                // Index already exists, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_mode_required_context');
    }
};
