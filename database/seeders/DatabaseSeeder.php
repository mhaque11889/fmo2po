<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
        User::create([
            'name' => 'Md Manzarul Haque',
            'email' => 'mhaque@aes.ac.in',
            'role' => 'super_admin',
            // google_id will be populated on first Google login
        ]);
    }
}
