<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Activitylog\Models\RequestActivityLogRollup;

class AggregateRequestActivityLogRollupsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $hourStart = now()->subHour()->startOfHour();
        $hourEnd = $hourStart->copy()->endOfHour();

        $aggregate = DB::table('request_activity_logs')
            ->whereBetween('created_at', [$hourStart, $hourEnd])
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_requests')
            ->selectRaw('SUM(CASE WHEN response_status >= 400 THEN 1 ELSE 0 END) as error_requests')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_ips')
            ->selectRaw('COALESCE(SUM(duration_ms), 0) as total_duration_ms')
            ->first();

        if (! $aggregate || (int) $aggregate->total_requests === 0) {
            return;
        }

        RequestActivityLogRollup::updateOrCreate(
            [
                'bucket_date' => $hourStart->toDateString(),
                'bucket_hour' => (int) $hourStart->format('G'),
            ],
            [
                'total_requests' => (int) $aggregate->total_requests,
                'blocked_requests' => (int) $aggregate->blocked_requests,
                'error_requests' => (int) $aggregate->error_requests,
                'unique_ips' => (int) $aggregate->unique_ips,
                'total_duration_ms' => (int) $aggregate->total_duration_ms,
            ]
        );

        $dayStart = $hourStart->copy()->startOfDay();
        $dayEnd = $hourStart->copy()->endOfDay();

        $daily = DB::table('request_activity_log_rollups')
            ->where('bucket_date', $dayStart->toDateString())
            ->whereNotNull('bucket_hour')
            ->selectRaw('SUM(total_requests) as total_requests')
            ->selectRaw('SUM(blocked_requests) as blocked_requests')
            ->selectRaw('SUM(error_requests) as error_requests')
            ->selectRaw('SUM(unique_ips) as unique_ips')
            ->selectRaw('SUM(total_duration_ms) as total_duration_ms')
            ->first();

        if ($daily) {
            RequestActivityLogRollup::updateOrCreate(
                [
                    'bucket_date' => $dayStart->toDateString(),
                    'bucket_hour' => null,
                ],
                [
                    'total_requests' => (int) $daily->total_requests,
                    'blocked_requests' => (int) $daily->blocked_requests,
                    'error_requests' => (int) $daily->error_requests,
                    'unique_ips' => (int) $daily->unique_ips,
                    'total_duration_ms' => (int) $daily->total_duration_ms,
                ]
            );
        }
    }
}
