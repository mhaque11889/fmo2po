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
            $table->text('progress_remarks')->nullable()->after('remarks');
            $table->timestamp('progress_at')->nullable()->after('progress_remarks');
            $table->text('completion_remarks')->nullable()->after('progress_at');
            $table->timestamp('completed_at')->nullable()->after('completion_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropColumn(['progress_remarks', 'progress_at', 'completion_remarks', 'completed_at']);
        });
    }
};
