<?php

namespace Modules\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Seo\Support\SeoSettings;

class SeoGlobalHeadConfig extends Component
{
    public bool $gaEnabled;
    public ?string $gaMeasurementId;
    public bool $gaAnonymizeIp;
    public bool $gtmEnabled;
    public ?string $gtmContainerId;
    public ?string $facebookPixelId;
    public ?string $gscVerificationCode;
    public string $customHeadScripts;

    public function __construct()
    {
        $this->gaEnabled = (bool) SeoSettings::get(
            'analytics.ga_enabled',
            config('seo.analytics.google_analytics.enabled', false)
        );
        $this->gaMeasurementId = SeoSettings::get(
            'analytics.ga_measurement_id',
            config('seo.analytics.google_analytics.measurement_id')
        );
        $this->gaAnonymizeIp = (bool) SeoSettings::get(
            'analytics.ga_anonymize_ip',
            config('seo.analytics.google_analytics.anonymize_ip', true)
        );
        $this->gtmEnabled = (bool) SeoSettings::get(
            'analytics.gtm_enabled',
            config('seo.analytics.google_tag_manager.enabled', false)
        );
        $this->gtmContainerId = SeoSettings::get(
            'analytics.gtm_container_id',
            config('seo.analytics.google_tag_manager.container_id')
        );
        $this->facebookPixelId = SeoSettings::get(
            'analytics.facebook_pixel_id',
            config('seo.analytics.facebook_pixel.pixel_id')
        );
        $gscEnabled = (bool) SeoSettings::get(
            'analytics.gsc_enabled',
            config('seo.analytics.google_search_console.enabled', false)
        );
        $this->gscVerificationCode = SeoSettings::enabled() && $gscEnabled
            ? fn_normalize_gsc_verification_code(SeoSettings::get(
                'analytics.gsc_verification_code',
                config('seo.analytics.google_search_console.verification_code')
            ))
            : null;
        $scripts = SeoSettings::get('analytics.custom_head_scripts', '');
        $this->customHeadScripts = is_array($scripts)
            ? implode("\n", array_map('strval', $scripts))
            : (string) $scripts;
    }

    public function render(): View
    {
        return view('seo::components.seo-global-head-config');
    }
}
