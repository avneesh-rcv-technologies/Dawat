@extends('backend::layouts.app')

@section('title', 'Sitemap')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Sitemap', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Sitemap'" />
@endsection

@section('content')
<div class="mb-4 flex justify-between items-start gap-4">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Sitemap</h1>
        <p class="text-sm text-gray-500">Generate and publish your XML sitemap for search engines.</p>
    </div>
    <a href="{{ $publicUrl }}" target="_blank" class="text-blue-600 hover:underline text-sm whitespace-nowrap">View live sitemap &rarr;</a>
</div>

@if (session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="text-sm text-gray-500">URLs in sitemap</div>
        <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['url_count']) }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="text-sm text-gray-500">Status</div>
        <div class="text-lg font-semibold mt-1 {{ $stats['enabled'] ? 'text-green-600' : 'text-gray-400' }}">
            {{ $stats['enabled'] ? 'Enabled' : 'Disabled' }}
        </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="text-sm text-gray-500">Last regenerated</div>
        <div class="text-sm font-medium text-gray-900 mt-1">
            {{ $stats['last_generated_at'] ? \Carbon\Carbon::parse($stats['last_generated_at'])->diffForHumans() : 'Never' }}
        </div>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
    <div>
        <h2 class="text-md font-semibold text-gray-800">Regenerate sitemap</h2>
        <p class="text-sm text-gray-500 mt-1">
            Clears the cache, rebuilds the sitemap from registered pages and content sources,
            and serves the result from the live sitemap URL. Existing public files are left untouched.
        </p>
    </div>

    <dl class="text-sm space-y-2 text-gray-600">
        <div class="flex gap-2">
            <dt class="font-medium w-32">Public URL</dt>
            <dd class="font-mono text-xs break-all">{{ $publicUrl }}</dd>
        </div>
        @if ($stats['cached'])
            <div class="text-amber-600 text-xs">A cached version is currently being served.</div>
        @endif
    </dl>

    <form method="POST" action="{{ route('admin.seo.sitemap.regenerate') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Regenerate sitemap</button>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-lg p-6 mt-6 space-y-3">
    <h2 class="text-md font-semibold text-gray-800">Google Search Console</h2>
    <p class="text-sm text-gray-500">
        After verifying your domain under
        <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Search Console</a>,
        add this sitemap URL under <strong>Sitemaps</strong>:
    </p>
    <div class="rounded-md bg-gray-50 border border-gray-200 px-3 py-2 font-mono text-xs break-all text-gray-800">
        {{ $publicUrl }}
    </div>
    <p class="text-xs text-gray-500">
        Configure the verification meta tag under
        <a href="{{ route('admin.seo.analytics.index') }}" class="text-blue-600 hover:underline">SEO &rarr; Analytics</a>.
    </p>
</div>
@endsection
