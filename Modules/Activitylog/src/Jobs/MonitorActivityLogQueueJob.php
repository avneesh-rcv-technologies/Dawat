<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Modules\Cache\Services\AppCache;

class MonitorActivityLogQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->monitorQueueBacklog();
        $this->monitorFailedLogJobs();
    }

    protected function monitorQueueBacklog(): void
    {
        $threshold = (int) config('activitylog.queue_backlog_threshold', 500);
        $queueName = config('activitylog.log_queue', 'default');
        $depth = $this->queueDepth($queueName);

        if ($depth === null) {
            return;
        }

        if ($depth >= $threshold) {
            Log::warning('Activity log queue backlog is high.', [
                'queue' => $queueName,
                'depth' => $depth,
                'threshold' => $threshold,
            ]);
        }
    }

    protected function monitorFailedLogJobs(): void
    {
        $failedJobs = (int) AppCache::get('activity', 'failed_log_jobs', 0);
        $failedCacheWrites = (int) AppCache::get('activity', 'failed_cache_writes', 0);
        $threshold = (int) config('activitylog.failed_log_jobs_alert_threshold', 50);

        if ($failedJobs >= $threshold) {
            Log::error('Activity log failed job counter exceeded threshold.', [
                'failed_log_jobs' => $failedJobs,
                'failed_cache_writes' => $failedCacheWrites,
                'threshold' => $threshold,
            ]);
        }
    }

    protected function queueDepth(string $queueName): ?int
    {
        $connection = config('queue.default');

        if ($connection === 'redis') {
            $redisConnection = config('queue.connections.redis.connection', 'default');
            $prefix = (string) config('database.redis.options.prefix', '');

            return (int) Redis::connection($redisConnection)->llen($prefix . 'queues:' . $queueName);
        }

        if ($connection === 'database') {
            return (int) Queue::size($queueName);
        }

        return null;
    }
}
