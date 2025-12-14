<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin user
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'Superadmin@citation.com',
            'password' => Hash::make('SuperAdmin2025'),
        ]);

        // Display the credentials in the console
        $this->command->info('Admin user created: Superadmin@citation.com / SuperAdmin2025');
    }
}
