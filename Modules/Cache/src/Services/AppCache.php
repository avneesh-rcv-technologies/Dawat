<?php

namespace Modules\Cache\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Profile-based application cache with registry + optional meta for admin.
 *
 * Example:
 *   AppCache::remember('plans', 'landing_formatted', fn () => ...);
 *   AppCache::remember('seo', 'sitemap.xml', fn () => ..., 7200); // TTL override
 *   AppCache::flush('plans');
 */
class AppCache
{
    public static function enabled(): bool
    {
        return (bool) config('cache::app_cache.enabled', true);
    }

    public static function remember(string $profile, string $name, callable $callback, ?int $ttlOverride = null): mixed
    {
        if (! self::enabled()) {
            return $callback();
        }

        self::assertProfile($profile);

        $store = self::store($profile);
        $key = self::dataKey($profile, $name);
        [$forever, $ttl] = self::resolveTtl($profile, $ttlOverride);

        $resolver = function () use ($callback, $profile, $name, $forever, $ttl) {
            $value = $callback();
            self::registerEntry($profile, $name);
            self::writeMeta($profile, $name, $value, $forever, $ttl);

            return $value;
        };

        if ($forever) {
            return $store->rememberForever($key, $resolver);
        }

        return $store->remember($key, max(1, $ttl), $resolver);
    }

    public static function put(string $profile, string $name, mixed $value, ?int $ttlOverride = null): void
    {
        if (! self::enabled()) {
            return;
        }

        self::assertProfile($profile);

        $store = self::store($profile);
        $key = self::dataKey($profile, $name);
        [$forever, $ttl] = self::resolveTtl($profile, $ttlOverride);

        if ($forever) {
            $store->forever($key, $value);
        } else {
            $store->put($key, $value, max(1, $ttl));
        }

        self::registerEntry($profile, $name);
        self::writeMeta($profile, $name, $value, $forever, $ttl);
    }

    /**
     * Atomic add — only writes when the key is missing.
     */
    public static function add(string $profile, string $name, mixed $value, ?int $ttlOverride = null): bool
    {
        if (! self::enabled()) {
            return true;
        }

        self::assertProfile($profile);

        $store = self::store($profile);
        $key = self::dataKey($profile, $name);
        [$forever, $ttl] = self::resolveTtl($profile, $ttlOverride);

        $added = $forever
            ? $store->add($key, $value, now()->addYears(5))
            : $store->add($key, $value, max(1, $ttl));

        if ($added) {
            self::registerEntry($profile, $name);
            self::writeMeta($profile, $name, $value, $forever, $ttl);
        }

        return (bool) $added;
    }

    public static function get(string $profile, string $name, mixed $default = null): mixed
    {
        if (! self::enabled()) {
            return $default;
        }

        return self::store($profile)->get(self::dataKey($profile, $name), $default);
    }

    public static function has(string $profile, string $name): bool
    {
        if (! self::enabled()) {
            return false;
        }

        return self::store($profile)->has(self::dataKey($profile, $name));
    }

    public static function pull(string $profile, string $name, mixed $default = null): mixed
    {
        if (! self::enabled()) {
            return $default;
        }

        self::assertProfile($profile);

        $store = self::store($profile);
        $value = $store->pull(self::dataKey($profile, $name), $default);
        $store->forget(self::metaKey($profile, $name));

        $registryKey = self::registryKey($profile);
        $names = $store->get($registryKey, []);
        if (is_array($names)) {
            $names = array_values(array_filter($names, static fn ($n) => $n !== $name));
            if ($names === []) {
                $store->forget($registryKey);
            } else {
                $store->forever($registryKey, $names);
            }
        }

        return $value;
    }

    public static function increment(string $profile, string $name, int $amount = 1): int|bool
    {
        if (! self::enabled()) {
            return 0;
        }

        self::assertProfile($profile);

        $store = self::store($profile);
        $key = self::dataKey($profile, $name);

        if (! $store->has($key)) {
            [$forever, $ttl] = self::resolveTtl($profile, null);
            if ($forever) {
                $store->forever($key, 0);
            } else {
                $store->put($key, 0, max(1, $ttl));
            }
            self::registerEntry($profile, $name);
        }

        return $store->increment($key, $amount);
    }

    public static function forget(string $profile, string $name): void
    {
        $store = self::store($profile);
        $store->forget(self::dataKey($profile, $name));
        $store->forget(self::metaKey($profile, $name));

        $registryKey = self::registryKey($profile);
        $names = $store->get($registryKey, []);
        if (! is_array($names)) {
            $names = [];
        }

        $names = array_values(array_filter($names, static fn ($n) => $n !== $name));
        if ($names === []) {
            $store->forget($registryKey);
        } else {
            $store->forever($registryKey, $names);
        }
    }

    /**
     * Forget registry entries whose names start with $prefix (does not flush the whole profile).
     *
     * @return int Number of entries removed
     */
    public static function forgetMatching(string $profile, string $prefix): int
    {
        self::assertProfile($profile);

        $removed = 0;
        foreach (self::entries($profile) as $entry) {
            $name = (string) ($entry['name'] ?? '');
            if ($name !== '' && str_starts_with($name, $prefix)) {
                self::forget($profile, $name);
                $removed++;
            }
        }

        return $removed;
    }

    public static function flush(string $profile): void
    {
        $store = self::store($profile);
        $registryKey = self::registryKey($profile);
        $names = $store->get($registryKey, []);

        if (is_array($names)) {
            foreach ($names as $name) {
                $store->forget(self::dataKey($profile, (string) $name));
                $store->forget(self::metaKey($profile, (string) $name));
            }
        }

        $store->forget($registryKey);
    }

    public static function flushAll(bool $onlyClearable = true): void
    {
        foreach (array_keys((array) config('cache::app_cache.profiles', [])) as $profile) {
            if ($onlyClearable && ! config("cache::app_cache.profiles.{$profile}.admin_clearable", true)) {
                continue;
            }
            self::flush((string) $profile);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function profiles(): array
    {
        return (array) config('cache::app_cache.profiles', []);
    }

    /**
     * @return list<array{name: string, meta: array<string, mixed>|null, data_key: string}>
     */
    public static function entries(string $profile): array
    {
        $store = self::store($profile);
        $names = $store->get(self::registryKey($profile), []);
        if (! is_array($names)) {
            return [];
        }

        $out = [];
        foreach ($names as $name) {
            $name = (string) $name;
            $meta = $store->get(self::metaKey($profile, $name));
            $out[] = [
                'name' => $name,
                'data_key' => self::dataKey($profile, $name),
                'meta' => is_array($meta) ? $meta : null,
            ];
        }

        return $out;
    }

    /**
     * @return array{profiles: array<string, array{label: string, keys: int, bytes: int, ttl: ?int, forever: bool}>, total_bytes: int}
     */
    public static function stats(): array
    {
        $profiles = [];
        $totalBytes = 0;

        foreach (self::profiles() as $profile => $config) {
            $bytes = 0;
            $entries = self::entries((string) $profile);
            foreach ($entries as $entry) {
                $bytes += (int) ($entry['meta']['bytes'] ?? 0);
            }
            $totalBytes += $bytes;
            $profiles[$profile] = [
                'label' => (string) ($config['label'] ?? $profile),
                'keys' => count($entries),
                'bytes' => $bytes,
                'ttl' => ! empty($config['forever']) ? null : (int) ($config['ttl'] ?? 0),
                'forever' => (bool) ($config['forever'] ?? false),
            ];
        }

        return [
            'profiles' => $profiles,
            'total_bytes' => $totalBytes,
        ];
    }

    public static function formatBytes(int $bytes): string
    {
        $bytes = max(0, $bytes);
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;
        $value = $bytes / (1024 ** $power);

        return round($value, $power === 0 ? 0 : 2) . ' ' . $units[$power];
    }

    public static function profileExists(string $profile): bool
    {
        return array_key_exists($profile, self::profiles());
    }

    public static function isClearable(string $profile): bool
    {
        return (bool) config("cache::app_cache.profiles.{$profile}.admin_clearable", true);
    }

    /**
     * @return array{store: string, used_memory: ?string, used_memory_bytes: ?int, maxmemory: ?string, error: ?string}
     */
    public static function redisMemory(): array
    {
        $storeName = (string) config('cache::app_cache.store', config('cache.default', 'redis'));
        $out = [
            'store' => $storeName,
            'used_memory' => null,
            'used_memory_bytes' => null,
            'maxmemory' => null,
            'error' => null,
        ];

        try {
            if ($storeName !== 'redis') {
                $out['error'] = 'Memory details are available when CACHE_STORE=redis.';

                return $out;
            }

            $connection = config('cache.stores.redis.connection', 'cache');
            /** @var \Illuminate\Redis\Connections\Connection $redis */
            $redis = \Illuminate\Support\Facades\Redis::connection($connection);
            $info = $redis->info('memory');
            if (isset($info['Memory']) && is_array($info['Memory'])) {
                $info = $info['Memory'];
            }
            $used = (int) ($info['used_memory'] ?? 0);
            $max = (int) ($info['maxmemory'] ?? 0);

            $out['used_memory_bytes'] = $used;
            $out['used_memory'] = $info['used_memory_human'] ?? self::formatBytes($used);
            $out['maxmemory'] = $max > 0
                ? ($info['maxmemory_human'] ?? self::formatBytes($max))
                : 'unlimited';
        } catch (Throwable $e) {
            $out['error'] = $e->getMessage();
        }

        return $out;
    }

    public static function dataKey(string $profile, string $name): string
    {
        $prefix = (string) config('cache::app_cache.prefix', 'app');

        return "{$prefix}:{$profile}:{$name}";
    }

    public static function registryKey(string $profile): string
    {
        $prefix = (string) config('cache::app_cache.registry_prefix', 'app:registry');

        return "{$prefix}:{$profile}";
    }

    public static function metaKey(string $profile, string $name): string
    {
        $prefix = (string) config('cache::app_cache.meta_prefix', 'app:meta');

        return "{$prefix}:{$profile}:{$name}";
    }

    /**
     * @return array{0: bool, 1: int} [forever, ttlSeconds]
     */
    protected static function resolveTtl(string $profile, ?int $ttlOverride): array
    {
        $forever = (bool) config("cache::app_cache.profiles.{$profile}.forever", false);
        if ($ttlOverride !== null) {
            return [false, max(1, $ttlOverride)];
        }

        $ttl = (int) (config("cache::app_cache.profiles.{$profile}.ttl") ?? 3600);

        return [$forever, max(1, $ttl ?: 3600)];
    }

    protected static function store(?string $profile = null): Repository
    {
        $name = null;
        if ($profile !== null) {
            $name = config("cache::app_cache.profiles.{$profile}.store");
        }
        if (! is_string($name) || $name === '') {
            $name = (string) config('cache::app_cache.store', config('cache.default', 'redis'));
        }

        try {
            return Cache::store($name);
        } catch (Throwable) {
            return Cache::store();
        }
    }

    protected static function assertProfile(string $profile): void
    {
        if (! array_key_exists($profile, self::profiles())) {
            throw new \InvalidArgumentException("Unknown AppCache profile [{$profile}]. Add it to cache::app_cache.profiles.");
        }
    }

    protected static function registerEntry(string $profile, string $name): void
    {
        $store = self::store($profile);
        $registryKey = self::registryKey($profile);
        $names = $store->get($registryKey, []);
        if (! is_array($names)) {
            $names = [];
        }

        if (! in_array($name, $names, true)) {
            $names[] = $name;
            $store->forever($registryKey, $names);
        }
    }

    protected static function writeMeta(
        string $profile,
        string $name,
        mixed $value,
        bool $forever,
        int $ttl
    ): void {
        $meta = [
            'bytes' => self::estimateBytes($value),
            'written_at' => now()->toDateTimeString(),
            'ttl' => $forever ? null : max(1, $ttl),
            'forever' => $forever,
            'profile' => $profile,
        ];

        $store = self::store($profile);
        $metaKey = self::metaKey($profile, $name);

        if ($forever) {
            $store->forever($metaKey, $meta);
        } else {
            $store->put($metaKey, $meta, max(1, $ttl));
        }
    }

    protected static function estimateBytes(mixed $value): int
    {
        try {
            return strlen(serialize($value));
        } catch (Throwable) {
            return 0;
        }
    }
}
