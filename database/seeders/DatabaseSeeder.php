<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin; // Make sure you have an Admin model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // optional
        ]);

        // Create default admin user
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@celz5.com',
            'password' => Hash::make('password123'), // Change to a secure password
        ]);

        $this->command->info('Default User and Admin have been created.');
    }
}
