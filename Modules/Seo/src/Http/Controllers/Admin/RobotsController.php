<?php

namespace Modules\Seo\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Seo\Services\RobotsBuilder;

class RobotsController extends Controller
{
    public function __construct(
        protected RobotsBuilder $builder,
    ) {}

    public function index(): View
    {
        $useCustom = (bool) fn_get_seo_setting('robots.use_custom', false);
        $customContent = (string) fn_get_seo_setting('robots.custom_content', '');
        $generated = $this->builder->buildFromConfig();

        return view('seo::admin.robots.index', [
            'useCustom' => $useCustom,
            'customContent' => $customContent !== '' ? $customContent : $generated,
            'generatedPreview' => $generated,
            'publicUrl' => url(config('seo.robots.path', '/robots.txt')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'use_custom' => ['nullable', 'boolean'],
            'custom_content' => ['nullable', 'string', 'max:50000'],
        ]);

        $useCustom = $request->boolean('use_custom');
        fn_set_seo_setting('robots.use_custom', $useCustom ? '1' : '0');
        if (array_key_exists('custom_content', $validated)) {
            fn_set_seo_setting('robots.custom_content', $validated['custom_content'] ?? '');
        }

        return redirect()
            ->route('admin.seo.robots.index')
            ->with('success', 'Robots.txt saved successfully.');
    }
}
