@extends('backend::layouts.app')

@section('title', $restaurantId ? 'Edit Restaurant' : 'Create Restaurant')

@section('breadcrumbs')
@php
$title = $restaurantId ? 'Edit Restaurant' : 'Create Restaurant';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
['label' => $title],
];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

@php
$restaurantId = request('id');

@endphp

@php
// $activeTab = session('tab', $restaurantId ? 'address' : 'general');
$activeTab = session('tab', 'general');

@endphp

<div class="mx-auto">

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

            @if ($restaurantId)
            <button class="tab-btn {{ $activeTab === 'address' ? 'tab-active' : '' }}" data-tab="address">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Address Details
            </button>

            <button class="tab-btn {{ $activeTab === 'hours' ? 'tab-active' : '' }}" data-tab="hours">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Opening Hours
            </button>

            <button class="tab-btn {{ $activeTab === 'holidays' ? 'tab-active' : '' }}" data-tab="holidays">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                Holidays
            </button>

            <button class="tab-btn {{ $activeTab === 'settings' ? 'tab-active' : '' }}" data-tab="settings">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </button>

            <button class="tab-btn {{ $activeTab === 'gallery' ? 'tab-active' : '' }}" data-tab="gallery">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                Gallery
            </button>
            @endif
        </div>
    </div>


    {{-- ===================== TAB CONTENT ===================== --}}

    {{-- TAB 1 : GENERAL --}}
    <div class="tab-content {{ $activeTab !== 'general' ? 'hidden' : '' }}" id="tab-general">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Restaurant Information</h3>
                <p class="text-sm text-gray-600">Enter the basic details for your restaurant</p>
            </div>

            <form method="POST" action="{{ route('admin.restaurant.store.general') }}" enctype="multipart/form-data">
                @csrf

                @if($restaurantId)
                <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}">
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Restaurant Name <span class="text-red-500">*</span></label>
                        <input name="name" id="name" type="text"
                            class="input-field @error('name') input-error @enderror"
                            value="{{ old('name', $restaurant->name ?? '') }}" placeholder="Enter restaurant name"
                            onkeyup="generateSlug()">
                        @error('name')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slug <span class="text-red-500">*</span></label>
                        <input name="slug" id="slug" type="text" readonly
                            class="input-field bg-gray-100 cursor-not-allowed @error('slug') input-error @enderror"
                            value="{{ old('slug', $restaurant->slug ?? '') }}" placeholder="restaurant-slug">

                        <p class="text-xs text-gray-500 mt-1">
                            Auto-generated from restaurant name
                        </p>
                        @error('slug')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                        <input name="phone" type="tel" class="input-field @error('phone') input-error @enderror"
                            value="{{ old('phone', $restaurant->phone ?? '') }}" placeholder="+1 (555) 123-4567">
                        @error('phone')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        {{-- <input name="email" type="email" class="input-field @error('email') input-error @enderror"
                            value="{{ old('email') }}" placeholder="restaurant@example.com"> --}}
                        <input name="email" type="email"
                            class="input-field @error('email') input-error @enderror {{ $restaurantId ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                            value="{{ old('email', $restaurant->admin->email ?? '') }}"
                            placeholder="restaurant@example.com" {{ $restaurantId ? 'readonly' : '' }}>


                        @error('email')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="password" name="password"
                                class="input-field @error('password') input-error @enderror pr-10"
                                placeholder="Enter secure password">
                            <button type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4"
                            class="input-field @error('description') input-error @enderror"
                            placeholder="Enter a short description of the restaurant">{{ old('description', $restaurant->description ?? '') }}</textarea>
                        @error('description')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Restaurant Image --}}
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Restaurant Image</label>
                        @include('filemanager::components.file-uploader', [
                        'object_type' => 'restaurant',
                        'object_id' => $restaurantId ?? 0,
                        'name' => 'image',
                        ])
                        @error('image')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-8">
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




    {{-- TAB 2 : ADDRESS --}}
    @if ($restaurantId)
    <div class="tab-content {{ $activeTab !== 'address' ? 'hidden' : '' }}" id="tab-address">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Address Information</h3>
                <p class="text-sm text-gray-600">Provide the complete address details for your restaurant</p>
            </div>

            <form method="POST" action="{{ route('admin.restaurant.update.address', $restaurantId) }}" id="addressForm">
                @csrf

                @php
                $restaurantAddress = $restaurant->addresses()->where('address_type', 'primary')->first();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Address Line 1 <span class="text-red-500">*</span></label>
                        <input name="address_line1" type="text"
                            class="input-field @error('address_line1') input-error @enderror"
                            value="{{ old('address_line1', $restaurantAddress->address_line_1 ?? $restaurant->address_line1 ?? '') }}"
                            placeholder="Street address" required>
                        @error('address_line1')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address Line 2</label>
                        <input name="address_line2" type="text" class="input-field"
                            value="{{ old('address_line2', $restaurantAddress->address_line_2 ?? $restaurant->address_line2 ?? '') }}"
                            placeholder="Apartment, suite, etc.">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country <span class="text-red-500">*</span></label>
                        <select name="country_id" id="country_id"
                            class="input-field @error('country_id') input-error @enderror" required>
                            <option value="">Select Country</option>
                            @foreach(\Modules\Countries\Models\Country::orderBy('name')->get() as $country)
                            <option value="{{ $country->id }}" {{ old('country_id', $restaurantAddress->country_id ??
                                '') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('country_id')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">State/Province <span class="text-red-500">*</span></label>
                        <select name="state_id" id="state_id"
                            class="input-field @error('state_id') input-error @enderror" required>
                            <option value="">Select State</option>
                            @if(old('country_id', $restaurantAddress->country_id ?? ''))
                            @foreach(\Modules\Countries\Models\CountryState::where('country_id', old('country_id',
                            $restaurantAddress->country_id ?? ''))->orderBy('name')->get() as $state)
                            <option value="{{ $state->id }}" {{ old('state_id', $restaurantAddress->state_id ?? '') ==
                                $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                        @error('state_id')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">City <span class="text-red-500">*</span></label>
                        <select name="city_id" id="city_id" class="input-field @error('city_id') input-error @enderror"
                            required>
                            <option value="">Select City</option>
                            @if(old('state_id', $restaurantAddress->state_id ?? ''))
                            @foreach(\Modules\Countries\Models\City::where('state_id', old('state_id',
                            $restaurantAddress->state_id ?? ''))->orderBy('name')->get() as $city)
                            <option value="{{ $city->id }}" {{ old('city_id', $restaurantAddress->city_id ?? '') ==
                                $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                        @error('city_id')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Postal Code <span class="text-red-500">*</span></label>
                        <input name="postal_code" type="text"
                            class="input-field @error('postal_code') input-error @enderror"
                            value="{{ old('postal_code', $restaurantAddress->zip_code ?? $restaurant->postal_code ?? '') }}"
                            placeholder="ZIP/Postal code" required>
                        @error('postal_code')
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Latitude
                            <span class="text-xs text-blue-600 ml-2">(Auto-filled)</span>
                        </label>
                        <input name="latitude" id="latitude" type="text" class="input-field bg-gray-50" readonly
                            value="{{ old('latitude', $restaurantAddress->latitude ?? $restaurant->latitude ?? '') }}"
                            placeholder="Auto-generated from address">
                        <p class="text-xs text-gray-500 mt-1">Automatically geocoded from address</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Longitude
                            <span class="text-xs text-blue-600 ml-2">(Auto-filled)</span>
                        </label>
                        <input name="longitude" id="longitude" type="text" class="input-field bg-gray-50" readonly
                            value="{{ old('longitude', $restaurantAddress->longitude ?? $restaurant->longitude ?? '') }}"
                            placeholder="Auto-generated from address">
                        <p class="text-xs text-gray-500 mt-1">Automatically geocoded from address</p>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>



    {{-- TAB 3 : Working Hours --}}
    <div class="tab-content {{ $activeTab !== 'hours' ? 'hidden' : '' }}" id="tab-hours">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Working Hours</h3>
                <p class="text-sm text-gray-600">Set the operating days for your restaurant</p>
            </div>

            <form method="POST" action="{{ route('admin.restaurant.update.hours', $restaurantId) }}">
                @csrf

                <div class="mb-6">
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900">Open Whole Day</h4>
                            <p class="text-sm text-gray-600">If checked, restaurant will be open 24/7 for all days.</p>
                        </div>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_open_whole_day"
                                value="1"
                                class="w-4 h-4 whole-day-checkbox"
                                {{ !empty($hours['is_open_whole_day']) ? 'checked' : '' }}>
                            <span class="ml-2 font-medium">24/7 Open</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-4" id="hours-container">
                    @foreach ($hours as $day => $data)
                    @if ($day !== 'is_open_whole_day')
                    @php
                    $dayHours = array_merge(['open' => false, 'from' => '09:00', 'to' => '22:00'], is_array($data) ? $data : []);
                    @endphp
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                        <span class="capitalize font-medium w-24">{{ $day }}</span>

                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="hours[{{ $day }}][open]"
                                    value="1"
                                    class="day-checkbox"
                                    {{ $dayHours['open'] ? 'checked' : '' }}>
                                <span class="ml-2">Open</span>
                            </label>

                            <input
                                type="time"
                                name="hours[{{ $day }}][from]"
                                value="{{ $dayHours['from'] }}"
                                class="border rounded px-2 py-1 time-input">

                            <span>to</span>

                            <input
                                type="time"
                                name="hours[{{ $day }}][to]"
                                value="{{ $dayHours['to'] }}"
                                class="border rounded px-2 py-1 time-input">
                        </div>
                    </div>
                    @endif
                    @endforeach

                </div>

                <div class="flex justify-between items-center mt-8">
                    <div class="flex space-x-2">
                        <button type="button" class="btn btn-secondary" onclick="toggleAllDays(true)">
                            Select All
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleAllDays(false)">
                            Deselect All
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Save Working Hours
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- TAB 4 : HOLIDAYS --}}
    <div class="tab-content {{ $activeTab !== 'holidays' ? 'hidden' : '' }}" id="tab-holidays">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Holiday Management</h3>
                <p class="text-sm text-gray-600">Set dates when your restaurant will be closed</p>
            </div>

            <form method="POST" action="{{ route('admin.restaurant.update.holidays', $restaurantId) }}">
                @csrf

                <div class="space-y-6">
                    {{-- Add New Holiday Section --}}
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Add New Holiday</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="new_holiday_date" class="input-field"
                                    min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Reason</label>
                                <input type="text" name="new_holiday_reason" class="input-field"
                                    placeholder="e.g., Christmas Day, Staff Training">
                            </div>
                            <div class="form-group flex items-end">
                                <button type="button" onclick="addHoliday()" class="btn btn-secondary w-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Holiday
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Existing Holidays List --}}
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-4">Scheduled Holidays</h4>
                        <div id="holidays-list" class="space-y-3">
                            @forelse($holidays ?? [] as $index => $holiday)
                            <div
                                class="holiday-item flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ date('F j, Y', strtotime($holiday['date'])) }}</p>
                                        @if ($holiday['reason'])
                                        <p class="text-sm text-gray-600">{{ $holiday['reason'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" onclick="removeHoliday({{ $index }})"
                                    class="text-red-600 hover:text-red-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                                <input type="hidden" name="holidays[{{ $index }}][date]" value="{{ $holiday['date'] }}">
                                <input type="hidden" name="holidays[{{ $index }}][reason]"
                                    value="{{ $holiday['reason'] ?? '' }}">
                            </div>
                            @empty
                            <div id="no-holidays" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p>No holidays scheduled</p>
                                <p class="text-sm">Add dates when your restaurant will be closed</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Quick Holiday Templates --}}
                    {{-- <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Quick Add Common Holidays</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button type="button" onclick="addQuickHoliday('{{ date('Y') }}-01-01', 'New Year\'s Day')"
                                class="btn btn-outline">New Year</button>
                            <button type="button" onclick="addQuickHoliday('{{ date('Y') }}-07-04', 'Independence Day')"
                                class="btn btn-outline">July 4th</button>
                            <button type="button" onclick="addQuickHoliday('{{ date('Y') }}-12-25', 'Christmas Day')"
                                class="btn btn-outline">Christmas</button>
                            <button type="button" onclick="addQuickHoliday('{{ date('Y') }}-11-28', 'Thanksgiving')"
                                class="btn btn-outline">Thanksgiving</button>
                        </div>
                    </div> --}}
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Save Holidays
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- TAB 5 : SETTINGS --}}
    <div class="tab-content {{ $activeTab !== 'settings' ? 'hidden' : '' }}" id="tab-settings">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

            <form method="POST" action="{{ route('admin.restaurant.update.settings', $restaurantId) }}">
                @csrf

                <div class="space-y-8">
                    {{-- HEADER --}}
                    <div class="mb-6 flex items-start justify-between gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                Restaurant Settings
                            </h3>
                            <p class="text-sm text-gray-600">
                                Configure your restaurant preferences and services
                            </p>
                        </div>

                        {{-- Status Dropdown (Right Side) --}}
                        <div class="min-w-[220px]">
                            <label class="form-label text-sm">Restaurant Status</label>
                            <select name="status" class="input-field">
                                <option value="draft" {{ ($restaurant->status ?? 'draft') === 'draft' ? 'selected' : ''
                                    }}>
                                    Draft
                                </option>
                                <option value="active" {{ ($restaurant->status ?? 'draft') === 'active' ? 'selected' :
                                    '' }}>
                                    Publish (Active)
                                </option>
                                <option value="inactive" {{ ($restaurant->status ?? 'draft') === 'inactive' ? 'selected'
                                    : '' }}>
                                    Inactive
                                </option>
                            </select>

                            <p class="text-xs text-gray-500 mt-1">
                                Draft · Publish · Inactive
                            </p>
                        </div>
                    </div>

                    {{-- Food Type Section --}}
                    <div class="border-b border-gray-200 pb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Food Type</h4>
                        <div class="form-group">
                            <label class="form-label">Select Food Category</label>
                            <select name="food_type" class="input-field">
                                <option value="all" {{ ($restaurant->food_type ?? 'all') === 'all' ? 'selected' : '' }}>
                                    All Types
                                </option>
                                <option value="veg" {{ ($restaurant->food_type ?? 'all') === 'veg' ? 'selected' : '' }}>
                                    Vegetarian Only
                                </option>
                                <option value="non_veg" {{ ($restaurant->food_type ?? 'all') === 'non_veg' ? 'selected'
                                    : '' }}>
                                    Non-Vegetarian
                                </option>
                                <option value="vegan" {{ ($restaurant->food_type ?? 'all') === 'vegan' ? 'selected' : ''
                                    }}>
                                    Vegan Only
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Service Options Section --}}
                    <div class="border-b border-gray-200 pb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Service Options</h4>
                        <div class="space-y-4">
                            <label
                                class="flex items-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="checkbox" name="is_takeaway" value="1" {{ $restaurant->is_takeaway ?? false
                                ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500
                                focus:ring-2">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Enable Takeaway</span>
                                    <p class="text-xs text-gray-500">Allow customers to pick up orders</p>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="checkbox" name="is_delivery" value="1" {{ $restaurant->is_delivery ?? false
                                ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500
                                focus:ring-2">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Enable Delivery</span>
                                    <p class="text-xs text-gray-500">Offer delivery service to customers</p>
                                </div>
                            </label>

                            <label
                                class="flex items-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition-colors">
                                <input type="checkbox" name="is_accepting_orders" value="1" {{
                                    $restaurant->is_accepting_orders ?? false ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500
                                focus:ring-2">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Accept Orders</span>
                                    <p class="text-xs text-gray-500">Currently accepting new orders</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Order Management Section --}}
                    {{-- Order Management Section --}}
                    <div class="border-b border-gray-200 pb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Order Management</h4>

                        <div class="form-group max-w-md">
                            <label class="form-label">Order Edit / Refund Time Limit</label>

                            <div class="relative">
                                <input type="number" name="order_time_limit" class="input-field pr-12"
                                    value="{{ old('order_time_limit', $restaurant->order_time_limit ?? 30) }}" min="0"
                                    max="1440" placeholder="30">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">mins</span>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-1">
                                Customers can edit orders or request refunds within this time limit (in minutes)
                            </p>
                        </div>

                        {{-- Cancellation Policy Section --}}
                        <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Order Cancellation Policy
                            </h4>

                            {{-- Allow Cancellation Toggle --}}
                            <div class="form-group">
                                <div
                                    class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
                                    <div class="flex-1">
                                        <label class="font-medium text-gray-900">Allow Customer Cancellations</label>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Enable customers to cancel their orders based on your policy
                                        </p>
                                    </div>
                                    <div class="ml-4">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="allow_order_cancellation" value="1"
                                                class="sr-only peer" {{ old('allow_order_cancellation',
                                                $restaurant->allow_order_cancellation ?? true) ? 'checked' : '' }}>
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Cancellation Time Limit - HIDDEN (Status based only) --}}
                            <input type="hidden" name="cancellation_time_limit" value="15">
                            {{--
                            <div class="form-group">
                                <label class="form-label">Cancellation Time Limit</label>
                                <div class="relative max-w-md">
                                    <input type="number" name="cancellation_time_limit" class="input-field pr-16"
                                        value="{{ old('cancellation_time_limit', $restaurant->cancellation_time_limit ?? 15) }}"
                                        min="0" max="120" placeholder="15">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">minutes</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    How many minutes after order placement can customers cancel? (0-120 minutes)
                                </p>
                            </div>
                            --}}

                            {{-- Cancellable Statuses --}}
                            <div class="form-group">
                                <label class="form-label">Cancellable Order Statuses</label>
                                <p class="text-xs text-gray-500 mb-3">
                                    Select which order statuses allow customer cancellation
                                </p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @php
                                    $allStatuses = fn_get_order_statuses('O');
                                    $selectedStatuses = old('cancellable_statuses', $restaurant->cancellable_statuses ??
                                    ['P', 'O', 'H']);
                                    @endphp
                                    @foreach($allStatuses as $status)
                                    <label
                                        class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors">
                                        <input type="checkbox" name="cancellable_statuses[]"
                                            value="{{ $status->status_code }}"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                            {{ in_array($status->status_code, $selectedStatuses) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $status->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Policy Preview --}}
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h5 class="text-sm font-medium text-blue-900 mb-1">Policy Preview</h5>
                                        <p class="text-sm text-blue-700" id="policy-preview-text">
                                            @if(isset($restaurant) && $restaurant->id)
                                            {{ $restaurant->getCancellationPolicyText() }}
                                            @else
                                            You can cancel your order within 15 minutes of placement.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Save Button --}}
                    <div class="flex justify-end mt-8">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TAB 6 : GALLERY --}}
    <div class="tab-content {{ $activeTab !== 'gallery' ? 'hidden' : '' }}" id="tab-gallery">
        @include('restaurant::admin.partials.gallery', ['restaurantId' => $restaurantId])
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

            /* ================= PASSWORD TOGGLE ================= */
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.closest('.relative')?.querySelector('input');
                    if (!input) return;

                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';

                    const icon = this.querySelector('svg');
                    icon.classList.toggle('text-blue-500', isPassword);
                    icon.classList.toggle('text-gray-400', !isPassword);
                });
            });
        });

        /* ================= WORKING HOURS ================= */
        function toggleWholeDayHours() {
            const checkbox = document.querySelector('.whole-day-checkbox');
            const hoursContainer = document.getElementById('hours-container');

            if (!checkbox || !hoursContainer) {
                return;
            }

            const dayCheckboxes = hoursContainer.querySelectorAll('.day-checkbox');
            const timeInputs = hoursContainer.querySelectorAll('.time-input');

            if (checkbox.checked) {
                dayCheckboxes.forEach(cb => {
                    cb.checked = true;
                    cb.disabled = true;
                });

                timeInputs.forEach(input => {
                    input.value = input.name.includes('[from]') ? '00:00' : '23:59';
                    input.disabled = true;
                });

                hoursContainer.style.opacity = '0.6';
                hoursContainer.style.pointerEvents = 'none';
            } else {
                dayCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });

                timeInputs.forEach(input => {
                    input.disabled = false;
                });

                hoursContainer.style.opacity = '1';
                hoursContainer.style.pointerEvents = 'auto';
            }
        }

        function toggleAllDays(checked) {
            document.querySelectorAll('.day-checkbox')
                .forEach(cb => cb.checked = checked);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const wholeDayCheckbox = document.querySelector('.whole-day-checkbox');
            if (wholeDayCheckbox) {
                wholeDayCheckbox.addEventListener('change', toggleWholeDayHours);
                toggleWholeDayHours();
            }
        });

        /* ================= HOLIDAY MANAGEMENT ================= */
        let holidayIndex = {{ count($holidays ?? []) }};

        function addHoliday() {
            const dateInput = document.querySelector('input[name="new_holiday_date"]');
            const reasonInput = document.querySelector('input[name="new_holiday_reason"]');

            if (!dateInput.value) {
                alert('Please select a date');
                return;
            }

            const date = dateInput.value;
            const reason = reasonInput.value || '';

            // Check if holiday already exists
            const existingHolidays = document.querySelectorAll('input[name*="[date]"]');
            for (let input of existingHolidays) {
                if (input.value === date) {
                    alert('Holiday for this date already exists');
                    return;
                }
            }

            addHolidayToList(date, reason);

            // Clear inputs
            dateInput.value = '';
            reasonInput.value = '';
        }

        function addQuickHoliday(date, reason) {
            // Check if holiday already exists
            const existingHolidays = document.querySelectorAll('input[name*="[date]"]');
            for (let input of existingHolidays) {
                if (input.value === date) {
                    alert('Holiday for this date already exists');
                    return;
                }
            }

            addHolidayToList(date, reason);
        }

        function addHolidayToList(date, reason) {
            const holidaysList = document.getElementById('holidays-list');
            const noHolidays = document.getElementById('no-holidays');

            if (noHolidays) {
                noHolidays.remove();
            }

            const formattedDate = new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            const holidayHtml = `
                <div class="holiday-item flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">${formattedDate}</p>
                            ${reason ? `<p class="text-sm text-gray-600">${reason}</p>` : ''}
                        </div>
                    </div>
                    <button type="button" onclick="removeHoliday(${holidayIndex})" 
                            class="text-red-600 hover:text-red-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <input type="hidden" name="holidays[${holidayIndex}][date]" value="${date}">
                    <input type="hidden" name="holidays[${holidayIndex}][reason]" value="${reason}">
                </div>
            `;

            holidaysList.insertAdjacentHTML('beforeend', holidayHtml);
            holidayIndex++;
        }

        function removeHoliday(index) {
            const holidayItems = document.querySelectorAll('.holiday-item');
            const targetItem = Array.from(holidayItems).find(item => {
                const hiddenInput = item.querySelector(`input[name="holidays[${index}][date]"]`);
                return hiddenInput !== null;
            });

            if (targetItem) {
                targetItem.remove();
            }

            // Show "no holidays" message if list is empty
            const remainingItems = document.querySelectorAll('.holiday-item');
            if (remainingItems.length === 0) {
                const holidaysList = document.getElementById('holidays-list');
                holidaysList.innerHTML = `
                    <div id="no-holidays" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>No holidays scheduled</p>
                        <p class="text-sm">Add dates when your restaurant will be closed</p>
                    </div>
                `;
            }
        }

        function generateSlug() {
            // If editing existing restaurant, do not overwrite slug
            @if ($restaurantId)
                return;
            @endif

            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            if (!nameInput || !slugInput) return;

            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
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

<script>
    // Cascading dropdowns for Country -> State -> City
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('country_id');
            const stateSelect = document.getElementById('state_id');
            const citySelect = document.getElementById('city_id');

            if (countrySelect) {
                countrySelect.addEventListener('change', function() {
                    const countryId = this.value;
                    stateSelect.innerHTML = '<option value="">Loading...</option>';
                    citySelect.innerHTML = '<option value="">Select City</option>';

                    if (countryId) {
                        console.log('Loading states for country:', countryId);
                        
                        fetch(`/api/countries/states-by-country/${countryId}`)
                            .then(response => {
                                console.log('States API response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('States API response data:', data);
                                stateSelect.innerHTML = '<option value="">Select State</option>';
                                
                                // Check if data has states array
                                const states = data.states || data;
                                
                                if (states && Array.isArray(states) && states.length > 0) {
                                    console.log(`Loading ${states.length} states`);
                                    states.forEach(state => {
                                        const option = document.createElement('option');
                                        option.value = state.id;
                                        option.textContent = state.name;
                                        stateSelect.appendChild(option);
                                    });
                                } else {
                                    console.warn('No states found in response');
                                    stateSelect.innerHTML = '<option value="">No states found</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading states:', error);
                                stateSelect.innerHTML = '<option value="">Error loading states</option>';
                            });
                    } else {
                        stateSelect.innerHTML = '<option value="">Select State</option>';
                    }
                });
            }

            if (stateSelect) {
                stateSelect.addEventListener('change', function() {
                    const stateId = this.value;
                    citySelect.innerHTML = '<option value="">Loading...</option>';

                    if (stateId) {
                        console.log('Loading cities for state:', stateId);
                        
                        fetch(`/api/countries/cities-by-state/${stateId}`)
                            .then(response => {
                                console.log('Cities API response status:', response.status);
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Cities API response data:', data);
                                citySelect.innerHTML = '<option value="">Select City</option>';
                                
                                // Check if data has cities array
                                const cities = data.cities || data;
                                
                                if (cities && Array.isArray(cities) && cities.length > 0) {
                                    console.log(`Loading ${cities.length} cities`);
                                    cities.forEach(city => {
                                        const option = document.createElement('option');
                                        option.value = city.id;
                                        option.textContent = city.name;
                                        citySelect.appendChild(option);
                                    });
                                } else {
                                    console.warn('No cities found in response');
                                    citySelect.innerHTML = '<option value="">No cities found</option>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading cities:', error);
                                citySelect.innerHTML = '<option value="">Error loading cities</option>';
                            });
                    } else {
                        citySelect.innerHTML = '<option value="">Select City</option>';
                    }
                });
            }
        });
</script>

<!-- Google Maps Places API -->
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ fn_get_setting('general.google_maps_api_key') }}&libraries=places">
</script>

<!-- Google Places Autocomplete & Auto Geocoding Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addressForm = document.getElementById('addressForm');
        
        if (!addressForm) return;

        const addressLine1 = document.querySelector('input[name="address_line1"]');
        const addressLine2 = document.querySelector('input[name="address_line2"]');
        const postalCode = document.querySelector('input[name="postal_code"]');
        const latitudeInput = document.querySelector('input[name="latitude"]');
        const longitudeInput = document.querySelector('input[name="longitude"]');
        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');

        // Initialize Google Places Autocomplete
        if (typeof google !== 'undefined' && google.maps && google.maps.places && addressLine1) {
            const autocomplete = new google.maps.places.Autocomplete(addressLine1, {
                types: ['address'],
                fields: ['address_components', 'geometry', 'formatted_address']
            });

            // When user selects an address from autocomplete
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();

                if (!place.geometry) {
                    console.warn('No geometry found for selected place');
                    return;
                }

                // Extract address components
                const addressComponents = place.address_components;
                let streetNumber = '';
                let route = '';
                let locality = '';
                let adminArea1 = '';
                let country = '';
                let postalCodeValue = '';

                addressComponents.forEach(component => {
                    const types = component.types;
                    
                    if (types.includes('street_number')) {
                        streetNumber = component.long_name;
                    }
                    if (types.includes('route')) {
                        route = component.long_name;
                    }
                    if (types.includes('locality')) {
                        locality = component.long_name;
                    }
                    if (types.includes('administrative_area_level_1')) {
                        adminArea1 = component.long_name;
                    }
                    if (types.includes('country')) {
                        country = component.long_name;
                    }
                    if (types.includes('postal_code')) {
                        postalCodeValue = component.long_name;
                    }
                });

                // Update address line 1
                const fullStreetAddress = `${streetNumber} ${route}`.trim();
                if (fullStreetAddress) {
                    addressLine1.value = fullStreetAddress;
                }

                // Update postal code
                if (postalCodeValue && postalCode) {
                    postalCode.value = postalCodeValue;
                }

                // Update coordinates
                const location = place.geometry.location;
                latitudeInput.value = location.lat().toFixed(8);
                longitudeInput.value = location.lng().toFixed(8);

                // Visual feedback
                latitudeInput.classList.add('border-green-500');
                longitudeInput.classList.add('border-green-500');
                
                setTimeout(() => {
                    latitudeInput.classList.remove('border-green-500');
                    longitudeInput.classList.remove('border-green-500');
                }, 2000);

                // Try to match and select country, state, city from dropdowns
                matchAndSelectDropdowns(country, adminArea1, locality);

                console.log('Address autocompleted:', place.formatted_address);
            });
        }

        // Function to match and select values in dropdowns
        function matchAndSelectDropdowns(countryName, stateName, cityName) {
            // Match country
            if (countryName && countrySelect) {
                const countryOptions = Array.from(countrySelect.options);
                const matchedCountry = countryOptions.find(opt => 
                    opt.text.toLowerCase() === countryName.toLowerCase()
                );
                
                if (matchedCountry) {
                    countrySelect.value = matchedCountry.value;
                    countrySelect.dispatchEvent(new Event('change'));
                    
                    // Wait for states to load, then match state
                    setTimeout(() => {
                        if (stateName && stateSelect) {
                            const stateOptions = Array.from(stateSelect.options);
                            const matchedState = stateOptions.find(opt => 
                                opt.text.toLowerCase().includes(stateName.toLowerCase()) ||
                                stateName.toLowerCase().includes(opt.text.toLowerCase())
                            );
                            
                            if (matchedState) {
                                stateSelect.value = matchedState.value;
                                stateSelect.dispatchEvent(new Event('change'));
                                
                                // Wait for cities to load, then match city
                                setTimeout(() => {
                                    if (cityName && citySelect) {
                                        const cityOptions = Array.from(citySelect.options);
                                        const matchedCity = cityOptions.find(opt => 
                                            opt.text.toLowerCase() === cityName.toLowerCase()
                                        );
                                        
                                        if (matchedCity) {
                                            citySelect.value = matchedCity.value;
                                        }
                                    }
                                }, 500);
                            }
                        }
                    }, 500);
                }
            }
        }

        // Debounce function to avoid too many API calls
        let geocodeTimeout;
        function debounceGeocode() {
            clearTimeout(geocodeTimeout);
            geocodeTimeout = setTimeout(geocodeAddress, 1000);
        }

        // Attach listeners to address fields for manual entry
        if (postalCode) postalCode.addEventListener('blur', debounceGeocode);
        if (countrySelect) countrySelect.addEventListener('change', debounceGeocode);
        if (stateSelect) stateSelect.addEventListener('change', debounceGeocode);
        if (citySelect) citySelect.addEventListener('change', debounceGeocode);

        function geocodeAddress() {
            // Only geocode if lat/lng are empty
            if (latitudeInput.value && longitudeInput.value) {
                return;
            }

            // Build address string
            const addressParts = [];
            
            if (addressLine1 && addressLine1.value) {
                addressParts.push(addressLine1.value);
            }
            
            if (addressLine2 && addressLine2.value) {
                addressParts.push(addressLine2.value);
            }

            if (citySelect && citySelect.selectedOptions[0] && citySelect.value) {
                addressParts.push(citySelect.selectedOptions[0].text);
            }

            if (stateSelect && stateSelect.selectedOptions[0] && stateSelect.value) {
                addressParts.push(stateSelect.selectedOptions[0].text);
            }

            if (postalCode && postalCode.value) {
                addressParts.push(postalCode.value);
            }

            if (countrySelect && countrySelect.selectedOptions[0] && countrySelect.value) {
                addressParts.push(countrySelect.selectedOptions[0].text);
            }

            const fullAddress = addressParts.join(', ');

            if (!fullAddress || addressParts.length < 2) {
                return; // Need at least 2 address components
            }

            // Use Google Maps Geocoding API
            if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                
                geocoder.geocode({ address: fullAddress }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        const location = results[0].geometry.location;
                        
                        // Update latitude and longitude fields
                        latitudeInput.value = location.lat().toFixed(8);
                        longitudeInput.value = location.lng().toFixed(8);
                        
                        // Visual feedback
                        latitudeInput.classList.add('border-green-500');
                        longitudeInput.classList.add('border-green-500');
                        
                        setTimeout(() => {
                            latitudeInput.classList.remove('border-green-500');
                            longitudeInput.classList.remove('border-green-500');
                        }, 2000);
                        
                        console.log('Geocoded address:', fullAddress, 'to', location.lat(), location.lng());
                    } else {
                        console.warn('Geocoding failed:', status);
                    }
                });
            }
        }

        // Allow manual override - if user manually enters lat/lng, don't auto-geocode
        if (latitudeInput) {
            latitudeInput.addEventListener('input', function() {
                if (this.value) {
                    // User is manually entering coordinates, disable auto-geocoding
                    postalCode?.removeEventListener('blur', debounceGeocode);
                }
            });
        }
    });
</script>
@endsection
