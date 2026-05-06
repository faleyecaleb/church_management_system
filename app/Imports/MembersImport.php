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

        // Force phone to be a string (Excel often parses numeric phone numbers as floats/ints)
        if (isset($normalizedRow['phone'])) {
            $normalizedRow['phone'] = (string) $normalizedRow['phone'];
        }

        $validator = Validator::make($normalizedRow, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'membership_status' => 'nullable|in:active,inactive,pending',
            'gender' => 'nullable|in:male,female,other',
        ]);

        if ($validator->fails()) {
            $errors = implode(', ', $validator->errors()->all());
            throw new \Exception("Validation failed: {$errors}");
        }

        return [
            'first_name' => $normalizedRow['first_name'],
            'last_name' => $normalizedRow['last_name'],
            'email' => strtolower(trim($normalizedRow['email'])),
            'phone' => $normalizedRow['phone'] ?? null,
            'address' => $normalizedRow['address'] ?? null,
            'date_of_birth' => !empty($normalizedRow['date_of_birth']) ? Carbon::parse($normalizedRow['date_of_birth']) : null,
            'baptism_date' => !empty($normalizedRow['baptism_date']) ? Carbon::parse($normalizedRow['baptism_date']) : null,
            'membership_status' => $normalizedRow['membership_status'] ?? 'active',
            'gender' => $normalizedRow['gender'] ?? null,
        ];
    }

    /**
     * Normalize column names to handle different naming conventions
     */
    private function normalizeColumnNames($row)
    {
        $normalized = [];
        $columnMapping = [
            // Standard names
            'first_name' => ['first_name', 'firstname', 'first name', 'given_name', 'given name'],
            'last_name' => ['last_name', 'lastname', 'last name', 'surname', 'family_name', 'family name'],
            'email' => ['email', 'email_address', 'email address', 'e_mail', 'e mail'],
            'phone' => ['phone', 'phone_number', 'phone number', 'mobile', 'cell', 'telephone'],
            'address' => ['address', 'home_address', 'home address', 'street_address', 'street address'],
            'date_of_birth' => ['date_of_birth', 'dob', 'birth_date', 'birth date', 'birthday'],
            'baptism_date' => ['baptism_date', 'baptism date', 'baptized_date', 'baptized date'],
            'membership_status' => ['membership_status', 'membership status', 'status', 'member_status', 'member status'],
            'gender' => ['gender', 'sex'],
            'departments' => ['departments', 'department', 'ministry', 'ministries', 'groups', 'group']
        ];

        foreach ($columnMapping as $standardName => $variations) {
            foreach ($variations as $variation) {
                if (isset($row[$variation])) {
                    $normalized[$standardName] = $row[$variation];
                    break;
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
        
        // Handle departments
        $normalizedRow = $this->normalizeColumnNames($row);
        if (!empty($normalizedRow['departments'])) {
            $this->assignDepartments($member, $normalizedRow['departments']);
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
        
        // Handle departments - remove existing and add new ones
        $normalizedRow = $this->normalizeColumnNames($row);
        if (!empty($normalizedRow['departments'])) {
            $member->departments()->delete();
            $this->assignDepartments($member, $normalizedRow['departments']);
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
        
        foreach ($departments as $department) {
            if (!empty($department)) {
                MemberDepartment::create([
                    'member_id' => $member->id,
                    'department' => $department
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