<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Services\SeoSettingService;

class AnalyticsController extends Controller
{
    public function __construct(
        protected SeoSettingService $settingService,
    ) {}

    public function index(): View
    {
        $settings = $this->settingService->allWithDefaults();

        return view('seo::admin.analytics.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.analytics.ga_enabled' => ['nullable', 'boolean'],
            'settings.analytics.gtm_enabled' => ['nullable', 'boolean'],
            'settings.analytics.gtm_enabled_on_portal' => ['nullable', 'boolean'],
            'settings.analytics.ga_measurement_id' => ['nullable', 'string', 'max:255'],
            'settings.analytics.gtm_container_id' => ['nullable', 'string', 'max:255'],
            'settings.analytics.facebook_pixel_id' => ['nullable', 'string', 'max:255'],
            'settings.analytics.gsc_enabled' => ['nullable', 'boolean'],
            'settings.analytics.gsc_verification_code' => ['nullable', 'string', 'max:255'],
            'settings.analytics.custom_head_scripts' => ['nullable', 'string'],
            'settings.analytics.custom_body_scripts' => ['nullable', 'string'],
        ]);

        $flat = $this->flattenSettings($validated['settings'] ?? []);

        if (isset($flat['analytics.gsc_verification_code'])) {
            $flat['analytics.gsc_verification_code'] = fn_normalize_gsc_verification_code(
                $flat['analytics.gsc_verification_code']
            );
        }

        $this->settingService->save($flat);

        return redirect()
            ->route('admin.seo.analytics.index')
            ->with('success', __('Analytics settings saved successfully.'));
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function flattenSettings(array $settings): array
    {
        $flat = [];

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $flat["{$key}.{$childKey}"] = $childValue;
                }
            } else {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }
}
