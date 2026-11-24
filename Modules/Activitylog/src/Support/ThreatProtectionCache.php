<?php

namespace Modules\Activitylog\Support;

use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Models\AllowedIp;
use Modules\Activitylog\Models\BlockedIp;
use Modules\Cache\Services\AppCache;
use Throwable;

class ThreatProtectionCache
{
    public const BLOCKED_IPS_KEY = 'blocked_ips:active';

    public const ALLOWED_ADMIN_IPS_KEY = 'allowed_admin_ips:active';

    public const TTL_SECONDS = 300;

    public static function isIpBlocked(string $ipAddress): bool
    {
        try {
            if (self::isSettingAllowedIp($ipAddress)) {
                return false;
            }

            return in_array($ipAddress, self::blockedIpAddresses(), true);
        } catch (Throwable $e) {
            Log::warning('Failed to resolve blocked IP cache', [
                'error' => $e->getMessage(),
                'ip' => $ipAddress,
            ]);

            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    public static function allowedAdminIpAddresses(): array
    {
        try {
            return AppCache::remember(self::profile(), self::ALLOWED_ADMIN_IPS_KEY, function (): array {
                $dbAllowedIps = AllowedIp::query()
                    ->active()
                    ->pluck('ip_address')
                    ->all();

                return array_values(array_unique(array_merge(
                    $dbAllowedIps,
                    self::settingAllowedIps()
                )));
            }, self::TTL_SECONDS);
        } catch (Throwable $e) {
            Log::warning('Failed to resolve allowed admin IP cache', [
                'error' => $e->getMessage(),
            ]);

            return self::settingAllowedIps();
        }
    }

    public static function forgetBlockedIps(): void
    {
        AppCache::forget(self::profile(), self::BLOCKED_IPS_KEY);
    }

    public static function forgetAllowedAdminIps(): void
    {
        AppCache::forget(self::profile(), self::ALLOWED_ADMIN_IPS_KEY);
    }

    public static function forgetAll(): void
    {
        if (function_exists('fn_clear_security_cache')) {
            fn_clear_security_cache();

            return;
        }

        self::forgetBlockedIps();
        self::forgetAllowedAdminIps();
    }

    /**
     * @return array<int, string>
     */
    protected static function blockedIpAddresses(): array
    {
        try {
            return AppCache::remember(self::profile(), self::BLOCKED_IPS_KEY, function (): array {
                return BlockedIp::query()
                    ->active()
                    ->pluck('ip_address')
                    ->all();
            }, self::TTL_SECONDS);
        } catch (Throwable $e) {
            Log::warning('Failed to load blocked IP addresses', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected static function profile(): string
    {
        return 'security';
    }

    /**
     * @return array<int, string>
     */
    protected static function settingAllowedIps(): array
    {
        $raw = explode(',', (string) fn_get_setting('general.allowed_ips', ''));
        $ips = array_map(static fn (string $ip): string => trim($ip), $raw);

        return array_values(array_filter($ips, static fn (string $ip): bool => $ip !== ''));
    }

    protected static function isSettingAllowedIp(string $ipAddress): bool
    {
        return in_array($ipAddress, self::settingAllowedIps(), true);
    }
}
