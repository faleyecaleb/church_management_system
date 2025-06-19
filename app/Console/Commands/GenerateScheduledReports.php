<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send all scheduled reports that are due';

    /**
     * The reporting service instance.
     *
     * @var \App\Services\ReportingService
     */
    protected $reportingService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReportingService $reportingService)
    {
        parent::__construct();
        $this->reportingService = $reportingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting scheduled report generation...');

            $reports = ScheduledReport::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('last_run_at')
                        ->orWhere('next_run_at', '<=', now());
                })
                ->get();

            $count = 0;
            foreach ($reports as $report) {
                try {
                    $this->processReport($report);
                    $count++;
                } catch (\Exception $e) {
                    Log::error("Failed to process report {$report->id}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info("Successfully generated {$count} scheduled reports.");
            Log::info("GenerateScheduledReports: Generated {$count} reports");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating scheduled reports: ' . $e->getMessage());
            Log::error('GenerateScheduledReports failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process a single scheduled report
     */
    protected function processReport(ScheduledReport $report)
    {
        // Generate the report
        $export = $this->reportingService->generateReport(
            $report->template_id,
            $report->filters ?? [],
            $report->format
        );

        // Update the report schedule
        $report->update([
            'last_run_at' => now(),
            'next_run_at' => $this->calculateNextRunDate($report)
        ]);

        // Send the report to recipients
        foreach ($report->recipients as $recipient) {
            try {
                // Implement notification logic here
                // You might want to use Laravel's notification system
                // Notification::route('mail', $recipient)
                //     ->notify(new ScheduledReportGenerated($export));
            } catch (\Exception $e) {
                Log::error("Failed to send report {$report->id} to {$recipient}: " . $e->getMessage());
            }
        }
    }

    /**
     * Calculate the next run date based on frequency
     */
    protected function calculateNextRunDate(ScheduledReport $report)
    {
        $now = Carbon::now();
        $config = $report->schedule_config;

        switch ($report->frequency) {
            case 'daily':
                return $now->addDay()->setTime(
                    $config['hour'] ?? 0,
                    $config['minute'] ?? 0
                );

            case 'weekly':
                return $now->next($config['day'] ?? 1)->setTime(
                    $config['hour'] ?? 0,
                    $config['minute'] ?? 0
                );

            case 'monthly':
                return $now->addMonth()->setDay($config['day'] ?? 1)->setTime(
                    $config['hour'] ?? 0,
                    $config['minute'] ?? 0
                );

            default:
                throw new \InvalidArgumentException(
                    "Invalid frequency: {$report->frequency}"
                );
        }
    }
}