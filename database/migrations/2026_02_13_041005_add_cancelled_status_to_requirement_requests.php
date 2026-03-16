<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `requirement_requests` MODIFY COLUMN `status` ENUM('pending','approved','assigned','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `requirement_requests` SET `status` = 'pending' WHERE `status` = 'cancelled'");
        DB::statement("ALTER TABLE `requirement_requests` MODIFY COLUMN `status` ENUM('pending','approved','assigned','in_progress','completed','rejected') NOT NULL DEFAULT 'pending'");
    }
};
