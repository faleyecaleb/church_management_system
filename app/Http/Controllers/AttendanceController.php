<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:attendance.view')->only(['index', 'show', 'report', 'getStats', 'dashboard']);
        $this->middleware('permission:attendance.create')->only(['create', 'store', 'checkIn', 'showQrCode', 'processQrCode', 'mobileCheckIn', 'checkInMultiple', 'checkInMember']);
        $this->middleware('permission:attendance.update')->only(['edit', 'update', 'checkOutMember', 'checkOutAll']);
        $this->middleware('permission:attendance.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Attendance::with(['member', 'service', 'checkedInBy'])
            ->latest('check_in_time');

        // Apply filters
        if ($request->filled('date')) {
            $date = $request->input('date');
            $query->whereDate('check_in_time', $date);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        if ($request->filled('check_in_method')) {
            $query->where('check_in_method', $request->input('check_in_method'));
        }

        $attendances = $query->paginate(20);
        $services = Service::active()->get();

        return view('attendance.index', compact('attendances', 'services'));
    }

    public function report(Request $request)
    {
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
        $averageAttendance = $query->avg(DB::raw('1')) ?? 0;
        $peakAttendance = $query->select(DB::raw('DATE(check_in_time) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderByDesc('count')
            ->value('count') ?? 0;

        // Calculate growth rate
        $previousPeriodCount = $query->where('check_in_time', '<', now()->subMonth())->count();
        $currentPeriodCount = $query->where('check_in_time', '>=', now()->subMonth())->count();
        $growthRate = $previousPeriodCount > 0 
            ? (($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100
            : 0;

        // Get attendance records for the table
        $attendanceRecords = $query->select(
                DB::raw('DATE(check_in_time) as date'),
                'services.name as service_name',
                DB::raw('COUNT(*) as count'),
                'check_in_method'
            )
            ->join('services', 'services.id', '=', 'attendances.service_id')
            ->groupBy('date', 'services.name', 'check_in_method')
            ->orderByDesc('date')
            ->paginate(10);

        // Prepare chart data
        $chartData = [
            'labels' => $attendanceRecords->map(fn($record) => $record->date)->toArray(),
            'data' => $attendanceRecords->map(fn($record) => $record->count)->toArray()
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

    public function create()
    {
        $members = Member::orderBy('first_name')->get();
        $services = Service::active()->get();
        return view('attendance.create', compact('members', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'service_id' => 'required|exists:services,id',
            'check_in_time' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        // Check for duplicate attendance
        $existingAttendance = Attendance::where('member_id', $validated['member_id'])
            ->where('service_id', $validated['service_id'])
            ->whereDate('check_in_time', $validated['check_in_time'])
            ->exists();

        if ($existingAttendance) {
            return back()->with('error', 'Attendance already recorded for this member and service.');
        }

        $attendance = Attendance::create([
            'member_id' => $validated['member_id'],
            'service_id' => $validated['service_id'],
            'check_in_time' => $validated['check_in_time'],
            'check_in_method' => 'manual',
            'checked_in_by' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('attendance.service')
            ->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['member', 'service']);
        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $members = Member::orderBy('first_name')->get();
        $services = Service::active()->get();
        return view('attendance.edit', compact('attendance', 'members', 'services'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'service_id' => 'required|exists:services,id',
            'check_in_time' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Check for duplicate attendance excluding current record
        $existingAttendance = Attendance::where('member_id', $validated['member_id'])
            ->where('service_id', $validated['service_id'])
            ->whereDate('check_in_time', $validated['check_in_time'])
            ->where('id', '!=', $attendance->id)
            ->exists();

        if ($existingAttendance) {
            return back()->with('error', 'Attendance already recorded for this member and service.');
        }

        $attendance->update($validated);

        return redirect()->route('attendance.show', $attendance)
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()
            ->route('attendance.service')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Display QR code for check-in.
     */
    public function showQrCode(Service $service)
    {
        if (!$service->isCheckInAllowed()) {
            return back()->with('error', 'Check-in is not currently allowed for this service.');
        }

        $token = $this->generateQrToken($service);
        $qrCode = QrCode::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate(route('attendance.process-qr', [
                'service' => $service->id,
                'token' => $token
            ]));

        $expiryTime = $this->getQrExpiryTime($service);

        return view('attendance.qr-code', compact('service', 'qrCode', 'expiryTime'));
    }

    /**
     * Process QR code check-in.
     */
    public function processQrCode(Request $request, Service $service)
    {
        try {
            if (!$this->validateQrToken($request->token, $service)) {
                throw new \Exception('Invalid or expired QR code.');
            }

            if (!$service->isCheckInAllowed()) {
                throw new \Exception('Check-in is not currently allowed.');
            }

            // Get the authenticated member
            $member = auth()->user()->member;

            if (!$member) {
                throw new \Exception('No member profile found.');
            }

            // Check for duplicate attendance
            $existingAttendance = Attendance::where('member_id', $member->id)
                ->where('service_id', $service->id)
                ->whereDate('check_in_time', now())
                ->exists();

            if ($existingAttendance) {
                throw new \Exception('You have already checked in for this service.');
            }

            // Verify location if geofencing is enabled
            if (config('attendance.require_geofencing')) {
                $this->verifyLocation($request);
            }

            DB::transaction(function () use ($member, $service, $request) {
                Attendance::create([
                    'member_id' => $member->id,
                    'service_id' => $service->id,
                    'check_in_time' => now(),
                    'check_in_method' => 'qr',
                    'check_in_location' => $request->ip(),
                    'location_verified' => config('attendance.require_geofencing'),
                    'checked_in_by' => auth()->id(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check in a single member.
     */
    public function checkInMember(Service $service, Member $member)
    {
        try {
            DB::beginTransaction();

            // Check if member is already checked in
            $existing = Attendance::where('service_id', $service->id)
                ->where('member_id', $member->id)
                ->whereDate('check_in_time', now())
                ->first();

            if ($existing) {
                return response()->json([
                    'error' => 'Member is already checked in'
                ], 400);
            }

            // Create attendance record
            $attendance = Attendance::create([
                'service_id' => $service->id,
                'member_id' => $member->id,
                'check_in_time' => now(),
                'check_in_method' => 'manual',
                'checked_in_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Member checked in successfully',
                'attendance' => $attendance->load('member'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to check in member'], 500);
        }
    }

    /**
     * Check in multiple members at once.
     */
    public function checkInMultiple(Service $service, Request $request)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
        ]);

        try {
            DB::beginTransaction();

            $checkedIn = 0;
            $alreadyCheckedIn = 0;
            $now = now();

            foreach ($request->member_ids as $memberId) {
                // Skip if already checked in
                if (Attendance::where('service_id', $service->id)
                    ->where('member_id', $memberId)
                    ->whereDate('check_in_time', now())
                    ->exists()) {
                    $alreadyCheckedIn++;
                    continue;
                }

                // Create attendance record
                Attendance::create([
                    'service_id' => $service->id,
                    'member_id' => $memberId,
                    'check_in_time' => $now,
                    'check_in_method' => 'manual_bulk',
                    'checked_in_by' => auth()->id(),
                ]);

                $checkedIn++;
            }

            DB::commit();

            return response()->json([
                'message' => "{$checkedIn} members checked in successfully" .
                    ($alreadyCheckedIn > 0 ? " ({$alreadyCheckedIn} already checked in)" : ''),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to check in members'], 500);
        }
    }

    /**
     * Check out a member.
     */
    public function checkOutMember(Service $service, Member $member)
    {
        try {
            $attendance = Attendance::where('service_id', $service->id)
                ->where('member_id', $member->id)
                ->whereDate('check_in_time', now())
                ->whereNull('check_out_time')
                ->firstOrFail();

            $attendance->update([
                'check_out_time' => now(),
                'checked_out_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Member checked out successfully',
                'attendance' => $attendance->fresh()->load('member'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to check out member'], 500);
        }
    }

    /**
     * Check out all members.
     */
    public function checkOutAll(Service $service)
    {
        try {
            DB::beginTransaction();

            $count = Attendance::where('service_id', $service->id)
                ->whereDate('check_in_time', now())
                ->whereNull('check_out_time')
                ->update([
                    'check_out_time' => now(),
                    'checked_out_by' => auth()->id(),
                ]);

            DB::commit();

            return response()->json([
                'message' => "{$count} members checked out successfully",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to check out members'], 500);
        }
    }

    /**
     * Get attendance statistics.
     */
    public function getStats(Request $request)
    {
        $query = Attendance::query();

        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $stats = [
            'total' => $query->count(),
            'by_method' => $query->groupBy('check_in_method')
                ->selectRaw('check_in_method, count(*) as count')
                ->pluck('count', 'check_in_method'),
            'by_service' => $query->groupBy('service_id')
                ->selectRaw('service_id, count(*) as count')
                ->pluck('count', 'service_id'),
            'trend' => $query->groupBy(DB::raw('DATE(check_in_time)'))
                ->selectRaw('DATE(check_in_time) as date, count(*) as count')
                ->orderBy('date')
                ->get()
                ->pluck('count', 'date')
        ];

        return response()->json($stats);
    }

    /**
     * Generate QR code token.
     */
    protected function generateQrToken(Service $service)
    {
        $data = [
            'service_id' => $service->id,
            'timestamp' => now()->timestamp,
            'expiry' => $this->getQrExpiryTime($service)->timestamp,
        ];

        return encrypt($data);
    }

    /**
     * Validate QR code token.
     */
    protected function validateQrToken($token, Service $service)
    {
        try {
            $data = decrypt($token);
            
            return $data['service_id'] === $service->id &&
                   now()->timestamp <= $data['expiry'];

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get QR code expiry time.
     */
    protected function getQrExpiryTime(Service $service)
    {
        return $service->start_time->addMinutes(config('attendance.qr_expiry_after', 15));
    }

    /**
     * Verify user's location for check-in.
     */
    protected function verifyLocation(Request $request)
    {
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        
        if (!$userLat || !$userLng) {
            throw new \Exception('Location data is required for check-in.');
        }

        $churchLat = config('attendance.church_latitude');
        $churchLng = config('attendance.church_longitude');
        $maxDistance = config('attendance.allowed_distance', 100);

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $userLat,
            $userLng,
            $churchLat,
            $churchLng
        );

        if ($distance > $maxDistance) {
            throw new \Exception('You are too far from the church to check in.');
        }
    }

    /**
     * Calculate distance between two points using Haversine formula.
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Display the attendance dashboard.
     */
    public function dashboard(Request $request)
    {
        $today = now()->format('Y-m-d');
        $currentService = null;
        
        // Get today's services
        $todaysServices = Service::where('day_of_week', now()->dayOfWeek)
            ->where('status', 'active')
            ->orderBy('start_time')
            ->get();
            
        // Find the most recent or current service
        if ($todaysServices->isNotEmpty()) {
            $currentService = $todaysServices->first();
        }
        
        // Get attendance data for today or selected date
        $selectedDate = $request->get('date', $today);
        $showToday = $request->get('tab', 'today') === 'today';
        
        if ($showToday && $currentService) {
            $attendanceQuery = Attendance::with(['member', 'service'])
                ->where('service_id', $currentService->id)
                ->whereDate('check_in_time', $selectedDate);
        } else {
            $attendanceQuery = Attendance::with(['member', 'service'])
                ->whereDate('check_in_time', $selectedDate);
        }
        
        $attendances = $attendanceQuery->get();
        
        // Calculate KPIs
        $totalMarked = $attendances->count();
        $totalMembers = Member::where('membership_status', 'active')->count();
        $present = $attendances->whereNull('check_out_time')->count();
        $presentCount = $attendances->where('is_present', 1)->count();
        $absentCount = $attendances->where('is_absent', 1)->count();
        $absent = $totalMembers - $totalMarked;
        $late = $attendances->where('check_in_time', '>', function($query) use ($currentService) {
            if ($currentService) {
                return $currentService->start_time->addMinutes(15);
            }
            return now()->subHour();
        })->count();
        
        // Gender-based analytics
        // Check if gender column exists in the members table
        $hasGenderColumn = Schema::hasColumn('members', 'gender');
        
        if ($hasGenderColumn) {
            $maleAttendance = $attendances->filter(function($attendance) {
                return $attendance->member->gender === 'male';
            });
            $femaleAttendance = $attendances->filter(function($attendance) {
                return $attendance->member->gender === 'female';
            });
            
            $maleStats = [
                'total' => $maleAttendance->count(),
                'present' => $maleAttendance->where('is_present', 1)->count(),
                'absent' => $maleAttendance->where('is_absent', 1)->count(),
                // 'absent' => Member::where('gender', 'male')->where('membership_status', 'active')->count() - $maleAttendance->count(),
                'late' => $maleAttendance->where('check_in_time', '>', function($query) use ($currentService) {
                    if ($currentService) {
                        return $currentService->start_time->addMinutes(15);
                    }
                    return now()->subHour();
                })->count()
            ];
            
            $femaleStats = [
                'total' => $femaleAttendance->count(),
                'present' => $femaleAttendance->whereNull('check_out_time')->count(),
                'absent' => Member::where('gender', 'female')->where('membership_status', 'active')->count() - $femaleAttendance->count(),
                'late' => $femaleAttendance->where('check_in_time', '>', function($query) use ($currentService) {
                    if ($currentService) {
                        return $currentService->start_time->addMinutes(15);
                    }
                    return now()->subHour();
                })->count()
            ];
        } else {
            // Provide default values if gender column doesn't exist
            $maleStats = [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0
            ];
            
            $femaleStats = [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0
            ];
        }
        
        // Department attendance
        $departmentStats = Member::select('department', DB::raw('COUNT(*) as total_members'))
            ->where('membership_status', 'active')
            ->whereNotNull('department')
            ->groupBy('department')
            ->get()
            ->map(function($dept) use ($attendances) {
                $deptAttendance = $attendances->filter(function($attendance) use ($dept) {
                    return $attendance->member->department === $dept->department;
                })->count();
                
                return [
                    'name' => $dept->department,
                    'total_members' => $dept->total_members,
                    'present' => $deptAttendance,
                    'percentage' => $dept->total_members > 0 ? round(($deptAttendance / $dept->total_members) * 100, 1) : 0
                ];
            });
            
        // Recent attendance activity (last 7 services)
        if (Schema::hasColumn('attendances', 'check_out_time')) {
            $recentActivity = Attendance::select(
                    DB::raw('DATE(check_in_time) as date'),
                    'service_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, service.start_time, check_in_time) <= 15 AND check_out_time IS NULL THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, service.start_time, check_in_time) > 15 THEN 1 ELSE 0 END) as late'),
                    DB::raw('COUNT(CASE WHEN check_out_time IS NOT NULL THEN 1 END) as left_early')
                )
                ->join('services as service', 'attendances.service_id', '=', 'service.id')
                ->with('service')
                ->groupBy('date', 'service_id')
                ->orderByDesc('date')
                ->limit(7)
                ->get()
                ->map(function($activity) {
                    $service = Service::find($activity->service_id);
                    $totalMembers = Member::where('membership_status', 'active')->count();
                    $absent = $totalMembers - $activity->total;
                    
                    return [
                    'date' => $activity->date,
                    'service_name' => $service ? $service->name : 'Unknown Service',
                    'total' => $activity->total,
                    'present' => $activity->present,
                    'late' => $activity->late,
                    'absent' => $absent,
                    'left_early' => $activity->left_early,
                    'percentage' => $totalMembers > 0 ? round(($activity->total / $totalMembers) * 100, 1) : 0
                ];
            });
        } else {
            // Fallback if check_out_time column doesn't exist yet
            $recentActivity = Attendance::select(
                    DB::raw('DATE(check_in_time) as date'),
                    'service_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, service.start_time, check_in_time) <= 15 THEN 1 ELSE 0 END) as present'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, service.start_time, check_in_time) > 15 THEN 1 ELSE 0 END) as late'),
                    DB::raw('0 as left_early')
                )
                ->join('services as service', 'attendances.service_id', '=', 'service.id')
                ->with('service')
                ->groupBy('date', 'service_id')
                ->orderByDesc('date')
                ->limit(7)
                ->get()
                ->map(function($activity) {
                    $service = Service::find($activity->service_id);
                    $totalMembers = Member::where('membership_status', 'active')->count();
                    $absent = $totalMembers - $activity->total;
                    
                    return [
                        'date' => $activity->date,
                        'service_name' => $service ? $service->name : 'Unknown Service',
                        'total' => $activity->total,
                        'present' => $activity->present,
                        'late' => $activity->late,
                        'absent' => $absent,
                        'left_early' => $activity->left_early,
                        'percentage' => $totalMembers > 0 ? round(($activity->total / $totalMembers) * 100, 1) : 0
                    ];
                });
        }
        
        return view('attendance.dashboard', compact(
            'currentService',
            'todaysServices',
            'totalMarked',
            'totalMembers',
            'present',
            'absent',
            'late',
            'maleStats',
            'femaleStats',
            'departmentStats',
            'recentActivity',
            'selectedDate',
            'showToday',
            'presentCount',
            'absentCount'
        ));
    }
}