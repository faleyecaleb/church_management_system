<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\MessageTemplate;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->middleware('auth');
    }

    /**
     * Display message dashboard
     */
    public function index(Request $request)
    {
        $query = Message::query();

        // Apply filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->latest()->paginate(15);
        $stats = $this->messageService->getMessageStats();

        return view('messages.index', compact('messages', 'stats'));
    }

    /**
     * Show message creation form
     */
    public function create()
    {
        $groups = MessageGroup::active()->get();
        $templates = MessageTemplate::active()->get();

        return view('messages.create', compact('groups', 'templates'));
    }

    /**
     * Store a new message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sms,prayer,internal',
            'recipient_type' => 'required|in:individual,group',
            'recipient_id' => 'required',
            'subject' => 'required_if:type,prayer,internal',
            'content' => 'required',
            'template_id' => 'nullable|exists:message_templates,id',
            'scheduled_at' => 'nullable|date|after:now',
            'metadata' => 'nullable|array'
        ]);

        try {
            switch ($validated['type']) {
                case 'sms':
                    $messages = $this->messageService->sendBulkSMS($validated);
                    $message = 'SMS messages queued successfully.';
                    break;

                case 'prayer':
                    $message = $this->messageService->createPrayerRequest($validated);
                    $message = 'Prayer request created successfully.';
                    break;

                case 'internal':
                    $message = $this->messageService->sendInternalMessage($validated);
                    $message = 'Internal message sent successfully.';
                    break;
            }

            return redirect()->route('messages.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Display message details
     */
    public function show(Message $message)
    {
        return view('messages.show', compact('message'));
    }

    /**
     * Show message edit form
     */
    public function edit(Message $message)
    {
        if (!in_array($message->status, ['pending', 'failed'])) {
            return back()->with('error', 'Only pending or failed messages can be edited.');
        }

        $groups = MessageGroup::active()->get();
        $templates = MessageTemplate::active()->get();

        return view('messages.edit', compact('message', 'groups', 'templates'));
    }

    /**
     * Update message
     */
    public function update(Request $request, Message $message)
    {
        if (!in_array($message->status, ['pending', 'failed'])) {
            return back()->with('error', 'Only pending or failed messages can be updated.');
        }

        $validated = $request->validate([
            'subject' => 'required_if:type,prayer,internal',
            'content' => 'required',
            'scheduled_at' => 'nullable|date|after:now',
            'metadata' => 'nullable|array'
        ]);

        $message->update($validated);

        return redirect()->route('messages.show', $message)
            ->with('success', 'Message updated successfully.');
    }

    /**
     * Delete message
     */
    public function destroy(Message $message)
    {
        if (!in_array($message->status, ['pending', 'failed'])) {
            return back()->with('error', 'Only pending or failed messages can be deleted.');
        }

        $message->delete();

        return redirect()->route('messages.index')
            ->with('success', 'Message deleted successfully.');
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Message $message)
    {
        if ($message->type === 'internal') {
            $message->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    /**
     * Retry failed message
     */
    public function retry(Message $message)
    {
        if ($message->status !== 'failed') {
            return back()->with('error', 'Only failed messages can be retried.');
        }

        try {
            $this->messageService->processMessage($message);
            return back()->with('success', 'Message retry initiated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry message: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled message
     */
    public function cancel(Message $message)
    {
        if ($message->status !== 'pending' || !$message->scheduled_at) {
            return back()->with('error', 'Only pending scheduled messages can be cancelled.');
        }

        $message->update([
            'status' => 'cancelled',
            'metadata' => array_merge($message->metadata ?? [], [
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id()
            ])
        ]);

        return back()->with('success', 'Scheduled message cancelled successfully.');
    }
}