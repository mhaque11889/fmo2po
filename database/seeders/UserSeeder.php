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
        $users = [
            ['name' => 'Pushkar Bisht',    'email' => 'pbisht@aes.ac.in',      'role' => 'fmo_user'],
            ['name' => 'Rajesh Kumar',     'email' => 'rajekumar@aes.ac.in',   'role' => 'fmo_user'],
            ['name' => 'Swarup',           'email' => 'sdalei@aes.ac.in',      'role' => 'fmo_user'],
            ['name' => 'Jagat Singh',      'email' => 'jsingh@aes.ac.in',      'role' => 'fmo_user'],
            ['name' => 'Somiya Gupta',     'email' => 'smgupta@aes.ac.in',     'role' => 'fmo_user'],
            ['name' => 'Rohit Kumar',      'email' => 'rokumar@aes.ac.in',     'role' => 'fmo_user'],
            ['name' => 'Piyush Bansal',    'email' => 'pbansal@aes.ac.in',     'role' => 'fmo_user'],
            ['name' => 'Mohd. Saleem',     'email' => 'msaleem@aes.ac.in',     'role' => 'fmo_user'],
            ['name' => 'Ajay Sharma',      'email' => 'ajsharma@aes.ac.in',    'role' => 'fmo_user'],
            ['name' => 'Robin Gilbert',    'email' => 'rgilbert@aes.ac.in',    'role' => 'fmo_admin'],
            ['name' => 'Pooja Dixit',      'email' => 'pdixit@aes.ac.in',      'role' => 'fmo_admin'],
            ['name' => 'Sarabjeet Kaur',   'email' => 'sakaur@aes.ac.in',      'role' => 'fmo_admin'],
            ['name' => 'Kuldeep Singh',    'email' => 'kusingh@aes.ac.in',     'role' => 'po_admin'],
            ['name' => 'Jyotsana Nagarkoti', 'email' => 'jnagarkoti@aes.ac.in', 'role' => 'po_user'],
            ['name' => 'Jasraj Gill',      'email' => 'jgill@aes.ac.in',       'role' => 'po_user'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
