@extends('backend::layouts.app')

@section('title', $redirect->exists ? 'Edit Redirect' : 'Create Redirect')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Redirects', 'url' => route('admin.seo.redirects.index')],
    ['label' => $redirect->exists ? 'Edit' : 'Create', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$redirect->exists ? 'Edit Redirect' : 'Create Redirect'" />
@endsection

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ $redirect->exists ? route('admin.seo.redirects.update', $redirect) : route('admin.seo.redirects.store') }}" class="space-y-6 bg-white border border-gray-200 rounded-lg p-6">
        @csrf
        @if ($redirect->exists)
            @method('PUT')
        @endif

        <div>
            <label class="custom-label">From path</label>
            <input type="text" name="from_path" value="{{ old('from_path', $redirect->from_path) }}" class="input-field font-mono" placeholder="/old-page" required>
            @error('from_path')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="custom-label">To URL</label>
            <input type="text" name="to_url" value="{{ old('to_url', $redirect->to_url) }}" class="input-field font-mono" placeholder="/new-page" required>
            @error('to_url')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 ">
            <div class="hidden">
                <label class="custom-label">Status code</label>
                <select name="status_code" class="input-field">
                    @foreach ([301, 302, 307, 308] as $code)
                        <option value="{{ $code }}" {{ (int) old('status_code', $redirect->status_code ?: 301) === $code ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="custom-label">Match type</label>
                <select name="match_type" class="input-field">
                    @foreach (['exact', 'prefix', 'wildcard', 'regex', 'pattern'] as $type)
                        <option value="{{ $type }}" {{ old('match_type', $redirect->match_type ?: 'exact') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="custom-label">Notes</label>
            <input type="text" name="notes" value="{{ old('notes', $redirect->notes) }}" class="input-field">
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $redirect->exists ? $redirect->is_active : true) ? 'checked' : '' }}>
            <span>Active</span>
        </label>

        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.seo.redirects.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
