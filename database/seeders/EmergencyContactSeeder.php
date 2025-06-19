<?php

namespace Database\Seeders;

use App\Models\EmergencyContact;
use App\Models\Member;
use Illuminate\Database\Seeder;

class EmergencyContactSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();

        foreach ($members as $member) {
            // Create a primary emergency contact
            EmergencyContact::create([
                'member_id' => $member->id,
                'name' => fake()->name(),
                'relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Child', 'Friend']),
                'phone' => fake()->phoneNumber(),
                'alternate_phone' => fake()->optional()->phoneNumber(),
                'email' => fake()->optional()->safeEmail(),
                'address' => fake()->optional()->address(),
                'is_primary' => true,
            ]);

            // Randomly create 1-2 secondary emergency contacts
            $secondaryContactsCount = fake()->numberBetween(1, 2);
            for ($i = 0; $i < $secondaryContactsCount; $i++) {
                EmergencyContact::create([
                    'member_id' => $member->id,
                    'name' => fake()->name(),
                    'relationship' => fake()->randomElement(['Parent', 'Sibling', 'Child', 'Friend', 'Relative']),
                    'phone' => fake()->phoneNumber(),
                    'alternate_phone' => fake()->optional()->phoneNumber(),
                    'email' => fake()->optional()->safeEmail(),
                    'address' => fake()->optional()->address(),
                    'is_primary' => false,
                ]);
            }
        }
    }
}