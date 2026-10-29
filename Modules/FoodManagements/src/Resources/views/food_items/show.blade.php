    @extends('backend::layouts.app')

@section('title', 'Food Item Preview - ' . $item->name)

@section('breadcrumbs')
@php

$title = $item->name;
$restaurantId = session('food_item_restaurant_id');
$restaurant = $restaurantId ? \Modules\Restaurant\Models\Restaurant::find($restaurantId) : null;
if ($restaurant) {
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'Restaurants', 'url' => route('admin.restaurant.index')],
['label' => $restaurant->name, 'url' => route('admin.restaurant.edit', $restaurant->id)],
['label' => 'Food Management', 'url' => route('admin.restaurant.edit', $restaurant->id) . '?tab=food'],
['label' => $title],
];
} else {
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'Food Items', 'url' => route('admin.foodmanagements.food-items.index')],
['label' => $title],
];
}

// $breadcrumbs = [
// ['label' => 'Dashboard', 'url' => route('dashboard.index')],
// ['label' => 'Food Items', 'url' => route('admin.foodmanagements.food-items.index')],
// ['label' => 'View Details'],
// ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Food Item Preview'" />
@endsection

@section('content')
<div class="mx-auto">
    <!-- Header -->
    <div class="bg-white py-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <!-- Back Arrow -->
                @if($restaurant)
                    <a href="{{route('admin.restaurant.edit', $restaurant->id) . '?tab=food'}}"
                        class="p-2 rounded-md hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition"
                        title="Back to Restaurant Food Management">
                @else
                    <a href="{{ route('admin.foodmanagements.food-items.index') }}"
                        class="p-2 rounded-md hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition"
                        title="Back to Food Items List">
                @endif

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <!-- Food Item Icon -->
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                            </path>
                        </svg>
                    </div>

                    <!-- Title -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $item->name }}
                        </h2>
                        <p class="text-sm text-gray-500">Food Item Details</p>
                    </div>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Status Badges -->
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                </span>

                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $item->is_available ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $item->is_available ? 'Available' : 'Unavailable' }}
                </span>

                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $item->in_stock ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                    {{ $item->in_stock ? 'In Stock' : 'Out of Stock' }}
                </span>

                @if (bouncer('admin')->hasPermission('admin.foodmanagements.food-items.edit'))
                <a href="{{ route('admin.foodmanagements.food-items.edit', $item->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-[var(--color-black)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Item
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('basic-info')"
                    class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="basic-info">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Basic Information
                    </div>
                </button>

                <button onclick="showTab('pricing')"
                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="pricing">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v22
                         M17 5.5c0-1.933-2.239-3.5-5-3.5s-5 1.567-5 3.5
                         2.239 3.5 5 3.5
                         5 1.567 5 3.5
                         -2.239 3.5-5 3.5
                         -5-1.567-5-3.5" />
                        </svg>
                        Pricing
                    </div>
                </button>

                @if ($item->description || $item->long_description)
                <button onclick="showTab('descriptions')"
                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="descriptions">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                        Descriptions
                    </div>
                </button>
                @endif

                @if ($item->categories->count() > 0)
                <button onclick="showTab('categories')"
                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="categories">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Categories
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $item->categories->count() }}
                        </span>
                    </div>
                </button>
                @endif

                @if ($item->tags->count() > 0)
                <button onclick="showTab('tags')"
                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="tags">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Tags
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $item->tags->count() }}
                        </span>
                    </div>
                </button>
                @endif

                @if ($item->optionGroups->count() > 0)
                <button onclick="showTab('options')"
                    class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                    data-tab="options">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                        Customization
                        <span
                            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $item->optionGroups->count() }}
                        </span>
                    </div>
                </button>
                @endif
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Basic Information Tab -->
            <div id="basic-info-tab" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Food Name:</span>
                                <span class="text-sm text-gray-900 font-medium">{{ $item->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Slug:</span>
                                <span class="text-sm text-gray-700 font-mono bg-gray-100 px-2 py-1 rounded">{{
                                    $item->slug }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Stock Status:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->in_stock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->in_stock ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Quantity Type:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->quantity_type === 'manual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $item->quantity_type === 'manual' ? 'Manual' : 'Grouping' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">{{ $item->quantity_label }}:</span>
                                <span class="text-sm text-gray-900 font-medium">
                                    {{ $item->formatted_quantity }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Created:</span>
                                <span class="text-sm text-gray-900">{{ $item->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Last Updated:</span>
                                <span class="text-sm text-gray-900">{{ $item->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Information -->
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 {{ $item->quantity_type === 'manual' ? 'text-blue-600' : 'text-purple-600' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($item->quantity_type === 'manual')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                @endif
                            </svg>
                            Quantity Management
                        </h3>

                        <div class="space-y-4">
                            <!-- Quantity Type Card -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-900">Type</span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $item->quantity_type === 'manual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $item->quantity_type === 'manual' ? 'Manual Stock' : 'Group Booking' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600">
                                    @if($item->quantity_type === 'manual')
                                    This item uses manual stock management. The quantity represents available inventory
                                    units.
                                    @else
                                    This item is for group bookings. The quantity represents the minimum number of
                                    guests required to place an order.
                                    @endif
                                </p>
                            </div>

                            <!-- Quantity Value Card -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $item->quantity_label }}</span>
                                    <span
                                        class="text-lg font-bold {{ $item->quantity ? 'text-gray-900' : 'text-gray-400' }}">
                                        {{ $item->quantity ?? 'Not Set' }}
                                    </span>
                                </div>
                                @if($item->quantity)
                                <div class="flex items-center text-xs text-gray-600">
                                    @if($item->quantity_type === 'manual')
                                    <svg class="w-3 h-3 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ $item->quantity }} units available in inventory
                                    @else
                                    <svg class="w-3 h-3 mr-1 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                    </svg>
                                    Minimum {{ $item->quantity }} guests required for booking
                                    @endif
                                </div>
                                @else
                                <p class="text-xs text-gray-500">
                                    {{ $item->quantity_type === 'manual' ? 'Stock quantity not configured' : 'Minimum
                                    guest count not set' }}
                                </p>
                                @endif
                            </div>

                            <!-- Status Summary -->
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">Current Status</span>
                                    <div class="flex items-center space-x-2">
                                        @if($item->in_stock && $item->is_available && $item->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Ready to Order
                                        </span>
                                        @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Not Available
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Information Section (moved below) -->
                <div class="mt-6">
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 rounded-full {{ $item->is_active ? 'bg-green-400' : 'bg-red-400' }} mr-3">
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">Active Status</span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 rounded-full {{ $item->is_available ? 'bg-blue-400' : 'bg-gray-400' }} mr-3">
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">Availability</span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_available ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 rounded-full {{ $item->in_stock ? 'bg-emerald-400' : 'bg-orange-400' }} mr-3">
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">Stock Status</span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->in_stock ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $item->in_stock ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
                                <div class="flex items-center">
                                    <div
                                        class="w-3 h-3 rounded-full {{ $item->quantity_type === 'manual' ? 'bg-blue-400' : 'bg-purple-400' }} mr-3">
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ $item->quantity_label
                                            }}</span>
                                        <p class="text-xs text-gray-500">
                                            {{ $item->quantity_type === 'manual' ? 'Stock management' : 'Minimum guest
                                            requirement' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ $item->quantity ? $item->quantity : 'Not set' }}
                                    </span>
                                    @if($item->quantity)
                                    <p class="text-xs text-gray-500">
                                        {{ $item->quantity_type === 'manual' ? 'units' : 'guests min' }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Tab -->
            <div id="pricing-tab" class="tab-content hidden">
                <div class="">
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Pricing Details</h3>
                        <div class="space-y-4">
                            <div
                                class="flex justify-between items-center p-4 bg-white rounded-lg border border-gray-200">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Sale Price</span>
                                    <p class="text-xs text-gray-500">Current selling price</p>
                                </div>
                                <span class="text-2xl font-bold text-green-600">{{ fn_format_default_currency() }}
                                    {{ number_format($item->sale_price, 2) }}</span>
                            </div>

                            @if ($item->list_price)
                            <div
                                class="flex justify-between items-center p-4 bg-white rounded-lg border border-gray-200">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">List Price (MRP)</span>
                                    <p class="text-xs text-gray-500">Maximum retail price</p>
                                </div>
                                <span class="text-lg font-semibold text-gray-900">{{ fn_format_default_currency() }}
                                    {{ number_format($item->list_price, 2) }}</span>
                            </div>
                            @endif

                            @if ($item->cost_price)
                            <div
                                class="flex justify-between items-center p-4 bg-white rounded-lg border border-gray-200">
                                <div>
                                    <span class="text-sm font-medium text-gray-600">Cost Price</span>
                                    <p class="text-xs text-gray-500">Purchase/production cost</p>
                                </div>
                                <span class="text-lg font-semibold text-gray-900">{{ fn_format_default_currency() }}
                                    {{ number_format($item->cost_price, 2) }}</span>
                            </div>
                            @endif


                            @if ($item->list_price && $item->sale_price < $item->list_price)
                                <div
                                    class="flex justify-between items-center p-4 bg-green-50 rounded-lg border border-green-200">
                                    <div>
                                        <span class="text-sm font-medium text-green-700">Savings</span>
                                        <p class="text-xs text-green-600">Savings from MRP</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-semibold text-green-700">{{
                                            fn_format_default_currency() }}
                                            {{ number_format($item->list_price - $item->sale_price, 2) }}</span>
                                        <p class="text-xs text-green-600">
                                            {{ number_format((($item->list_price - $item->sale_price) /
                                            $item->list_price) * 100, 1) }}%
                                            off</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Tax Information -->
                                @php
                                $taxCalculation = $item->getTaxCalculation();
                                $taxRate = $taxCalculation['tax_rate'];
                                @endphp

                                @if ($taxRate > 0)
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <h4 class="text-md font-semibold text-blue-900 mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        Tax Information
                                    </h4>

                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-blue-700">Tax Calculation Type</span>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $taxCalculation['is_tax_inclusive'] ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                {{ $taxCalculation['is_tax_inclusive'] ? 'Tax Inclusive' : 'Tax
                                                Exclusive' }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-blue-700">Tax Rate</span>
                                            <span class="text-sm font-semibold text-blue-900">{{ $taxRate }}%</span>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-blue-700">Tax Amount</span>
                                            <span class="text-sm font-semibold text-blue-900">{{
                                                fn_format_default_currency() }} {{
                                                number_format($taxCalculation['tax_amount'], 2) }}</span>
                                        </div>

                                        @if ($taxCalculation['is_tax_inclusive'])
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-blue-700">Price Without Tax</span>
                                            <span class="text-sm font-semibold text-blue-900">{{
                                                fn_format_default_currency() }} {{
                                                number_format($taxCalculation['price_without_tax'], 2) }}</span>
                                        </div>
                                        @endif

                                        <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                                            <span class="text-sm font-medium text-blue-700">Final Display Price</span>
                                            <span class="text-lg font-bold text-blue-900">{{
                                                fn_format_default_currency() }} {{
                                                number_format($taxCalculation['display_price'], 2) }}</span>
                                        </div>

                                        <div class="mt-3 p-3 bg-blue-100 rounded-md">
                                            <p class="text-xs text-blue-800">
                                                @if ($taxCalculation['is_tax_inclusive'])
                                                <strong>Tax Inclusive:</strong> The sale price already includes {{
                                                $taxRate }}% tax. Customers will pay exactly {{
                                                fn_format_default_currency() }} {{
                                                number_format($taxCalculation['display_price'], 2) }}.
                                                @else
                                                <strong>Tax Exclusive:</strong> {{ $taxRate }}% tax will be added to the
                                                sale price. Customers will pay {{ fn_format_default_currency() }} {{
                                                number_format($taxCalculation['display_price'], 2) }} ({{
                                                fn_format_default_currency() }} {{ number_format($item->sale_price, 2)
                                                }} + {{ fn_format_default_currency() }} {{
                                                number_format($taxCalculation['tax_amount'], 2) }} tax).
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-600">No Tax Applied</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 ml-6">Tax rate is set to 0% or not configured.
                                        Customers will pay the sale price as-is.</p>
                                </div>
                                @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descriptions Tab -->
            @if ($item->description || $item->long_description)
            <div id="descriptions-tab" class="tab-content hidden">
                <div class="space-y-6">
                    @if ($item->description)
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Short Description</h3>
                        <textarea rows="4" editor="true"
                            class="w-full text-sm text-gray-900 bg-white p-4 rounded border border-gray-300 focus:outline-none resize-none">{{ $item->description }}</textarea>
                    </div>
                    @endif

                    @if ($item->long_description)
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Description</h3>
                        <textarea rows="8" editor="true"
                            class="w-full text-sm text-gray-900 bg-white p-4 rounded border border-gray-300 focus:outline-none resize-none leading-relaxed">{{ $item->long_description }}</textarea>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Categories Tab -->
            @if ($item->categories->count() > 0)
            <div id="categories-tab" class="tab-content hidden">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Categories</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $item->categories->count() }}
                            {{ $item->categories->count() === 1 ? 'Category' : 'Categories' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($item->categories as $category)
                        <div class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-sm transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Category
                                </span>
                            </div>
                            @if ($category->parent)
                            <p class="text-xs text-gray-500">Parent: {{ $category->parent->name }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Tags Tab -->
            @if ($item->tags->count() > 0)
            <div id="tags-tab" class="tab-content hidden">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Tags</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ $item->tags->count() }} {{ $item->tags->count() === 1 ? 'Tag' : 'Tags' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($item->tags as $tag)
                        <span
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            {{ $tag->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Customization Options Tab -->
            @if ($item->optionGroups->count() > 0)
            <div id="options-tab" class="tab-content hidden">
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Customization Options</h3>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                            {{ $item->optionGroups->count() }}
                            {{ $item->optionGroups->count() === 1 ? 'Group' : 'Groups' }}
                        </span>
                    </div>

                    @foreach ($item->optionGroups as $optionGroup)
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $optionGroup->name }}</h4>
                            <div class="flex items-center space-x-2">
                                @if ($optionGroup->is_required)
                                <span
                                    class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full font-medium">Required</span>
                                @else
                                <span
                                    class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full font-medium">Optional</span>
                                @endif
                                <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded">Min:
                                    {{ $optionGroup->min }} | Max: {{ $optionGroup->max }}</span>
                            </div>
                        </div>

                        @if ($optionGroup->options->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($optionGroup->options as $option)
                            <div
                                class="bg-white rounded-lg p-4 border border-gray-200 hover:shadow-sm transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="w-3 h-3 rounded-full {{ $option->is_active ? 'bg-green-400' : 'bg-red-400' }} mr-3">
                                        </div>
                                        <span
                                            class="text-sm font-medium {{ $option->is_active ? 'text-gray-900' : 'text-gray-500' }}">
                                            {{ $option->name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                        $optionPrice = $item->getOptionPrice($option->id);
                                        @endphp
                                        @if ($optionPrice > 0)
                                        <span class="text-sm font-medium text-green-600">+{{
                                            fn_format_default_currency() }}{{ number_format($optionPrice, 2) }}</span>
                                        @else
                                        <span class="text-sm text-gray-500">Free</span>
                                        @endif
                                        @if (!$option->is_active)
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                            <p class="text-sm text-gray-500">No options available in this group</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .tab-button {
        border-bottom-color: transparent;
        color: #6B7280;
    }

    .tab-button.active {
        border-bottom-color: #621F09;
        color: #621F09;
    }

    .tab-button:hover {
        color: #621F09;
        border-bottom-color: #621F09;
    }

    .tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab content
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }

            // Add active class to clicked tab button
            const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
            if (selectedButton) {
                selectedButton.classList.add('active');
            }
        }

        // Initialize first tab as active on page load
        document.addEventListener('DOMContentLoaded', function() {
            showTab('basic-info');
        });
</script>
@endsection