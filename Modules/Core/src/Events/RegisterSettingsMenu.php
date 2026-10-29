<?php

namespace Modules\Core\Events;

use Illuminate\Support\Facades\Cache;

class RegisterSettingsMenu
{
    private const CACHE_KEY_ITEMS = 'core.events.settings_menu.items';

    /**
     * Guards to use for settings menu
     *
     * @var array
     */
    public array $guards = [];

    /**
     * Predefined Categories
     *
     * @var array
     */
    public array $categories = [
        'general' => [
            'name' => 'System Preference',
            'description' => 'General application settings',
            'sort_order' => 1,
            'code' => 'general',
        ],
        'notifications' => [
            'name' => 'Notifications',
            'description' => 'Email / SMS & in-app notifications settings',
            'sort_order' => 2,
            'code' => 'notifications',
        ],
        'access_control' => [
            'name' => 'Access Control & Monitoring',
            'description' => 'Roles, permissions & access rules',
            'sort_order' => 3,
            'code' => 'access_control',
        ],
        'interface' => [
            'name' => 'Interface Customization',
            'description' => 'Connect external services & APIs',
            'sort_order' => 4,
            'code' => 'interface',
        ],
        'third_party_integrations' => [
            'name' => 'Third Party Integrations',
            'description' => 'Connect external services & APIs',
            'sort_order' => 4,
            'code' => 'third_party_integrations',
        ],
        'anyaltics' => [
            'name' => 'Analytics',
            'description' => 'Analytics & data visualization settings',
            'sort_order' => 4,
            'code' => 'anyaltics',
        ],
        'cache' => [
            'name' => 'Cache',
            'description' => 'Cache management',
            'sort_order' => 4,
            'code' => 'cache',
        ],
        'others' => [
            'name' => 'Others',
            'description' => 'Other related settings',
            'sort_order' => 5,
            'code' => 'others',
        ],
    ];
    
    /**
     * Constructor to set guards
     */
    public function __construct(array $guards = null)
    {
        $this->guards = $guards ?? (array_keys(config('acl::guards.guards')) ?? ['admin']);
    }

    /**
     * @var array
     */
    public array $items = [];

    /**
     * Get cached grouped menu (builds and caches via event listeners).
     */
    public static function getCachedGroupedMenu(): array
    {
        $items = static::getCachedItems();
        $event = new self();
        $event->items = $items;

        return $event->getGroupedMenu();
    }

    /**
     * Clear cached settings menu items.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ITEMS);
    }

    protected static function getCachedItems(): array
    {
        if (!static::cacheEnabled()) {
            return static::buildItems();
        }

        return Cache::remember(self::CACHE_KEY_ITEMS, static::cacheTtl(), function () {
            return static::buildItems();
        });
    }

    protected static function buildItems(): array
    {
        $event = new self();
        \event($event);

        return $event->items;
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
     * Register menu item directly under category
     */
    public function register(array $item): void
    {
        if (!isset($item['category_code'])) {
            throw new \Exception("Menu item missing required key: category_code");
        }
        if (!isset($this->categories[$item['category_code']])) {
            throw new \Exception("Category '{$item['category_code']}' not found in settings menu.");
        }
        $guards = $item['guard'] ?? (array_keys(config('acl::guards.guards')) ?? ['admin']);
        if (!is_array($guards)) {
            $guards = [$guards];
        }
        foreach ($guards as $guard) {
            $item['guard'] = $guard;
            $this->items[] = $item;
        }
    }

    /**
     * Get menu grouped by category
     */
    public function getGroupedMenu(): array
    {
        $grouped = [];

        // Prepare groups
        foreach ($this->categories as $code => $category) {
            $grouped[$code] = [
                'category' => $category,
                'items' => [],
            ];
        }

        // Get current user's guard (example: from auth or session)
        $currentGuard = \Illuminate\Support\Facades\Auth::getDefaultDriver();
        $defaultGuards = array_keys(config('acl::guards.guards')) ?? ['admin'];
        // Attach items into categories, filter by guard
        foreach ($this->items as $item) {
            $itemGuards = isset($item['guard']) ? (is_array($item['guard']) ? $item['guard'] : [$item['guard']]) : $defaultGuards;
            if (in_array($currentGuard, $itemGuards)) {
                $grouped[$item['category_code']]['items'][] = $item;
            }
        }

        // Sort by sort_order
        uasort($grouped, fn($a, $b) => $a['category']['sort_order'] <=> $b['category']['sort_order']);

        return $grouped;
    }
}
