<?php

namespace App\Http\Controllers;

use App\Models\CounsellingBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CounsellingBookingController extends Controller
{
    public function __construct()
    {
        if (!request()->is('api/*')) {
            // Both Super Admin and PA can view the dashboard
            $this->middleware('auth');
        }
    }

    /**
     * Display a listing of the counselling bookings.
     */
    public function index(Request $request)
    {
        // Check if user is Super Admin or PA
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasPermission('counselling.manage')) {
            abort(403, 'Unauthorized access to Counselling Bookings.');
        }

        $query = CounsellingBooking::with(['member'])
            ->orderBy('requested_date', 'desc')
            ->orderBy('requested_time', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(15);

        return view('counselling.index', compact('bookings'));
    }

    /**
     * Update the status of a booking (Approve/Reject)
     */
    public function updateStatus(Request $request, CounsellingBooking $booking)
    {
        // ONLY the PA (who has counselling.manage permission) can do this.
        // Super Admins should ideally not interfere with PA's daily scheduling.
        if (!Auth::user()->hasPermission('counselling.manage')) {
            return response()->json([
                'success' => false,
                'message' => 'Only the Personal Assistant is authorized to approve or reject bookings.'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected,completed,cancelled',
            'admin_notes' => 'nullable|string'
        ]);

        $booking->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes
        ]);

        // Dispatch Expo Push Notification if member has a device token registered
        try {
            $member = $booking->member;
            if ($member) {
                $statusLabel = ucfirst($booking->status);
                $title = "Counselling Booking: {$statusLabel}";
                $body = "Your counselling request has been marked as {$booking->status}. Reason: " . substr($booking->reason, 0, 40) . "...";
                
                $expoService = new \App\Services\ExpoNotificationService();
                $expoService->notifyMember($member, $title, $body, [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'type' => 'counselling_booking_update'
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to dispatch push notification for counselling booking update: ' . $e->getMessage());
        }

        // TODO: Phase 5 - Send Email Notification to the Member here

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully to ' . ucfirst($request->status) . '.',
            'status' => $booking->status
        ]);
    }

    /**
     * Get counselling bookings for the authenticated member.
     */
    public function apiIndex(Request $request)
    {
        $user = auth()->user();
        $member = $user instanceof \App\Models\Member ? $user : $user->member;

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'No member profile found.'
            ], 404);
        }

        $bookings = CounsellingBooking::where('member_id', $member->id)
            ->orderBy('requested_date', 'desc')
            ->orderBy('requested_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Store a new counselling booking via API.
     */
    public function apiStore(Request $request)
    {
        $user = auth()->user();
        $member = $user instanceof \App\Models\Member ? $user : $user->member;

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'No member profile found.'
            ], 404);
        }

        $validated = $request->validate([
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_time' => 'required|string', // Expecting 'H:i' format
            'reason' => 'required|string|max:1000',
        ]);

        $booking = CounsellingBooking::create([
            'member_id' => $member->id,
            'church_id' => $member->church_id,
            'requested_date' => $validated['requested_date'],
            'requested_time' => $validated['requested_time'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Counselling booking submitted successfully. Pending PA approval!',
            'data' => $booking
        ], 201);
    }
}
