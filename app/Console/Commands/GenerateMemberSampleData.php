<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;

class GenerateMemberSampleData extends Command
{
    protected $signature = 'members:generate-sample-csv {count=100} {--file=member_sample_data.csv}';
    protected $description = 'Generate sample member data CSV for testing bulk import';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $filename = $this->option('file');
        $faker = Faker::create();

        $this->info("Generating {$count} sample member records...");

        $file = fopen(storage_path("app/{$filename}"), 'w');
        
        // Write headers
        fputcsv($file, [
            'first_name',
            'last_name', 
            'email',
            'phone',
            'address',
            'date_of_birth',
            'baptism_date',
            'membership_status',
            'gender',
            'departments'
        ]);

        $departments = [
            'choir', 'youth', 'children_ministry', 'women_ministry', 'men_ministry',
            'ushering', 'media_team', 'prayer_team', 'evangelism', 'counseling',
            'finance', 'administration', 'security', 'hospitality', 'worship_team'
        ];

        $membershipStatuses = ['active', 'inactive', 'pending'];
        $genders = ['male', 'female'];

        for ($i = 0; $i < $count; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');
            
            // Random departments (1-3 departments per member)
            $memberDepartments = $faker->randomElements($departments, $faker->numberBetween(1, 3));
            
            fputcsv($file, [
                $firstName,
                $lastName,
                $email,
                $faker->phoneNumber,
                $faker->address,
                $faker->date('Y-m-d', '-18 years'), // At least 18 years old
                $faker->optional(0.8)->date('Y-m-d', '-1 year'), // 80% chance of baptism date
                $faker->randomElement($membershipStatuses),
                $faker->randomElement($genders),
                implode(',', $memberDepartments)
            ]);
        }

        fclose($file);

        $this->info("✅ Generated {$count} sample records in storage/app/{$filename}");
        $this->info("You can download this file and use it to test the bulk import feature.");
        
        return 0;
    }
}