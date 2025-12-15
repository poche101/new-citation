<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // check if already exists
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // change to a secure password
                'remember_token' => Str::random(10),
                'is_admin' => true, // if you have a flag
            ]
        );
    }
}
