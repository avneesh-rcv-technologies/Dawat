<?php

namespace Modules\Activitylog\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Activitylog\Models\RequestActivityLogRollup;

class BackfillActivityLogRollupsCommand extends Command
{
    protected $signature = 'activitylog:backfill-rollups {--days=30 : Number of days to backfill}';

    protected $description = 'Backfill hourly and daily request activity log rollups';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $start = now()->subDays($days)->startOfHour();

        $this->info("Backfilling rollups from {$start->toDateTimeString()}...");

        while ($start->lte(now()->startOfHour())) {
            $hourEnd = $start->copy()->endOfHour();

            $aggregate = DB::table('request_activity_logs')
                ->whereBetween('created_at', [$start, $hourEnd])
                ->selectRaw('COUNT(*) as total_requests')
                ->selectRaw('SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_requests')
                ->selectRaw('SUM(CASE WHEN response_status >= 400 THEN 1 ELSE 0 END) as error_requests')
                ->selectRaw('COUNT(DISTINCT ip_address) as unique_ips')
                ->selectRaw('COALESCE(SUM(duration_ms), 0) as total_duration_ms')
                ->first();

            if ($aggregate && (int) $aggregate->total_requests > 0) {
                RequestActivityLogRollup::updateOrCreate(
                    [
                        'bucket_date' => $start->toDateString(),
                        'bucket_hour' => (int) $start->format('G'),
                    ],
                    [
                        'total_requests' => (int) $aggregate->total_requests,
                        'blocked_requests' => (int) $aggregate->blocked_requests,
                        'error_requests' => (int) $aggregate->error_requests,
                        'unique_ips' => (int) $aggregate->unique_ips,
                        'total_duration_ms' => (int) $aggregate->total_duration_ms,
                    ]
                );
            }

            $start->addHour();
        }

        $this->info('Rollup backfill completed.');

        return self::SUCCESS;
    }
}
