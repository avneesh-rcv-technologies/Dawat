<?php

namespace Modules\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Seo\Support\SeoSettings;

class SeoGlobalBodyConfig extends Component
{
    public bool $gtmEnabled;
    public ?string $gtmContainerId;
    public string $customBodyScripts;

    public function __construct()
    {
        $this->gtmEnabled = (bool) SeoSettings::get(
            'analytics.gtm_enabled',
            config('seo.analytics.google_tag_manager.enabled', false)
        );
        $this->gtmContainerId = SeoSettings::get(
            'analytics.gtm_container_id',
            config('seo.analytics.google_tag_manager.container_id')
        );
        $scripts = SeoSettings::get('analytics.custom_body_scripts', '');
        $this->customBodyScripts = is_array($scripts)
            ? implode("\n", array_map('strval', $scripts))
            : (string) $scripts;
    }

    public function render(): View
    {
        return view('seo::components.seo-global-body-config');
    }
}
