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
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->timestamp('completed_seen_at')->nullable()->after('completion_remarks');
        });

        // Mark all already-completed requests as seen so existing data doesn't flood the bell
        \Illuminate\Support\Facades\DB::table('requirement_requests')
            ->where('status', 'completed')
            ->update(['completed_seen_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropColumn('completed_seen_at');
        });
    }
};
