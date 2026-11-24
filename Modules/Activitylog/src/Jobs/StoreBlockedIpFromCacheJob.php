<?php

namespace Modules\Activitylog\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Activitylog\Http\Middleware\TrackRequestActivity;
use Modules\Cache\Services\AppCache;
use Throwable;

class StoreBlockedIpFromCacheJob implements ShouldQueue
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

        TrackRequestActivity::storeBlockedIpPayload($payload);
    }

    public function failed(Throwable $exception): void
    {
        try {
            $payload = AppCache::pull('activity', $this->cacheKey);

            if (is_array($payload) && $payload !== []) {
                TrackRequestActivity::storeBlockedIpPayload($payload);
            }
        } catch (Throwable $e) {
            // swallow
        }
    }
}
