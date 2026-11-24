@extends('backend::layouts.app')

@section('title', 'SEO Pages')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'SEO Pages', 'url' => null],
  ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'SEO Pages'" />
@endsection

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-slate-500">Manage meta title, description, and robots for each registered frontend page.</p>
        </div>
        <div class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-white px-3.5 py-1.5 shadow-sm">
            <span class="text-sm font-semibold text-slate-900">{{ count($pages) }}</span>
            <span class="text-sm text-slate-500">pages</span>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Page</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Route</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Meta title</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pages as $page)
                        <tr class="hover:bg-[var(--color-primary)]/[0.03] transition-colors">
                            <td class="px-4 py-3.5">
                                <p class="text-sm font-semibold text-slate-900">{{ $page['label'] }}</p>
                                <p class="mt-0.5 text-xs font-mono text-slate-400">{{ $page['key'] }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-sm">
                                @if ($page['route_url'])
                                    <a href="{{ $page['route_url'] }}" target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1 text-xs font-mono text-[var(--color-primary)] hover:text-[var(--color-primary-dark)]">
                                        {{ $page['route_name'] }}
                                        <span class="material-icons-outlined text-[14px]">open_in_new</span>
                                    </a>
                                @else
                                    <span class="text-xs font-mono text-slate-400">{{ $page['route_name'] ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-sm text-slate-600 max-w-xs truncate">{{ $page['title'] ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-sm">
                                @if ($page['has_override'])
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-primary-50)] px-2.5 py-0.5 text-[11px] font-semibold text-[var(--color-primary-dark)] ring-1 ring-[var(--color-primary-200)]">Customized</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">Config default</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.seo.pages.edit', $page['key']) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-black text-white shadow-sm transition hover:bg-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black"
                                    title="Edit meta"
                                    aria-label="Edit meta for {{ $page['label'] }}">
                                    <span class="material-icons-outlined text-[18px]">edit</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--color-primary)]/10">
                                    <span class="material-icons-outlined text-[var(--color-primary)]">travel_explore</span>
                                </div>
                                <p class="mt-3 text-sm font-medium text-slate-700">No pages registered</p>
                                <p class="mt-1 text-xs text-slate-500">Add pages in <code class="text-[11px]">config/seo.php</code>.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
