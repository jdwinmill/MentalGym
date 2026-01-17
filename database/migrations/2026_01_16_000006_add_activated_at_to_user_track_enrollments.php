<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_track_enrollments', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('enrolled_at');
        });

        // Set activated_at to enrolled_at for existing active enrollments
        DB::table('user_track_enrollments')
            ->where('status', 'active')
            ->whereNull('activated_at')
            ->update(['activated_at' => DB::raw('enrolled_at')]);
    }

    public function down(): void
    {
        Schema::table('user_track_enrollments', function (Blueprint $table) {
            $table->dropColumn('activated_at');
        });
    }
};
