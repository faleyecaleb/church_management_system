<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckMemberAbsences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:check-absences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for member absences and create notifications';

    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for member absences...');

        try {
            $this->notificationService->checkAndNotifyAbsences();
            $this->info('Successfully checked member absences and created notifications.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to check member absences: ' . $e->getMessage());
            return 1;
        }
    }
}