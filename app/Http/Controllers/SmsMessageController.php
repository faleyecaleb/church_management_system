<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Models\SmsTemplate;
use App\Models\Member;
use App\Models\MessageGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsMessageController extends Controller
{
    public function __construct()
    {
        if (!request()->is('api/*')) {
            $this->middleware('auth');
            $this->middleware('permission:communication.view')->only(['index', 'show', 'report']);
            $this->middleware('permission:communication.create')->only(['create', 'store', 'send', 'schedule']);
            $this->middleware('permission:communication.update')->only(['edit', 'update']);
            $this->middleware('permission:communication.delete')->only(['destroy', 'cancel']);
        }
    }

    public function index(Request $request)
    {
        $query = SmsMessage::with('template');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('recipient_numbers', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15);
        $templates = SmsTemplate::active()->get();

        return view('sms.index', compact('messages', 'templates'));
    }

    public function create()
    {
        $templates = SmsTemplate::active()->get();
        $members = Member::whereNotNull('phone')->get();
        $groups = MessageGroup::active()->get();

        return view('sms.create', compact('templates', 'members', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:sms_templates,id',
            'content' => 'required_without:template_id|string',
            'recipient_type' => 'required|in:individual,group,all',
            'recipient_ids' => 'required_unless:recipient_type,all|array',
            'recipient_ids.*' => 'required_unless:recipient_type,all|integer',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,scheduled,queued'
        ]);

        if ($request->filled('template_id')) {
            $template = SmsTemplate::findOrFail($validated['template_id']);
            $validated['content'] = $template->content;
        }

        // Get recipient numbers based on type
        $recipientNumbers = $this->getRecipientNumbers(
            $validated['recipient_type'],
            $validated['recipient_ids'] ?? []
        );

        if (empty($recipientNumbers)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid recipients found.');
        }

        $message = SmsMessage::create([
            'content' => $validated['content'],
            'recipient_numbers' => $recipientNumbers,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => $validated['status'],
            'template_id' => $validated['template_id'] ?? null
        ]);

        if ($validated['status'] === 'queued') {
            $message->send();
        }

        return redirect()->route('sms.show', $message)
            ->with('success', 'SMS message created successfully.');
    }

    public function show(SmsMessage $message)
    {
        $message->load('template');
        return view('sms.show', compact('message'));
    }

    public function edit(SmsMessage $message)
    {
        if (!in_array($message->status, ['draft', 'scheduled'])) {
            return redirect()->route('sms.index')
                ->with('error', 'Only draft or scheduled messages can be edited.');
        }

        $templates = SmsTemplate::active()->get();
        $members = Member::whereNotNull('phone')->get();
        $groups = MessageGroup::active()->get();

        return view('sms.edit', compact('message', 'templates', 'members', 'groups'));
    }

    public function update(Request $request, SmsMessage $message)
    {
        if (!in_array($message->status, ['draft', 'scheduled'])) {
            return redirect()->route('sms.index')
                ->with('error', 'Only draft or scheduled messages can be updated.');
        }

        $validated = $request->validate([
            'template_id' => 'nullable|exists:sms_templates,id',
            'content' => 'required_without:template_id|string',
            'recipient_type' => 'required|in:individual,group,all',
            'recipient_ids' => 'required_unless:recipient_type,all|array',
            'recipient_ids.*' => 'required_unless:recipient_type,all|integer',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,scheduled,queued'
        ]);

        if ($request->filled('template_id')) {
            $template = SmsTemplate::findOrFail($validated['template_id']);
            $validated['content'] = $template->content;
        }

        // Get recipient numbers based on type
        $recipientNumbers = $this->getRecipientNumbers(
            $validated['recipient_type'],
            $validated['recipient_ids'] ?? []
        );

        if (empty($recipientNumbers)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid recipients found.');
        }

        $message->update([
            'content' => $validated['content'],
            'recipient_numbers' => $recipientNumbers,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => $validated['status'],
            'template_id' => $validated['template_id'] ?? null
        ]);

        if ($validated['status'] === 'queued') {
            $message->send();
        }

        return redirect()->route('sms.show', $message)
            ->with('success', 'SMS message updated successfully.');
    }

    public function destroy(SmsMessage $message)
    {
        if (!in_array($message->status, ['draft', 'failed'])) {
            return redirect()->route('sms.index')
                ->with('error', 'Only draft or failed messages can be deleted.');
        }

        $message->delete();

        return redirect()->route('sms.index')
            ->with('success', 'SMS message deleted successfully.');
    }

    public function send(SmsMessage $message)
    {
        if ($message->status !== 'draft') {
            return redirect()->route('sms.index')
                ->with('error', 'Only draft messages can be sent.');
        }

        $message->send();

        return redirect()->route('sms.show', $message)
            ->with('success', 'SMS message queued for sending.');
    }

    public function schedule(SmsMessage $message)
    {
        if ($message->status !== 'draft') {
            return redirect()->route('sms.index')
                ->with('error', 'Only draft messages can be scheduled.');
        }

        $validated = request()->validate([
            'scheduled_at' => 'required|date|after:now'
        ]);

        $message->schedule($validated['scheduled_at']);

        return redirect()->route('sms.show', $message)
            ->with('success', 'SMS message scheduled successfully.');
    }

    public function cancel(SmsMessage $message)
    {
        if ($message->status !== 'scheduled') {
            return redirect()->route('sms.index')
                ->with('error', 'Only scheduled messages can be cancelled.');
        }

        $message->cancel();

        return redirect()->route('sms.show', $message)
            ->with('success', 'SMS message cancelled successfully.');
    }

    protected function getRecipientNumbers($type, $ids = [])
    {
        switch ($type) {
            case 'individual':
                return Member::whereIn('id', $ids)
                    ->whereNotNull('phone')
                    ->pluck('phone')
                    ->toArray();

            case 'group':
                return Member::whereHas('messageGroups', function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                })
                    ->whereNotNull('phone')
                    ->pluck('phone')
                    ->toArray();

            case 'all':
                return Member::whereNotNull('phone')
                    ->pluck('phone')
                    ->toArray();

            default:
                return [];
        }
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        // Messages by status
        $byStatus = SmsMessage::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Daily message counts
        $dailyCounts = SmsMessage::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Template usage
        $templateUsage = SmsMessage::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('template_id')
            ->with('template')
            ->select('template_id', DB::raw('COUNT(*) as count'))
            ->groupBy('template_id')
            ->get();

        return view('sms.report', compact(
            'byStatus',
            'dailyCounts',
            'templateUsage',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get announcements (sent SMS messages and Notifications) for the authenticated member via API.
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

        // --- 1. FETCH APPLICABLE SMS MESSAGES ---
        $departments = $member->departments()
            ->with('department')
            ->get()
            ->map(function ($md) {
                return $md->department ? strtolower($md->department->name) : null;
            })
            ->filter()
            ->toArray();

        $smsQuery = SmsMessage::where('status', 'sent')
            ->where(function ($q) use ($member, $departments) {
                $q->where('recipient_group', 'all')
                  ->orWhere('recipient_group', 'members')
                  ->orWhereIn(DB::raw('LOWER(recipient_group)'), $departments)
                  ->orWhereJsonContains('recipient_ids', $member->id);
            });

        $smsMessages = $smsQuery->get()->map(function ($msg) {
            return [
                'id' => 'sms_' . $msg->id,
                'source' => 'sms',
                'type' => 'announcement',
                'sender' => 'Church Administration',
                'title' => $msg->title ?: 'Church Update',
                'content' => $msg->content,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        // --- 2. FETCH APPLICABLE NOTIFICATIONS ---
        $notifications = \App\Models\Notification::whereIn('status', ['sent', 'pending', 'scheduled'])
            ->where(function ($q) use ($member) {
                // Direct notifications to the member
                $q->where(function ($subQ) use ($member) {
                    $subQ->where('recipient_type', 'App\\Models\\Member')
                         ->where('recipient_id', $member->id);
                })
                // Global announcements (recipient_id is null)
                ->orWhereNull('recipient_id');
            })
            ->get()
            ->map(function ($notif) {
                // Determine sender dynamically based on type
                $sender = 'CAC Hosanna Admin';
                if ($notif->type === 'followup') {
                    $sender = 'Pastoral Team';
                } elseif ($notif->type === 'birthday' || $notif->type === 'anniversary') {
                    $sender = 'Hosanna Celebrations';
                }

                return [
                    'id' => 'notif_' . $notif->id,
                    'source' => 'notification',
                    'type' => $notif->type ?: 'general',
                    'sender' => $sender,
                    'title' => $notif->title ?: 'New Notification',
                    'content' => $notif->message,
                    'created_at' => $notif->created_at->toIso8601String(),
                ];
            });

        // --- 3. FETCH SERVICE ASSIGNMENTS (ORDER OF SERVICE) ---
        $fullName = strtolower($member->first_name . ' ' . $member->last_name);
        $firstName = strtolower($member->first_name);
        $lastName = strtolower($member->last_name);

        $assignments = \App\Models\OrderOfService::with('service')
            ->where(function ($q) use ($fullName, $firstName, $lastName) {
                $q->whereRaw('LOWER(leader) = ?', [$fullName])
                  ->orWhereRaw('LOWER(leader) = ?', [$firstName])
                  ->orWhereRaw('LOWER(leader) = ?', [$lastName]);
            })
            ->get()
            ->map(function ($item) {
                $timeStr = $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('h:i A') : 'Time not set';
                $serviceName = $item->service ? $item->service->name : 'Upcoming Service';
                
                return [
                    'id' => 'assignment_' . $item->id,
                    'source' => 'notification',
                    'type' => 'assignment',
                    'sender' => 'Pastoral Scheduler',
                    'title' => 'Service Assignment: ' . $item->program,
                    'content' => "You have been scheduled to lead the \"{$item->program}\" during the \"{$serviceName}\".\nScheduled Time: {$timeStr}.",
                    'created_at' => $item->created_at->toIso8601String(),
                ];
            });

        // --- 4. MERGE AND SORT AND PAGINATE ---
        $merged = collect($smsMessages)
            ->merge($notifications)
            ->merge($assignments)
            ->sortByDesc('created_at')
            ->values();

        // Strategy A: Return only a limited subset (e.g. latest 5 for dashboard)
        if ($request->has('limit')) {
            $limit = (int) $request->input('limit');
            return response()->json([
                'success' => true,
                'data' => $merged->take($limit)->toArray()
            ]);
        }

        // Strategy B: Return manually paginated slice of merged collection
        $perPage = 15;
        $page = (int) $request->input('page', 1);
        $total = $merged->count();
        $sliced = $merged->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => $sliced->toArray(),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ]
        ]);
    }
}