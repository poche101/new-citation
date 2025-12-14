<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin; // Make sure you have an Admin model
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin user
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'Superadmin@citation.com',
            'password' => Hash::make('SuperAdmin2025')
        ]);

        $this->command->info('Admin user created: admin@celz5.com / password123');
    }
}


