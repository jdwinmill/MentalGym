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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('age', 'birth_year');
            $table->renameColumn('location', 'zip_code');
        });

        // Update column constraints
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->integer('birth_year')->nullable()->change();
            $table->string('zip_code', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->renameColumn('birth_year', 'age');
            $table->renameColumn('zip_code', 'location');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->integer('age')->nullable()->change();
            $table->string('location', 100)->nullable()->change();
        });
    }
};
