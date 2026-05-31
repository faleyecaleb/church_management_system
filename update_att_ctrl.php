<?php
$content = file_get_contents('app/Http/Controllers/AttendanceController.php');

$oldReportStart = <<<'PHP'
    public function report(Request $request)    {
        // Get services for the filter dropdown
        $services = Service::active()->get();

        // Initialize query with date range filter
        $query = Attendance::query();
        if ($request->filled(['start_date', 'end_date'])) {
            $query->whereBetween('check_in_time', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }

        // Filter by service if specified
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        // Calculate statistics
        $totalAttendance = $query->count();
PHP;

$newReportStart = <<<'PHP'
    public function report(Request $request)    {
        // Get services for the filter dropdown
        $services = Service::active()->get();

        // Standard dropdown lists for filters
        $formDepartments = ['CHOIR', 'EVANGELISM', 'USHERING', 'DECORATION', 'INTERPRETATION', 'SUNDAY SCHOOL', 'DOCUMENTATION', 'DRAMA', 'SECURITY', 'MEDIA', 'PROTOCOL', 'SANCTUARY KEEPER', 'TECHNICAL', 'PRAYER', 'NONE'];
        $formGroups = ['The Levites', 'The Light bearers', 'The Root of Jesse', 'Ark of Covenant', 'God\'s Workmanship', 'Glorious star', 'Bread of Life', 'Wisdom of God', 'The Gospellers', 'Balm of Gilead', 'New creature', 'Heaven Ambassadors', 'Battle axe', 'PEACE FELLOWSHIP', 'REDEEMED', 'Light of the World', 'THE LORD CHOSEN', 'Salt of the World', 'Daughters of Zion'];

        // Apply filters closure
        $applyFilters = function($q) use ($request) {
            if ($request->filled(['start_date', 'end_date'])) {
                $q->whereBetween('check_in_time', [
                    $request->input('start_date') . ' 00:00:00',
                    $request->input('end_date') . ' 23:59:59'
                ]);
            }
            if ($request->filled('service_id')) {
                $q->where('service_id', $request->input('service_id'));
            }
            
            // Join member if we need to filter by department or group
            if ($request->filled('department') || $request->filled('church_group')) {
                $q->join('members', 'attendances.member_id', '=', 'members.id');
                
                if ($request->filled('department')) {
                    $q->join('member_departments', 'members.id', '=', 'member_departments.member_id')
                      ->join('departments', 'member_departments.department_id', '=', 'departments.id')
                      ->where('departments.name', $request->input('department'));
                }
                
                if ($request->filled('church_group')) {
                    $q->where('members.church_group', $request->input('church_group'));
                }
                // select attendances.* to avoid ambiguity
                $q->select('attendances.*');
            }
        };

        // Initialize query
        $query = Attendance::query();
        $applyFilters($query);

        // Calculate statistics
        $totalAttendance = $query->count();
PHP;

$content = str_replace($oldReportStart, $newReportStart, $content);

$oldReturn = <<<'PHP'
        $chartData = [
            'labels' => $chartDataRaw->pluck('date')->toArray(),
            'data' => $chartDataRaw->pluck('count')->toArray()
        ];

        return view('attendance.report', compact(
            'services',
            'totalAttendance',
            'averageAttendance',
            'peakAttendance',
            'growthRate',
            'attendanceRecords',
            'chartData'
        ));
    }
PHP;

$newReturn = <<<'PHP'
        $chartDataRaw = $chartQuery->select(
                DB::raw('DATE(attendances.check_in_time) as date'),
                DB::raw('COUNT(attendances.id) as count')
            )
            ->groupBy(DB::raw('DATE(attendances.check_in_time)'))
            ->orderBy(DB::raw('DATE(attendances.check_in_time)'))
            ->get();

        $chartData = [
            'labels' => $chartDataRaw->pluck('date')->toArray(),
            'data' => $chartDataRaw->pluck('count')->toArray()
        ];

        // Fetch distribution for Departments
        $deptQuery = Attendance::query()
            ->join('members', 'attendances.member_id', '=', 'members.id')
            ->join('member_departments', 'members.id', '=', 'member_departments.member_id')
            ->join('departments', 'member_departments.department_id', '=', 'departments.id');
            
        if ($request->filled(['start_date', 'end_date'])) {
            $deptQuery->whereBetween('attendances.check_in_time', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        if ($request->filled('service_id')) {
            $deptQuery->where('attendances.service_id', $request->input('service_id'));
        }
        
        $deptDistributionRaw = $deptQuery->select('departments.name as label', DB::raw('COUNT(attendances.id) as count'))
            ->groupBy('departments.name')
            ->get();

        $deptChartData = [
            'labels' => $deptDistributionRaw->pluck('label')->toArray(),
            'data' => $deptDistributionRaw->pluck('count')->toArray()
        ];

        // Fetch distribution for Groups
        $groupQuery = Attendance::query()
            ->join('members', 'attendances.member_id', '=', 'members.id')
            ->whereNotNull('members.church_group')
            ->where('members.church_group', '!=', '');
            
        if ($request->filled(['start_date', 'end_date'])) {
            $groupQuery->whereBetween('attendances.check_in_time', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }
        if ($request->filled('service_id')) {
            $groupQuery->where('attendances.service_id', $request->input('service_id'));
        }
        
        $groupDistributionRaw = $groupQuery->select('members.church_group as label', DB::raw('COUNT(attendances.id) as count'))
            ->groupBy('members.church_group')
            ->get();

        $groupChartData = [
            'labels' => $groupDistributionRaw->pluck('label')->toArray(),
            'data' => $groupDistributionRaw->pluck('count')->toArray()
        ];

        return view('attendance.report', compact(
            'services',
            'formDepartments',
            'formGroups',
            'totalAttendance',
            'averageAttendance',
            'peakAttendance',
            'growthRate',
            'attendanceRecords',
            'chartData',
            'deptChartData',
            'groupChartData'
        ));
    }
PHP;

$content = str_replace(
    [
        <<<'PHP'
        $chartDataRaw = $chartQuery->select(
                DB::raw('DATE(check_in_time) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(check_in_time)'))
            ->orderBy(DB::raw('DATE(check_in_time)'))
            ->get();

        $chartData = [
            'labels' => $chartDataRaw->pluck('date')->toArray(),
            'data' => $chartDataRaw->pluck('count')->toArray()
        ];

        return view('attendance.report', compact(
            'services',
            'totalAttendance',
            'averageAttendance',
            'peakAttendance',
            'growthRate',
            'attendanceRecords',
            'chartData'
        ));
    }
PHP
    ], 
    $newReturn, 
    $content
);

file_put_contents('app/Http/Controllers/AttendanceController.php', $content);
