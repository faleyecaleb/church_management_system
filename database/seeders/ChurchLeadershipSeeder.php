<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChurchLeadershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Senior Pastor (Super Admin)
        $seniorPastorEmail = 'senior_pastor@hosanna';
        
        $seniorPastor = User::firstOrCreate(
            ['email' => $seniorPastorEmail],
            [
                'name' => 'Senior Pastor',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        $superAdminRole = Role::findBySlug(Role::SUPER_ADMIN);
        if ($superAdminRole) {
            $seniorPastor->roles()->sync([$superAdminRole->id]);
        }

        $this->command->info("Senior Pastor account created successfully!");
        $this->command->info("Email: {$seniorPastorEmail}");
        $this->command->info("Password: password123");

        // 2. Seed Adult Church Curate Account
        $adultChurch = Church::where('name', 'Adult Church')->first();
        
        if ($adultChurch) {
            $curatePastorEmail = 'adult_curate@church.com';
            
            $adultCurate = User::firstOrCreate(
                ['email' => $curatePastorEmail],
                [
                    'name' => 'Adult Curate Pastor',
                    'password' => Hash::make('password123'),
                    'role' => 'curate_pastor',
                    'church_id' => $adultChurch->id,
                    'email_verified_at' => now(),
                ]
            );

            $curateRole = Role::findBySlug('curate_pastor');
            if ($curateRole) {
                $adultCurate->roles()->sync([$curateRole->id]);
            }

            $this->command->info("Adult Curate Pastor account created successfully!");
            $this->command->info("Email: {$curatePastorEmail}");
            $this->command->info("Password: password123");
        } else {
            $this->command->error("Adult Church not found. Unable to create adult church curate account.");
        }
    }
}
