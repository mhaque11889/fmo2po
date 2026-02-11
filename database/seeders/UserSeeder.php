<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users for each role
        // Note: In production, users will be created via Google OAuth
        // This is just for testing purposes

        User::create([
            'name' => 'FMO User',
            'email' => 'fmouser@example.com',
            'role' => 'fmo_user',
            'google_id' => 'test_fmo_user_001',
        ]);

        User::create([
            'name' => 'FMO Admin',
            'email' => 'fmoadmin@example.com',
            'role' => 'fmo_admin',
            'google_id' => 'test_fmo_admin_001',
        ]);

        User::create([
            'name' => 'PO Admin',
            'email' => 'poadmin@example.com',
            'role' => 'po_admin',
            'google_id' => 'test_po_admin_001',
        ]);

        User::create([
            'name' => 'PO User',
            'email' => 'pouser@example.com',
            'role' => 'po_user',
            'google_id' => 'test_po_user_001',
        ]);
    }
}
