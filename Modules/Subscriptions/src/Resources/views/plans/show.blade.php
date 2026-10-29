@extends('backend::layouts.app')

@section('title', 'Plan Details')

@section('breadcrumbs')
    @php
        $label = $plan->name;
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Subscriptions', 'url' => null],
            ['label' => 'Plans', 'url' => route('subscriptions.plans.index')],
            ['label' => $plan->name, 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$label" />
@endsection

@section('content')

    <!-- Plan Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h2>
                    @if ($plan->is_popular)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <span class="material-icons-outlined text-sm mr-1">star</span>
                            Popular
                        </span>
                    @endif
                    @if ($plan->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Inactive
                        </span>
                    @endif
                </div>
                @if ($plan->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $plan->description }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <!-- Edit Button -->
                <a href="{{ route('subscriptions.plans.edit', $plan->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    <span class="material-icons-outlined text-sm mr-1">edit</span>
                    Edit Plan
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Products Using This Plan -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Products with Plan</span>
                <span class="material-icons-outlined text-purple-500">shopping_cart</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</div>
            <p class="text-xs text-gray-500 mt-1">Total products mapped to this plan</p>
        </div>

        <!-- Active Product Mappings -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active Mappings</span>
                <span class="material-icons-outlined text-green-500">check_circle</span>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active_products']) }}</div>
            <p class="text-xs text-gray-500 mt-1">Active product-plan mappings</p>
        </div>

        <!-- Total Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total Subscriptions</span>
                <span class="material-icons-outlined text-blue-500">subscriptions</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_subscriptions']) }}</div>
            <p class="text-xs text-gray-500 mt-1">Users subscribed to this plan</p>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Active Subscriptions</span>
                <span class="material-icons-outlined text-emerald-500">verified</span>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($stats['active_subscriptions']) }}</div>
            <p class="text-xs text-gray-500 mt-1">Currently active subscriptions</p>
        </div>
    </div>

    <!-- Plan Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Pricing Information -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="material-icons-outlined text-blue-500 mr-2">payments</span>
                Pricing Information
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Price:</span>
                    <span class="text-lg font-bold text-gray-900">${{ number_format($plan->price, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Billing Cycle:</span>
                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($plan->billing_cycle) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Trial Period:</span>
                    <span class="text-sm font-medium text-gray-900">
                        @if ($plan->trial_days > 0)
                            {{ $plan->trial_days }} days
                        @else
                            No trial
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Plan Limits -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="material-icons-outlined text-purple-500 mr-2">settings</span>
                Plan Limits
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Max Users:</span>
                    <span class="text-sm font-medium text-gray-900">
                        @if ($plan->max_users)
                            {{ number_format($plan->max_users) }}
                        @else
                            <span class="text-green-600">Unlimited</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Max Projects:</span>
                    <span class="text-sm font-medium text-gray-900">
                        @if ($plan->max_projects)
                            {{ number_format($plan->max_projects) }}
                        @else
                            <span class="text-green-600">Unlimited</span>
                        @endif
                    </span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Sort Order:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $plan->sort_order }}</span>
                </div>
            </div>
        </div>

        <!-- Plan Metadata -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="material-icons-outlined text-gray-500 mr-2">info</span>
                Metadata
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Created:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $plan->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Updated:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $plan->updated_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">Plan ID:</span>
                    <span class="text-sm font-medium text-gray-900">#{{ $plan->id }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->
    @if ($plan->features && count($plan->features) > 0)
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="material-icons-outlined text-green-500 mr-2">check_circle</span>
                Plan Features
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($plan->features as $feature)
                    <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-lg">
                        <span class="material-icons-outlined text-green-500 text-sm mt-0.5">check</span>
                        <span class="text-sm text-gray-700">{{ $feature }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Products Mapped to This Plan -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="material-icons-outlined text-purple-500 mr-2">inventory_2</span>
                Products Mapped to This Plan
                <span class="ml-2 px-2 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                    {{ $stats['total_products'] }}
                </span>
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Products configured to use this subscription plan. When customers purchase these products, they receive subscriptions with this plan.
            </p>
        </div>

        @if ($subscriptionProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($subscriptionProducts as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $product->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-outlined text-blue-500 text-sm">category</span>
                                        <span class="font-medium">{{ class_basename($product->product_type) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="text-gray-600">#{{ $product->product_id }}</span>
                                    @if ($product->product)
                                        <br>
                                        <span class="text-xs text-gray-500">{{ $product->product->name ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${{ number_format($product->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->created_at->format('M d, Y') }}
                                    <br>
                                    <span class="text-xs text-gray-400">{{ $product->created_at->format('h:i A') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($subscriptionProducts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $subscriptionProducts->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <span class="material-icons-outlined text-gray-300 text-6xl mb-4">inventory_2</span>
                <p class="text-gray-500 text-lg font-medium">No products mapped yet</p>
                <p class="text-gray-400 text-sm mt-2">Products mapped to this plan will appear here</p>
            </div>
        @endif
    </div>

    <!-- Features Section -->
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="material-icons-outlined text-blue-500 mr-2">assignment</span>
                Features
            </h3>
            <a href="{{ route('subscriptions.plans.features.edit', $plan->id) }}" 
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <span class="material-icons-outlined text-sm mr-1">edit</span>
                Manage Features
            </a>
        </div>

        @php
            $planFeatures = $plan->planFeatures()->get();
            $groupedFeatures = $planFeatures->groupBy(function($item) {
                return $item->group ?: 'Uncategorized';
            });
        @endphp

        @if($planFeatures->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($groupedFeatures as $group => $features)
                <div class="px-6 py-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ $group ?: 'Uncategorized' }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($features as $feature)
                        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1 mb-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $feature->display_name }}</p>
                                    @php
                                        $typeColors = [
                                            'boolean' => 'bg-blue-100 text-blue-800',
                                            'limit' => 'bg-green-100 text-green-800',
                                            'package' => 'bg-purple-100 text-purple-800',
                                        ];
                                        $typeClass = $typeColors[$feature->type] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $typeClass }}">
                                        {{ substr(ucfirst($feature->type), 0, 1) }}
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $feature->pivot->value }}
                                    @if($feature->unit)
                                        <span class="text-xs text-gray-500">{{ $feature->unit }}</span>
                                    @endif
                                </p>
                                @if($feature->pivot->is_highlighted)
                                    <span class="inline-flex items-center gap-1 mt-1 text-xs text-yellow-700">
                                        <span class="material-icons-outlined text-sm">star</span>
                                        Highlighted
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <span class="material-icons-outlined text-gray-300 text-6xl mb-4">dashboard</span>
                <p class="text-gray-500 text-lg font-medium">No features assigned yet</p>
                <p class="text-gray-400 text-sm mt-2">Assign features to this plan to grant them to subscribers</p>
                <a href="{{ route('subscriptions.plans.features.edit', $plan->id) }}" 
                   class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <span class="material-icons-outlined text-sm mr-1">add</span>
                    Add Features
                </a>
            </div>
        @endif
    </div>

@endsection
