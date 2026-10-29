<?php

namespace Modules\Reports\Console;

use Illuminate\Console\Command;
use Modules\Reports\Jobs\SendScheduledReportJob;
use Modules\Reports\Models\ReportSchedule;

class DispatchScheduledReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:dispatch-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch due scheduled reports to queue for email delivery';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (fn_get_setting('reports.enabled', 'Y') === 'N') {
            $this->info('Reports scheduler is disabled from settings.');
            return self::SUCCESS;
        }

        $batchSize = 50;

        $dueSchedules = ReportSchedule::query()
            ->with('report')
            ->active()
            ->where('next_run_at', '<=', now())
            ->whereHas('report', function ($query) {
                $query->where('is_active', true)
                    ->where('is_scheduled', true);
            })
            ->orderBy('next_run_at')
            ->limit(max(1, $batchSize))
            ->get();

        if ($dueSchedules->isEmpty()) {
            $this->info('No due scheduled reports found.');
            return self::SUCCESS;
        }

        foreach ($dueSchedules as $schedule) {
            SendScheduledReportJob::dispatch($schedule->id);
        }

        $this->info("Dispatched {$dueSchedules->count()} report schedule(s) to queue.");

        return self::SUCCESS;
    }
}
