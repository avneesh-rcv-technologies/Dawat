@extends('backend::layouts.app')

@section('title', 'SEO Configuration')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Configuration', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'SEO Configuration'" />
@endsection

@section('content')
@if ($errors->any())
    <ul class="text-sm text-red-600 mb-4" role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
<div class="space-y-5">
   

<form method="POST" action="{{ route('admin.seo.settings.update') }}" class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
    @csrf
    @method('PUT')

    <div class="px-5 py-6 sm:px-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <label class="custom-label">Module enabled</label>
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="settings[enabled]" value="0">
                    <input type="checkbox" name="settings[enabled]" value="1"
                           {{ old('settings.enabled', fn_get_seo_setting('enabled', $settings['enabled'] ?? true)) ? 'checked' : '' }}
                           class="hidden peer">
                    <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-primary)]"></div>
                </label>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Global meta defaults</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1"><label class="custom-label">Title suffix</label></div>
                <div class="md:col-span-2">
                    <input type="text" name="settings[defaults][title_suffix]" class="input-field"
                           value="{{ old('settings.defaults.title_suffix', fn_get_seo_setting('defaults.title_suffix', $settings['defaults.title_suffix'] ?? '')) }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <div class="md:col-span-1"><label class="custom-label">Default description</label></div>
                <div class="md:col-span-2">
                    <textarea name="settings[defaults][description]" rows="3" class="input-field">{{ old('settings.defaults.description', fn_get_seo_setting('defaults.description', $settings['defaults.description'] ?? '')) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <div class="md:col-span-1"><label class="custom-label">Default robots</label></div>
                <div class="md:col-span-2">
                    <select name="settings[defaults][robots]" class="input-field">
                        @foreach (['index,follow', 'noindex,nofollow', 'index,nofollow', 'noindex,follow'] as $robots)
                            <option value="{{ $robots }}" {{ old('settings.defaults.robots', fn_get_seo_setting('defaults.robots', $settings['defaults.robots'] ?? '')) === $robots ? 'selected' : '' }}>
                                {{ $robots }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <div class="md:col-span-1"><label class="custom-label">Default social sharing image</label></div>
                <div class="md:col-span-2">
                    <input type="text" name="settings[defaults][og_image]" class="input-field"
                           value="{{ old('settings.defaults.og_image', fn_get_seo_setting('defaults.og_image', $settings['defaults.og_image'] ?? '')) }}">
                    <p class="text-sm text-slate-500 mt-1">Used for Open Graph and Twitter when no page or article image is available. Accepts an HTTP(S) URL or relative image path. Optional deployment default: SEO_DEFAULT_SOCIAL_IMAGE.</p>
                </div>
            </div>
        </div>

        
    </div>

    <div class="px-5 py-4 sm:px-6 bg-slate-50/80 border-t border-slate-100 flex justify-end">
        <button type="submit" class="btn-primary inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium">
            <span class="material-icons-outlined text-[16px]">save</span>
            Save settings
        </button>
    </div>
</form>
</div>
@endsection
