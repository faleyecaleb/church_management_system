<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDepartment;
use App\Exports\MembersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MemberExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:member.view');
    }

    /**
     * Show the export form
     */
    public function showExportForm()
    {
        // Get available departments for filter
        $departments = MemberDepartment::select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        // Get membership statuses
        $membershipStatuses = Member::select('membership_status')
            ->distinct()
            ->whereNotNull('membership_status')
            ->orderBy('membership_status')
            ->pluck('membership_status');

        // Get member statistics
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('membership_status', 'active')->count(),
            'inactive_members' => Member::where('membership_status', 'inactive')->count(),
            'male_members' => Member::where('gender', 'male')->count(),
            'female_members' => Member::where('gender', 'female')->count(),
        ];

        return view('members.export', compact('departments', 'membershipStatuses', 'stats'));
    }

    /**
     * Export members based on filters
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'membership_status' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'department' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'search' => 'nullable|string|max:255',
            'include_inactive' => 'boolean'
        ]);

        try {
            $filters = $request->only([
                'membership_status', 'gender', 'department', 
                'date_from', 'date_to', 'search'
            ]);

            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return !empty($value);
            });

            $format = $request->input('format', 'xlsx');
            $timestamp = now()->format('Y-m-d_H-i-s');
            
            // Generate filename based on filters
            $filename = $this->generateFilename($filters, $format, $timestamp);

            // Create export
            $export = new MembersExport($filters, $format);

            // Return download based on format
            switch ($format) {
                case 'csv':
                    return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
                case 'pdf':
                    return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
                default:
                    return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Export members to Excel format (CSV fallback)
     */
    public function exportExcel(Request $request)
    {
        try {
            // Try to use Excel package if available
            if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                $timestamp = now()->format('Y-m-d_H-i-s');
                $filename = "members_export_{$timestamp}.xlsx";
                $export = new MembersExport([], 'xlsx');
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
            }
        } catch (\Exception $e) {
            // Fall back to CSV export
        }

        // Fallback: Generate CSV manually
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "members_export_{$timestamp}.csv";
        
        $members = Member::with(['departments'])->get();
        
        $csvData = [];
        $csvData[] = [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Gender',
            'Date of Birth',
            'Address',
            'Membership Status',
            'Departments',
            'Member Since'
        ];
        
        foreach ($members as $member) {
            $csvData[] = [
                $member->id,
                $member->first_name,
                $member->last_name,
                $member->email,
                $member->phone,
                $member->gender,
                $member->date_of_birth,
                $member->address,
                $member->membership_status,
                $member->departments->pluck('department')->join(', '),
                $member->created_at->format('Y-m-d')
            ];
        }
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick export all members
     */
    public function exportAll(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "all_members_{$timestamp}.{$format}";

        $export = new MembersExport([], $format);

        switch ($format) {
            case 'csv':
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
            case 'pdf':
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    /**
     * Export members by department
     */
    public function exportByDepartment(Request $request, $department)
    {
        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "members_{$department}_{$timestamp}.{$format}";

        $filters = ['department' => $department];
        $export = new MembersExport($filters, $format);

        switch ($format) {
            case 'csv':
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
            case 'pdf':
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    /**
     * Preview export data
     */
    public function preview(Request $request)
    {
        $filters = $request->only([
            'membership_status', 'gender', 'department', 
            'date_from', 'date_to', 'search'
        ]);

        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return !empty($value);
        });

        try {
            $query = Member::with(['departments']);

            // Apply same filters as export
            if (!empty($filters['membership_status'])) {
                $query->where('membership_status', $filters['membership_status']);
            }

            if (!empty($filters['gender'])) {
                $query->where('gender', $filters['gender']);
            }

            if (!empty($filters['department'])) {
                $query->whereHas('departments', function($q) use ($filters) {
                    $q->where('department', $filters['department']);
                });
            }

            if (!empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $totalCount = $query->count();
            $previewMembers = $query->orderBy('first_name')
                                  ->orderBy('last_name')
                                  ->limit(10)
                                  ->get();

            return response()->json([
                'success' => true,
                'total_count' => $totalCount,
                'preview_members' => $previewMembers->map(function($member) {
                    return [
                        'id' => $member->id,
                        'full_name' => $member->full_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'membership_status' => $member->membership_status,
                        'gender' => $member->gender,
                        'departments' => $member->departments->pluck('department')->join(', '),
                        'member_since' => $member->created_at->format('Y-m-d')
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate filename based on filters
     */
    private function generateFilename($filters, $format, $timestamp)
    {
        $parts = ['members'];

        if (!empty($filters['membership_status'])) {
            $parts[] = $filters['membership_status'];
        }

        if (!empty($filters['gender'])) {
            $parts[] = $filters['gender'];
        }

        if (!empty($filters['department'])) {
            $parts[] = str_replace(' ', '_', $filters['department']);
        }

        if (!empty($filters['search'])) {
            $parts[] = 'search_' . str_replace(' ', '_', substr($filters['search'], 0, 10));
        }

        $parts[] = $timestamp;

        return implode('_', $parts) . '.' . $format;
    }
}