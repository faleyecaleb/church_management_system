<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-notifications')->only(['index', 'show']);
        $this->middleware('permission:create-notifications')->only(['create', 'store']);
        $this->middleware('permission:edit-notifications')->only(['edit', 'update']);
        $this->middleware('permission:delete-notifications')->only('destroy');
    }

    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by read/unread
        if ($request->has('read')) {
            if ($request->read) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->latest()->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
            'types' => [
                Notification::TYPE_BIRTHDAY => 'Birthday',
                Notification::TYPE_ANNIVERSARY => 'Anniversary',
                Notification::TYPE_MILESTONE => 'Milestone',
                Notification::TYPE_CUSTOM => 'Custom',
                Notification::TYPE_FOLLOWUP => 'Follow-up'
            ],
            'statuses' => [
                Notification::STATUS_PENDING => 'Pending',
                Notification::STATUS_SCHEDULED => 'Scheduled',
                Notification::STATUS_SENT => 'Sent',
                Notification::STATUS_FAILED => 'Failed'
            ]
        ]);
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        $members = Member::all();
        return view('notifications.create', compact('members'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', [
                Notification::TYPE_BIRTHDAY,
                Notification::TYPE_ANNIVERSARY,
                Notification::TYPE_MILESTONE,
                Notification::TYPE_CUSTOM,
                Notification::TYPE_FOLLOWUP
            ]),
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_id' => 'required|exists:members,id',
            'scheduled_at' => 'nullable|date|after:now',
            'data' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $notification = new Notification([
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'recipient_id' => $validated['recipient_id'],
                'recipient_type' => Member::class,
                'data' => $validated['data'] ?? null,
                'status' => $request->has('scheduled_at') 
                    ? Notification::STATUS_SCHEDULED 
                    : Notification::STATUS_PENDING
            ]);

            if ($request->has('scheduled_at')) {
                $notification->scheduled_at = Carbon::parse($validated['scheduled_at']);
            }

            $notification->save();

            DB::commit();

            return redirect()
                ->route('notifications.index')
                ->with('success', 'Notification created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create notification. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        return view('notifications.show', compact('notification'));
    }

    /**
     * Show the form for editing the specified notification.
     */
    public function edit(Notification $notification)
    {
        if ($notification->isSent()) {
            return back()->with('error', 'Cannot edit sent notifications.');
        }

        $members = Member::all();
        return view('notifications.edit', compact('notification', 'members'));
    }

    /**
     * Update the specified notification.
     */
    public function update(Request $request, Notification $notification)
    {
        if ($notification->isSent()) {
            return back()->with('error', 'Cannot update sent notifications.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'scheduled_at' => 'nullable|date|after:now',
            'data' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $notification->update([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => $validated['data'] ?? null,
                'status' => $request->has('scheduled_at') 
                    ? Notification::STATUS_SCHEDULED 
                    : Notification::STATUS_PENDING,
                'scheduled_at' => $request->has('scheduled_at')
                    ? Carbon::parse($validated['scheduled_at'])
                    : null
            ]);

            DB::commit();

            return redirect()
                ->route('notifications.show', $notification)
                ->with('success', 'Notification updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update notification. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->isSent()) {
            return back()->with('error', 'Cannot delete sent notifications.');
        }

        try {
            $notification->delete();
            return redirect()
                ->route('notifications.index')
                ->with('success', 'Notification deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete notification.');
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        try {
            DB::table('notifications')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return back()->with('success', 'All notifications marked as read.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to mark notifications as read.');
        }
    }
}