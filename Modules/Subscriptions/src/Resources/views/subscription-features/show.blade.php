@extends('backend::layouts.app')

@section('title', $feature->display_name . ' - Usage')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => 'Subscription #' . $subscription->id, 'url' => route('subscriptions.show', $subscription->id)],
        ['label' => 'Features', 'url' => route('subscriptions.features.index', $subscription->id)],
        ['label' => $feature->display_name, 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$feature->display_name . ' - Usage'" />
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                        {{ $feature->display_name }}
                        @if($feature->pivot->is_custom)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                Custom Override
                            </span>
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        @if($feature->description)
                            {{ $feature->description }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Feature Info -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">Feature Details</h4>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Name</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $feature->name }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Type</span>
                    <p class="text-sm text-gray-900 mt-1">
                        @php
                            $typeColors = [
                                'boolean' => 'bg-blue-100 text-blue-800',
                                'limit' => 'bg-green-100 text-green-800',
                                'package' => 'bg-purple-100 text-purple-800',
                            ];
                            $typeClass = $typeColors[$feature->type] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeClass }}">
                            {{ ucfirst($feature->type) }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Group</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $feature->group ?? 'Uncategorized' }}</p>
                </div>
            </div>
        </div>

        <!-- Current Value -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">Current Value</h4>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Assigned Value</span>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ $feature->pivot->value }}
                        @if($feature->unit)
                            <span class="text-sm text-gray-500">{{ $feature->unit }}</span>
                        @endif
                    </p>
                </div>
                @if($feature->pivot->custom_notes)
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase">Notes</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $feature->pivot->custom_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Usage Section (for limit type features) -->
    @if($feature->type === 'limit')
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900">Usage Tracking</h4>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-6">
                <!-- Usage Progress -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Current Usage</span>
                        <span class="text-2xl font-bold text-gray-900">
                            {{ number_format($feature->pivot->used) }}
                            <span class="text-sm text-gray-500">
                                @if($feature->pivot->value === 'unlimited' || $feature->pivot->value === '-1')
                                    of ∞
                                @else
                                    of {{ number_format($feature->pivot->value) }}
                                @endif
                                @if($feature->unit)
                                    {{ $feature->unit }}
                                @endif
                            </span>
                        </span>
                    </div>

                    @if($feature->pivot->value !== 'unlimited' && $feature->pivot->value !== '-1')
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $usagePercent }}%"></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ $usagePercent }}% used</p>

                        <!-- Remaining Info -->
                        @php
                            $remaining = (float)$feature->pivot->value - (float)$feature->pivot->used;
                            $remaining = max(0, $remaining);
                        @endphp
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                <span class="text-xs font-medium text-blue-700">Remaining</span>
                                <p class="text-lg font-bold text-blue-900 mt-1">
                                    {{ number_format($remaining) }} {{ $feature->unit }}
                                </p>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                                <span class="text-xs font-medium text-orange-700">Usage %</span>
                                <p class="text-lg font-bold text-orange-900 mt-1">{{ $usagePercent }}%</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <p class="text-sm text-green-800">
                                <span class="material-icons-outlined text-sm mr-1 align-middle">check_circle</span>
                                This feature has <strong>unlimited</strong> usage
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Reset Usage -->
                @if($feature->pivot->used > 0)
                <div class="pt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('subscriptions.features.resetUsage', [$subscription->id, $feature->id]) }}" onsubmit="return confirm('Are you sure? This will reset the usage counter to 0.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                            <span class="material-icons-outlined text-sm mr-1">refresh</span>
                            Reset Usage Counter
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Reset Time Info -->
    @if($feature->pivot->usage_reset_at)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <span class="material-icons-outlined text-blue-600 mt-0.5">schedule</span>
            <div>
                <h4 class="text-sm font-medium text-blue-900">Usage Reset Schedule</h4>
                <p class="text-sm text-blue-700 mt-1">
                    Usage will automatically reset on <strong>{{ $feature->pivot->usage_reset_at->format('M d, Y') }}</strong>
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
