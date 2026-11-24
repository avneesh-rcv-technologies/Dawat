<?php

namespace Modules\Seo\Repositories;

use Modules\Seo\Models\SeoSetting;

class SeoSettingRepository
{
    public function all(): array
    {
        return SeoSetting::query()
            ->orderBy('key')
            ->pluck('value', 'key')
            ->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return fn_get_seo_setting($key, $default);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function setMany(array $settings): void
    {
        fn_set_seo_settings($settings);
    }
}
