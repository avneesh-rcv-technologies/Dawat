<?php

namespace Modules\Seo\Support;

class SeoSettings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $configDefault = data_get(config('seo'), str_replace('/', '.', $key), $default);

        if (! function_exists('fn_get_seo_setting')) {
            return $configDefault;
        }

        return fn_get_seo_setting($key, $configDefault);
    }

    public static function enabled(): bool
    {
        return (bool) self::get('enabled', config('seo.settings.enabled', true));
    }
}
