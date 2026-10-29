@extends('backend::layouts.app')

@section('title', 'Plan Features - ' . $plan->name)

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
    ['label' => 'Plans', 'url' => route('subscriptions.plans.index')],
    ['label' => $plan->name, 'url' => route('subscriptions.plans.show', $plan->id)],
    ['label' => 'Features', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Plan Features'" />
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $plan->name }} - Feature Mapping</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Assign features and their values to this plan. These features will automatically be granted to users who subscribe to this plan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Features Assigned</span>
                <span class="material-icons-outlined text-blue-500">assignment</span>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $planFeatures->count() }}</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Available Features</span>
                <span class="material-icons-outlined text-green-500">library_add</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $features->count() }}</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Feature Groups</span>
                <span class="material-icons-outlined text-purple-500">folder</span>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $groupedFeatures->count() }}</div>
        </div>
    </div>

    <!-- Feature Mapping Form -->
    <form method="POST" action="{{ route('subscriptions.plans.features.update', $plan->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($groupedFeatures as $group => $groupFeatures)
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <!-- Group Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="material-icons-outlined text-blue-500">folder</span>
                    {{ $group ?: 'Uncategorized' }}
                    <span class="text-xs font-normal text-gray-500">{{ $groupFeatures->count() }} features</span>
                </h3>
            </div>

            <!-- Features List -->
            <div class="divide-y divide-gray-200">
                @foreach($groupFeatures as $feature)
                @php
                    $planFeature = $planFeatures->get($feature->id);
                    $isAssigned = $planFeature !== null;
                    $value = $planFeature?->pivot?->value ?? $feature->default_value;
                @endphp
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-4">
                        <!-- Feature Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ $feature->display_name }}</h4>
                                @php
                                    $typeColors = [
                                        'boolean' => 'bg-blue-100 text-blue-800',
                                        'limit' => 'bg-green-100 text-green-800',
                                        'package' => 'bg-purple-100 text-purple-800',
                                    ];
                                    $typeClass = $typeColors[$feature->type] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeClass }}">
                                    {{ ucfirst($feature->type) }}
                                </span>
                            </div>
                            @if($feature->description)
                                <p class="text-xs text-gray-500">{{ $feature->description }}</p>
                            @endif
                        </div>

                        <!-- Feature Input -->
                        <div class="flex items-end gap-3 flex-wrap justify-end">
                            @if($feature->type === 'boolean')
                                <select name="features[{{ $feature->id }}][value]" class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Not Assigned</option>
                                    <option value="true" {{ $value === 'true' ? 'selected' : '' }}>Enabled</option>
                                    <option value="false" {{ $value === 'false' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            @elseif($feature->type === 'limit')
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                           name="features[{{ $feature->id }}][value]" 
                                           value="{{ $value ?? '' }}"
                                           class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm w-32"
                                           placeholder="Value or unlimited">
                                    @if($feature->unit)
                                        <span class="text-sm text-gray-500">{{ $feature->unit }}</span>
                                    @endif
                                </div>
                            @elseif($feature->type === 'package')
                                <select name="features[{{ $feature->id }}][value]" class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Not Assigned</option>
                                    @foreach($feature->metadata['options'] ?? [] as $option)
                                        <option value="{{ $option }}" {{ $value === $option ? 'selected' : '' }}>
                                            {{ ucfirst($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <!-- Highlight Checkbox -->
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="features[{{ $feature->id }}][is_highlighted]" 
                                       value="1"
                                       {{ $planFeature?->pivot?->is_highlighted ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       title="Show prominently in pricing table">
                                <label class="ml-2 text-xs text-gray-600 cursor-help">Highlight</label>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($isAssigned)
                    <div class="mt-3 ml-0">
                        <input type="text" 
                               name="features[{{ $feature->id }}][notes]" 
                               value="{{ $planFeature->pivot->notes ?? '' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-xs"
                               placeholder="Optional notes (e.g., 'Premium support included')">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('subscriptions.plans.show', $plan->id) }}" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <span class="material-icons-outlined text-sm mr-1 inline">save</span>
                Save Feature Mapping
            </button>
        </div>
    </form>
</div>
@endsection
