<?php

namespace Database\Seeders;

use App\Models\Church;
use Illuminate\Database\Seeder;

class ChurchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Church::firstOrCreate(['name' => 'Adult Church'], ['type' => 'adult', 'description' => 'The Main Adult Church']);
        Church::firstOrCreate(['name' => 'Youth Church'], ['type' => 'youth', 'description' => 'The Youth Ministry']);
        Church::firstOrCreate(['name' => 'Children Church'], ['type' => 'children', 'description' => 'The Children Ministry']);
    }
}
