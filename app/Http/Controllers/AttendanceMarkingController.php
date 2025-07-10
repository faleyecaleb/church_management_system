<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Member;
use App\Models\MemberDepartment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceMarkingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:attendance.create']);
    }

    /**
     * Display the first step of attendance marking (service selection)
     */
    public function index()
    {
        $services = Service::orderBy('day_of_week')->orderBy('start_time')->get();
        return view('attendance.marking.step1', compact('services'));
    }

    /**
     * Process the first step and show the second step (member selection)
     */
    public function processStep1(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'attendance_date' => 'required|date',
            'default_status' => 'required|in:present,absent,late',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $attendanceDate = Carbon::parse($validated['attendance_date']);
        $defaultStatus = $validated['default_status'];

        // Get all members
        $members = Member::where('membership_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get existing attendance records for this service and date
        $existingAttendance = Attendance::where('service_id', $service->id)
            ->whereDate('check_in_time', $attendanceDate)
            ->get()
            ->keyBy('member_id');

        return view('attendance.marking.step2', compact(
            'service',
            'attendanceDate',
            'defaultStatus',
            'members',
            'existingAttendance'
        ));
    }

    /**
     * Process the second step and save attendance records
     */
    public function processStep2(Request $request)
    {
        info('Attendance marking request data:', [
            'all_data' => $request->all(),
            'member_status' => $request->input('member_status')
        ]);

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'attendance_date' => 'required|date',
            'member_status' => 'required|array',
            'member_status.*' => 'required|in:present,absent,late',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $attendanceDate = Carbon::parse($validated['attendance_date']);
        $memberStatuses = $validated['member_status'];
        
        DB::beginTransaction();
        
        try {
            $now = now();
            $checkedIn = 0;
            $updated = 0;
            $count = 0;
            
            foreach ($memberStatuses as $memberId => $status) {
                $count++;
                info('Processing member attendance:', [
                    'member_id' => $memberId,
                    'status' => $status,
                    'service_id' => $service->id,
                    'date' => $attendanceDate->format('Y-m-d')
                ]);

                if ($status === 'absent') {
                    // Create or update attendance record for absent member
                    $attendance = Attendance::updateOrCreate(
                        [
                            'member_id' => $memberId,
                            'service_id' => $service->id,
                            'check_in_time' => Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s'))
                        ],
                        [
                            'check_in_method' => 'manual',
                            'checked_in_by' => Auth::id(),
                            'is_present' => false,
                            'is_absent' => true
                        ]
                    );
                    
                    info('Updated/Created absent record');
                    continue;
                }
                
                // Check if attendance record already exists
                $attendance = Attendance::where('member_id', $memberId)
                    ->where('service_id', $service->id)
                    ->whereDate('check_in_time', $attendanceDate)
                    ->first();
                
                info('Existing attendance record:', ['attendance' => $attendance]);
                
                if ($attendance) {
                    info('Updating existing attendance record:', ['attendance_id' => $attendance->id]);
                    // Update existing record
                    $attendance->update([
                        'check_in_method' => 'manual',
                        'checked_in_by' => Auth::id(),
                        // For 'late' status, set check-in time to 15 minutes after service start
                        'check_in_time' => $status === 'late' 
                            ? Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s'))->addMinutes(15)
                            : Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s')),
                        'is_present' => true,
                        'is_absent' => false
                    ]);
                    info("After update -  check in time of status whether late or not {$attendance->check_in_time}{$attendanceDate}");
                    $updated++;
                } else {
                    info('Creating new attendance record');
                    // Create new attendance record
                    Attendance::create([
                        'member_id' => $memberId,
                        'service_id' => $service->id,
                        'check_in_method' => 'manual',
                        'checked_in_by' => Auth::id(),
                        // For 'late' status, set check-in time to 15 minutes after service start
                        'check_in_time' => $status === 'late' 
                            ? Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s'))->addMinutes(15)
                            : Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s')),
                        'is_present' => true,
                        'is_absent' => false
                    ]);
                    info('Creating new attendance record Done');
                    $checkedIn++;
                }
            }
            
            DB::commit();
            info("Attendance marked successfully: {$checkedIn} new records, {$updated} updated. {$count}");
            $message = "Attendance marked successfully: {$checkedIn} new records, {$updated} updated.";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('attendance.service', [
                        'service_id' => $service->id,
                        'date' => $attendanceDate->format('Y-m-d')
                    ])
                ]);
            }
            
            return redirect()->route('attendance.service', [
                'service_id' => $service->id,
                'date' => $attendanceDate->format('Y-m-d')
            ])->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Failed to mark attendance: ' . $e->getMessage();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Show bulk attendance marking page
     */
    public function bulkMarking()
    {
        $services = Service::orderBy('name')->get();
        return view('attendance.bulk-marking', compact('services'));
    }

    /**
     * Get members for bulk marking with optimized loading
     */
    public function getBulkMembers(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'attendance_date' => 'required|date'
        ]);

        try {
            $serviceId = $request->service_id;
            $attendanceDate = $request->attendance_date;

            // Get members with their departments and existing attendance
            $members = Member::with(['departments'])
                ->select('id', 'first_name', 'last_name', 'email', 'gender')
                ->where('membership_status', 'active')
                ->get()
                ->map(function ($member) use ($serviceId, $attendanceDate) {
                    // Get existing attendance for this date/service
                    $attendance = Attendance::where([
                        'member_id' => $member->id,
                        'service_id' => $serviceId,
                        'attendance_date' => $attendanceDate
                    ])->first();

                    return [
                        'id' => $member->id,
                        'full_name' => $member->full_name,
                        'email' => $member->email,
                        'gender' => $member->gender,
                        'departments' => $member->departments->pluck('department')->toArray(),
                        'status' => $attendance ? $attendance->status : null
                    ];
                });

            return response()->json([
                'success' => true,
                'members' => $members
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load members: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark attendance for multiple members in bulk
     */
    public function bulkMark(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'attendance_date' => 'required|date',
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
            'status' => 'required|in:present,absent,late'
        ]);

        try {
            DB::beginTransaction();

            $serviceId = $request->service_id;
            $attendanceDate = $request->attendance_date;
            $memberIds = $request->member_ids;
            $status = $request->status;
            $markedBy = Auth::id();

            $count = 0;

            foreach ($memberIds as $memberId) {
                // Check if attendance already exists
                $attendance = Attendance::where([
                    'member_id' => $memberId,
                    'service_id' => $serviceId,
                    'attendance_date' => $attendanceDate
                ])->first();

                if ($attendance) {
                    // Update existing record
                    $attendance->update([
                        'check_in_time' => $status === 'present' ? Carbon::parse($attendanceDate . ' ' . now()->format('H:i:s')) : $attendance->check_in_time,
                        'check_in_method' => 'bulk_manual',
                        'checked_in_by' => $markedBy,
                        'is_present' => $status === 'present',
                        'is_absent' => $status === 'absent',
                        'status' => $status
                    ]);
                } else {
                    // Create new record
                    Attendance::create([
                        'member_id' => $memberId,
                        'service_id' => $serviceId,
                        'attendance_date' => $attendanceDate,
                        'check_in_time' => $status === 'present' ? Carbon::parse($attendanceDate . ' ' . now()->format('H:i:s')) : null,
                        'check_in_method' => 'bulk_manual',
                        'checked_in_by' => $markedBy,
                        'is_present' => $status === 'present',
                        'is_absent' => $status === 'absent',
                        'status' => $status
                    ]);
                }
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully marked {$count} members as {$status}",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }
    }
}