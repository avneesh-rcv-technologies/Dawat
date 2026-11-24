@extends('backend::layouts.app')

@section('title', 'Edit SEO — ' . $page['label'])

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Pages', 'url' => route('admin.seo.pages.index')],
    ['label' => $page['label'], 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Edit Page SEO'" />
@endsection

@section('content')
@if ($errors->any())
    <ul class="text-sm text-red-600 mb-4" role="alert">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
<div class="space-y-5 max-w-3xl">
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm px-4 py-3.5 sm:px-5">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('admin.seo.pages.index') }}"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors shrink-0"
                aria-label="Back to SEO pages">
                <span class="material-icons-outlined text-[20px]">arrow_back</span>
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="text-lg font-semibold text-slate-900 truncate">{{ $page['label'] }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    Page key: <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $page['key'] }}</code>
                    @if ($page['route_url'])
                        <span class="mx-1">·</span>
                        <a href="{{ $page['route_url'] }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-0.5 text-[var(--color-primary)] hover:text-[var(--color-primary-dark)]">
                            View page
                            <span class="material-icons-outlined text-[14px]">open_in_new</span>
                        </a>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.seo.pages.update', $page['key']) }}"
        class="space-y-5 rounded-xl border border-slate-200 bg-white shadow-sm p-5 sm:p-6">
        @csrf
        @method('PUT')

        <div>
            <label class="custom-label">Meta title</label>
            <input type="text" name="title" value="{{ old('title', $page['title']) }}" class="input-field" placeholder="Page title for search engines">
            @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="custom-label">Meta description</label>
            <textarea name="description" rows="3" class="input-field" placeholder="Short description for search results">{{ old('description', $page['description']) }}</textarea>
            @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="custom-label">Meta keywords</label>
            <input type="text" name="keywords" value="{{ old('keywords', $page['keywords']) }}" class="input-field" placeholder="keyword1, keyword2">
            @error('keywords')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="custom-label">Robots</label>
            <select name="robots" class="input-field">
                @foreach (['index,follow', 'noindex,nofollow', 'index,nofollow', 'noindex,follow'] as $robots)
                    <option value="{{ $robots }}" {{ old('robots', $page['robots']) === $robots ? 'selected' : '' }}>{{ $robots }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="custom-label">OG image URL</label>
            <input type="text" name="og_image" value="{{ old('og_image', $page['og_image']) }}" class="input-field" placeholder="https://example.com/images/og.jpg">
        </div>

        <div>
            <label class="custom-label">Twitter image URL</label>
            <input type="text" name="twitter_image" value="{{ old('twitter_image', $page['twitter_image']) }}" class="input-field" placeholder="https://example.com/images/twitter.jpg">
        </div>

        <div>
            <label class="custom-label">Schema JSON</label>
            <textarea name="schema" rows="8" class="input-field font-mono text-sm" placeholder='{"@type":"WebPage","name":"Example"}'>{{ old('schema', $page['schema']) }}</textarea>
            <p class="helping-text">Enter valid JSON-LD object or array. Leave empty to use config defaults.</p>
            @error('schema')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="custom-label">Canonical URL override</label>
            <input type="text" name="canonical" value="{{ old('canonical', $page['canonical']) }}" class="input-field" placeholder="Leave empty to use route URL">
        </div>

        <label class="inline-flex items-center gap-2.5 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page['is_active']) ? 'checked' : '' }}
                class="h-4 w-4 rounded border-slate-300 text-[var(--color-primary)] focus:ring-[var(--color-primary)]">
            <span class="text-sm font-medium text-slate-700">SEO active for this page</span>
        </label>

        <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
            <button type="submit" class="btn-primary inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium">
                <span class="material-icons-outlined text-[16px]">save</span>
                Save
            </button>
            <a href="{{ route('admin.seo.pages.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
