@extends('backend::layouts.app')

@section('title', 'SEO Analytics')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Analytics', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'SEO Analytics'" />
@endsection

@section('content')
@if ($errors->any())
    <ul class="text-sm text-red-600 mb-4" role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
<div class="mb-4">
    <h1 class="text-xl font-semibold text-gray-900">Analytics & Tracking</h1>
    <p class="text-sm text-gray-500">Configure GA4, Google Tag Manager, Facebook Pixel, Google Search Console, and custom tracking scripts.</p>
</div>

@if (session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.seo.analytics.update') }}" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    @csrf
    @method('PUT')

    <div class="px-6 py-6 space-y-6">
        @foreach ([
            ['analytics.ga_enabled', 'GA4 enabled'],
            ['analytics.gtm_enabled', 'GTM enabled'],
         
        ] as [$key, $label])
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 {{ $loop->first ? '' : 'pt-4' }}">
            <div class="md:col-span-1"><label class="custom-label">{{ $label }}</label></div>
            <div class="md:col-span-2">
                @php [$group, $field] = explode('.', $key, 2); @endphp
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="settings[{{ $group }}][{{ $field }}]" value="0">
                    <input type="checkbox" name="settings[{{ $group }}][{{ $field }}]" value="1"
                           {{ old("settings.{$group}.{$field}", fn_get_seo_setting($key, $settings[$key] ?? false)) ? 'checked' : '' }}
                           class="hidden peer">
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-primary)]"></div>
                </label>
            </div>
        </div>
        @endforeach

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1"><label class="custom-label">GA4 measurement ID</label></div>
            <div class="md:col-span-2">
                <input type="text" name="settings[analytics][ga_measurement_id]" class="input-field" placeholder="G-XXXXXXXXXX"
                       value="{{ old('settings.analytics.ga_measurement_id', fn_get_seo_setting('analytics.ga_measurement_id', $settings['analytics.ga_measurement_id'] ?? '')) }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1"><label class="custom-label">GTM container ID</label></div>
            <div class="md:col-span-2">
                <input type="text" name="settings[analytics][gtm_container_id]" class="input-field" placeholder="GTM-XXXXXXX"
                       value="{{ old('settings.analytics.gtm_container_id', fn_get_seo_setting('analytics.gtm_container_id', $settings['analytics.gtm_container_id'] ?? '')) }}">
            </div>
        </div>

       

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div class="md:col-span-1"><label class="custom-label">Facebook Pixel ID</label></div>
            <div class="md:col-span-2">
                <input type="text" name="settings[analytics][facebook_pixel_id]" class="input-field"
                       value="{{ old('settings.analytics.facebook_pixel_id', fn_get_seo_setting('analytics.facebook_pixel_id', $settings['analytics.facebook_pixel_id'] ?? '')) }}">
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-md font-semibold text-gray-800 mb-4">Google Search Console</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <label class="custom-label">GSC verification enabled</label>
                    <p class="text-xs text-gray-500 mt-1">Outputs the HTML meta tag on public pages for domain verification.</p>
                </div>
                <div class="md:col-span-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="settings[analytics][gsc_enabled]" value="0">
                        <input type="checkbox" name="settings[analytics][gsc_enabled]" value="1"
                               {{ old('settings.analytics.gsc_enabled', fn_get_seo_setting('analytics.gsc_enabled', $settings['analytics.gsc_enabled'] ?? false)) ? 'checked' : '' }}
                               class="hidden peer">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-primary)]"></div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <div class="md:col-span-1">
                    <label class="custom-label">Verification code</label>
                    <p class="text-xs text-gray-500 mt-1">Paste the <code class="text-xs">content</code> value from the HTML tag method, or the full meta tag.</p>
                </div>
                <div class="md:col-span-2 space-y-2">
                    <input type="text" name="settings[analytics][gsc_verification_code]" class="input-field font-mono" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                           value="{{ old('settings.analytics.gsc_verification_code', fn_get_seo_setting('analytics.gsc_verification_code', $settings['analytics.gsc_verification_code'] ?? '')) }}">
                    <p class="text-xs text-gray-500">
                        After verification, submit your sitemap in
                        <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Google Search Console</a>:
                        <code class="text-xs break-all">{{ url(config('seo.sitemap.path', '/sitemap.xml')) }}</code>
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-md font-semibold text-gray-800 mb-4">Custom scripts</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1"><label class="custom-label">Custom head scripts</label></div>
                <div class="md:col-span-2">
                    <textarea name="settings[analytics][custom_head_scripts]" rows="5" class="input-field font-mono text-sm">{{ old('settings.analytics.custom_head_scripts', fn_get_seo_setting('analytics.custom_head_scripts', $settings['analytics.custom_head_scripts'] ?? '')) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <div class="md:col-span-1"><label class="custom-label">Custom body scripts</label></div>
                <div class="md:col-span-2">
                    <textarea name="settings[analytics][custom_body_scripts]" rows="5" class="input-field font-mono text-sm">{{ old('settings.analytics.custom_body_scripts', fn_get_seo_setting('analytics.custom_body_scripts', $settings['analytics.custom_body_scripts'] ?? '')) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
        <button type="submit" class="btn btn-primary">Save analytics</button>
    </div>
</form>
@endsection
