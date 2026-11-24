<?php

use Modules\Cache\Services\AppCache;

if (! function_exists('app_cache')) {
    /**
     * @return class-string<AppCache>
     */
    function app_cache(): string
    {
        return AppCache::class;
    }
}

if (! function_exists('app_cache_remember')) {
    function app_cache_remember(string $profile, string $name, callable $callback): mixed
    {
        return AppCache::remember($profile, $name, $callback);
    }
}

if (! function_exists('app_cache_flush')) {
    function app_cache_flush(string $profile): void
    {
        AppCache::flush($profile);
    }
}

if (! function_exists('app_cache_forget')) {
    function app_cache_forget(string $profile, string $name): void
    {
        AppCache::forget($profile, $name);
    }
}

if (! function_exists('app_cache_filters_key')) {
    function app_cache_filters_key(array $filters = []): string
    {
        $normalized = $filters;
        ksort($normalized);

        return hash('sha256', (string) json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
