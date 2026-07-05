<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:communication.view')->only(['index', 'show']);
        $this->middleware('permission:communication.create')->only(['create', 'store']);
        $this->middleware('permission:communication.update')->only(['edit', 'update']);
        
        $this->middleware(function ($request, $next) {
            if ($request->user() && ($request->user()->isAdmin() || $request->user()->can('communication.delete'))) {
                return $next($request);
            }
            abort(403, 'Unauthorized action.');
        })->only('destroy');
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
                Notification::TYPE_FOLLOWUP => 'Follow-up',
                Notification::TYPE_SERMON => 'Sermon Banner',
            ],
            'statuses' => [
                Notification::STATUS_PENDING => 'Pending',
                Notification::STATUS_SCHEDULED => 'Scheduled',
                Notification::STATUS_SENT => 'Sent',
                Notification::STATUS_FAILED => 'Failed',
            ],
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
            'type' => 'required|in:'.implode(',', [
                Notification::TYPE_BIRTHDAY,
                Notification::TYPE_ANNIVERSARY,
                Notification::TYPE_MILESTONE,
                Notification::TYPE_CUSTOM,
                Notification::TYPE_FOLLOWUP,
                Notification::TYPE_SERMON,
            ]),
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'selection_type' => 'required|in:broadcast,target',
            'recipient_ids' => 'required_if:selection_type,target|array',
            'recipient_ids.*' => 'exists:members,id',
            'scheduled_at' => 'nullable|date|after:now',
            'data' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('notifications/images', 'public');
            }

            $status = $request->has('scheduled_at') && !empty($validated['scheduled_at'])
                ? Notification::STATUS_SCHEDULED
                : Notification::STATUS_PENDING;

            $scheduledAt = $request->has('scheduled_at') && $validated['scheduled_at']
                ? Carbon::parse($validated['scheduled_at'])
                : null;

            if ($request->input('selection_type') === 'broadcast') {
                // Create single Global/Broadcast Notification (recipient_id = null)
                $notification = Notification::create([
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'image_url' => $imagePath,
                    'recipient_id' => null,
                    'recipient_type' => Member::class,
                    'data' => $validated['data'] ?? null,
                    'status' => $status,
                    'scheduled_at' => $scheduleDate ?? $scheduled_at ?? $scheduled_at = null,
                ]);
                if ($scheduled_at = $validated['scheduled_at'] ?? null) {
                    $notification->scheduled_at = Carbon::parse($scheduled_at);
                    $notification->save();
                }
            } else {
                // Create targeted notifications for each selected recipient
                $recipientIds = $validated['recipient_ids'] ?? [];
                foreach ($memberIds = array_unique($recipientIds) as $recipientId) {
                    $notification = Notification::create([
                        'type' => $validated['type'],
                        'title' => $validated['title'],
                        'message' => $validated['message'],
                        'image_url' => $imagePath,
                        'recipient_id' => $recipientId,
                        'recipient_type' => Member::class,
                        'data' => $validated['data'] ?? null,
                        'status' => $status,
                    ]);
                    if ($scheduledAt) {
                        $notification->scheduled_at = $scheduledAt;
                        $notification->save();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('notifications.index')
                ->with('success', 'Notification(s) created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to create notification. '.$e->getMessage());
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
            'data' => 'nullable|array',
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
                    : null,
            ]);

            DB::commit();

            return redirect()
                ->route('notifications.show', $notification)
                ->with('success', 'Notification updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to update notification. '.$e->getMessage());
        }
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification)
    {
        // Allowed deleting sent notifications per requirements

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
