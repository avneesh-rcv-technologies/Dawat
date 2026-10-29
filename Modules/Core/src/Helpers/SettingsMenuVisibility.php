<?php

namespace Modules\Core\Helpers;

class SettingsMenuVisibility
{
    /**
     * Determine whether a settings menu item should be visible based on permission and guard rules.
     *
     * @param array $item
     * @return bool
     */
    public static function visible(array $item): bool
    {
        if (empty($item['permission'])) {
            return true;
        }

        $mode = $item['permission_mode'] ?? 'any';
        $permissions = $item['permission'];
        $guards = $item['guard'] ?? array_keys(config('acl::guards.guards')) ?? ['admin'];
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        if (!is_array($guards)) {
            $guards = [$guards];
        }

        $results = [];
        foreach ($permissions as $permission) {
            $permResult = false;
            foreach ($guards as $guard) {
                try {
                    if (bouncer($guard)->hasPermission($permission)) {
                        $permResult = true;
                        break;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }
            $results[] = $permResult;
        }

        if ($mode === 'all') {
            return !in_array(false, $results, true);
        }
        return in_array(true, $results, true);
    }
}
