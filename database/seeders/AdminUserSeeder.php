<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@church.com',
            'password' => Hash::make('admin123'), // Change this password in production
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}