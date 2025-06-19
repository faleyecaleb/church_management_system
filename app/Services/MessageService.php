<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageGroup;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class MessageService
{
    protected $smsGateway;

    public function __construct()
    {
        // Initialize SMS gateway service here
        // $this->smsGateway = new SMSGatewayService();
    }

    /**
     * Send bulk SMS to a group of recipients
     */
    public function sendBulkSMS(array $data)
    {
        try {
            $template = null;
            if (isset($data['template_id'])) {
                $template = MessageTemplate::findOrFail($data['template_id']);
                $compiled = $template->compile($data['variables'] ?? []);
                $data['subject'] = $compiled['subject'];
                $data['content'] = $compiled['content'];
            }

            $recipients = $this->getRecipients($data['recipient_type'], $data['recipient_id']);
            $messages = [];

            foreach ($recipients as $recipient) {
                $message = Message::create([
                    'type' => 'sms',
                    'sender_id' => auth()->id(),
                    'recipient_type' => 'individual',
                    'recipient_id' => $recipient->id,
                    'subject' => $data['subject'] ?? null,
                    'content' => $data['content'],
                    'template_id' => $template?->id,
                    'status' => 'pending',
                    'scheduled_at' => $data['scheduled_at'] ?? null,
                    'metadata' => $data['metadata'] ?? null
                ]);

                $messages[] = $message;

                if (empty($data['scheduled_at'])) {
                    $this->processMessage($message);
                }
            }

            return $messages;
        } catch (Exception $e) {
            Log::error('Bulk SMS sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process a single message for sending
     */
    public function processMessage(Message $message)
    {
        try {
            // Implement actual SMS sending logic here
            // $response = $this->smsGateway->send([
            //     'to' => $message->recipient->phone,
            //     'message' => $message->content
            // ]);

            // For development, simulate successful sending
            $message->markAsSent();
            $message->markAsDelivered();

            return true;
        } catch (Exception $e) {
            $message->markAsFailed($e->getMessage());
            Log::error('Message processing failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process scheduled messages
     */
    public function processScheduledMessages()
    {
        $messages = Message::where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($messages as $message) {
            $this->processMessage($message);
        }

        return $messages->count();
    }

    /**
     * Get recipients based on type and ID
     */
    protected function getRecipients(string $type, $id)
    {
        if ($type === 'group') {
            $group = MessageGroup::findOrFail($id);
            return $group->members();
        } else {
            return [User::findOrFail($id)];
        }
    }

    /**
     * Create a prayer request
     */
    public function createPrayerRequest(array $data)
    {
        return Message::create([
            'type' => 'prayer',
            'sender_id' => auth()->id(),
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => 'pending',
            'metadata' => array_merge($data['metadata'] ?? [], [
                'prayer_status' => 'new',
                'prayer_responses' => []
            ])
        ]);
    }

    /**
     * Send internal message
     */
    public function sendInternalMessage(array $data)
    {
        return Message::create([
            'type' => 'internal',
            'sender_id' => auth()->id(),
            'recipient_type' => $data['recipient_type'],
            'recipient_id' => $data['recipient_id'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => $data['metadata'] ?? null
        ]);
    }

    /**
     * Get message statistics
     */
    public function getMessageStats(array $filters = [])
    {
        $query = Message::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        $messages = $query->get();

        return [
            'total_messages' => $messages->count(),
            'by_type' => [
                'sms' => $messages->where('type', 'sms')->count(),
                'prayer' => $messages->where('type', 'prayer')->count(),
                'internal' => $messages->where('type', 'internal')->count()
            ],
            'by_status' => [
                'pending' => $messages->where('status', 'pending')->count(),
                'sent' => $messages->where('status', 'sent')->count(),
                'delivered' => $messages->where('status', 'delivered')->count(),
                'failed' => $messages->where('status', 'failed')->count()
            ],
            'delivery_rate' => $messages->count() > 0
                ? round(($messages->where('status', 'delivered')->count() / $messages->count()) * 100, 2)
                : 0
        ];
    }
}