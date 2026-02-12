<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Users must be pre-registered in the database before they can log in.
     * Only users whose email exists in the users table can authenticate via Google.
     *
     * How it works:
     * 1. Admin adds user with email and role (google_id is null initially)
     * 2. User logs in via Google with their email
     * 3. System checks if email exists in users table
     * 4. If found: google_id, name, and avatar are populated from Google
     * 5. If not found: access denied
     */
    public function run(): void
    {
        // Test users for local development
        // In production, use the admin panel to add users or seed with real emails

        User::create([
            'name' => 'FMO User',
            'email' => 'fmouser@example.com',
            'role' => 'fmo_user',
            // google_id will be populated on first Google login
        ]);

        User::create([
            'name' => 'FMO Admin',
            'email' => 'fmoadmin@example.com',
            'role' => 'fmo_admin',
        ]);

        User::create([
            'name' => 'PO Admin',
            'email' => 'poadmin@example.com',
            'role' => 'po_admin',
        ]);

        User::create([
            'name' => 'PO User',
            'email' => 'pouser@example.com',
            'role' => 'po_user',
        ]);
    }
}
