<?php

namespace Modules\Subscriptions\Console\Commands;

use Illuminate\Console\Command;
use Modules\Subscriptions\Services\ReminderService;

class ProcessSubscriptionRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-reminders {--force : Force processing even if not scheduled}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Process and send pending subscription reminders';

    /**
     * The reminder service instance.
     *
     * @var ReminderService
     */
    protected $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing subscription reminders...');

        try {
            $this->reminderService->sendPendingReminders();
            $this->info('✓ Subscription reminders processed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Error processing reminders: ' . $e->getMessage());
            return 1;
        }
    }
}
