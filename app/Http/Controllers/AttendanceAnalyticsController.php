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
            $startMonth = $request->get('start_month', Carbon::now()->month);
            $startYear = $request->get('start_year', Carbon::now()->year);
            $endMonth = $request->get('end_month', Carbon::now()->month);
            $endYear = $request->get('end_year', Carbon::now()->year);
            
            $startDate = Carbon::createFromDate($startYear, $startMonth, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($endYear, $endMonth, 1)->endOfMonth();
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
                'members.id', 'members.first_name', 'members.last_name', 'members.other_names', 'members.email', 
                'members.password', 'members.remember_token', 'members.phone', 'members.address', 
                'members.date_of_birth', 'members.birth_day', 'members.birth_month', 
                'members.baptism_date', 'members.membership_status', 'members.marital_status', 
                'members.partner_name', 'members.state_of_origin', 'members.lga_of_origin', 
                'members.state_of_residence', 'members.city_of_residence', 'members.profession', 
                'members.church_group', 'members.is_baptized', 'members.baptism_year_and_place', 
                'members.baptism_church_name', 'members.spiritual_gifts', 'members.emergency_contact_details',
                'members.member_type', 'members.profile_photo', 'members.gender', 
                'members.emergency_contacts', 'members.custom_fields', 'members.created_at', 
                'members.updated_at', 'members.deleted_at', 'members.church_id', 'members.unique_id'
            )
            ->orderBy('attendance_count', 'desc');

        if ($request->filled('department')) {
            $mostRegularQuery->whereHas('departments', function ($q) use ($request) {
                $q->whereHas('department', function ($subQ) use ($request) {
                    $subQ->where('name', $request->input('department'));
                });
            });
        }
        if ($request->filled('church_group')) {
            $mostRegularQuery->where('members.church_group', $request->input('church_group'));
        }

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
                'members.id', 'members.first_name', 'members.last_name', 'members.other_names', 'members.email', 
                'members.password', 'members.remember_token', 'members.phone', 'members.address', 
                'members.date_of_birth', 'members.birth_day', 'members.birth_month', 
                'members.baptism_date', 'members.membership_status', 'members.marital_status', 
                'members.partner_name', 'members.state_of_origin', 'members.lga_of_origin', 
                'members.state_of_residence', 'members.city_of_residence', 'members.profession', 
                'members.church_group', 'members.is_baptized', 'members.baptism_year_and_place', 
                'members.baptism_church_name', 'members.spiritual_gifts', 'members.emergency_contact_details',
                'members.member_type', 'members.profile_photo', 'members.gender', 
                'members.emergency_contacts', 'members.custom_fields', 'members.created_at', 
                'members.updated_at', 'members.deleted_at', 'members.church_id', 'members.unique_id'
            )
            ->having('total_attendances', '>=', 2) // Must have attended at least twice to be considered
            ->orderBy('avg_minutes_late', 'asc'); // Lowest average (most negative) is best

        if ($request->filled('department')) {
            $mostPunctualQuery->whereHas('departments', function ($q) use ($request) {
                $q->whereHas('department', function ($subQ) use ($request) {
                    $subQ->where('name', $request->input('department'));
                });
            });
        }
        if ($request->filled('church_group')) {
            $mostPunctualQuery->where('members.church_group', $request->input('church_group'));
        }

        if ($limit) {
            $mostPunctualQuery->take($limit);
        }
        $mostPunctual = $mostPunctualQuery->get();

        $formDepartments = ['CHOIR', 'EVANGELISM', 'USHERING', 'DECORATION', 'INTERPRETATION', 'SUNDAY SCHOOL', 'DOCUMENTATION', 'DRAMA', 'SECURITY', 'MEDIA', 'PROTOCOL', 'SANCTUARY KEEPER', 'TECHNICAL', 'PRAYER', 'NONE'];
        $formGroups = ['The Levites', 'The Light bearers', 'The Root of Jesse', 'Ark of Covenant', 'God\'s Workmanship', 'Glorious star', 'Bread of Life', 'Wisdom of God', 'The Gospellers', 'Balm of Gilead', 'New creature', 'Heaven Ambassadors', 'Battle axe', 'PEACE FELLOWSHIP', 'REDEEMED', 'Light of the World', 'THE LORD CHOSEN', 'Salt of the World', 'Daughters of Zion'];

        return [
            'mostRegular' => $mostRegular,
            'mostPunctual' => $mostPunctual,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterType' => $filterType,
            'formDepartments' => $formDepartments,
            'formGroups' => $formGroups,
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
            'start_month' => $request->get('start_month', Carbon::now()->month),
            'start_year' => $request->get('start_year', Carbon::now()->year),
            'end_month' => $request->get('end_month', Carbon::now()->month),
            'end_year' => $request->get('end_year', Carbon::now()->year),
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
