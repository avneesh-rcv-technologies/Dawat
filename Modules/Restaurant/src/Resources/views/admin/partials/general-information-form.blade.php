@php
    $restaurantAddress = isset($restaurant) && $restaurant
        ? $restaurant->addresses()->where('address_type', 'primary')->first()
        : null;
    $selectedCountryId = old('country_id', $restaurant->country_id ?? $restaurantAddress->country_id ?? '');
    $selectedStateId = old('state_id', $restaurant->state_id ?? $restaurantAddress->state_id ?? '');
    $selectedCityId = old('city_id', $restaurant->city_id ?? $restaurantAddress->city_id ?? '');
    $latitudeValue = old('latitude', $restaurantAddress->latitude ?? $restaurant->latitude ?? '');
    $longitudeValue = old('longitude', $restaurantAddress->longitude ?? $restaurant->longitude ?? '');
    $descriptionValue = old('description', $restaurant->description ?? '');
@endphp

<style>
    .gi { --gi-brand: #ff6210; --gi-brand-soft: #fff4ed; --gi-brand-ring: rgba(255, 98, 16, .18);
          --gi-ink: #111827; --gi-muted: #6b7280; --gi-line: #e5e7eb; --gi-danger: #dc2626; }
    .gi.section-card { background: #fff; border: 1px solid var(--gi-line); border-radius: 14px; box-shadow: none; overflow: hidden; }
    .gi .section-card-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 24px; background: #fff; border-bottom: 1px solid var(--gi-line); }
    .gi-header-main { display: flex; align-items: center; gap: 14px; }
    .gi-icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; background: var(--gi-brand-soft); color: var(--gi-brand); flex-shrink: 0; }
    .gi .section-card-title { font-size: 16px; font-weight: 600; color: var(--gi-ink); line-height: 1.3; }
    .gi .section-card-desc { font-size: 13px; color: var(--gi-muted); margin-top: 2px; }
    .gi .section-card-body { padding: 0 24px 24px; }

    /* Settings-style rows: description on the left, fields on the right */
    .gi-section { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; padding: 24px 0; border-bottom: 1px solid #f1f2f4; }
    .gi-section:last-of-type { border-bottom: none; }
    @media (min-width: 1024px) { .gi-section { grid-template-columns: 240px minmax(0, 1fr); gap: 32px; } }
    .gi-section-title { font-size: 14px; font-weight: 600; color: var(--gi-ink); }
    .gi-section-desc { font-size: 13px; color: var(--gi-muted); margin-top: 4px; line-height: 1.5; }

    .gi-fields { display: grid; grid-template-columns: minmax(0, 1fr); gap: 18px; }
    @media (min-width: 768px) {
        .gi-fields.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .gi-fields.cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .gi-fields .span-all { grid-column: 1 / -1; }
    }
    .gi .form-group { margin-bottom: 0; }
    .gi .form-label { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .gi .form-label .req { color: var(--gi-danger); }

    .gi .input-field { height: 44px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; color: var(--gi-ink); background-color: #fff; }
    .gi textarea.input-field { height: auto; padding: 12px 14px; line-height: 1.55; resize: vertical; min-height: 112px; }
    .gi select.input-field { padding-right: 36px; }
    .gi .input-field:focus { border-color: var(--gi-brand); box-shadow: 0 0 0 3px var(--gi-brand-ring); }
    .gi .input-field.input-error { border-color: var(--gi-danger); box-shadow: 0 0 0 3px rgba(220, 38, 38, .12); }
    .gi .input-field[readonly] { background: #f9fafb; color: #4b5563; cursor: default; }
    .gi .input-field[readonly]:focus { border-color: #d1d5db; box-shadow: none; }
    .gi .input-field.border-green-500 { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, .15); }
    .gi .error-message { color: var(--gi-danger); font-size: 12px; margin-top: 6px; }
    .gi-help { font-size: 12px; color: var(--gi-muted); margin-top: 6px; display: flex; align-items: center; gap: 4px; }
    .gi-help-row { display: flex; justify-content: space-between; gap: 12px; }

    .gi-affix { position: relative; }
    .gi-affix > svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
    .gi-affix > .input-field { padding-left: 40px; }
    .gi-lock { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
    .gi-affix .input-field[readonly] { padding-right: 36px; }

    .gi-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #f3f4f6; color: #4b5563; }
    .gi-badge.is-brand { background: var(--gi-brand-soft); color: var(--gi-brand); }
    .gi-geo-status { font-size: 13px; color: var(--gi-muted); }
    .gi-geo-status[data-tone="success"] { color: #15803d; }
    .gi-geo-status[data-tone="warning"] { color: #b45309; }
    .gi-geo-status[data-tone="error"] { color: var(--gi-danger); }
    .gi-map-link[hidden] { display: none; }
    .gi-map-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--gi-brand); text-decoration: none; }
    .gi-map-link:hover { text-decoration: underline; }

    .gi .section-card-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin: 0 -24px -24px; padding: 16px 24px; background: #fafafa; border-top: 1px solid var(--gi-line); }
    .gi-footer-note { font-size: 13px; color: var(--gi-muted); }
    .gi-footer-note.is-dirty { color: #b45309; font-weight: 500; }
    .gi-btn { height: 44px; padding: 0 20px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border: 1px solid transparent; background: var(--gi-brand); color: #fff; cursor: pointer; transition: background-color .15s, box-shadow .15s; }
    .gi-btn:hover { background: #e85508; }
    .gi-btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--gi-brand-ring); }
    .gi fieldset:disabled { opacity: .7; }
    .gi fieldset:disabled .gi-btn { display: none; }

    @media (max-width: 640px) {
        .gi .section-card-header { padding: 16px; }
        .gi .section-card-body { padding: 0 16px 16px; }
        .gi .section-card-footer { margin: 0 -16px -16px; padding: 16px; }
        .gi-btn { width: 100%; }
    }
</style>

{{-- ===== RESTAURANT INFORMATION ===== --}}
<div class="section-card gi">
    <div class="section-card-header">
        <div class="gi-header-main">
            <span class="gi-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </span>
            <div>
                <h3 class="section-card-title">Restaurant Information</h3>
                <p class="section-card-desc">Basic details customers see about the restaurant</p>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <form method="POST" action="{{ $generalAction ?? route('admin.restaurant.store.general') }}"
            enctype="multipart/form-data" data-track-changes>
            @csrf
            <fieldset @disabled(!($canUpdateGeneral ?? true))>
            @if($restaurantId)
                <input type="hidden" name="restaurant_id" value="{{ $restaurantId }}">
            @endif

            {{-- Basic details --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">Basic details</h4>
                    <p class="gi-section-desc">The restaurant's display name. The slug is used in its web address.</p>
                </div>
                <div class="gi-fields cols-2">
                    <div class="form-group">
                        <label for="name" class="form-label">Restaurant name <span class="req">*</span></label>
                        <input name="name" id="name" type="text"
                            class="input-field @error('name') input-error @enderror"
                            value="{{ old('name', $restaurant->name ?? '') }}"
                            placeholder="e.g. Dream Restaurant" onkeyup="generateSlug()">
                        @error('name')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="slug" class="form-label">Slug <span class="req">*</span></label>
                        <div class="gi-affix">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <input name="slug" id="slug" type="text" readonly
                                class="input-field @error('slug') input-error @enderror"
                                value="{{ old('slug', $restaurant->slug ?? '') }}" placeholder="restaurant-slug">
                            <svg class="gi-lock" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <p class="gi-help">{{ $restaurantId ? 'The slug cannot be changed after creation.' : 'Generated from the restaurant name.' }}</p>
                        @error('slug')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">Contact</h4>
                    <p class="gi-section-desc">How customers and the Dawat team can reach the restaurant.</p>
                </div>
                <div class="gi-fields cols-2">
                    <div class="form-group">
                        <label for="gi-phone" class="form-label">Phone number <span class="req">*</span></label>
                        <div class="gi-affix">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <input id="gi-phone" name="phone" type="tel" class="input-field @error('phone') input-error @enderror"
                                value="{{ old('phone', $restaurant->phone ?? '') }}"
                                placeholder="+1 (555) 123-4567">
                        </div>
                        @error('phone')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="gi-email" class="form-label">Email address <span class="req">*</span></label>
                        <div class="gi-affix">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input id="gi-email" name="email" type="email"
                                class="input-field @error('email') input-error @enderror"
                                value="{{ old('email', $restaurant->admin->email ?? '') }}"
                                placeholder="restaurant@example.com" {{ $restaurantId ? 'readonly' : '' }}>
                            @if($restaurantId)
                                <svg class="gi-lock" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            @endif
                        </div>
                        @if($restaurantId)
                            <p class="gi-help">This is the login email and cannot be changed here.</p>
                        @endif
                        @error('email')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    @if($showPasswordField ?? true)
                        <div class="form-group span-all">
                            <label for="gi-password" class="form-label">Password @unless($restaurantId)<span class="req">*</span>@endunless</label>
                            <input id="gi-password" type="password" name="password" autocomplete="new-password"
                                class="input-field @error('password') input-error @enderror"
                                placeholder="{{ $restaurantId ? 'Leave blank to keep current password' : 'Enter a secure password' }}">
                            @error('password')<p class="error-message">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- About --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">About</h4>
                    <p class="gi-section-desc">A short description and a cover image for the restaurant's page.</p>
                </div>
                <div class="gi-fields">
                    <div class="form-group">
                        <label for="gi-description" class="form-label">Description</label>
                        <textarea id="gi-description" name="description" rows="4"
                            class="input-field @error('description') input-error @enderror"
                            placeholder="Tell customers what makes the restaurant special: cuisine, specialities, ambience…">{{ $descriptionValue }}</textarea>
                        <div class="gi-help-row">
                            @error('description')<p class="error-message">{{ $message }}</p>@else<span></span>@enderror
                            <p class="gi-help" data-char-count-for="gi-description">{{ mb_strlen($descriptionValue) }} characters</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Restaurant image</label>
                        @include('filemanager::components.file-uploader', [
                            'object_type' => 'restaurant',
                            'is_update' => $canUpdateGeneral ?? true,
                            'is_delete' => $canDeleteGeneralImage ?? true,
                            'object_id' => $restaurantId ?? 0,
                            'name' => 'image',
                            'is_multiple' => false,
                            'accept' => 'image/jpeg,image/png',
                            'span_text' => 'Drag and drop an image here or click to select a file. (JPEG, PNG)',
                        ])
                        @error('image')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="section-card-footer">
                <p class="gi-footer-note" data-save-note>{{ $restaurantId ? 'All changes saved.' : 'Fill in the required fields to continue.' }}</p>
                <button type="submit" class="gi-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $restaurantId ? 'Save changes' : 'Save & continue' }}
                </button>
            </div>
            </fieldset>
        </form>
    </div>
</div>


{{-- ===== ADDRESS INFORMATION ===== --}}
<div class="section-card gi">
    <div class="section-card-header">
        <div class="gi-header-main">
            <span class="gi-icon" aria-hidden="true">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
            <div>
                <h3 class="section-card-title">Address Information</h3>
                <p class="section-card-desc">Where the restaurant is located, used for delivery and pickup</p>
            </div>
        </div>
    </div>

    <div class="section-card-body">
        <form
            action="{{ $addressAction ?? (isset($restaurant) && $restaurant->id ? route('admin.restaurant.update.address', $restaurant->id) : '#') }}"
            method="POST" id="addressForm" data-track-changes>
            @csrf
            <fieldset @disabled(!($canUpdateAddress ?? true))>

            {{-- Street --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">Street address</h4>
                    <p class="gi-section-desc">The building and street where the restaurant operates.</p>
                </div>
                <div class="gi-fields cols-2">
                    <div class="form-group span-all">
                        <label for="gi-address1" class="form-label">Address line 1 <span class="req">*</span></label>
                        <input id="gi-address1" name="address_line1" type="text"
                            class="input-field @error('address_line1') input-error @enderror"
                            value="{{ old('address_line1', $restaurantAddress->address_line_1 ?? $restaurant->address_line1 ?? '') }}"
                            placeholder="Street address" required autocomplete="address-line1">
                        @error('address_line1')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="gi-address2" class="form-label">Address line 2 <span class="gi-badge">Optional</span></label>
                        <input id="gi-address2" name="address_line2" type="text" class="input-field"
                            value="{{ old('address_line2', $restaurantAddress->address_line_2 ?? $restaurant->address_line2 ?? '') }}"
                            placeholder="Apartment, suite, floor…" autocomplete="address-line2">
                    </div>

                    <div class="form-group">
                        <label for="gi-postal" class="form-label">Postal code <span class="req">*</span></label>
                        <input id="gi-postal" name="postal_code" type="text"
                            class="input-field @error('postal_code') input-error @enderror"
                            value="{{ old('postal_code', $restaurantAddress->zip_code ?? $restaurant->postal_code ?? '') }}"
                            placeholder="ZIP / postal code" required autocomplete="postal-code">
                        @error('postal_code')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Region --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">Region</h4>
                    <p class="gi-section-desc">Pick the country first; states and cities load based on your choice.</p>
                </div>
                <div class="gi-fields cols-3">
                    <div class="form-group">
                        <label for="country_id" class="form-label">Country <span class="req">*</span></label>
                        <select name="country_id" id="country_id"
                            class="input-field @error('country_id') input-error @enderror" required>
                            <option value="">Select country</option>
                            @foreach(\Modules\Countries\Models\Country::orderBy('name')->get() as $country)
                                <option value="{{ $country->id }}" {{ $selectedCountryId == $country->id ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="state_id" class="form-label">State / province <span class="req">*</span></label>
                        <select name="state_id" id="state_id"
                            class="input-field @error('state_id') input-error @enderror" required>
                            <option value="">Select state</option>
                            @if($selectedCountryId)
                                @foreach(\Modules\Countries\Models\CountryState::where('country_id', $selectedCountryId)->orderBy('name')->get() as $state)
                                    <option value="{{ $state->id }}" {{ $selectedStateId == $state->id ? 'selected' : '' }}>
                                        {{ $state->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('state_id')<p class="error-message">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label for="city_id" class="form-label">City <span class="req">*</span></label>
                        <select name="city_id" id="city_id"
                            class="input-field @error('city_id') input-error @enderror" required>
                            <option value="">Select city</option>
                            @if($selectedStateId)
                                @foreach(\Modules\Countries\Models\City::where('state_id', $selectedStateId)->where('country_id', $selectedCountryId)->orderBy('name')->get() as $city)
                                    <option value="{{ $city->id }}" {{ $selectedCityId == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('city_id')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Coordinates --}}
            <div class="gi-section">
                <div>
                    <h4 class="gi-section-title">Map location</h4>
                    <p class="gi-section-desc">Coordinates are filled in automatically from the address.</p>
                    <a id="geo-map-link" class="gi-map-link" style="margin-top: 10px;" target="_blank" rel="noopener noreferrer"
                        href="{{ filled($latitudeValue) && filled($longitudeValue) ? 'https://www.google.com/maps?q=' . urlencode($latitudeValue . ',' . $longitudeValue) : '#' }}"
                        @unless(filled($latitudeValue) && filled($longitudeValue)) hidden @endunless>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View on Google Maps
                    </a>
                </div>
                <div class="gi-fields cols-2">
                    <div class="form-group">
                        <label for="latitude" class="form-label">Latitude <span class="gi-badge is-brand">Auto</span></label>
                        <input name="latitude" id="latitude" type="text" class="input-field" readonly
                            value="{{ $latitudeValue }}" placeholder="Filled in from the address">
                    </div>

                    <div class="form-group">
                        <label for="longitude" class="form-label">Longitude <span class="gi-badge is-brand">Auto</span></label>
                        <input name="longitude" id="longitude" type="text" class="input-field" readonly
                            value="{{ $longitudeValue }}" placeholder="Filled in from the address">
                    </div>

                    <p id="geo-status" class="gi-geo-status span-all" role="status" aria-live="polite" hidden></p>
                </div>
            </div>

            <div class="section-card-footer">
                <p class="gi-footer-note" data-save-note>{{ $restaurantAddress ? 'All changes saved.' : 'No address saved yet.' }}</p>
                <button type="submit" class="gi-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save address
                </button>
            </div>
            </fieldset>
        </form>
    </div>
</div>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        // Live character count for the description.
        document.querySelectorAll('[data-char-count-for]').forEach(function (counter) {
            const field = document.getElementById(counter.dataset.charCountFor);
            if (!field) return;
            const update = function () {
                const n = Array.from(field.value).length;
                counter.textContent = `${n} character${n === 1 ? '' : 's'}`;
            };
            field.addEventListener('input', update);
            update();
        });

        // Flag unsaved edits in each form's footer.
        document.querySelectorAll('form[data-track-changes]').forEach(function (form) {
            const note = form.querySelector('[data-save-note]');
            if (!note) return;
            const markDirty = function () {
                note.textContent = 'You have unsaved changes.';
                note.classList.add('is-dirty');
            };
            form.addEventListener('input', markDirty);
            form.addEventListener('change', markDirty);
        });
    });
})();
</script>
