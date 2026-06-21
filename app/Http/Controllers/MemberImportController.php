<?php

namespace App\Http\Controllers;

use App\Imports\MembersImport;
use App\Exports\MemberTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $isYouthChurch = auth()->check() && auth()->user()->church && auth()->user()->church->type === 'youth';

        $sampleData = [
            [
                'EMAIL',
                'SURNAME',
                'FIRSTNAME',
                'OTHER NAME',
                'DAY OF BIRTH',
                'MONTH OF BIRTH',
                'GENDER',
                'EMERGENCY CONTACT NAME & PHONE NUMBER',
                'MARITAL STATUS',
                'NAME OF PARTNER (if married)',
                'PHONE NUMBER (primary)',
                'STATE OF ORIGIN',
                'LOCAL GOVERNMENT',
                'STATE OF RESIDENCE',
                'CITY OF RESIDENCE',
                'STREET NAME & NUMBER',
                'PROFESSION/OCCUPATION',
                'GROUP IN CHURCH',
                'DEPARTMENT IN CHURCH',
                'BAPTIZED',
                'LOCATION & YEAR OF BAPTISM',
                'CHURCH OF BAPTISM',
                'SPIRITUAL GIFTS'
            ],
            [
                'john.doe@example.com',
                'Doe',
                'John',
                'David',
                '15',
                'January',
                'MALE',
                'Jane Doe: 09012345678',
                'MARRIED',
                'Jane Doe',
                '08012345678',
                'Lagos',
                'Ikeja',
                'Lagos',
                'Ikeja',
                '123 Main St, Ikeja',
                $isYouthChurch ? 'Student' : 'Software Engineer',
                'Men Fellowship',
                'CHOIR',
                'YES',
                '2010 - Lagos',
                'RCCG',
                'Teaching, Healing'
            ]
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
     * Process the import file (CSV or Excel)
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        $extension = strtolower($request->file('import_file')->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls'])) {
            return back()->with('error', 'The import file must be a file of type: csv, txt, xlsx, xls.');
        }

        try {
            $file = $request->file('import_file');
            $skipDuplicates = $request->boolean('skip_duplicates');
            $updateExisting = $request->boolean('update_existing');

            $results = $this->processExcelImport($file, $skipDuplicates, $updateExisting);

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
     * Preview CSV or Excel data before import
     */
    public function preview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|max:10240'
        ]);

        $extension = strtolower($request->file('import_file')->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xlsx', 'xls'])) {
            return response()->json([
                'success' => false,
                'error' => 'The import file must be a file of type: csv, txt, xlsx, xls.'
            ], 422);
        }

        try {
            $file = $request->file('import_file');
            $previewData = $this->previewExcelFile($file);

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
     * Process import using Laravel Excel
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
     * Preview file data using Laravel Excel
     */
    private function previewExcelFile($file)
    {
        $data = Excel::toArray(new MembersImport(), $file);

        if (empty($data) || empty($data[0])) {
            throw new \Exception('The import file appears to be empty or invalid.');
        }

        $rows = $data[0]; // Get first sheet

        if (empty($rows)) {
            throw new \Exception('No data found in the file.');
        }

        // Since MembersImport implements WithHeadingRow, $rows are already associative arrays
        $headers = array_keys($rows[0]);

        $previewData = array_slice($rows, 0, 10);

        return [
            'total_rows' => count($rows),
            'preview_data' => $previewData,
            'headers' => $headers
        ];
    }
}
