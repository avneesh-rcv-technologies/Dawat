<?php

namespace Modules\Core\Events;

use Illuminate\Support\Facades\Cache;

class RegisterPermissions
{
    private const CACHE_KEY = 'core.events.permissions';

    protected array $permissions = [];

    /**
     * Register permission group
     * Structure example:
     * [
     *   'name' => 'Settings',
     *   'code' => 'general',
     *   'key' => ['settings.index' => 'View Settings']
     * ]
     */
    public function registerArray(array $group)
    {
        $name = $group['name'] ?? 'general';
        $code = $group['code'] ?? 'other';
        $keys = $group['key'] ?? [];
        $guards = $group['guard'] ?? (array_keys(config('acl::guards.guards')) ?? ['admin']);

        foreach ($guards as $guard) {

            if ($code) {
                // Third level (category → code-group → permissions)
                foreach ($keys as $k => $v) {
                    $this->permissions[$guard][$name][$code][$k] = $v;
                }
            } else {
                // Secondary level (category without sub code)
                foreach ($keys as $k => $v) {
                    $this->permissions[$guard][$name][$k] = $v;
                }
            }
        }
    }

    /**
     * Get cached permissions (builds and caches via event listeners).
     */
    public static function getCachedPermissions(): array
    {
        if (!static::cacheEnabled()) {
            return static::buildPermissions();
        }

        return Cache::remember(self::CACHE_KEY, static::cacheTtl(), function () {
            return static::buildPermissions();
        });
    }

    /**
     * Clear cached permissions.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function buildPermissions(): array
    {
        $event = new self();
        \event($event);

        return $event->getPermissions();
    }

    protected static function cacheEnabled(): bool
    {
        return (bool) fn_get_setting('general.cache_enable', true);
    }

    protected static function cacheTtl(): int
    {
        return (int) fn_get_setting('general.cache_ttl', 3600);
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
