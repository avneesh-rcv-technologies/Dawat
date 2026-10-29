<?php

namespace Modules\Core\Helpers;

use function bouncer;

class MenuVisibility
{
    /**
     * Determine whether a menu item should be visible based on permission rules.
     * Supported item keys:
     * - 'permission' => string|array of permissions or arrays
     * - 'guard' => string|array default guard(s) to check when permission is a string
     * - 'permission_mode' => 'any' (default) | 'all'
     *
     * Permission formats:
     * - 'manage.users' (string) — checked with provided 'guard' or default 'admin'
     * - ['manage.users','manage.roles'] (array of strings) — any of them passing will show the item (unless permission_mode='all')
     * - [ ['guard'=>'web','permission'=>'web.perm'], ['guard'=>'admin','permission'=>'admin.perm'] ] — explicit guard-permission pairs
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

        $checkSingle = function ($perm, $guard = null) use ($item) {
            $guard = $guard ?? ($perm && is_array($perm) && isset($perm['guard']) ? $perm['guard'] : null);
            // If perm is an associative array with guard/permission
            if (is_array($perm) && isset($perm['permission'])) {
                $guard = $perm['guard'] ?? null;
                $permission = $perm['permission'];
            } else {
                $permission = is_array($perm) ? null : $perm;
            }

            if (!$permission) return false;

            // Default guard fallback (may be string or array)
            $guard = $guard ?? ($item['guard'] ?? 'admin');

            // If guard is an array, try any guard and return true on first success
            if (is_array($guard)) {
                foreach ($guard as $g) {
                    try {
                        if (bouncer($g)->hasPermission($permission)) {
                            return true;
                        }
                    } catch (\Throwable $e) {
                        // ignore and try next guard
                        continue;
                    }
                }

                // none matched
                return false;
            }

            try {
                return bouncer($guard)->hasPermission($permission);
            } catch (\Throwable $e) {
                // If bouncer or guard is not available, be conservative and deny
                return false;
            }
        };

        $permissions = $item['permission'];

        // Normalize to array
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        $results = [];
        foreach ($permissions as $p) {
            if (is_array($p) && isset($p['permission'])) {
                $results[] = $checkSingle($p, $p['guard'] ?? null);
            } else {
                $results[] = $checkSingle($p, $item['guard'] ?? null);
            }
        }

        if ($mode === 'all') {
            return !in_array(false, $results, true);
        }

        // default any
        return in_array(true, $results, true);
    }
}
