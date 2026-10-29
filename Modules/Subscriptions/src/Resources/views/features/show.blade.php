@extends('backend::layouts.app')

@section('title', 'Feature Details')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => 'Features', 'url' => route('subscriptions.features.index')],
    ['label' => $feature->display_name, 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$feature->display_name" />
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Feature Header -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                    {{ $feature->display_name }}
                    @if($feature->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="material-icons-outlined text-xs mr-1">check_circle</span>Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <span class="material-icons-outlined text-xs mr-1">cancel</span>Inactive
                        </span>
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    <code class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $feature->name }}</code>
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('subscriptions.features.edit', $feature->id) }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="material-icons-outlined text-sm mr-1">edit</span>
                    Edit
                </a>
            </div>
        </div>

        <!-- Feature Details -->
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <!-- Description -->
                @if($feature->description)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $feature->description }}</dd>
                </div>
                @endif

                <!-- Type -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1">
                        @php
                            $colors = [
                                'boolean' => 'bg-blue-100 text-blue-800',
                                'limit' => 'bg-green-100 text-green-800',
                                'package' => 'bg-purple-100 text-purple-800',
                            ];
                            $icons = [
                                'boolean' => 'toggle_on',
                                'limit' => 'speed',
                                'package' => 'inventory_2',
                            ];
                            $class = $colors[$feature->type] ?? 'bg-gray-100 text-gray-800';
                            $icon = $icons[$feature->type] ?? 'label';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
                            <span class="material-icons-outlined text-xs mr-1">{{ $icon }}</span>
                            {{ ucfirst($feature->type) }}
                        </span>
                    </dd>
                </div>

                <!-- Default Value -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Default Value</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $feature->default_value ?? '-' }}
                        @if($feature->unit)
                            <span class="text-gray-500">{{ $feature->unit }}</span>
                        @endif
                    </dd>
                </div>

                <!-- Unit -->
                @if($feature->unit)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Unit</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $feature->unit }}</dd>
                </div>
                @endif

                <!-- Group -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Group</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($feature->group)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $feature->group }}
                            </span>
                        @else
                            <span class="text-gray-400">Uncategorized</span>
                        @endif
                    </dd>
                </div>

                <!-- Sort Order -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $feature->sort_order }}</dd>
                </div>

                <!-- Metadata -->
                @if(is_array($feature->metadata) && count($feature->metadata) > 0)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 mb-2">Configuration</dt>
                    <dd class="mt-1">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            @if($feature->type === 'limit')
                                <div class="grid grid-cols-3 gap-4">
                                    @if(isset($feature->metadata['min']))
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">Minimum:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $feature->metadata['min'] }}</span>
                                    </div>
                                    @endif
                                    @if(isset($feature->metadata['max']))
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">Maximum:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $feature->metadata['max'] }}</span>
                                    </div>
                                    @endif
                                    @if(isset($feature->metadata['step']))
                                    <div>
                                        <span class="text-xs font-medium text-gray-500">Step:</span>
                                        <span class="text-sm text-gray-900 ml-1">{{ $feature->metadata['step'] }}</span>
                                    </div>
                                    @endif
                                </div>
                            @elseif($feature->type === 'package' && isset($feature->metadata['options']))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 block mb-2">Available Options:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($feature->metadata['options'] as $option)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white border border-gray-300 text-gray-700">
                                                {{ $option }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <pre class="text-xs text-gray-700">{{ json_encode($feature->metadata, JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </div>
                    </dd>
                </div>
                @endif

                <!-- Created At -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $feature->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <!-- Updated At -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $feature->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Usage Statistics</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-900">Plans Using</span>
                        <span class="material-icons-outlined text-blue-500">assignment</span>
                    </div>
                    <div class="text-2xl font-bold text-blue-700 mt-2">{{ $plans->count() }}</div>
                </div>

                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-green-900">Active Subscriptions</span>
                        <span class="material-icons-outlined text-green-500">verified</span>
                    </div>
                    <div class="text-2xl font-bold text-green-700 mt-2">{{ $activeSubscriptionsCount }}</div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-purple-900">Total Subscriptions</span>
                        <span class="material-icons-outlined text-purple-500">subscriptions</span>
                    </div>
                    <div class="text-2xl font-bold text-purple-700 mt-2">{{ $subscriptionsCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Using This Feature -->
    @if($plans->count() > 0)
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Plans Using This Feature</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Plan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Value
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Highlighted
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Notes
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($plans as $plan)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $plan->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        ${{ number_format($plan->price, 2) }} / {{ $plan->billing_cycle }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $plan->pivot->value }}
                                @if($feature->unit)
                                    <span class="text-gray-500 font-normal">{{ $feature->unit }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($plan->pivot->is_highlighted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <span class="material-icons-outlined text-xs mr-1">star</span>
                                    Highlighted
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                {{ $plan->pivot->notes ?? '-' }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
