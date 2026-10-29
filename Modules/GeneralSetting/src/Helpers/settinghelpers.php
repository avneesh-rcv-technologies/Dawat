<?php

use Modules\GeneralSetting\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('fn_settings_cache_key')) {
    function fn_settings_cache_key(): string
    {
        return 'generalsetting.settings.all';
    }
}

if (!function_exists('fn_settings_cache_enabled')) {
    function fn_settings_cache_enabled(): bool
    {
        return (bool) config('generalsetting.settings.cache', true);
    }
}

if (!function_exists('fn_settings_cache_ttl')) {
    function fn_settings_cache_ttl(): int
    {
        return (int) config('generalsetting.settings.cache_ttl', 3600);
    }
}

if (!function_exists('fn_clear_settings_cache')) {
    function fn_clear_settings_cache(): void
    {
        if (fn_settings_cache_enabled()) {
            Cache::forget(fn_settings_cache_key());
        }
    }
}

if (!function_exists('fn_get_all_settings')) {
    function fn_get_all_settings(): array
    {
        $cacheKey = fn_settings_cache_key();

        if (fn_settings_cache_enabled()) {
            return Cache::remember($cacheKey, fn_settings_cache_ttl(), function () {
                return Setting::pluck('value', 'key')->toArray();
            });
        }

        return Setting::pluck('value', 'key')->toArray();
    }
}

if (!function_exists('fn_get_setting')) {
    function fn_get_setting(string $key, $default = null)
    {
        $settings = fn_get_all_settings();
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('fn_set_setting')) {
    function fn_set_setting(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        fn_clear_settings_cache();
    }
}

if (!function_exists('fn_delete_setting')) {
    function fn_delete_setting(string $key): bool
    {
        $query = Setting::where('key', $key);
        $deleted = $query->delete();

        if ($deleted) {
            fn_clear_settings_cache();
        }

        return $deleted;
    }
}

if (!function_exists('fn_get_name_placeholder')) {
    function fn_get_name_placeholder($name)
    {
        $nameHash = crc32($name);
        $colors = [
            'bg-red-100 text-red-600',
            'bg-blue-100 text-blue-600',
            'bg-green-100 text-green-600',
            'bg-yellow-100 text-yellow-600',
            'bg-purple-100 text-purple-600',
            'bg-pink-100 text-pink-600',
            'bg-indigo-100 text-indigo-600'
        ];
        $colorIndex = abs($nameHash) % count($colors);
        $colorClass = $colors[$colorIndex];
        $initials = strtoupper(substr($name, 0, 2));

        return [$initials, $colorClass];
    }
}

if (!function_exists('fn_get_settings')) {

    function fn_get_settings(): array
    {
        return fn_get_all_settings();
    }
}

if (!function_exists('fn_get_setting')) {

    function fn_get_setting(string $key, $default = null)
    {
        $globalSetting = Setting::where('key', $key)
            ->first();
        return $globalSetting ? $globalSetting->value : $default;
    }
}


if (!function_exists('fn_update_setting')) {
    function fn_update_setting(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        fn_clear_settings_cache();
    }
}

if (!function_exists('fn_delete_setting')) {
    function fn_delete_setting(string $key): bool
    {
        $deleted = Setting::where('key', $key)->delete();

        if ($deleted) {
            fn_clear_settings_cache();
        }

        return $deleted;
    }
}

if (!function_exists('fn_format_bytes')) {
    function fn_format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('fn_get_name_placeholder')) {
    function fn_get_name_placeholder($name)
    {
        $nameHash = crc32($name);
        $colors = [
            'bg-red-100 text-red-600',
            'bg-blue-100 text-blue-600',
            'bg-green-100 text-green-600',
            'bg-yellow-100 text-yellow-600',
            'bg-purple-100 text-purple-600',
            'bg-pink-100 text-pink-600',
            'bg-indigo-100 text-indigo-600'
        ];
        $colorIndex = abs($nameHash) % count($colors);
        $colorClass = $colors[$colorIndex];
        $initials = strtoupper(substr($name, 0, 2));

        return [$initials, $colorClass];
    }
}
