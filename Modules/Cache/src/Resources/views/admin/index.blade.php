@extends('admin::layouts.app')

@section('title', __('Application Cache'))

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => __('Dashboard'), 'url' => route('dashboard.index')],
            ['label' => __('Application Cache'), 'url' => null],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="__('Application Cache')" />
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Store</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $redis['store'] ?? config('cache.default') }}</p>
            <p class="mt-1 text-sm text-slate-500">config cache::app_cache.store</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tracked size</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ \Modules\Cache\Services\AppCache::formatBytes((int) ($stats['total_bytes'] ?? 0)) }}</p>
            <p class="mt-1 text-sm text-slate-500">Sum of registered entry meta</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Redis used</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $redis['used_memory'] ?? '—' }}</p>
            <p class="mt-1 text-sm text-slate-500">Max: {{ $redis['maxmemory'] ?? '—' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Profiles</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ count($stats['profiles'] ?? []) }}</p>
            @if(bouncer()->hasPermission('cache.flush-all'))
                <form method="POST" action="{{ route('cache.flush-all') }}" class="mt-3"
                    data-confirm="All clearable cache profiles will be flushed." data-confirm-title="Flush all caches?" data-confirm-button="Flush all" data-confirm-tone="danger">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-rose-700">
                        <span class="material-icons-outlined text-base">delete_forever</span>
                        Flush all clearable
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(!empty($redis['error']))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ $redis['error'] }}
        </div>
    @endif

    <x-data-view :data="$lists" title="" is_export="false" />
@endsection
