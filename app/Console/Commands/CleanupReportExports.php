<?php

namespace App\Console\Commands;

use App\Models\ReportExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupReportExports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-exports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired report exports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting report exports cleanup...');

            // Find expired exports
            $expiredExports = ReportExport::where('expires_at', '<=', now())
                ->orWhere('created_at', '<=', Carbon::now()->subDays(30))
                ->get();

            $count = 0;
            foreach ($expiredExports as $export) {
                try {
                    // Delete the actual file
                    if (Storage::exists($export->file_path)) {
                        Storage::delete($export->file_path);
                    }

                    // Delete the database record
                    $export->delete();

                    $count++;
                } catch (\Exception $e) {
                    Log::error("Failed to cleanup export {$export->id}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info("Successfully cleaned up {$count} expired report exports.");
            Log::info("CleanupReportExports: Removed {$count} expired exports");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error cleaning up report exports: ' . $e->getMessage());
            Log::error('CleanupReportExports failed: ' . $e->getMessage());
            return 1;
        }
    }
}