<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Models\Member;
use App\Models\MessageGroup;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmailMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:communication.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:communication.create')->only(['create', 'store', 'send', 'schedule']);
        $this->middleware('permission:communication.update')->only(['edit', 'update']);
        $this->middleware('permission:communication.delete')->only(['destroy', 'cancel']);
    }

    public function index(Request $request)
    {
        $query = EmailMessage::with(['template', 'attachments']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('recipient_emails', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(15);
        $templates = EmailTemplate::active()->get();

        return view('email.index', compact('messages', 'templates'));
    }

    public function create()
    {
        $templates = EmailTemplate::active()->get();
        $members = Member::whereNotNull('email')->get();
        $groups = MessageGroup::active()->get();

        return view('email.create', compact('templates', 'members', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'content' => 'required_without:template_id|string',
            'recipient_type' => 'required|in:individual,group,all',
            'recipient_ids' => 'required_unless:recipient_type,all|array',
            'recipient_ids.*' => 'required_unless:recipient_type,all|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,scheduled,queued'
        ]);

        if ($request->filled('template_id')) {
            $template = EmailTemplate::findOrFail($validated['template_id']);
            $validated['content'] = $template->content;
            $validated['subject'] = $template->subject;
        }

        // Get recipient emails based on type
        $recipientEmails = $this->getRecipientEmails(
            $validated['recipient_type'],
            $validated['recipient_ids'] ?? []
        );

        if (empty($recipientEmails)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid recipients found.');
        }

        DB::beginTransaction();

        try {
            $message = EmailMessage::create([
                'subject' => $validated['subject'],
                'content' => $validated['content'],
                'recipient_emails' => $recipientEmails,
                'scheduled_at' => $validated['scheduled_at'],
                'status' => $validated['status'],
                'template_id' => $validated['template_id'] ?? null
            ]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('email-attachments');
                    $message->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            if ($validated['status'] === 'queued') {
                $message->send();
            }

            DB::commit();

            return redirect()->route('email.show', $message)
                ->with('success', 'Email message created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create email message: ' . $e->getMessage());
        }
    }

    public function show(EmailMessage $message)
    {
        $message->load(['template', 'attachments']);
        return view('email.show', compact('message'));
    }

    public function edit(EmailMessage $message)
    {
        if (!in_array($message->status, ['draft', 'scheduled'])) {
            return redirect()->route('email.index')
                ->with('error', 'Only draft or scheduled messages can be edited.');
        }

        $templates = EmailTemplate::active()->get();
        $members = Member::whereNotNull('email')->get();
        $groups = MessageGroup::active()->get();

        return view('email.edit', compact('message', 'templates', 'members', 'groups'));
    }

    public function update(Request $request, EmailMessage $message)
    {
        if (!in_array($message->status, ['draft', 'scheduled'])) {
            return redirect()->route('email.index')
                ->with('error', 'Only draft or scheduled messages can be updated.');
        }

        $validated = $request->validate([
            'template_id' => 'nullable|exists:email_templates,id',
            'subject' => 'required|string|max:255',
            'content' => 'required_without:template_id|string',
            'recipient_type' => 'required|in:individual,group,all',
            'recipient_ids' => 'required_unless:recipient_type,all|array',
            'recipient_ids.*' => 'required_unless:recipient_type,all|integer',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'integer|exists:attachments,id',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'required|in:draft,scheduled,queued'
        ]);

        if ($request->filled('template_id')) {
            $template = EmailTemplate::findOrFail($validated['template_id']);
            $validated['content'] = $template->content;
            $validated['subject'] = $template->subject;
        }

        // Get recipient emails based on type
        $recipientEmails = $this->getRecipientEmails(
            $validated['recipient_type'],
            $validated['recipient_ids'] ?? []
        );

        if (empty($recipientEmails)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No valid recipients found.');
        }

        DB::beginTransaction();

        try {
            $message->update([
                'subject' => $validated['subject'],
                'content' => $validated['content'],
                'recipient_emails' => $recipientEmails,
                'scheduled_at' => $validated['scheduled_at'],
                'status' => $validated['status'],
                'template_id' => $validated['template_id'] ?? null
            ]);

            // Remove selected attachments
            if ($request->filled('remove_attachments')) {
                $attachments = Attachment::whereIn('id', $validated['remove_attachments'])
                    ->where('attachable_type', EmailMessage::class)
                    ->where('attachable_id', $message->id)
                    ->get();

                foreach ($attachments as $attachment) {
                    Storage::delete($attachment->path);
                    $attachment->delete();
                }
            }

            // Add new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('email-attachments');
                    $message->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            if ($validated['status'] === 'queued') {
                $message->send();
            }

            DB::commit();

            return redirect()->route('email.show', $message)
                ->with('success', 'Email message updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update email message: ' . $e->getMessage());
        }
    }

    public function destroy(EmailMessage $message)
    {
        if (!in_array($message->status, ['draft', 'failed'])) {
            return redirect()->route('email.index')
                ->with('error', 'Only draft or failed messages can be deleted.');
        }

        DB::beginTransaction();

        try {
            // Delete attachments
            foreach ($message->attachments as $attachment) {
                Storage::delete($attachment->path);
                $attachment->delete();
            }

            $message->delete();

            DB::commit();

            return redirect()->route('email.index')
                ->with('success', 'Email message deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('email.index')
                ->with('error', 'Failed to delete email message: ' . $e->getMessage());
        }
    }

    public function send(EmailMessage $message)
    {
        if ($message->status !== 'draft') {
            return redirect()->route('email.index')
                ->with('error', 'Only draft messages can be sent.');
        }

        $message->send();

        return redirect()->route('email.show', $message)
            ->with('success', 'Email message queued for sending.');
    }

    public function schedule(EmailMessage $message)
    {
        if ($message->status !== 'draft') {
            return redirect()->route('email.index')
                ->with('error', 'Only draft messages can be scheduled.');
        }

        $validated = request()->validate([
            'scheduled_at' => 'required|date|after:now'
        ]);

        $message->schedule($validated['scheduled_at']);

        return redirect()->route('email.show', $message)
            ->with('success', 'Email message scheduled successfully.');
    }

    public function cancel(EmailMessage $message)
    {
        if ($message->status !== 'scheduled') {
            return redirect()->route('email.index')
                ->with('error', 'Only scheduled messages can be cancelled.');
        }

        $message->cancel();

        return redirect()->route('email.show', $message)
            ->with('success', 'Email message cancelled successfully.');
    }

    protected function getRecipientEmails($type, $ids = [])
    {
        switch ($type) {
            case 'individual':
                return Member::whereIn('id', $ids)
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();

            case 'group':
                return Member::whereHas('messageGroups', function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                })
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();

            case 'all':
                return Member::whereNotNull('email')
                    ->pluck('email')
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
        $byStatus = EmailMessage::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Daily message counts
        $dailyCounts = EmailMessage::whereBetween('created_at', [$startDate, $endDate])
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
        $templateUsage = EmailMessage::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('template_id')
            ->with('template')
            ->select('template_id', DB::raw('COUNT(*) as count'))
            ->groupBy('template_id')
            ->get();

        // Attachment statistics
        $attachmentStats = EmailMessage::whereBetween('created_at', [$startDate, $endDate])
            ->withCount('attachments')
            ->select(
                DB::raw('COUNT(*) as total_messages'),
                DB::raw('SUM(CASE WHEN EXISTS (SELECT 1 FROM attachments WHERE attachable_id = email_messages.id AND attachable_type = "App\\Models\\EmailMessage") THEN 1 ELSE 0 END) as messages_with_attachments')
            )
            ->first();

        return view('email.report', compact(
            'byStatus',
            'dailyCounts',
            'templateUsage',
            'attachmentStats',
            'startDate',
            'endDate'
        ));
    }
}