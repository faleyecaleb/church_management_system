<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessNotifications::class,
        Commands\ProcessScheduledMessages::class,
        Commands\GenerateScheduledReports::class,
        Commands\CheckMemberAbsences::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Process notifications every 5 minutes
        $schedule->command('notifications:process')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/notifications.log'));

        // Process scheduled messages every minute
        $schedule->command('messages:process-scheduled')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/scheduled-messages.log'));

        // Generate scheduled reports every 5 minutes
        $schedule->command('reports:generate-scheduled')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/scheduled-reports.log'));

        // Clean up old report exports daily
        $schedule->command('reports:cleanup-exports')
                 ->daily()
                 ->appendOutputTo(storage_path('logs/report-cleanup.log'));

        // Check for member absences weekly
        $schedule->command('members:check-absences')
                 ->weekly()
                 ->sundays()
                 ->at('23:00')
                 ->appendOutputTo(storage_path('logs/absence-checks.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}