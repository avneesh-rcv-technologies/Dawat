<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Activitylog\Models\RequestActivityLog;

class ArchiveRequestActivityLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if (! Schema::hasTable('request_activity_logs_archive')) {
            return;
        }

        $days = (int) config('activitylog.archive_after_days', 90);
        $cutoff = now()->subDays($days);

        RequestActivityLog::query()
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(500, function ($logs): void {
                $rows = [];

                foreach ($logs as $log) {
                    $payload = $log->only($log->getFillable());
                    $payload['archived_at'] = now();
                    $payload['created_at'] = $log->created_at;
                    $payload['updated_at'] = $log->updated_at;

                    foreach (['query_params', 'request_body', 'headers', 'request_middleware', 'web_middleware_map', 'response_headers'] as $jsonColumn) {
                        if (isset($payload[$jsonColumn]) && is_array($payload[$jsonColumn])) {
                            $payload[$jsonColumn] = json_encode($payload[$jsonColumn]);
                        }
                    }

                    $rows[] = $payload;
                }

                if ($rows !== []) {
                    DB::table('request_activity_logs_archive')->insert($rows);
                }

                RequestActivityLog::query()
                    ->whereIn('id', $logs->pluck('id'))
                    ->delete();
            });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Request activity log archive job failed.', [
            'message' => $exception->getMessage(),
        ]);
    }
}
