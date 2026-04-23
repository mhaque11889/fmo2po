<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE requirement_requests MODIFY COLUMN status ENUM(
            'group_pending',
            'pending',
            'approved',
            'assigned',
            'in_progress',
            'completed',
            'rejected',
            'cancelled',
            'clarification_needed'
        ) NOT NULL DEFAULT 'pending'");

        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('group_approved_by')->nullable();
            $table->timestamp('group_approved_at')->nullable();
            $table->foreign('group_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropForeign(['group_approved_by']);
            $table->dropColumn(['group_approved_by', 'group_approved_at']);
        });

        DB::statement("ALTER TABLE requirement_requests MODIFY COLUMN status ENUM(
            'pending',
            'approved',
            'assigned',
            'in_progress',
            'completed',
            'rejected',
            'cancelled',
            'clarification_needed'
        ) NOT NULL DEFAULT 'pending'");
    }
};
