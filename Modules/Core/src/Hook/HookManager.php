<?php

namespace Modules\Core\Hook;

class HookManager
{
    protected static array $hooks = [];
    protected static array $overrides = [];

    public static function add(string $hook, string $view, int $priority = 10)
    {
        self::$hooks[$hook][] = [
            'view' => $view,
            'priority' => $priority,
        ];
    }

    public static function override(string $hook, string $view)
    {
        self::$overrides[$hook] = $view;
    }

    public static function get(string $hook): array
    {
        if (isset(self::$overrides[$hook])) {
            return [
                ['view' => self::$overrides[$hook], 'priority' => 0]
            ];
        }

        $list = self::$hooks[$hook] ?? [];

        usort($list, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $list;
    }
}