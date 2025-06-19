<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Member;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage attendance']);
    }

    /**
     * Display the service attendance management page.
     */
    public function index(Request $request)
    {
        $services = Service::orderBy('day_of_week')->orderBy('start_time')->get();
        $members = Member::orderBy('first_name')->orderBy('last_name')->get();
        $selectedDate = $request->date ? Carbon::parse($request->date) : now();

        $selectedService = null;
        $attendances = collect();

        if ($request->service_id) {
            $selectedService = Service::findOrFail($request->service_id);
            $attendances = Attendance::with('member')
                ->where('service_id', $selectedService->id)
                ->whereDate('check_in_time', $selectedDate)
                ->orderBy('check_in_time')
                ->get();
        }

        // dd($selectedService);

        return view('attendance.service-attendance', compact(
            'services',
            'members',
            'selectedService',
            'selectedDate',
            'attendances'
        ));
    }

    /**
     * Check in multiple members for a service.
     */
    public function checkInMultiple(Request $request, Service $service)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
            'date' => 'required|date'
        ]);

        $date = Carbon::parse($request->date);
        $checkInTime = now();

        DB::beginTransaction();

        try {
            foreach ($request->member_ids as $memberId) {
                // Check if member is already checked in
                $exists = Attendance::where('member_id', $memberId)
                    ->where('service_id', $service->id)
                    ->whereDate('check_in_time', $date)
                    ->exists();

                if (!$exists) {
                    Attendance::create([
                        'member_id' => $memberId,
                        'service_id' => $service->id,
                        'check_in_time' => $checkInTime,
                        'check_in_method' => 'manual',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Members checked in successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to check in members'
            ], 500);
        }
    }

    /**
     * Check out a specific member.
     */
    public function checkOut(Attendance $attendance)
    {
        if ($attendance->check_out_time) {
            return response()->json([
                'error' => 'Member is already checked out'
            ], 400);
        }

        $attendance->update([
            'check_out_time' => now()
        ]);

        return response()->json([
            'message' => 'Member checked out successfully'
        ]);
    }

    /**
     * Check out all members for a specific service and date.
     */
    public function checkOutAll(Request $request, Service $service)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = Carbon::parse($request->date);

        Attendance::where('service_id', $service->id)
            ->whereDate('check_in_time', $date)
            ->whereNull('check_out_time')
            ->update([
                'check_out_time' => now()
            ]);

        return response()->json([
            'message' => 'All members checked out successfully'
        ]);
    }
}