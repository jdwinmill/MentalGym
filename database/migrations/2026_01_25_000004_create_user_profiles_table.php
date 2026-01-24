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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Demographics
            $table->integer('age')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('location', 100)->nullable();

            // Career Context
            $table->string('job_title', 100)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('company_size', 50)->nullable(); // startup, smb, enterprise
            $table->string('career_level', 50)->nullable(); // entry, mid, senior, executive, founder
            $table->integer('years_in_role')->nullable();
            $table->integer('years_experience')->nullable();

            // Team & Reporting Structure
            $table->boolean('manages_people')->default(false);
            $table->integer('direct_reports')->nullable();
            $table->string('reports_to_role', 100)->nullable();
            $table->string('team_composition', 50)->nullable(); // colocated, remote, hybrid, international

            // Work Environment
            $table->string('collaboration_style', 50)->nullable(); // async, meeting-heavy, mixed
            $table->json('cross_functional_teams')->nullable(); // ['engineering', 'design', 'sales']
            $table->json('communication_tools')->nullable(); // ['slack', 'email']

            // Professional Goals
            $table->json('improvement_areas')->nullable(); // ['communication', 'leadership']
            $table->json('upcoming_challenges')->nullable(); // ['new_role', 'first_time_manager']

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
