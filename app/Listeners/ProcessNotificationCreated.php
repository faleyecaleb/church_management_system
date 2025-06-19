<?php

namespace App\Listeners;

use App\Events\NotificationCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessNotificationCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create the event listener.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NotificationCreated  $event
     * @return void
     */
    public function handle(NotificationCreated $event)
    {
        $notification = $event->notification;

        // If notification is not scheduled, process it immediately
        if (!$notification->scheduled_at) {
            try {
                // Here you would implement the actual notification sending logic
                // This could include sending emails, SMS, or other notification methods
                // For now, we'll just mark it as sent
                $notification->markAsSent();

                // Log successful processing
                \Log::info('Notification processed successfully', [
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                    'recipient_id' => $notification->recipient_id
                ]);

            } catch (\Exception $e) {
                // Mark notification as failed
                $notification->markAsFailed();

                // Log the error
                \Log::error('Failed to process notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Optionally re-throw the exception to trigger job failure
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \App\Events\NotificationCreated  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(NotificationCreated $event, \Throwable $exception)
    {
        // Mark the notification as failed
        $event->notification->markAsFailed();

        // Log the failure
        \Log::error('Notification processing failed', [
            'notification_id' => $event->notification->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}