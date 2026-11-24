<?php

namespace Modules\Activitylog\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Activitylog\Support\ActivityLogSettings;
use Modules\Activitylog\Support\ThreatProtectionCache;
use Modules\Cache\Services\AppCache;

class ActivityLogHealthCheckCommand extends Command
{
    protected $signature = 'activitylog:health-check';

    protected $description = 'Verify activity log caches, settings, and monitoring counters';

    public function handle(): int
    {
        $ok = true;

        $cacheDriver = config('cache.default');
        $this->line("Cache driver: {$cacheDriver}");
        if ($cacheDriver !== 'redis') {
            $this->warn('Production should use CACHE_STORE=redis for threat protection and rate limiting.');
            $ok = false;
        }

        $queueDriver = config('queue.default');
        $this->line("Queue driver: {$queueDriver}");
        if ($queueDriver !== 'redis') {
            $this->warn('Production should use QUEUE_CONNECTION=redis for log ingestion.');
            $ok = false;
        }

        $this->line('Threat protection enabled: ' . (ActivityLogSettings::threatProtectionEnabled() ? 'yes' : 'no'));
        $this->line('Request logging enabled: ' . (ActivityLogSettings::requestLoggingEnabled() ? 'yes' : 'no'));

        ThreatProtectionCache::forgetAll();
        $blockedProbe = ThreatProtectionCache::isIpBlocked('203.0.113.255');
        $this->line('Blocked IP cache probe: ' . ($blockedProbe === false ? 'ok' : 'unexpected'));

        $failedJobs = (int) AppCache::get('activity', 'failed_log_jobs', 0);
        $failedCacheWrites = (int) AppCache::get('activity', 'failed_cache_writes', 0);
        $this->line("Failed log jobs: {$failedJobs}");
        $this->line("Failed cache writes: {$failedCacheWrites}");

        $threshold = (int) config('activitylog.failed_log_jobs_alert_threshold', 50);
        if ($failedJobs >= $threshold) {
            $this->error("Failed log jobs exceed alert threshold ({$threshold}).");
            $ok = false;
        }

        if (DB::getSchemaBuilder()->hasTable('request_activity_log_rollups')) {
            $rollupCount = DB::table('request_activity_log_rollups')->count();
            $this->line("Rollup rows: {$rollupCount}");
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
