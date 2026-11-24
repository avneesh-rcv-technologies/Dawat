@extends('backend::layouts.app')

@section('title', 'System  - Activity Logs')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'System Logs', 'url' => route('activity-log.advance-logging') ],
    ['label' => 'Log Detail - ' . $log->ip_address, 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Log Detail - ' . $log->ip_address" />
@endsection

@section('content')
@php
    $isActiveBlock = !empty($block) && $block->is_active && ($block->expires_at === null || optional($block->expires_at)->isFuture());

    if (!empty($block) && ($block->expires_at === null || $block->expires_at->isFuture())) {
        $actionText  = 'Unblock IP';
        $modalTitle  = 'Unblock IP Address';
        $btnCls      = 'bg-emerald-600 text-white hover:bg-emerald-700';
    } else {
        $actionText  = 'Block IP';
        $modalTitle  = 'Block IP Address';
        $btnCls      = 'bg-rose-600 text-white hover:bg-rose-700';
    }
@endphp

<div class="space-y-3">

    {{-- ── Section 1: Summary ──────────────────────────────────────── --}}
    <div x-data="{ open: true }" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <button type="button" @click="open = !open"
            class="flex w-full items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                    <span class="material-icons-outlined text-base text-indigo-600">info</span>
                </span>
                <span class="text-sm font-semibold text-gray-800">Request Group Summary</span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isActiveBlock ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $isActiveBlock ? 'Blocked' : 'Not Blocked' }}
                </span>
            </div>
            <span class="material-icons-outlined text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>

        <div x-show="open" x-collapse>
            <div class="border-t border-gray-100 px-5 pb-5 pt-4">
                <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">IP Address</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $log->ip_address ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">User</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $log->causer_type ? class_basename($log->causer_type) . ' #' . ($log->causer_id ?? '-') : 'Guest / System' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Guard</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ $log->guard ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Latest Seen</p>
                        <p class="mt-1 font-semibold text-gray-800">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</p>
                    </div>
                </div>

                @if(!empty($block))
                <div class="mt-4 rounded-lg border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Block Details</p>
                    <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                        <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Reason</p>
                            <p class="mt-1 font-medium text-gray-800">{{ $block->reason ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Expires At</p>
                            <p class="mt-1 font-medium text-gray-800">{{ $block->expires_at ? optional($block->expires_at)->format('Y-m-d H:i:s') : 'Indefinite' }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-white px-3 py-2">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Blocked By</p>
                            <p class="mt-1 font-medium text-gray-800">{{ $block->blocked_by_type ? class_basename($block->blocked_by_type) . ' #' . ($block->blocked_by_id ?? '-') : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4 flex justify-end">
                    <x-modal
                        buttonText="{{ $actionText }}"
                        type='link'
                        modalTitle="{{ $modalTitle }}"
                        id="block_ip"
                        ajaxUrl="{{ route('activity-log.advance-logging.block-ip', $log->id) }}"
                        buttonClass="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium {{ $btnCls }} transition-colors"
                        modalSize="3xl" />
                </div>
            </div>
        </div>
    </div>

    {{-- ── Section 2: Filters ──────────────────────────────────────── --}}
    @php
        $hasActiveFilters = request()->hasAny(['search','method','status','is_blocked','route_name','date_from','date_to']);
    @endphp
    <div x-data="{ open: {{ $hasActiveFilters ? 'true' : 'false' }} }" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <button type="button" @click="open = !open"
            class="flex w-full items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                    <span class="material-icons-outlined text-base text-blue-600">filter_list</span>
                </span>
                <span class="text-sm font-semibold text-gray-800">Filters</span>
                @if($hasActiveFilters)
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700">Active</span>
                @endif
            </div>
            <span class="material-icons-outlined text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>

        <div x-show="open" x-collapse>
            <div class="border-t border-gray-100 px-5 pb-5 pt-4">
                <form method="GET" action="{{ route('activity-log.advance-logging.show', $log->id) }}"
                    class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search path, url, agent…" class="input-field" />

                    <select name="method" class="input-field">
                        <option value="">All Methods</option>
                        @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $m)
                            <option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="input-field">
                        <option value="">All Status Codes</option>
                        @foreach ($statusOptions as $s)
                            <option value="{{ $s }}" @selected((string) request('status') === (string) $s)>{{ $s }}</option>
                        @endforeach
                    </select>

                    <select name="is_blocked" class="input-field">
                        <option value="">Blocked: All</option>
                        <option value="1" @selected(request('is_blocked') === '1')>Blocked</option>
                        <option value="0" @selected(request('is_blocked') === '0')>Not Blocked</option>
                    </select>

                    <input type="text"  name="route_name" value="{{ request('route_name') }}" placeholder="Route name" class="input-field" />
                    <input type="date"  name="date_from"  value="{{ request('date_from') }}"  class="input-field" />
                    <input type="date"  name="date_to"    value="{{ request('date_to') }}"    class="input-field" />

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                            <span class="material-icons-outlined text-sm">search</span> Apply
                        </button>
                        <a href="{{ route('activity-log.advance-logging.show', $log->id) }}"
                            class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                            <span class="material-icons-outlined text-sm">refresh</span> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Section 3: Requests accordion list ─────────────────────── --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50">
                    <span class="material-icons-outlined text-base text-violet-600">list_alt</span>
                </span>
                <span class="text-sm font-semibold text-gray-800">All Requests</span>
            </div>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ $requests->total() }} records</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($requests as $index => $requestLog)
                @php
                    $method  = $requestLog->method ?? 'N/A';
                    $status  = $requestLog->response_status ?? 0;
                    $isError = $status >= 400;
                    $methodColors = [
                        'GET'    => 'bg-blue-100 text-blue-700',
                        'POST'   => 'bg-emerald-100 text-emerald-700',
                        'PUT'    => 'bg-amber-100 text-amber-700',
                        'PATCH'  => 'bg-orange-100 text-orange-700',
                        'DELETE' => 'bg-rose-100 text-rose-700',
                    ];
                    $methodCls = $methodColors[$method] ?? 'bg-gray-100 text-gray-700';

                    $responseBody    = $requestLog->response_body;
                    $decodedResponse = null;
                    if (is_string($responseBody) && $responseBody !== '') {
                        $decoded = json_decode($responseBody, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $decodedResponse = $decoded;
                        }
                    }
                @endphp

                <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
                    {{-- Accordion header --}}
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center gap-3 px-5 py-3.5 text-left hover:bg-gray-50 transition-colors focus:outline-none">

                        {{-- Chevron --}}
                        <span class="material-icons-outlined flex-shrink-0 text-gray-400 transition-transform duration-200 text-base"
                            :class="open ? 'rotate-90' : ''">chevron_right</span>

                        {{-- Method badge --}}
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $methodCls }} flex-shrink-0">
                            {{ $method }}
                        </span>

                        {{-- Path --}}
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800">
                            {{ $requestLog->path ?? '-' }}
                        </span>

                        {{-- Right side meta --}}
                        <div class="ml-auto flex flex-shrink-0 items-center gap-3">
                            {{-- Status code --}}
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $isError ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $status ?: '-' }}
                            </span>

                            {{-- Duration --}}
                            @if($requestLog->duration_ms)
                            <span class="hidden text-xs text-gray-500 sm:inline">{{ $requestLog->duration_ms }} ms</span>
                            @endif

                            {{-- Blocked badge --}}
                            @if($requestLog->is_blocked)
                            <span class="hidden rounded-full bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700 sm:inline">Blocked</span>
                            @endif

                            {{-- Timestamp --}}
                            <span class="hidden text-xs text-gray-400 lg:inline">
                                {{ optional($requestLog->created_at)->format('Y-m-d H:i:s') }}
                            </span>
                        </div>
                    </button>

                    {{-- Accordion body --}}
                    <div x-show="open" x-collapse>
                        <div class="border-t border-gray-100 bg-gray-50/50 px-5 pb-5 pt-4">

                            {{-- Route name + timestamp (mobile) --}}
                            <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                                <span><span class="font-medium text-gray-600">Route:</span> {{ $requestLog->route_name ?: '-' }}</span>
                                <span><span class="font-medium text-gray-600">At:</span> {{ optional($requestLog->created_at)->format('Y-m-d H:i:s') }}</span>
                                @if($requestLog->duration_ms)
                                <span><span class="font-medium text-gray-600">Duration:</span> {{ $requestLog->duration_ms }} ms</span>
                                @endif
                                @if($requestLog->is_blocked)
                                <span class="text-rose-600"><span class="font-medium">Blocked:</span> Yes — {{ $requestLog->blocked_reason ?? 'no reason' }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                                {{-- ── REQUEST panel ── --}}
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-2.5">
                                        <h4 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            <span class="material-icons-outlined text-sm text-blue-500">arrow_upward</span> Request
                                        </h4>
                                    </div>
                                    <div class="divide-y divide-gray-100 text-sm">
                                        <div class="px-4 py-2.5">
                                            <p class="text-xs text-gray-400">URL</p>
                                            <p class="mt-0.5 break-all font-medium text-gray-700">{{ $requestLog->url ?? '-' }}</p>
                                        </div>
                                        <div class="px-4 py-2.5">
                                            <p class="text-xs text-gray-400">Referer</p>
                                            <p class="mt-0.5 break-all font-medium text-gray-700">{{ $requestLog->referer ?: '-' }}</p>
                                        </div>
                                        <div class="px-4 py-2.5">
                                            <p class="text-xs text-gray-400">User Agent</p>
                                            <p class="mt-0.5 break-all font-medium text-gray-700">{{ $requestLog->user_agent ?: '-' }}</p>
                                        </div>

                                        {{-- Sub-accordion: Request Headers --}}
                                        <div x-data="{ s: false }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Request Headers</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ json_encode($requestLog->headers ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        {{-- Sub-accordion: Query Params --}}
                                        <div x-data="{ s: false }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Query Params</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ json_encode($requestLog->query_params ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        {{-- Sub-accordion: Request Body --}}
                                        <div x-data="{ s: false }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Request Body</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ json_encode($requestLog->request_body ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        {{-- Sub-accordion: Middleware --}}
                                        <div x-data="{ s: false }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Middleware</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ json_encode($requestLog->request_middleware ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── RESPONSE panel ── --}}
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-2.5">
                                        <h4 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                            <span class="material-icons-outlined text-sm {{ $isError ? 'text-rose-500' : 'text-emerald-500' }}">arrow_downward</span> Response
                                        </h4>
                                    </div>
                                    <div class="divide-y divide-gray-100 text-sm">
                                        <div class="grid grid-cols-3 divide-x divide-gray-100">
                                            <div class="px-4 py-2.5">
                                                <p class="text-xs text-gray-400">Status</p>
                                                <p class="mt-0.5 font-semibold {{ $isError ? 'text-rose-600' : 'text-emerald-600' }}">{{ $status ?: '-' }}</p>
                                            </div>
                                            <div class="px-4 py-2.5">
                                                <p class="text-xs text-gray-400">Duration</p>
                                                <p class="mt-0.5 font-medium text-gray-700">{{ $requestLog->duration_ms ? $requestLog->duration_ms . ' ms' : '-' }}</p>
                                            </div>
                                            <div class="px-4 py-2.5">
                                                <p class="text-xs text-gray-400">Blocked</p>
                                                <p class="mt-0.5 font-medium {{ $requestLog->is_blocked ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $requestLog->is_blocked ? 'Yes' : 'No' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($requestLog->is_blocked && $requestLog->blocked_reason)
                                        <div class="px-4 py-2.5">
                                            <p class="text-xs text-gray-400">Block Reason</p>
                                            <p class="mt-0.5 font-medium text-rose-700">{{ $requestLog->blocked_reason }}</p>
                                        </div>
                                        @endif

                                        {{-- Sub-accordion: Response Headers --}}
                                        <div x-data="{ s: false }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Response Headers</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ json_encode($requestLog->response_headers ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>

                                        {{-- Sub-accordion: Response Body --}}
                                        <div x-data="{ s: true }">
                                            <button type="button" @click="s = !s"
                                                class="flex w-full items-center justify-between px-4 py-2.5 text-left hover:bg-gray-50">
                                                <span class="text-xs font-medium text-gray-500">Response Body</span>
                                                <span class="material-icons-outlined text-xs text-gray-400 transition-transform" :class="s ? 'rotate-180' : ''">expand_more</span>
                                            </button>
                                            <div x-show="s" x-collapse>
                                                <pre class="mx-4 mb-3 max-h-72 overflow-auto rounded border border-gray-100 bg-gray-50 p-2 text-xs">{{ $decodedResponse !== null ? json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : ($responseBody ?: '-') }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>{{-- /grid --}}
                        </div>
                    </div>
                </div>

            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <span class="material-icons-outlined mb-3 text-3xl text-gray-300">inbox</span>
                    <p class="text-sm text-gray-500">No request records found for this group.</p>
                </div>
            @endforelse
        </div>

        @if ($requests->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $requests->links() }}</div>
        @endif
    </div>

</div>
@endsection
