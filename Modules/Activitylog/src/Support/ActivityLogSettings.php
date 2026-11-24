<?php

namespace Modules\Activitylog\Support;

class ActivityLogSettings
{
    public static function isEnabled(): bool
    {
        return fn_get_setting('activitylog.enabled', 'Y') === 'Y';
    }

    public static function threatProtectionEnabled(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        return fn_get_setting('activitylog.threat_protection_enabled', 'Y') === 'Y';
    }

    public static function requestLoggingEnabled(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        return fn_get_setting('activitylog.request_logging_enabled', 'Y') === 'Y';
    }

    public static function storeResponseBodyForErrorsOnly(): bool
    {
        return fn_get_setting('activitylog.store_response_body_errors_only', 'Y') === 'Y';
    }

    /**
     * Read activitylog.* first, then general.* (UI saves many keys under general.*).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $activityValue = fn_get_setting("activitylog.{$key}");

        if ($activityValue !== null && $activityValue !== '') {
            return $activityValue;
        }

        $generalValue = fn_get_setting("general.{$key}");

        if ($generalValue !== null && $generalValue !== '') {
            return $generalValue;
        }

        return $default;
    }

    public static function getInt(string $key, int $default): int
    {
        return (int) self::get($key, $default);
    }
}
