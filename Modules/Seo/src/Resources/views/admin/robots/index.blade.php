@extends('backend::layouts.app')

@section('title', 'Robots.txt')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Robots.txt', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Robots.txt'" />
@endsection

@section('content')
<div class="mb-4 flex justify-between items-start gap-4">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Robots.txt</h1>
        <p class="text-sm text-gray-500">Edit your robots.txt rules manually or use auto-generated defaults.</p>
    </div>
    <a href="{{ $publicUrl }}" target="_blank" class="text-blue-600 hover:underline text-sm whitespace-nowrap">View live robots.txt &rarr;</a>
</div>

@if (session('success'))
    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.seo.robots.update') }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="use_custom" value="0">
            <input type="checkbox" name="use_custom" value="1" {{ old('use_custom', $useCustom) ? 'checked' : '' }}>
            <span class="font-medium text-gray-800">Use custom robots.txt content</span>
        </label>
        <p class="text-sm text-gray-500">When enabled, the saved content below is served at <code>/robots.txt</code>. When disabled, generated rules are served instead.</p>

        <div>
            <label class="custom-label">Robots.txt content</label>
            <textarea name="custom_content" rows="16" class="input-field font-mono text-sm w-full"
                      placeholder="User-agent: *&#10;Disallow: /admin/">{{ old('custom_content', $customContent) }}</textarea>
            @error('custom_content')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-2">Auto-generated preview (from config)</h2>
        <pre class="text-xs font-mono text-gray-600 whitespace-pre-wrap bg-white border border-gray-200 rounded p-4">{{ $generatedPreview }}</pre>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Save robots.txt</button>
    </div>
</form>
@endsection
