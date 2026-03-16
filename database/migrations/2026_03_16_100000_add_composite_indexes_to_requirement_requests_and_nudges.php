<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->index(['created_by', 'status'], 'rr_created_by_status');
            $table->index(['assigned_to', 'status'], 'rr_assigned_to_status');
            $table->index('status', 'rr_status');
        });

        Schema::table('request_nudges', function (Blueprint $table) {
            $table->index(['requirement_request_id', 'target_user_id'], 'rn_request_target');
            $table->index(['target_user_id', 'acknowledged_at'], 'rn_target_acknowledged');
            $table->index(['sent_by', 'reply_seen_at'], 'rn_sent_by_reply_seen');
        });
    }

    public function down(): void
    {
        Schema::table('requirement_requests', function (Blueprint $table) {
            $table->dropIndex('rr_created_by_status');
            $table->dropIndex('rr_assigned_to_status');
            $table->dropIndex('rr_status');
        });

        Schema::table('request_nudges', function (Blueprint $table) {
            $table->dropIndex('rn_request_target');
            $table->dropIndex('rn_target_acknowledged');
            $table->dropIndex('rn_sent_by_reply_seen');
        });
    }
};
