<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        if (User::where('email', 'admin@church.com')->exists()) {
            $this->command->info('Admin user already exists!');
            return;
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@church.com',
            'password' => Hash::make('admin123'), // Change this password in production
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@church.com');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login.');
    }
}
