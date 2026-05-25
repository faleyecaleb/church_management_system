<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\MemberDepartment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class MembersImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $skipDuplicates;
    protected $updateExisting;
    protected $results;

    public function __construct($skipDuplicates = true, $updateExisting = false)
    {
        $this->skipDuplicates = $skipDuplicates;
        $this->updateExisting = $updateExisting;
        $this->results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we skip header and array is 0-indexed
            
            try {
                $memberData = $this->validateAndPrepareData($row->toArray(), $rowNumber);
                
                if (!$memberData) {
                    $this->results['errors']++;
                    continue;
                }

                // Check if member already exists
                $existingMember = Member::where('email', $memberData['email'])->first();

                if ($existingMember) {
                    if ($this->updateExisting) {
                        $this->updateMember($existingMember, $memberData, $row->toArray());
                        $this->results['updated']++;
                        $this->results['details'][] = "Row {$rowNumber}: Updated {$memberData['first_name']} {$memberData['last_name']}";
                    } elseif ($this->skipDuplicates) {
                        $this->results['skipped']++;
                        $this->results['details'][] = "Row {$rowNumber}: Skipped {$memberData['first_name']} {$memberData['last_name']} (duplicate email)";
                    } else {
                        $this->results['errors']++;
                        $this->results['details'][] = "Row {$rowNumber}: Error - Email {$memberData['email']} already exists";
                    }
                } else {
                    $member = $this->createMember($memberData, $row->toArray());
                    $this->results['created']++;
                    $this->results['details'][] = "Row {$rowNumber}: Created {$memberData['first_name']} {$memberData['last_name']}";
                }

            } catch (\Exception $e) {
                $this->results['errors']++;
                $this->results['details'][] = "Row {$rowNumber}: Error - " . $e->getMessage();
            }
        }
    }

    /**
     * Validate and prepare member data
     */
    private function validateAndPrepareData($row, $rowNumber)
    {
        // Normalize column names (handle different naming conventions)
        $normalizedRow = $this->normalizeColumnNames($row);

        // Force phone to be a string
        if (isset($normalizedRow['phone'])) {
            $normalizedRow['phone'] = (string) $normalizedRow['phone'];
        }

        $validator = Validator::make($normalizedRow, [
            'email' => 'required|email|max:255',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'birth_day' => 'required|string|max:2',
            'birth_month' => 'required|string|max:20',
            'gender' => 'required|string|in:male,female,other,MALE,FEMALE,OTHER',
            'emergency_contact_details' => 'nullable|string',
            'marital_status' => 'required|string',
            'partner_name' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'state_of_origin' => 'required|string',
            'lga_of_origin' => 'required|string',
            'state_of_residence' => 'required|string',
            'city_of_residence' => 'required|string',
            'address' => 'required|string',
            'profession' => 'required|string',
            'church_group' => 'nullable|string',
            'department' => 'required|string',
            'is_baptized' => 'required|string',
            'baptism_year_and_place' => 'nullable|string',
            'baptism_church_name' => 'nullable|string',
            'spiritual_gifts' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = implode(', ', $validator->errors()->all());
            throw new \Exception("Validation failed: {$errors}");
        }

        return [
            'email' => strtolower(trim($normalizedRow['email'])),
            'last_name' => $normalizedRow['last_name'],
            'first_name' => $normalizedRow['first_name'],
            'other_names' => $normalizedRow['other_names'] ?? null,
            'birth_day' => $normalizedRow['birth_day'],
            'birth_month' => $normalizedRow['birth_month'],
            'gender' => strtolower($normalizedRow['gender']),
            'emergency_contact_details' => $normalizedRow['emergency_contact_details'] ?? null,
            'marital_status' => $normalizedRow['marital_status'],
            'partner_name' => $normalizedRow['partner_name'] ?? null,
            'phone' => $normalizedRow['phone'],
            'state_of_origin' => $normalizedRow['state_of_origin'],
            'lga_of_origin' => $normalizedRow['lga_of_origin'],
            'state_of_residence' => $normalizedRow['state_of_residence'],
            'city_of_residence' => $normalizedRow['city_of_residence'],
            'address' => $normalizedRow['address'],
            'profession' => $normalizedRow['profession'],
            'church_group' => $normalizedRow['church_group'] ?? null,
            'department' => $normalizedRow['department'], // we'll use this below
            'is_baptized' => $normalizedRow['is_baptized'],
            'baptism_year_and_place' => $normalizedRow['baptism_year_and_place'] ?? null,
            'baptism_church_name' => $normalizedRow['baptism_church_name'] ?? null,
            'spiritual_gifts' => $normalizedRow['spiritual_gifts'] ?? null,
            'membership_status' => 'active',
            'member_type' => 'main_member',
        ];
    }

    /**
     * Normalize column names to handle different naming conventions
     */
    private function normalizeColumnNames($row)
    {
        $normalized = [];
        $columnMapping = [
            'email' => ['email', 'email_address', 'e_mail'],
            'last_name' => ['lastname_surname', 'last_name', 'surname', 'lastname', 'family_name'],
            'first_name' => ['firstname', 'first_name', 'given_name'],
            'other_names' => ['others', 'other_names', 'middle_name'],
            'birth_day' => ['day_of_birth', 'birth_day', 'day'],
            'birth_month' => ['month_of_birth', 'birth_month', 'month'],
            'gender' => ['gender', 'sex'],
            'emergency_contact_details' => ['emergency_contact_name_phone_number', 'emergency_contact', 'emergency_contact_details'],
            'marital_status' => ['marital_status', 'status'],
            'partner_name' => ['name_of_partner_if_married', 'name_of_partner', 'partner_name', 'spouse_name'],
            'phone' => ['phone_number_primary', 'phone_number', 'phone', 'mobile'],
            'state_of_origin' => ['state_of_origin', 'state_origin'],
            'lga_of_origin' => ['local_government_of_origin', 'lga_of_origin', 'lga'],
            'state_of_residence' => ['state_of_residence', 'state_residence'],
            'city_of_residence' => ['city_of_residence', 'city_residence'],
            'address' => ['street_no_and_name_eg_2_korogboji', 'street_no_and_name', 'address', 'street'],
            'profession' => ['profession_occupation', 'profession', 'occupation'],
            'church_group' => ['group_in_church', 'church_group', 'group'],
            'department' => ['department_in_church', 'department', 'departments', 'ministry'],
            'is_baptized' => ['are_you_baptized', 'is_baptized', 'baptized'],
            'baptism_year_and_place' => ['what_year_and_where', 'baptism_year_and_place'],
            'baptism_church_name' => ['name_of_the_church', 'baptism_church_name'],
            'spiritual_gifts' => ['spiritual_gifts', 'gifts']
        ];

        // Ensure keys are strictly lowercase alphanumeric for matching from exact headings
        $cleanRow = [];
        foreach ($row as $key => $value) {
            $cleanKey = preg_replace('/[^a-z0-9_]/', '_', strtolower(trim($key)));
            $cleanRow[$cleanKey] = $value;
        }

        foreach ($columnMapping as $standardName => $variations) {
            foreach ($variations as $variation) {
                // Check exact variation or slugified version of typical headers
                if (isset($cleanRow[$variation])) {
                    $normalized[$standardName] = $cleanRow[$variation];
                    break;
                }
                
                // Fallback check on original keys just in case
                foreach ($row as $rawKey => $rawValue) {
                    if (str_replace(' ', '_', strtolower(trim($rawKey))) === $variation) {
                        $normalized[$standardName] = $rawValue;
                        break 2;
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * Create a new member
     */
    private function createMember($memberData, $row)
    {
        // Set default password to lowercase last name
        if (empty($memberData['password'])) {
            $memberData['password'] = \Illuminate\Support\Facades\Hash::make(strtolower(trim($memberData['last_name'])));
        }

        $member = Member::create($memberData);
        
        // Handle department
        $normalizedRow = $this->normalizeColumnNames($row);
        if (!empty($normalizedRow['department'])) {
            $this->assignDepartments($member, $normalizedRow['department']);
        }

        $this->syncUserAccount($memberData);

        return $member;
    }

    /**
     * Update an existing member
     */
    private function updateMember($member, $memberData, $row)
    {
        $member->update($memberData);
        
        // Handle department - remove existing and add new ones
        $normalizedRow = $this->normalizeColumnNames($row);
        if (!empty($normalizedRow['department'])) {
            $member->departments()->delete();
            $this->assignDepartments($member, $normalizedRow['department']);
        }

        $this->syncUserAccount(array_merge($memberData, ['password' => $member->password]));

        return $member;
    }

    /**
     * Assign departments to a member
     */
    private function assignDepartments($member, $departmentsString)
    {
        $departments = array_map('trim', explode(',', $departmentsString));
        
        foreach ($departments as $departmentName) {
            if (!empty($departmentName)) {
                // Find or create department by name
                $department = \App\Models\Department::firstOrCreate(
                    ['name' => $departmentName],
                    ['is_active' => true, 'description' => $departmentName . ' Department']
                );
                
                \App\Models\MemberDepartment::updateOrCreate([
                    'member_id' => $member->id,
                    'department_id' => $department->id
                ]);
            }
        }
    }

    /**
     * Sync member data with the users table for future login access
     */
    private function syncUserAccount($memberData)
    {
        $user = \App\Models\User::firstOrNew(['email' => $memberData['email']]);
        
        // Only update if they are a regular member or a brand new account
        // This prevents accidentally converting an admin into a member if their email is in the CSV
        if (!$user->exists || $user->role === 'member') {
            $user->name = $memberData['first_name'] . ' ' . $memberData['last_name'];
            
            if (!$user->exists) {
                $user->password = $memberData['password']; // Set password only on creation
                $user->role = 'member';
                if (auth()->check()) {
                    $user->church_id = auth()->user()->church_id;
                }
            }
            $user->save();

            // Attach dynamic member role
            $memberRole = \App\Models\Role::where('slug', 'member')->first();
            if ($memberRole) {
                $user->roles()->syncWithoutDetaching([$memberRole->id]);
            }
        }
    }

    /**
     * Get import results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Batch size for processing
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }
}