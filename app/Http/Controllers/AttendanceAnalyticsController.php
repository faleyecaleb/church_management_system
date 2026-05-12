<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceAnalyticsExport;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceAnalyticsController extends Controller
{
    public function __construct()
    {
        // Only those who can view reports should see these analytics
        $this->middleware(['auth', 'permission:attendance.view']);
    }

    private function getAnalyticsData(Request $request, $limit = 10)
    {
        $filterType = $request->get('filter_type', 'timeframe');

        if ($filterType === 'month') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } else {
            $timeframe = $request->get('timeframe', '30'); // Default to 30 days
            $startDate = Carbon::now()->subDays((int)$timeframe)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        // 1. Most Regular Members (Highest Attendance Count)
        $mostRegularQuery = Member::select('members.*', DB::raw('COUNT(attendances.id) as attendance_count'))
            ->join('attendances', 'members.id', '=', 'attendances.member_id')
            ->where('attendances.is_present', true)
            ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
            ->groupBy(
                'members.id', 'members.first_name', 'members.last_name', 'members.email', 
                'members.password', 'members.remember_token', 'members.phone', 'members.address', 
                'members.date_of_birth', 'members.baptism_date', 'members.membership_status', 
                'members.member_type', 'members.profile_photo', 'members.gender', 
                'members.emergency_contacts', 'members.custom_fields', 'members.created_at', 
                'members.updated_at', 'members.deleted_at', 'members.church_id', 'members.unique_id'
            )
            ->orderBy('attendance_count', 'desc');

        if ($limit) {
            $mostRegularQuery->take($limit);
        }
        $mostRegular = $mostRegularQuery->get();

        // 2. Most Punctual Members
        $mostPunctualQuery = Member::select(
                'members.*', 
                DB::raw('COUNT(attendances.id) as total_attendances'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, 
                    TIMESTAMP(attendances.attendance_date, services.start_time), 
                    attendances.check_in_time
                )) as avg_minutes_late')
            )
            ->join('attendances', 'members.id', '=', 'attendances.member_id')
            ->join('services', 'attendances.service_id', '=', 'services.id')
            ->where('attendances.is_present', true)
            ->whereNotNull('attendances.check_in_time')
            ->whereBetween('attendances.attendance_date', [$startDate, $endDate])
            ->groupBy(
                'members.id', 'members.first_name', 'members.last_name', 'members.email', 
                'members.password', 'members.remember_token', 'members.phone', 'members.address', 
                'members.date_of_birth', 'members.baptism_date', 'members.membership_status', 
                'members.member_type', 'members.profile_photo', 'members.gender', 
                'members.emergency_contacts', 'members.custom_fields', 'members.created_at', 
                'members.updated_at', 'members.deleted_at', 'members.church_id', 'members.unique_id'
            )
            ->having('total_attendances', '>=', 2) // Must have attended at least twice to be considered
            ->orderBy('avg_minutes_late', 'asc'); // Lowest average (most negative) is best

        if ($limit) {
            $mostPunctualQuery->take($limit);
        }
        $mostPunctual = $mostPunctualQuery->get();

        return [
            'mostRegular' => $mostRegular,
            'mostPunctual' => $mostPunctual,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getAnalyticsData($request, 10); // Display top 10

        // Prepare Data for Chart.js
        $chartData = [
            'regular_labels' => $data['mostRegular']->pluck('first_name')->toArray(),
            'regular_data' => $data['mostRegular']->pluck('attendance_count')->toArray(),
            'punctual_labels' => $data['mostPunctual']->pluck('first_name')->toArray(),
            // Ensure negative numbers are visually readable or handled gracefully
            'punctual_data' => $data['mostPunctual']->map(function($m) { return round($m->avg_minutes_late); })->toArray(),
        ];

        return view('attendance.analytics.index', array_merge($data, [
            'chartData' => $chartData,
            'timeframe' => $request->get('timeframe', '30'),
            'month' => $request->get('month', Carbon::now()->month),
            'year' => $request->get('year', Carbon::now()->year),
        ]));
    }

    public function export(Request $request)
    {
        $data = $this->getAnalyticsData($request, 100); // Export top 100

        return Excel::download(
            new AttendanceAnalyticsExport($data['mostRegular'], $data['mostPunctual'], $data['startDate'], $data['endDate']), 
            'Attendance_Analytics_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
