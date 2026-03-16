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
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('super_admin','fmo_user','fmo_admin','po_admin','po_user') NOT NULL DEFAULT 'fmo_user'");

        // Create the super admin user
        $existing = DB::table('users')->where('email', 'mhaque@aes.ac.in')->exists();

        if ($existing) {
            DB::table('users')
                ->where('email', 'mhaque@aes.ac.in')
                ->update([
                    'name' => 'Super Admin',
                    'role' => 'super_admin',
                    'google_id' => null,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('users')->insert([
                'name' => 'Super Admin',
                'email' => 'mhaque@aes.ac.in',
                'role' => 'super_admin',
                'google_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', 'mhaque@aes.ac.in')->delete();

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('fmo_user','fmo_admin','po_admin','po_user') NOT NULL DEFAULT 'fmo_user'");
    }
};
