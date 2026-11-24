<?php

namespace Modules\Seo\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Modules\Seo\Repositories\SeoSettingRepository;
use Modules\Seo\Rules\SeoUrl;
use Modules\Seo\Services\SitemapBuilder;

class SeoSettingService
{
    public function __construct(
        protected SeoSettingRepository $repository,
        protected SitemapBuilder $sitemapBuilder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function allWithDefaults(): array
    {
        $defaults = $this->defaultSettings();

        return array_merge($defaults, fn_get_all_seo_settings());
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function save(array $settings): void
    {
        $rules = [];
        foreach (['enabled', 'sitemap.enabled', 'robots.enabled', 'redirects.enabled',
            'analytics.ga_enabled', 'analytics.gtm_enabled', 'analytics.gtm_enabled_on_portal',
            'analytics.gsc_enabled'] as $key) {
            $rules['settings.'.$key] = ['nullable', 'boolean'];
        }
        $rules['settings.defaults.og_image'] = ['nullable', 'string', 'max:2048', new SeoUrl(true)];
        Validator::make(
            ['settings' => Arr::undot($settings)], $rules
        )->validate();

        $this->repository->setMany($settings);
        $this->sitemapBuilder->clearCache();
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultSettings(): array
    {
        return [
            'enabled' => config('seo.settings.enabled', true),
            'analytics.ga_enabled' => config('seo.analytics.google_analytics.enabled', false),
            'analytics.gtm_enabled' => config('seo.analytics.google_tag_manager.enabled', false),
            'analytics.gtm_enabled_on_portal' => config('seo.analytics.google_tag_manager.enabled_on_portal', false),
            'analytics.ga_measurement_id' => config('seo.analytics.google_analytics.measurement_id'),
            'analytics.gtm_container_id' => config('seo.analytics.google_tag_manager.container_id'),
            'analytics.facebook_pixel_id' => config('seo.analytics.facebook_pixel.pixel_id'),
            'analytics.gsc_enabled' => config('seo.analytics.google_search_console.enabled', false),
            'analytics.gsc_verification_code' => config('seo.analytics.google_search_console.verification_code'),
            'analytics.custom_head_scripts' => '',
            'analytics.custom_body_scripts' => '',
            'defaults.title_suffix' => config('seo.defaults.title_suffix'),
            'defaults.description' => config('seo.defaults.description'),
            'defaults.robots' => config('seo.defaults.robots'),
            'defaults.og_image' => data_get(config('seo.defaults.og'), 'image'),
            'sitemap.enabled' => config('seo.sitemap.enabled', true),
            'robots.enabled' => config('seo.robots.enabled', true),
            'redirects.enabled' => config('seo.redirects.enabled', true),
        ];
    }
}
