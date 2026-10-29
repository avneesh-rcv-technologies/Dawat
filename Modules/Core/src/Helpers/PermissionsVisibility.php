<?php

namespace Modules\Core\Helpers;

class PermissionsVisibility
{
    /**
     * Determine whether a permission should be visible for the current guard.
     *
     * @param array $permissionGroup
     * @param string|null $guard
     * @return bool
     */
    public static function visible(array $permissionGroup, ?string $guard = null): bool
    {
        $guards = $permissionGroup['guard'] ?? array_keys(config('acl::guards.guards')) ?? ['admin'];
        if (!is_array($guards)) {
            $guards = [$guards];
        }
        $currentGuard = $guard ?? ('admin');
        return in_array($currentGuard, $guards);
    }
}
