<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BaseCacheService
{
    //TTL constants
    const TTL_SHORT  = 300;   //  5 min
    const TTL_MEDIUM = 900;   // 15 min
    const TTL_LONG   = 1800;  // 30 min
    const TTL_HOUR   = 3600;  // 60 min
    const TTL_2_HOURS = 7200;  // 2 hours
    const TTL_4_HOURS = 14400; // 4 hours
    const TTL_6_HOURS = 21600; // 6 hours
    const TTL_DAY    = 86400;  // 24 hours

    /**
     *  cache remember 
     */
    protected function remember(string $key, int $ttl, callable $callback)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache operation failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            // Fallback to direct callback execution
            return $callback();
        }
    }

    /**
     * Forget single cache key
     */
    protected function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (\Exception $e) {
            Log::error('Cache forget failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Forget cache by pattern (wildcard support)
     * Works with Redis, gracefully fails with other drivers
     */
    protected function forgetPattern(string $pattern): int
    {
        try {
            $driver = config('cache.default');
            
            // Only works with Redis
            if ($driver !== 'redis') {
                Log::warning('forgetPattern only works with Redis driver', [
                    'pattern' => $pattern,
                    'current_driver' => $driver
                ]);
                return 0;
            }
            
            $redis = Cache::getRedis();
            $prefix = config('cache.prefix') ?: config('cache.stores.redis.prefix', '');
            $fullPattern = $prefix ? "{$prefix}:{$pattern}" : $pattern;
            
            $keys = $redis->keys($fullPattern);
            $count = 0;
            
            foreach ($keys as $key) {
                // Remove prefix before forgetting
                $cleanKey = $prefix ? str_replace("{$prefix}:", '', $key) : $key;
                Cache::forget($cleanKey);
                $count++;
            }
            
            return $count;
        } catch (\Exception $e) {
            Log::error('Cache pattern forget failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Forget multiple cache keys
     * Alternative to forgetPattern for non-Redis drivers
     */
    protected function forgetMultiple(array $keys): int
    {
        $count = 0;
        foreach ($keys as $key) {
            try {
                Cache::forget($key);
                $count++;
            } catch (\Exception $e) {
                Log::error('Cache forget failed', ['key' => $key, 'error' => $e->getMessage()]);
            }
        }
        return $count;
    }

    /**
     * Flush all cache
     */
    public function flush(): bool
    {
        try {
            return Cache::flush();
        } catch (\Exception $e) {
            Log::error('Cache flush failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
