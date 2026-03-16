<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `requirement_requests` MODIFY COLUMN `status` ENUM('pending','approved','assigned','in_progress','completed','rejected','cancelled','clarification_needed') NOT NULL DEFAULT 'pending'");

        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->text('clarification_remarks')->nullable()->after('remarks');
            $table->foreignId('clarification_requested_by')->nullable()->constrained('users')->onDelete('set null')->after('clarification_remarks');
            $table->timestamp('clarification_requested_at')->nullable()->after('clarification_requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `requirement_requests` SET `status` = 'pending' WHERE `status` = 'clarification_needed'");
        DB::statement("ALTER TABLE `requirement_requests` MODIFY COLUMN `status` ENUM('pending','approved','assigned','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropForeign(['clarification_requested_by']);
            $table->dropColumn(['clarification_remarks', 'clarification_requested_by', 'clarification_requested_at']);
        });
    }
};
