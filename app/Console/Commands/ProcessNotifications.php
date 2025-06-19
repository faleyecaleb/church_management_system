<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due notifications and schedule automated notifications';

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification processing...');

        try {
            // Process notifications that are due
            $this->info('Processing due notifications...');
            $this->notificationService->processDueNotifications();

            // Schedule birthday notifications
            $this->info('Scheduling birthday notifications...');
            $this->notificationService->scheduleBirthdayNotifications();

            // Schedule anniversary notifications
            $this->info('Scheduling anniversary notifications...');
            $this->notificationService->scheduleAnniversaryNotifications();

            // Schedule milestone notifications
            $this->info('Scheduling milestone notifications...');
            $this->notificationService->scheduleMilestoneNotifications();

            $this->info('Notification processing completed successfully.');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error processing notifications: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}