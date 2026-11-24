<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Rules\SeoUrl;
use Modules\Seo\Services\SeoSettingService;

class SettingsController extends Controller
{
    public function __construct(
        protected SeoSettingService $settingService,
    ) {}

    public function index(): View
    {
        $settings = $this->settingService->allWithDefaults();

        return view('seo::admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.enabled' => ['nullable', 'boolean'],
            'settings.defaults.title_suffix' => ['nullable', 'string', 'max:255'],
            'settings.defaults.description' => ['nullable', 'string'],
            'settings.defaults.robots' => ['nullable', 'string', 'max:255'],
            'settings.defaults.og_image' => ['nullable', 'string', 'max:2048', new SeoUrl(true)],
            'settings.sitemap.enabled' => ['nullable', 'boolean'],
            'settings.robots.enabled' => ['nullable', 'boolean'],
            'settings.redirects.enabled' => ['nullable', 'boolean'],
        ]);

        $flat = $this->flattenSettings($validated['settings'] ?? []);
        $this->settingService->save($flat);

        return redirect()
            ->route('admin.seo.settings.index')
            ->with('success', __('SEO settings saved successfully.'));
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
                    if (is_array($childValue)) {
                        foreach ($childValue as $grandKey => $grandValue) {
                            $flat["{$key}.{$childKey}.{$grandKey}"] = $grandValue;
                        }
                    } else {
                        $flat["{$key}.{$childKey}"] = $childValue;
                    }
                }
            } else {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }
}
