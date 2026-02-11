<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table to change the enum constraint
        DB::statement('PRAGMA foreign_keys=off;');

        // Create new users table without enum constraint (validate at app level)
        DB::statement("
            CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at TIMESTAMP,
                password VARCHAR(255),
                google_id VARCHAR(255) UNIQUE,
                avatar VARCHAR(255),
                role VARCHAR(255) DEFAULT 'fmo_user',
                remember_token VARCHAR(100),
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        ");

        // Copy data from old table
        DB::statement('INSERT INTO users_new SELECT * FROM users');

        // Drop old table
        DB::statement('DROP TABLE users');

        // Rename new table
        DB::statement('ALTER TABLE users_new RENAME TO users');

        DB::statement('PRAGMA foreign_keys=on;');

        // Create the super admin user
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'mhaque@aes.ac.in',
            'role' => 'super_admin',
            'google_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('email', 'mhaque@aes.ac.in')->delete();
    }
};
