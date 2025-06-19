<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmergencyContact>
 */
class EmergencyContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => \App\Models\Member::factory(),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Child', 'Friend', 'Relative']),
            'phone' => fake()->phoneNumber(),
            'alternate_phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->address(),
            'is_primary' => fake()->boolean(),
        ];
    }

    /**
     * Configure the model factory to create primary emergency contacts.
     */
    public function primary(): static
    {
        return $this->state(function (array $attributes) {
            return ['is_primary' => true];
        });
    }

    /**
     * Configure the model factory to create secondary emergency contacts.
     */
    public function secondary(): static
    {
        return $this->state(function (array $attributes) {
            return ['is_primary' => false];
        });
    }
}
