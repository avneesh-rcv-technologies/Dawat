<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Activitylog\Models\RequestActivityLog;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Jobs\ArchiveRequestActivityLogsJob;

class CleanupRequestLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (fn_get_setting('activitylog.enabled') !== 'Y') {
                return;
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('request_activity_logs_archive')) {
                ArchiveRequestActivityLogsJob::dispatchSync();

                return;
            }

            $this->cleanupLogs();
        } catch (\Exception $e) {
            Log::error('Error during request logs cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup old request logs.
     *
     * @return void
     */
    protected function cleanupLogs()
    {
        $days = (int) config('activitylog.archive_after_days', 90);
        $cutoff = now()->subDays($days);
        RequestActivityLog::where('created_at', '<', $cutoff)->delete();
    }
}
