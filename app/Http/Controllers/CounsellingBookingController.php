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
        // Both Super Admin and PA can view the dashboard
        $this->middleware('auth');
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

        // TODO: Phase 5 - Send Email Notification to the Member here

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully to ' . ucfirst($request->status) . '.',
            'status' => $booking->status
        ]);
    }
}
