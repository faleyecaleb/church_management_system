<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'recipient_group',
        'recipient_ids',
        'scheduled_at',
        'status',
        'total_recipients',
        'successful_sends',
        'failed_sends',
        'failure_reason'
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'scheduled_at' => 'datetime',
        'total_recipients' => 'integer',
        'successful_sends' => 'integer',
        'failed_sends' => 'integer'
    ];

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDueForSending($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<=', Carbon::now());
    }

    // Helper methods
    public function send()
    {
        try {
            // Get recipients based on group or specific IDs
            $recipients = $this->getRecipients();
            $this->total_recipients = count($recipients);
            $this->successful_sends = 0;
            $this->failed_sends = 0;

            foreach ($recipients as $recipient) {
                try {
                    // TODO: Implement actual SMS sending logic here
                    // This would typically involve a third-party SMS service
                    $this->sendToRecipient($recipient);
                    $this->successful_sends++;
                } catch (\Exception $e) {
                    $this->failed_sends++;
                    // Log the failure
                }
            }

            $this->status = $this->failed_sends === 0 ? 'sent' : 'partially_sent';
            $this->save();

            return true;
        } catch (\Exception $e) {
            $this->status = 'failed';
            $this->failure_reason = $e->getMessage();
            $this->save();

            return false;
        }
    }

    protected function getRecipients()
    {
        switch ($this->recipient_group) {
            case 'all':
                return Member::active()->get();
            case 'members':
                return Member::active()->where('membership_status', 'active')->get();
            case 'visitors':
                return Member::active()->where('membership_status', 'visitor')->get();
            case 'custom':
                return Member::whereIn('id', $this->recipient_ids)->get();
            default:
                return collect();
        }
    }

    protected function sendToRecipient($recipient)
    {
        // TODO: Implement actual SMS sending logic
        // This is a placeholder for the actual implementation
        $message = $this->parseMessageTemplate($recipient);
        
        // Example implementation using a hypothetical SMS service
        // $smsService->send($recipient->phone, $message);
    }

    protected function parseMessageTemplate($recipient)
    {
        $message = $this->content;

        // Replace placeholders with actual values
        $replacements = [
            '{name}' => $recipient->full_name,
            '{first_name}' => $recipient->first_name,
            '{last_name}' => $recipient->last_name,
            '{phone}' => $recipient->phone,
            // Add more placeholders as needed
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public static function getMessageStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $messages = $query->get();

        return [
            'total_messages' => $messages->count(),
            'total_recipients' => $messages->sum('total_recipients'),
            'successful_sends' => $messages->sum('successful_sends'),
            'failed_sends' => $messages->sum('failed_sends'),
            'by_status' => [
                'draft' => $messages->where('status', 'draft')->count(),
                'scheduled' => $messages->where('status', 'scheduled')->count(),
                'sent' => $messages->where('status', 'sent')->count(),
                'failed' => $messages->where('status', 'failed')->count()
            ],
            'by_recipient_group' => $messages->groupBy('recipient_group')
                ->map(fn ($items) => $items->count())
        ];
    }

    public function schedule($dateTime)
    {
        $this->update([
            'scheduled_at' => $dateTime,
            'status' => 'scheduled'
        ]);
    }

    public function cancel()
    {
        if ($this->status === 'scheduled') {
            $this->update(['status' => 'draft']);
            return true;
        }
        return false;
    }
}