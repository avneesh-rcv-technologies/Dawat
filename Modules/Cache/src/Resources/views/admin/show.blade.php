@extends('admin::layouts.app')

@section('title', __('Cache profile') . ': ' . $profile)

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => __('Dashboard'), 'url' => route('dashboard.index')],
            ['label' => __('Application Cache'), 'url' => route('cache.index')],
            ['label' => $profile, 'url' => null],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$profile" />
@endsection

@section('content')
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $config['label'] ?? $profile }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                Profile code: <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">{{ $profile }}</code>
                · Keys: <strong>{{ $entriesCount }}</strong>
                · Size: <strong>{{ $bytesLabel }}</strong>
                · TTL:
                <strong>
                    @if(!empty($config['forever']))
                        Forever
                    @else
                        {{ (int) ($config['ttl'] ?? 0) }}s
                    @endif
                </strong>
            </p>
            <p class="mt-2 text-xs text-slate-500">
                Data keys use prefix
                <code>app:{{ $profile }}:{name}</code>
                · Registry
                <code>app:registry:{{ $profile }}</code>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cache.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <span class="material-icons-outlined text-base">arrow_back</span>
                Back to profiles
            </a>
            @if($clearable && bouncer()->hasPermission('cache.flush'))
                <form method="POST" action="{{ route('cache.flush', $profile) }}"
                    data-confirm="All keys in the [{{ $profile }}] profile will be flushed." data-confirm-title="Flush cache profile?" data-confirm-button="Flush keys" data-confirm-tone="danger">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                        <span class="material-icons-outlined text-base">delete_sweep</span>
                        Flush profile
                    </button>
                </form>
            @endif
        </div>
    </div>

    <x-data-view :data="$lists" title="" is_export="false" />
@endsection
