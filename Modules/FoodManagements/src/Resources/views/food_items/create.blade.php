@extends('backend::layouts.app')

@section('title', $foodItemId ? 'Edit Food Item' : 'Create Food Item')

@section('breadcrumbs')
    @php
        $title = $foodItemId ? 'Edit Food Item' : 'Create Food Item';
        
        // Check if coming from restaurant context
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
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    @php
        $activeTab = session('tab', $foodItemId ? 'categories' : 'general');
    @endphp

    <div class="mx-auto px-4 py-2">

        {{-- ===================== TABS HEADER ===================== --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="flex border-b border-gray-200">
                <button class="tab-btn {{ $activeTab === 'general' ? 'tab-active' : '' }}" data-tab="general">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    General Information
                </button>

                @if ($foodItemId)
                    <button class="tab-btn {{ $activeTab === 'categories' ? 'tab-active' : '' }}" data-tab="categories">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        Categories
                    </button>

                    <button class="tab-btn {{ $activeTab === 'tags' ? 'tab-active' : '' }}" data-tab="tags">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                        Tags
                    </button>

                    <button class="tab-btn {{ $activeTab === 'options' ? 'tab-active' : '' }}" data-tab="options">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                            </path>
                        </svg>
                        Options
                    </button>

                    <button class="tab-btn {{ $activeTab === 'option_prices' ? 'tab-active' : '' }}"
                        data-tab="option_prices">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 1v22
                     M17 5.5c0-1.933-2.239-3.5-5-3.5s-5 1.567-5 3.5
                     2.239 3.5 5 3.5
                     5 1.567 5 3.5
                     -2.239 3.5-5 3.5
                     -5-1.567-5-3.5" />
                        </svg>
                        Option Prices
                    </button>

                    <button class="tab-btn {{ $activeTab === 'images' ? 'tab-active' : '' }}" data-tab="images">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Images
                    </button>

                    <button class="tab-btn {{ $activeTab === 'features' ? 'tab-active' : '' }}" data-tab="features">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        Features
                    </button>
                @endif
            </div>
        </div>

        {{-- ===================== TAB CONTENT ===================== --}}

        {{-- TAB 1 : GENERAL --}}
        <div class="tab-content {{ $activeTab !== 'general' ? 'hidden' : '' }}" id="tab-general">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Item Information</h3>
                    <p class="text-sm text-gray-600">Enter the basic details for your food item</p>
                </div>

                <form method="POST"
                    action="{{ $foodItemId
                        ? route('admin.foodmanagements.food-items.update.general', $foodItemId)
                        : route('admin.foodmanagements.food-items.store.general') }}">
                    @csrf

                    @if ($foodItemId)
                        @method('PUT')
                    @endif

                    <input type="hidden" name="restaurant_id" value="{{ request('restaurant_id') ?? session('food_item_restaurant_id') ?? '' }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label">Food Name <span class="text-red-500">*</span></label>
                            <input name="name" type="text" class="input-field @error('name') input-error @enderror"
                                value="{{ old('name', $item->name ?? '') }}" placeholder="Enter food item name">
                            @error('name')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sale Price <span class="text-red-500">*</span></label>
                            <input name="sale_price" type="number" step="0.01"
                                class="input-field @error('sale_price') input-error @enderror"
                                value="{{ old('sale_price', $item->sale_price ?? '') }}" placeholder="0.00">
                            @error('sale_price')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cost Price</label>
                            <input name="cost_price" type="number" step="0.01"
                                class="input-field @error('cost_price') input-error @enderror"
                                value="{{ old('cost_price', $item->cost_price ?? '') }}" placeholder="0.00">
                            @error('cost_price')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">List Price (MRP)</label>
                            <input name="list_price" type="number" step="0.01"
                                class="input-field @error('list_price') input-error @enderror"
                                value="{{ old('list_price', $item->list_price ?? '') }}" placeholder="0.00">
                            @error('list_price')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label class="form-label">Stock Status</label>
                            <select name="in_stock" class="input-field @error('in_stock') input-error @enderror">
                                <option value="1" @selected(old('in_stock', $item->in_stock ?? 1) == 1)>In Stock</option>
                                <option value="0" @selected(old('in_stock', $item->in_stock ?? 1) == 0)>Out of Stock</option>
                            </select>
                            @error('in_stock')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantity Type <span class="text-red-500">*</span></label>
                            <select name="quantity_type" id="quantity_type" class="input-field @error('quantity_type') input-error @enderror" onchange="updateQuantityLabel()">
                                <option value="piece" @selected(old('quantity_type', $item->quantity_type ?? 'manual') == 'piece')>Piece</option>
                                <option value="manual" @selected(old('quantity_type', $item->quantity_type ?? 'manual') == 'manual')>Manual</option>
                                <option value="grouping" @selected(old('quantity_type', $item->quantity_type ?? 'manual') == 'grouping')>Grouping</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                <span id="quantity_help_text">
                                    @if(old('quantity_type', $item->quantity_type ?? 'manual') == 'grouping')
                                        Grouping: Works as minimum guest count
                                    @else
                                        Manual: Works as stock quantity
                                    @endif
                                </span>
                            </p>
                            @error('quantity_type')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" id="quantity_label">
                                @if(old('quantity_type', $item->quantity_type ?? 'manual') == 'grouping')
                                    Minimum Guests
                                @else
                                    Stock Quantity
                                @endif
                            </label>
                            <input name="quantity" type="number" min="0"
                                class="input-field @error('quantity') input-error @enderror"
                                value="{{ old('quantity', $item->quantity ?? '') }}" 
                                placeholder="Enter quantity">
                            <p class="text-xs text-gray-500 mt-1" id="quantity_placeholder">
                                @if(old('quantity_type', $item->quantity_type ?? 'manual') == 'grouping')
                                    Enter minimum number of guests required
                                @else
                                    Enter available stock quantity
                                @endif
                            </p>
                            @error('quantity')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group md:col-span-2">
                            <label class="form-label">Short Description</label>

                            <textarea name="description" editor="false" rows="3"
                                class="input-field @error('description') input-error @enderror" placeholder="Brief description">{{ old('description', $item->description ?? '') }}</textarea>

                            @error('description')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="form-group md:col-span-2">
                            <label class="form-label">Detailed Description</label>
                            <textarea name="long_description" editor="false" rows="4"
                                class="input-field @error('long_description') input-error @enderror" placeholder="Detailed description">{{ old('long_description', $item->long_description ?? '') }}</textarea>
                            @error('long_description')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Availability</label>
                            <select name="is_available" class="input-field @error('is_available') input-error @enderror">
                                <option value="1" @selected(old('is_available', $item->is_available ?? 1) == 1)>Available</option>
                                <option value="0" @selected(old('is_available', $item->is_available ?? 1) == 0)>Unavailable</option>
                            </select>
                            @error('is_available')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Food Type <span class="text-red-500">*</span></label>
                            <select name="food_type" class="input-field @error('food_type') input-error @enderror">
                                <option value="veg" @selected(old('food_type', $item->food_type ?? 'veg') == 'veg')>Vegetarian</option>
                                <option value="non_veg" @selected(old('food_type', $item->food_type ?? 'veg') == 'non_veg')>Non-Vegetarian</option>
                            </select>
                            @error('food_type')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="input-field @error('is_active') input-error @enderror">
                                <option value="1" @selected(old('is_active', $item->is_active ?? 1) == 1)>Active</option>
                                <option value="0" @selected(old('is_active', $item->is_active ?? 1) == 0)>Inactive</option>
                            </select>
                            @error('is_active')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-8 space-x-3">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Save & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB 2 : SUB-CATEGORIES --}}
        @if ($foodItemId)
            <div class="tab-content {{ $activeTab !== 'categories' ? 'hidden' : '' }}" id="tab-categories">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Categories</h3>
                        <p class="text-sm text-gray-600">Select categories for this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.categories', $foodItemId) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($categories as $category)
                                <label
                                    class="flex items-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                        class="w-4 h-4 text-blue-600 rounded" @checked(isset($item) && $item->categories->contains($category->id))>

                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $category->parent ? $category->name : $category->name }}
                                        </span>

                                        @if ($category->parent)
                                            <p class="text-xs text-gray-500">
                                                Parent: {{ $category->parent->name }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                Save Categories
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 3 : TAGS --}}
            <div class="tab-content {{ $activeTab !== 'tags' ? 'hidden' : '' }}" id="tab-tags">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Tags</h3>
                            <p class="text-sm text-gray-600">Add tags to help customers find this item</p>
                        </div>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.tags', $foodItemId) }}">
                        @csrf

                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="tags-grid">
                            @foreach ($tags as $tag)
                                <label
                                    class="flex items-center p-3 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition-colors">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                        @if (isset($item) && $item->tags->contains($tag->id)) checked @endif
                                        @if (old('tags') && in_array($tag->id, old('tags'))) checked @endif
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-2 text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                Save Tags
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 4 : OPTIONS --}}
            <div class="tab-content {{ $activeTab !== 'options' ? 'hidden' : '' }}" id="tab-options">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Option Groups</h3>
                            <p class="text-sm text-gray-600">Select option groups for customization</p>
                        </div>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.options', $foodItemId) }}">
                        @csrf

                        <div class="space-y-6" id="options-grid">
                            @foreach ($optionGroups as $optionGroup)
                                <div class="bg-gray-50 rounded-lg border p-4">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox" name="option_groups[]" value="{{ $optionGroup->id }}"
                                            @if (isset($item) && $item->optionGroups->contains($optionGroup->id)) checked @endif
                                            @if (old('option_groups') && in_array($optionGroup->id, old('option_groups'))) checked @endif
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 mt-1">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-lg font-medium text-gray-900">{{ $optionGroup->name }}</span>
                                                <div class="flex items-center space-x-2">
                                                    @if ($optionGroup->is_required)
                                                        <span
                                                            class="inline-block px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Required</span>
                                                    @else
                                                        <span
                                                            class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Optional</span>
                                                    @endif
                                                    <span class="text-xs text-gray-500">Min: {{ $optionGroup->min }} |
                                                        Max: {{ $optionGroup->max }}</span>
                                                </div>
                                            </div>

                                            {{-- Individual Options --}}
                                            @if ($optionGroup->options->count() > 0)
                                                <div class="mt-3">
                                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Available Options:
                                                    </h4>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                                        @foreach ($optionGroup->options as $option)
                                                            <div
                                                                class="bg-white p-3 rounded border {{ $option->is_active ? 'border-gray-200' : 'border-red-200 bg-red-50' }}">
                                                                <div class="flex items-center justify-between">
                                                                    <span
                                                                        class="text-sm font-medium {{ $option->is_active ? 'text-gray-900' : 'text-red-600' }}">
                                                                        {{ $option->name }}
                                                                    </span>
                                                                    <div class="flex items-center space-x-2">
                                                                        @if (!$option->is_active)
                                                                            <span
                                                                                class="text-xs bg-red-100 text-red-800 px-1 rounded">Inactive</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                                                    <div class="flex items-center">
                                                        <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                            </path>
                                                        </svg>
                                                        <span class="text-sm text-yellow-800">No options available in this
                                                            group</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                Save Options
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 4.5 : OPTION PRICES --}}
            <div class="tab-content {{ $activeTab !== 'option_prices' ? 'hidden' : '' }}" id="tab-option_prices">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Option Prices</h3>
                        <p class="text-sm text-gray-600">Set specific prices for each option for this food item</p>
                    </div>

                    @if (isset($item) && $item->optionGroups->count() > 0)
                        <form method="POST"
                            action="{{ route('admin.foodmanagements.food-items.update.option-prices', $foodItemId) }}">
                            @csrf

                            <div class="space-y-6">
                                @foreach ($item->optionGroups as $optionGroup)
                                    @if ($optionGroup->options->count() > 0)
                                        <div class="bg-gray-50 rounded-lg border p-4">
                                            <h4 class="text-lg font-medium text-gray-900 mb-4">{{ $optionGroup->name }}
                                            </h4>

                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach ($optionGroup->options as $option)
                                                    @if ($option->is_active)
                                                        <div class="bg-white p-4 rounded border">
                                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                                {{ $option->name }}
                                                            </label>
                                                            <div class="relative">
                                                                {{-- <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span> --}}
                                                                <input type="number"
                                                                    name="option_prices[{{ $option->id }}]"
                                                                    value="{{ $item->getOptionPrice($option->id) }}"
                                                                    step="0.01" min="0"
                                                                    class="input-field pl-8" placeholder="0.00">
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="flex justify-end mt-8 space-x-3">
                                <button type="submit" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Option Prices
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Option Groups Selected</h3>
                            <p class="text-gray-500 mb-4">Please select option groups in the Options tab first to set
                                prices.</p>
                            <button type="button" class="btn btn-secondary" onclick="switchTab('options')">
                                Go to Options Tab
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB 5 : IMAGES --}}
            <div class="tab-content {{ $activeTab !== 'images' ? 'hidden' : '' }}" id="tab-images">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Images</h3>
                        <p class="text-sm text-gray-600">Upload images for this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.images', $foodItemId) }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            {{-- Upload New Images --}}
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-4">Upload New Images</h4>
                                @include('filemanager::components.file-uploader', [
                                    'object_type' => 'food_image',
                                    'object_id' => $foodItemId ?? 0,
                                    'name' => 'food_image[]',
                                    'is_multiple' => true,
                                    'accept' => 'image/jpeg,image/png',
                                    'span_text'=>'Drag and drop images here or click to select files. (JPEG, PNG)',
                                ])
                            </div>
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                Complete Food Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- TAB 6 : FEATURES --}}
            <div class="tab-content {{ $activeTab !== 'features' ? 'hidden' : '' }}" id="tab-features">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Features</h3>
                        <p class="text-sm text-gray-600">Assign features (e.g. Veg, Non-Veg) to this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.features', $foodItemId) }}">
                        @csrf

                        @if ($featureGroups->isEmpty())
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Feature Groups Found</h3>
                                <p class="text-gray-500">Please create feature groups and features first.</p>
                            </div>
                        @else
                            @php
                                // Pre-build a lookup: feature_id => saved values for this item
                                $savedValues = isset($item)
                                    ? $item->featureValues->groupBy('feature_id')
                                    : collect();
                            @endphp

                            <div class="space-y-6">
                                @foreach ($featureGroups as $group)
                                    @if ($group->children->isEmpty()) @continue @endif

                                    <div class="bg-gray-50 rounded-lg border p-4">
                                        <h4 class="text-base font-semibold text-gray-800 mb-4">
                                            {{ $group->description->name ?? 'Group #' . $group->feature_id }}
                                        </h4>

                                        <div class="space-y-4">
                                            @foreach ($group->children as $feature)
                                                @php
                                                    $fid        = $feature->feature_id;
                                                    $ftype      = $feature->feature_type; // C, S, M, N, O, T, D
                                                    
                                                    $fstyle     = $feature->feature_style;
                                                    $fname      = $feature->description->name ?? 'Feature #' . $fid;
                                                    $fSaved     = $savedValues->get($fid, collect());
                                                    $savedVariantIds = $fSaved->pluck('variant_id')->filter()->map(fn($v) => (string)$v)->toArray();
                                                    $savedValue = $fSaved->first()?->value ?? '';
                                                @endphp

                                                <div class="bg-white rounded-lg border p-4">
                                                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                                                        {{ $fname }}
                                                        <span class="ml-1 text-xs font-normal text-gray-400">({{ strtoupper($ftype) }})</span>
                                                    </label>

                                                    {{-- C: Single Checkbox (yes/no) --}}
                                                    @if ($ftype === 'C')
                                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                                            <input type="hidden" name="features[{{ $fid }}]" value="">
                                                            <input type="checkbox"
                                                                name="features[{{ $fid }}]"
                                                                value="Y"
                                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                                @if ($fSaved->isNotEmpty()) checked @endif>
                                                            <span class="text-sm text-gray-700">Yes</span>
                                                        </label>

                                                    {{-- S: Select — radio buttons (one variant) --}}
                                                    @elseif ($ftype === 'S')
                                                       
                                                        @if ($feature->variants->isEmpty())
                                                            <p class="text-xs text-gray-400">No variants defined.</p>
                                                        @else
                                                            @if ($fstyle === 'dropdown' || $fstyle === 'dropdown_labels')
                                                         
                                                                <select name="features[{{ $fid }}]"
                                                                    class="input-field max-w-xs text-sm">
                                                                   
                                                                    <option value="">— Select —</option>
                                                                   
                                                                    @foreach ($feature->variants as $variant)
                                                              
                                                                        @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                        <option value="{{ $variant->variant_id }}"
                                                                            @selected(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                            {{ $vname }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                {{-- radio --}}
                                                                <div class="flex flex-wrap gap-3">
                                                                    @foreach ($feature->variants as $variant)
                                                                        @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                        <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 bg-gray-50 rounded-lg border hover:bg-gray-100">
                                                                            <input type="radio"
                                                                                name="features[{{ $fid }}]"
                                                                                value="{{ $variant->variant_id }}"
                                                                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                                                                @checked(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                            <span class="text-sm text-gray-700">{{ $vname }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif

                                                    {{-- M: Multiselect — multiple checkboxes (many variants) --}}
                                                    @elseif ($ftype === 'M')
                                                        @if ($feature->variants->isEmpty())
                                                            <p class="text-xs text-gray-400">No variants defined.</p>
                                                        @else
                                                            <div class="flex flex-wrap gap-3">
                                                                
                                                                @foreach ($feature->variants as $variant)
                                                                    @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                    <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 bg-gray-50 rounded-lg border hover:bg-gray-100">
                                                                        <input type="checkbox"
                                                                            name="features[{{ $fid }}][]"
                                                                            value="{{ $variant->variant_id }}"
                                                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                                            @checked(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                        <span class="text-sm text-gray-700">{{ $vname }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                    {{-- N / O: Number input --}}
                                                    @elseif (in_array($ftype, ['N', 'O']))
                                                        <input type="number" step="any"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm"
                                                            placeholder="Enter value">

                                                    {{-- D: Date input --}}
                                                    @elseif ($ftype === 'D')
                                                  
                                                        <input type="date"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm">

                                                    {{-- T / default: Text input --}}
                                                    @else
                                                        <input type="text"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm"
                                                            placeholder="Enter value">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="submit" name="button" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Features
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

        @endif

    </div>

    {{-- ===================== TAB SCRIPT ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const activeTab = "{{ $activeTab }}";

            /* ================= TAB INIT ================= */
            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + activeTab)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            document.querySelector(`.tab-btn[data-tab="${activeTab}"]`)
                ?.classList.add('tab-active');

            /* ================= TAB SWITCH ================= */
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const tab = btn.dataset.tab;

                    document.querySelectorAll('.tab-content')
                        .forEach(c => c.classList.add('hidden'));

                    document.getElementById('tab-' + tab)
                        ?.classList.remove('hidden');

                    document.querySelectorAll('.tab-btn')
                        .forEach(b => b.classList.remove('tab-active'));

                    btn.classList.add('tab-active');
                });
            });
        });

        /* ================= IMAGE PREVIEW ================= */
        function previewImages(input) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';

            if (input.files && input.files.length > 0) {
                preview.classList.remove('hidden');

                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'relative';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                     class="w-full h-24 object-cover rounded-lg border">
                                <span class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-1 rounded">New</span>
                            `;
                            preview.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                preview.classList.add('hidden');
            }
        }

        /* ================= NOTIFICATION SYSTEM ================= */
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        /* ================= SWITCH TAB FUNCTION ================= */
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + tabName)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            document.querySelector(`.tab-btn[data-tab="${tabName}"]`)
                ?.classList.add('tab-active');
        }

        /* ================= QUANTITY TYPE HANDLER ================= */
        function updateQuantityLabel() {
            const quantityType = document.getElementById('quantity_type').value;
            const quantityLabel = document.getElementById('quantity_label');
            const quantityHelpText = document.getElementById('quantity_help_text');
            const quantityPlaceholder = document.getElementById('quantity_placeholder');
            
            if (quantityType === 'grouping') {
                quantityLabel.textContent = 'Minimum Guests';
                quantityHelpText.textContent = 'Grouping: Works as minimum guest count';
                quantityPlaceholder.textContent = 'Enter minimum number of guests required';
            } else if (quantityType === 'piece') {
                quantityLabel.textContent = 'Pieces';
                quantityHelpText.textContent = 'Piece: Works as individual pieces';
                quantityPlaceholder.textContent = 'Enter number of pieces';
            } else {
                quantityLabel.textContent = 'Stock Quantity';
                quantityHelpText.textContent = 'Manual: Works as stock quantity';
                quantityPlaceholder.textContent = 'Enter available stock quantity';
            }
        }
    </script>

    {{-- ===================== TAB STYLES ===================== --}}
    <style>
        /* Form Elements */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-field.input-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #621F09;
            color: #ffffff;
        }

        /* Tab Styles */
        .tab-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            background: none;
            border: none;
            border-radius: 0;
        }

        .tab-btn:hover {
            color: #374151;
            background-color: #f9fafb;
        }

        .tab-btn.tab-active {
            color: #621F09;
            border-bottom-color: #621F09;
            background-color: #eff6ff;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .grid.md\\:grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .tab-btn {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .tab-btn svg {
                width: 1rem;
                height: 1rem;
            }
        }
    </style>

@endsection
