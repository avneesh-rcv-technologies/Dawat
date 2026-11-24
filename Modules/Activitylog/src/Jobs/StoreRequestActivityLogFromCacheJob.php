<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Http\Middleware\TrackRequestActivity;
use Modules\Cache\Services\AppCache;
use Throwable;

class StoreRequestActivityLogFromCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public string $cacheKey)
    {
    }

    public function handle(): void
    {
        $payload = AppCache::pull('activity', $this->cacheKey);

        if (!is_array($payload) || $payload === []) {
            return;
        }

        TrackRequestActivity::storeLogPayload($payload);
    }

    public function failed(Throwable $exception): void
    {
        AppCache::increment('activity', 'failed_log_jobs');

        Log::warning('Failed to persist request activity log from cache.', [
            'cache_key' => $this->cacheKey,
            'message' => $exception->getMessage(),
        ]);

        try {
            $payload = AppCache::pull('activity', $this->cacheKey);

            if (is_array($payload) && $payload !== []) {
                TrackRequestActivity::storeLogPayload($payload);
            }
        } catch (Throwable $e) {
            Log::error('Activity log fallback persistence failed.', [
                'cache_key' => $this->cacheKey,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
