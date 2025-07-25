<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDepartment;
use App\Imports\MembersImport;
use App\Exports\MemberTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:member.create');
    }

    /**
     * Show the import form
     */
    public function showImportForm()
    {
        return view('members.import');
    }

    /**
     * Download sample CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="member_import_template.csv"',
        ];

        $sampleData = [
            ['first_name', 'last_name', 'email', 'phone', 'address', 'date_of_birth', 'baptism_date', 'membership_status', 'gender', 'departments'],
            ['John', 'Doe', 'john.doe@example.com', '+1234567890', '123 Main St, City', '1990-01-15', '2010-05-20', 'active', 'male', 'choir,youth'],
            ['Jane', 'Smith', 'jane.smith@example.com', '+1234567891', '456 Oak Ave, City', '1985-03-22', '2008-12-10', 'active', 'female', 'women_ministry'],
            ['Michael', 'Johnson', 'michael.j@example.com', '+1234567892', '789 Pine St, City', '1975-07-08', '2005-09-15', 'active', 'male', 'men_ministry,ushering'],
        ];

        $callback = function() use ($sampleData) {
            $file = fopen('php://output', 'w');
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Excel template
     */
    public function downloadExcelTemplate()
    {
        return Excel::download(new MemberTemplateExport, 'member_import_template.xlsx');
    }

    /**
     * Process the CSV import
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('import_file');
            $skipDuplicates = $request->boolean('skip_duplicates');
            $updateExisting = $request->boolean('update_existing');
            
            $fileExtension = $file->getClientOriginalExtension();
            
            if (in_array($fileExtension, ['xlsx', 'xls'])) {
                // Handle Excel files
                $results = $this->processExcelImport($file, $skipDuplicates, $updateExisting);
            } else {
                // Handle CSV files
                $csvData = $this->parseCsvFile($file);
                
                if (empty($csvData)) {
                    return back()->with('error', 'The file appears to be empty or invalid.');
                }

                $results = $this->processImport($csvData, $skipDuplicates, $updateExisting);
            }

            return back()->with('success', 
                "Import completed! " .
                "Created: {$results['created']}, " .
                "Updated: {$results['updated']}, " .
                "Skipped: {$results['skipped']}, " .
                "Errors: {$results['errors']}"
            )->with('import_details', $results['details']);

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV file and return data array
     */
    private function parseCsvFile($file)
    {
        $csvData = [];
        $headers = [];
        
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $rowIndex = 0;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($rowIndex === 0) {
                    // First row contains headers
                    $headers = array_map('trim', $row);
                } else {
                    // Data rows
                    if (count($row) === count($headers)) {
                        $csvData[] = array_combine($headers, array_map('trim', $row));
                    }
                }
                $rowIndex++;
            }
            
            fclose($handle);
        }
        
        return $csvData;
    }

    /**
     * Process the import data
     */
    private function processImport($csvData, $skipDuplicates, $updateExisting)
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2; // +2 because we skip header and array is 0-indexed
                
                try {
                    $memberData = $this->validateAndPrepareData($row, $rowNumber);
                    
                    if (!$memberData) {
                        $results['errors']++;
                        continue;
                    }

                    // Check if member already exists
                    $existingMember = Member::where('email', $memberData['email'])->first();

                    if ($existingMember) {
                        if ($updateExisting) {
                            $this->updateMember($existingMember, $memberData, $row);
                            $results['updated']++;
                            $results['details'][] = "Row {$rowNumber}: Updated {$memberData['first_name']} {$memberData['last_name']}";
                        } elseif ($skipDuplicates) {
                            $results['skipped']++;
                            $results['details'][] = "Row {$rowNumber}: Skipped {$memberData['first_name']} {$memberData['last_name']} (duplicate email)";
                        } else {
                            $results['errors']++;
                            $results['details'][] = "Row {$rowNumber}: Error - Email {$memberData['email']} already exists";
                        }
                    } else {
                        $member = $this->createMember($memberData, $row);
                        $results['created']++;
                        $results['details'][] = "Row {$rowNumber}: Created {$memberData['first_name']} {$memberData['last_name']}";
                    }

                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['details'][] = "Row {$rowNumber}: Error - " . $e->getMessage();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Validate and prepare member data
     */
    private function validateAndPrepareData($row, $rowNumber)
    {
        $validator = Validator::make($row, [
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
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => strtolower(trim($row['email'])),
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'date_of_birth' => !empty($row['date_of_birth']) ? Carbon::parse($row['date_of_birth']) : null,
            'baptism_date' => !empty($row['baptism_date']) ? Carbon::parse($row['baptism_date']) : null,
            'membership_status' => $row['membership_status'] ?? 'active',
            'gender' => $row['gender'] ?? null,
        ];
    }

    /**
     * Create a new member
     */
    private function createMember($memberData, $row)
    {
        $member = Member::create($memberData);
        
        // Handle departments
        if (!empty($row['departments'])) {
            $this->assignDepartments($member, $row['departments']);
        }

        return $member;
    }

    /**
     * Update an existing member
     */
    private function updateMember($member, $memberData, $row)
    {
        $member->update($memberData);
        
        // Handle departments - remove existing and add new ones
        if (!empty($row['departments'])) {
            $member->departments()->delete();
            $this->assignDepartments($member, $row['departments']);
        }

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
     * Preview CSV data before import
     */
    public function preview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('import_file');
            $fileExtension = $file->getClientOriginalExtension();
            
            if (in_array($fileExtension, ['xlsx', 'xls'])) {
                // Handle Excel files
                $previewData = $this->previewExcelFile($file);
            } else {
                // Handle CSV files
                $csvData = $this->parseCsvFile($file);
                $previewData = [
                    'total_rows' => count($csvData),
                    'preview_data' => array_slice($csvData, 0, 10),
                    'headers' => !empty($csvData) ? array_keys($csvData[0]) : []
                ];
            }
            
            return response()->json([
                'success' => true,
                'total_rows' => $previewData['total_rows'],
                'preview_data' => $previewData['preview_data'],
                'headers' => $previewData['headers']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process Excel import using Laravel Excel
     */
    private function processExcelImport($file, $skipDuplicates, $updateExisting)
    {
        $import = new MembersImport($skipDuplicates, $updateExisting);
        
        DB::beginTransaction();
        
        try {
            Excel::import($import, $file);
            DB::commit();
            return $import->getResults();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Preview Excel file data
     */
    private function previewExcelFile($file)
    {
        $data = Excel::toArray(new MembersImport(), $file);
        
        if (empty($data) || empty($data[0])) {
            throw new \Exception('Excel file appears to be empty or invalid.');
        }
        
        $rows = $data[0]; // Get first sheet
        $headers = array_shift($rows); // Remove header row
        
        // Convert rows to associative arrays
        $previewData = [];
        foreach (array_slice($rows, 0, 10) as $row) {
            if (count($row) === count($headers)) {
                $previewData[] = array_combine($headers, $row);
            }
        }
        
        return [
            'total_rows' => count($rows),
            'preview_data' => $previewData,
            'headers' => $headers
        ];
    }
}