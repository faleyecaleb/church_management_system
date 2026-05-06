<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Member;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScannerAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:attendance.create']);
    }

    /**
     * Show the rapid scanner interface
     */
    public function index()
    {
        // Get active services for today, or just list all recurring active ones
        $services = Service::active()->orderBy('day_of_week')->get();
        return view('attendance.scanner.index', compact('services'));
    }

    /**
     * Process a fast scan (barcode/fingerprint string or phone number)
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'scan_input' => 'required|string',
        ]);

        $input = trim($request->scan_input);
        $serviceId = $request->service_id;

        // Try to find the member by unique_id or phone number
        $member = Member::where('unique_id', $input)
            ->orWhere('phone', $input)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => "No member found for ID/Phone: {$input}"
            ], 404);
        }

        // Check if already checked in today for this service
        $today = Carbon::today();
        $existing = Attendance::where('member_id', $member->id)
            ->where('service_id', $serviceId)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->is_present) {
            return response()->json([
                'success' => true,
                'message' => "{$member->first_name} {$member->last_name} is already checked in.",
                'member' => $member,
                'status' => 'already_checked_in'
            ]);
        }

        // Check in the member
        if ($existing) {
            $existing->update([
                'is_present' => true,
                'is_absent' => false,
                'status' => 'present',
                'check_in_time' => now(),
                'check_in_method' => 'scanner',
                'checked_in_by' => auth()->id()
            ]);
        } else {
            Attendance::create([
                'member_id' => $member->id,
                'service_id' => $serviceId,
                'attendance_date' => $today,
                'is_present' => true,
                'status' => 'present',
                'check_in_time' => now(),
                'check_in_method' => 'scanner',
                'checked_in_by' => auth()->id()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully checked in: {$member->first_name} {$member->last_name}",
            'member' => $member,
            'status' => 'success'
        ]);
    }
}
