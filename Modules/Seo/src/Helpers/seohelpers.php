<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Cache\Services\AppCache;
use Modules\Seo\Models\SeoSetting;
use Modules\Seo\Support\SeoRequestCache;

if (! function_exists('fn_seo_settings_cache_key')) {
    function fn_seo_settings_cache_key(): string
    {
        return 'settings:all';
    }
}

if (! function_exists('fn_forget_seo_settings_cache')) {
    function fn_forget_seo_settings_cache(): void
    {
        SeoRequestCache::forget('settings');
        SeoRequestCache::forget('table:seo_settings');
        AppCache::forget('seo', fn_seo_settings_cache_key());
    }
}

if (! function_exists('fn_normalize_seo_setting_value')) {
    function fn_normalize_seo_setting_value(mixed $value): mixed
    {
        if ($value === '1' || $value === 1 || $value === true) {
            return true;
        }

        if ($value === '0' || $value === 0 || $value === false) {
            return false;
        }

        if ($value === 'null' || $value === '') {
            return null;
        }

        return $value;
    }
}

if (! function_exists('fn_load_seo_setting_from_db')) {
    function fn_load_seo_setting_from_db(string $key, mixed $default = null): mixed
    {
        if (! SeoRequestCache::remember('table:seo_settings', fn () => Schema::hasTable('seo_settings'))) {
            return $default;
        }

        $setting = SeoSetting::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return fn_normalize_seo_setting_value($setting->value) ?? $default;
    }
}

if (! function_exists('fn_load_all_seo_settings_from_db')) {
    /**
     * @return array<string, mixed>
     */
    function fn_load_all_seo_settings_from_db(): array
    {
        if (! SeoRequestCache::remember('table:seo_settings', fn () => Schema::hasTable('seo_settings'))) {
            return [];
        }

        return SeoSetting::query()
            ->pluck('value', 'key')
            ->map(fn ($value) => fn_normalize_seo_setting_value($value))
            ->all();
    }
}

if (! function_exists('fn_get_cached_seo_settings')) {
    /**
     * @return array<string, mixed>
     */
    function fn_get_cached_seo_settings(): array
    {
        // Never publish uncommitted settings into the shared snapshot.
        if (DB::connection()->transactionLevel() > 0) {
            return fn_load_all_seo_settings_from_db();
        }

        return SeoRequestCache::remember('settings', fn () => AppCache::remember(
            'seo', fn_seo_settings_cache_key(), fn () => fn_load_all_seo_settings_from_db()
        ));
    }
}

if (! function_exists('fn_get_seo_setting')) {
    function fn_get_seo_setting(string $key, mixed $default = null): mixed
    {
        $cached = fn_get_cached_seo_settings();

        return array_key_exists($key, $cached)
            ? $cached[$key]
            : $default;
    }
}

if (! function_exists('fn_get_all_seo_settings')) {
    /**
     * @return array<string, mixed>
     */
    function fn_get_all_seo_settings(): array
    {
        return fn_get_cached_seo_settings();
    }
}

if (! function_exists('fn_set_seo_setting')) {
    function fn_set_seo_setting(string $key, mixed $value): void
    {
        SeoSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
        );

        fn_forget_seo_settings_cache();
        if (DB::connection()->transactionLevel() > 0) {
            DB::afterCommit(fn () => fn_forget_seo_settings_cache());
        }
    }
}

if (! function_exists('fn_set_seo_settings')) {
    /**
     * @param  array<string, mixed>  $settings
     */
    function fn_set_seo_settings(array $settings): void
    {
        if ($settings === []) {
            return;
        }

        foreach ($settings as $key => $value) {
            fn_set_seo_setting((string) $key, $value);
        }
    }
}

if (! function_exists('fn_delete_seo_setting')) {
    function fn_delete_seo_setting(string $key): bool
    {
        $deleted = SeoSetting::query()->where('key', $key)->delete() > 0;
        fn_forget_seo_settings_cache();

        if (DB::connection()->transactionLevel() > 0) {
            DB::afterCommit(fn () => fn_forget_seo_settings_cache());
        }

        return $deleted;
    }
}

if (! function_exists('fn_seo_route_url')) {
    /**
     * Build a route URL only when all required route parameters are available.
     */
    function fn_seo_route_url(?string $routeName, array $parameters = []): ?string
    {
        if (! $routeName || ! \Illuminate\Support\Facades\Route::has($routeName)) {
            return null;
        }

        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
        if (! $route) {
            return null;
        }

        $missing = array_diff($route->parameterNames(), array_keys($parameters));
        if ($missing !== []) {
            return null;
        }

        return route($routeName, $parameters);
    }
}

if (! function_exists('fn_portal_gtm_enabled')) {
    function fn_portal_gtm_enabled(): bool
    {
        if (! (bool) fn_get_seo_setting('analytics.gtm_enabled', false)) {
            return false;
        }

        if (! (bool) fn_get_seo_setting('analytics.gtm_enabled_on_portal', false)) {
            return false;
        }

        $containerId = fn_get_seo_setting('analytics.gtm_container_id');

        return is_string($containerId) && trim($containerId) !== '';
    }
}

if (! function_exists('fn_normalize_gsc_verification_code')) {
    /**
     * Accept a raw verification token or a full HTML meta tag from Search Console.
     */
    function fn_normalize_gsc_verification_code(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/content=["\']([^"\']+)["\']/i', $value, $matches)) {
            return trim($matches[1]);
        }

        return $value;
    }
}
