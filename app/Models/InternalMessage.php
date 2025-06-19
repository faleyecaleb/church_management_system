<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class InternalMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'recipient_type',
        'recipient_ids',
        'subject',
        'content',
        'attachments',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'attachments' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    // Relationships
    public function sender()
    {
        return $this->belongsTo(Member::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->recipient_type === 'individual'
            ? Member::whereIn('id', $this->recipient_ids)->get()
            : MessageGroup::whereIn('id', $this->recipient_ids)->get();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForMember($query, $memberId)
    {
        return $query->where(function ($q) use ($memberId) {
            $q->where('sender_id', $memberId)
              ->orWhere(function ($q) use ($memberId) {
                  $q->where('recipient_type', 'individual')
                    ->whereJsonContains('recipient_ids', $memberId);
              })
              ->orWhere(function ($q) use ($memberId) {
                  $q->where('recipient_type', 'group')
                    ->whereHas('recipients', function ($q) use ($memberId) {
                        $q->whereJsonContains('member_ids', $memberId);
                    });
              });
        });
    }

    // Helper methods
    public function markAsRead($memberId = null)
    {
        $this->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);

        // TODO: Implement read receipt notification to sender if enabled
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null
        ]);
    }

    public function send()
    {
        // Send notifications to recipients
        $recipients = $this->getRecipientMembers();
        
        foreach ($recipients as $recipient) {
            // TODO: Implement notification logic
            // Could be email, SMS, or in-app notification
        }

        return true;
    }

    protected function getRecipientMembers()
    {
        if ($this->recipient_type === 'individual') {
            return Member::whereIn('id', $this->recipient_ids)->get();
        }

        return Member::whereHas('messageGroups', function ($query) {
            $query->whereIn('id', $this->recipient_ids);
        })->get();
    }

    public function canBeViewedBy($member)
    {
        if (!$member) {
            return false;
        }

        return $member->id === $this->sender_id ||
               ($this->recipient_type === 'individual' && in_array($member->id, $this->recipient_ids)) ||
               ($this->recipient_type === 'group' && $this->isInRecipientGroups($member));
    }

    protected function isInRecipientGroups($member)
    {
        return MessageGroup::whereIn('id', $this->recipient_ids)
            ->whereJsonContains('member_ids', $member->id)
            ->exists();
    }

    public static function getMessageStats($memberId = null, $startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($memberId) {
            $query->forMember($memberId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $messages = $query->get();

        return [
            'total_messages' => $messages->count(),
            'unread_messages' => $messages->where('is_read', false)->count(),
            'individual_messages' => $messages->where('recipient_type', 'individual')->count(),
            'group_messages' => $messages->where('recipient_type', 'group')->count(),
            'sent_messages' => $messages->where('sender_id', $memberId)->count(),
            'received_messages' => $messages->where('sender_id', '!=', $memberId)->count(),
            'with_attachments' => $messages->filter(function ($message) {
                return !empty($message->attachments);
            })->count()
        ];
    }

    public function addAttachment($file)
    {
        $attachments = $this->attachments ?? [];
        array_push($attachments, $file);
        $this->update(['attachments' => $attachments]);
    }

    public function removeAttachment($fileIndex)
    {
        $attachments = $this->attachments ?? [];
        if (isset($attachments[$fileIndex])) {
            unset($attachments[$fileIndex]);
            $this->update(['attachments' => array_values($attachments)]);
            return true;
        }
        return false;
    }

    public function forward($recipientIds, $recipientType = 'individual', $additionalContent = null)
    {
        $newContent = $additionalContent
            ? $additionalContent . "\n\n--- Forwarded Message ---\n" . $this->content
            : $this->content;

        return self::create([
            'sender_id' => auth()->id(),
            'recipient_type' => $recipientType,
            'recipient_ids' => $recipientIds,
            'subject' => 'Fwd: ' . $this->subject,
            'content' => $newContent,
            'attachments' => $this->attachments
        ]);
    }

    public function reply($content, $replyAll = false)
    {
        $recipientIds = $replyAll
            ? array_merge([$this->sender_id], array_diff($this->recipient_ids, [auth()->id()]))
            : [$this->sender_id];

        return self::create([
            'sender_id' => auth()->id(),
            'recipient_type' => 'individual',
            'recipient_ids' => $recipientIds,
            'subject' => 'Re: ' . $this->subject,
            'content' => $content
        ]);
    }
}