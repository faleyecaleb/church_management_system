<?php

namespace App\Console\Commands;

use App\Services\MessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all scheduled messages that are due for sending';

    /**
     * The message service instance.
     *
     * @var \App\Services\MessageService
     */
    protected $messageService;

    /**
     * Create a new command instance.
     */
    public function __construct(MessageService $messageService)
    {
        parent::__construct();
        $this->messageService = $messageService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting scheduled message processing...');

            $count = $this->messageService->processScheduledMessages();

            $this->info("Successfully processed {$count} scheduled messages.");
            Log::info("ProcessScheduledMessages: Processed {$count} messages");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error processing scheduled messages: ' . $e->getMessage());
            Log::error('ProcessScheduledMessages failed: ' . $e->getMessage());
            return 1;
        }
    }
}