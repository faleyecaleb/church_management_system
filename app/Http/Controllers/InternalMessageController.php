<?php

namespace App\Http\Controllers;

use App\Models\InternalMessage;
use App\Models\Member;
use App\Models\MessageGroup;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class InternalMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:communication.view')->only(['index', 'show', 'sent', 'archived']);
        $this->middleware('permission:communication.create')->only(['create', 'store', 'reply', 'forward']);
        $this->middleware('permission:communication.update')->only(['markAsRead', 'markAsUnread', 'archive', 'unarchive']);
        $this->middleware('permission:communication.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = InternalMessage::with(['sender', 'attachments'])
            ->forRecipient(Auth::id())
            ->unarchived();

        if ($request->filled('read')) {
            $query->where('is_read', $request->input('read') === 'true');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15);

        return view('messages.index', compact('messages'));
    }

    public function sent(Request $request)
    {
        $query = InternalMessage::with(['recipients', 'attachments'])
            ->where('sender_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15);

        return view('messages.sent', compact('messages'));
    }

    public function archived(Request $request)
    {
        $query = InternalMessage::with(['sender', 'attachments'])
            ->forRecipient(Auth::id())
            ->archived();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15);

        return view('messages.archived', compact('messages'));
    }

    public function create()
    {
        $members = Member::active()->get();
        $groups = MessageGroup::active()->get();

        return view('messages.create', compact('members', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:individual,group',
            'recipient_ids' => 'required|array',
            'recipient_ids.*' => 'required|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'
        ]);

        DB::beginTransaction();

        try {
            $message = InternalMessage::create([
                'subject' => $validated['subject'],
                'content' => $validated['content'],
                'sender_id' => Auth::id()
            ]);

            // Add recipients
            $recipientIds = $this->getRecipientIds(
                $validated['recipient_type'],
                $validated['recipient_ids']
            );

            $message->recipients()->attach($recipientIds);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('message-attachments');
                    $message->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('messages.show', $message)
                ->with('success', 'Message sent successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function show(InternalMessage $message)
    {
        if (!$message->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to view this message.');
        }

        // Mark as read if recipient
        if ($message->recipients->contains(Auth::id())) {
            $message->markAsRead();
        }

        $message->load(['sender', 'recipients', 'attachments']);
        return view('messages.show', compact('message'));
    }

    public function reply(InternalMessage $message)
    {
        if (!$message->recipients->contains(Auth::id())) {
            abort(403, 'You can only reply to messages you received.');
        }

        return view('messages.reply', compact('message'));
    }

    public function forward(InternalMessage $message)
    {
        if (!$message->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to forward this message.');
        }

        $members = Member::active()->get();
        $groups = MessageGroup::active()->get();

        return view('messages.forward', compact('message', 'members', 'groups'));
    }

    public function destroy(InternalMessage $message)
    {
        if (!$message->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to delete this message.');
        }

        DB::beginTransaction();

        try {
            // Delete attachments if sender
            if ($message->sender_id === Auth::id()) {
                foreach ($message->attachments as $attachment) {
                    Storage::delete($attachment->path);
                    $attachment->delete();
                }
                $message->delete();
            } else {
                // Remove user from recipients if recipient
                $message->recipients()->detach(Auth::id());
            }

            DB::commit();

            return redirect()->route('messages.index')
                ->with('success', 'Message deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete message: ' . $e->getMessage());
        }
    }

    public function markAsRead(InternalMessage $message)
    {
        if (!$message->recipients->contains(Auth::id())) {
            abort(403, 'You can only mark messages you received as read.');
        }

        $message->markAsRead();

        return redirect()->back()
            ->with('success', 'Message marked as read.');
    }

    public function markAsUnread(InternalMessage $message)
    {
        if (!$message->recipients->contains(Auth::id())) {
            abort(403, 'You can only mark messages you received as unread.');
        }

        $message->markAsUnread();

        return redirect()->back()
            ->with('success', 'Message marked as unread.');
    }

    public function archive(InternalMessage $message)
    {
        if (!$message->recipients->contains(Auth::id())) {
            abort(403, 'You can only archive messages you received.');
        }

        $message->archive();

        return redirect()->back()
            ->with('success', 'Message archived successfully.');
    }

    public function unarchive(InternalMessage $message)
    {
        if (!$message->recipients->contains(Auth::id())) {
            abort(403, 'You can only unarchive messages you received.');
        }

        $message->unarchive();

        return redirect()->back()
            ->with('success', 'Message unarchived successfully.');
    }

    protected function getRecipientIds($type, $ids)
    {
        switch ($type) {
            case 'individual':
                return $ids;

            case 'group':
                return Member::whereHas('messageGroups', function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                })
                    ->where('id', '!=', Auth::id())
                    ->pluck('id')
                    ->toArray();

            default:
                return [];
        }
    }
}