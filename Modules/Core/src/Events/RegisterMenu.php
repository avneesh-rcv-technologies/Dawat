<?php

namespace Modules\Core\Events;

use Illuminate\Support\Facades\Cache;

class RegisterMenu
{
    private const CACHE_KEY = 'core.events.menu';

    /**
     * Menu items array
     *
     * @var array
     */
    public array $menu = [];

    /**
     * Create a new event instance.
     *
     * @param array $menu Initial menu items
     */
    public function __construct(array $menu = [])
    {
        $this->menu = $menu;
    }

    /**
     * Get cached menu items (builds and caches via event listeners).
     */
    public static function getCachedMenu(): array
    {
        if (!static::cacheEnabled()) {
            return static::buildMenu();
        }

        return Cache::remember(self::CACHE_KEY, static::cacheTtl(), function () {
            return static::buildMenu();
        });
    }

    /**
     * Clear cached menu items.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function buildMenu(): array
    {
        $event = new self();
        \event($event);

        return $event->getMenu();
    }

    protected static function cacheEnabled(): bool
    {
        return (bool) fn_get_setting('general.cache_enable', true);
    }

    protected static function cacheTtl(): int
    {
        return (int) fn_get_setting('general.cache_ttl', 3600);
    }

    /**
     * Add a menu item
     *
     * @param array $item
     * @return void
     */
    public function addItem(array $item): void
    {
        // Normalize guard to array
        if (isset($item['guard'])) {
            if (!is_array($item['guard'])) {
                $item['guard'] = [$item['guard']];
            }
        } else {
            $item['guard'] = array_keys(config('acl::guards.guards')) ?? ['admin'];
        }

        // Add default guard to children items if they exist
        if (isset($item['children']) && is_array($item['children'])) {
            $item['children'] = array_map(function ($child) use ($item) {
                if (!isset($child['guard'])) {
                    $child['guard'] = $item['guard'];
                } elseif (!is_array($child['guard'])) {
                    $child['guard'] = [$child['guard']];
                }
                return $child;
            }, $item['children']);
        }

        $this->menu[] = $item;
    }

    /**
     * Get guards for a menu item
     */
    public function getItemGuards(array $item): array
    {
        return $item['guard'] ?? array_keys(config('acl::guards.guards')) ?? ['admin'];
    }

    /**
     * Get all menu items
     *
     * @return array
     */
    public function getMenu(): array
    {
        return $this->menu;
    }
}
