<style>
    /* Restaurant Registration Web Form */
    .reg-web-wrapper {
        min-height: 100vh;
        width: 100%;
        background-color: #0A0A0C;
        color: #FFFFFF;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        box-sizing: border-box;
        padding: 0 20px 60px;
    }

    .reg-web-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Top Web Header */
    .reg-web-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }

    .reg-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #A1A1AA;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        margin-bottom: 24px;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .reg-back-link:hover {
        color: #FFFFFF;
        transform: translateX(-3px);
    }

    .reg-header-center {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #71717A;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .reg-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .reg-store-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #141418;
        border: 1px solid #272730;
        border-radius: 9999px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 500;
        color: #E4E4E7;
    }

    .reg-store-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #FF6210;
    }

    .reg-logout-link {
        background: transparent;
        border: none;
        color: #71717A;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    .reg-logout-link:hover {
        color: #F43F5E;
        background: rgba(244, 63, 94, 0.08);
    }

    /* Hero Section */
    .reg-web-hero {
        margin-bottom: 32px;
    }

    .reg-web-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(32px, 4vw, 46px);
        line-height: 1.15;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 12px 0;
        letter-spacing: -0.5px;
    }

    .reg-web-subtitle {
        color: #A1A1AA;
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
        max-width: 640px;
    }

    /* Status Alert Banners */
    .reg-alert-banner {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 28px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reg-alert-banner.is-approved {
        background: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #34D399;
    }

    .reg-alert-banner.is-under-review {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.25);
        color: #60A5FA;
    }

    .reg-alert-banner.is-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }

    /* Logo Upload Card */
    .reg-logo-card {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .reg-logo-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(249, 115, 22, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .reg-logo-details {
        flex: 1;
        min-width: 0;
    }

    .reg-logo-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 4px;
    }

    .reg-logo-title {
        font-size: 15.5px;
        font-weight: 600;
        color: #FF6210;
        margin: 0;
    }

    .reg-logo-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 0 0 6px 0;
    }

    .reg-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #202026;
        color: #9CA3AF;
        display: inline-block;
    }

    /* Grid Layout for Sections */
    .reg-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    @media (max-width: 900px) {
        .reg-grid {
            grid-template-columns: 1fr;
        }
    }

    .reg-card {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 28px;
    }

    .reg-card-title {
        font-size: 18px;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 4px 0;
    }

    .reg-card-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 0 0 22px 0;
        line-height: 1.4;
    }

    /* Form Fields */
    .reg-field-group {
        margin-bottom: 20px;
    }

    .reg-field-group:last-child {
        margin-bottom: 0;
    }

    .reg-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #D4D4D8;
        margin-bottom: 8px;
    }

    .reg-req {
        color: #FF6210;
        margin-left: 2px;
    }

    .reg-input-wrap {
        position: relative;
    }

    .reg-control-input {
        width: 100%;
        height: 48px;
        background: #18181D;
        border: 1px solid #2D2D36;
        border-radius: 12px;
        padding: 0 16px;
        color: #FFFFFF;
        font-size: 14.5px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        box-sizing: border-box;
    }

    .reg-control-input:focus:not(:disabled) {
        border-color: #FF6210;
        box-shadow: 0 0 0 3px rgba(255, 98, 16, 0.15);
        background: #1C1C22;
    }

    .reg-control-input:disabled,
    .reg-control-input[readonly] {
        background: #121215;
        border-color: #202026;
        color: #E4E4E7;
        cursor: default;
    }

    .reg-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%238E8E93' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        padding-right: 40px;
        cursor: pointer;
    }

    .reg-select:disabled {
        cursor: default;
    }

    .reg-helper-text {
        font-size: 12px;
        color: #71717A;
        margin: 6px 0 0 0;
        line-height: 1.4;
    }

    .reg-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    @media (max-width: 500px) {
        .reg-row-2col {
            grid-template-columns: 1fr;
        }
    }

    /* Support Card */
    .reg-support-card-link {
        margin-top: 24px;
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .reg-support-card-link:hover {
        border-color: #383842;
        background-color: #17171B;
    }

    .reg-support-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .reg-support-title {
        font-size: 15px;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0;
    }

    .reg-support-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 3px 0 0 0;
    }

    /* CTA Section */
    .reg-actions-row {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 36px;
        padding-top: 28px;
        border-top: 1px solid #1E1E24;
    }

    .reg-nav-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-top: 28px;
    }

    .reg-submit-btn {
        max-width: 440px;
        width: 100%;
        height: 52px;
        background-color: #FF5500;
        color: #FFFFFF;
        border: none;
        border-radius: 9999px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
        box-shadow: 0 4px 18px rgba(255, 85, 0, 0.25);
        font-family: inherit;
    }

    .reg-submit-btn:hover:not(:disabled) {
        background-color: #FF6611;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(255, 85, 0, 0.35);
    }

    .reg-subnote {
        margin: 12px 0 0 0;
        font-size: 13px;
        color: #71717A;
        text-align: center;
    }

    .reg-error-msg {
        width: 100%;
        margin: 16px 0;
        padding: 14px 16px;
        border: 1px solid #fecaca;
        border-radius: 10px;
        background: #fef2f2;
        color: #a52e25;
        font-size: 14px;
        line-height: 1.6;
        text-align: left;
    }
</style>

@php
    $selectedRecord = $steps->firstWhere('key', 'registration');
    $stepStatus = $selectedRecord['status'] ?? 'draft';
    $stepEditable = \Modules\Restaurant\Services\RestaurantOnboardingAccessService::isStepEditable($stepStatus);
    $isApproved = (bool) ($selectedRecord['is_approved'] ?? false);
    $isUnderReview = $stepStatus === 'under_review';

    $countryList = $correctionData['countries'] ?? collect();
    $currentCountryId = $correctionData['countryId'] ?? null;
    $stateList = $correctionData['states'] ?? collect();
    $currentStateId = $correctionData['stateId'] ?? null;
    $cityList = $correctionData['cities'] ?? collect();
    $currentCityId = $correctionData['cityId'] ?? null;

    $cuisineOptions = [
        'Indian', 'Pakistani', 'Middle Eastern', 'Asian', 'American',
        'Italian', 'Mexican', 'Mediterranean', 'Fast Food', 'Bakery & Cafe',
        'Continental', 'Other'
    ];
    $selectedCuisine = old('cuisine_type', $correctionRestaurant->cuisine_type ?? 'Indian');
@endphp

<div class="reg-web-wrapper">
    <div class="reg-web-container">
        <!-- Top Web Navigation Header -->
        <header class="reg-web-header">
            

            <div class="reg-header-center">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
            </div>

            <div class="reg-header-right">
                <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="reg-logout-link" title="Logout">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
            
        </header>

        <!-- Hero Section (Figma Reference) -->

        <section class="reg-web-hero">
            <a href="{{ route('restaurant.onboarding.kyc') }}" class="reg-back-link" aria-label="Back to onboarding steps">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to onboarding steps</span>
            </a>
            
            <h1 class="reg-web-title">Make it yours.</h1>
            <p class="reg-web-subtitle">
                Tell us about your restaurant. These details help guests find and contact you.
            </p>
        </section>

        <!-- Status Alerts -->
        @if($isApproved)
            <div class="reg-alert-banner is-approved">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span><strong>Approved information</strong> · This step is approved and locked for editing.</span>
            </div>
        @elseif($isUnderReview)
            <div class="reg-alert-banner is-under-review">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span><strong>Under review</strong> · Updates are disabled until our team completes verification.</span>
            </div>
        @endif

        @if($stepDetails && !empty($stepDetails['rejection_reason']))
            <div class="reg-alert-banner is-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><strong>Review feedback:</strong> {{ $stepDetails['rejection_reason'] }}</span>
            </div>
        @endif

        <!-- Upload Restaurant Logo Card (Figma Reference) -->
        <!-- <div class="reg-logo-card">
            <div class="reg-logo-icon-box" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </div>
            <div class="reg-logo-details">
                <div class="reg-logo-title-row">
                    <h3 class="reg-logo-title">Upload restaurant logo</h3>
                    <span class="reg-pill">Optional</span>
                </div>
                <p class="reg-logo-sub">JPG or PNG · at least 400 × 400 px</p>
            </div>
            <div>
                <span class="reg-pill" style="background: rgba(255, 98, 16, 0.1); color: #FF6210; padding: 6px 14px; font-size: 12px;">
                    {{ $isApproved ? 'Verified brand' : 'Default brand mark' }}
                </span>
            </div>
        </div> -->
    
        @if($stepEditable)
            <form method="POST" action="{{ route('restaurant.onboarding.corrections.registration') }}"
                  data-location-selects="correction"
                  data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__ID__']) }}"
                  data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__ID__']) }}">
                @csrf
                <input type="hidden" name="step_key" value="registration">
        @else
            <div>
        @endif

            <!-- 2-Column Responsive Web Grid -->
            <div class="reg-grid">
                <!-- Left Column: Restaurant Details & Primary Contact -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Section 1: Restaurant Details -->
                    <div class="reg-card">
                        <h2 class="reg-card-title">Restaurant details</h2>
                        <p class="reg-card-sub">Basic information that diners see on your storefront.</p>

                        <!-- Restaurant display name -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="reg-name">
                                Restaurant display name <span class="reg-req">*</span>
                            </label>
                            <input type="text"
                                   id="reg-name"
                                   name="name"
                                   class="reg-control-input"
                                   value="{{ old('name', $correctionRestaurant->name) }}"
                                   placeholder="e.g. Saffron Table"
                                   maxlength="100"
                                   @required($stepEditable)
                                   @disabled(!$stepEditable)>
                        </div>

                        <!-- Cuisine -->
                        <!-- <div class="reg-field-group">
                            <label class="reg-label" for="reg-cuisine">
                                Cuisine <span class="reg-req">*</span>
                            </label>
                            <select id="reg-cuisine"
                                    name="cuisine_type"
                                    class="reg-control-input reg-select"
                                    @disabled(!$stepEditable)>
                                @foreach($cuisineOptions as $cuisine)
                                    <option value="{{ $cuisine }}" @selected(strcasecmp($selectedCuisine, $cuisine) === 0)>{{ $cuisine }}</option>
                                @endforeach
                            </select>
                        </div> -->

                        <!-- Restaurant phone -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="reg-phone">
                                Restaurant phone <span class="reg-req">*</span>
                            </label>
                            <input type="text"
                                   id="reg-phone"
                                   name="phone"
                                   class="reg-control-input"
                                   value="{{ old('phone', $correctionRestaurant->phone) }}"
                                   placeholder="+1 (718) 555-0142"
                                   maxlength="20"
                                   @required($stepEditable)
                                   @disabled(!$stepEditable)>
                            <p class="reg-helper-text">Shown to guests for order-related questions.</p>
                        </div>
                    </div>

                    <!-- Section 2: Primary Contact -->
                    <div class="reg-card">
                        <h2 class="reg-card-title">Primary contact</h2>
                        <p class="reg-card-sub">The store owner and account manager on file.</p>

                        <!-- Full name -->
                        <div class="reg-field-group">
                            <label class="reg-label">Full name <span class="reg-req">*</span></label>
                            <input type="text"
                                   class="reg-control-input"
                                   value="{{ $correctionRestaurant->admin?->name ?? 'Store Owner' }}"
                                   readonly
                                   disabled>
                        </div>

                        <!-- Role -->
                        <div class="reg-field-group">
                            <label class="reg-label">Role <span class="reg-req">*</span></label>
                            <input type="text"
                                   class="reg-control-input"
                                   value="{{ $correctionRestaurant->admin?->role?->name ?? 'Owner' }}"
                                   readonly
                                   disabled>
                        </div>

                        <!-- Email address -->
                        <div class="reg-field-group">
                            <label class="reg-label">Email address <span class="reg-req">*</span></label>
                            <input type="email"
                                   class="reg-control-input"
                                   value="{{ $correctionRestaurant->admin?->email }}"
                                   readonly
                                   disabled>
                            <p class="reg-helper-text">We'll send application updates to this address.</p>
                        </div>

                        <!-- Mobile number -->
                        <div class="reg-field-group">
                            <label class="reg-label">Mobile number <span class="reg-req">*</span></label>
                            <input type="text"
                                   class="reg-control-input"
                                   value="{{ $correctionRestaurant->phone }}"
                                   readonly
                                   disabled>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Where's your kitchen? & Support -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Section 3: Where's your kitchen? -->
                    <div class="reg-card">
                        <h2 class="reg-card-title">Where's your kitchen?</h2>
                        <p class="reg-card-sub">Use the physical address where orders are prepared.</p>

                        <!-- Street address -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="reg-address1">
                                Street address <span class="reg-req">*</span>
                            </label>
                            <input type="text"
                                   id="reg-address1"
                                   name="address_line1"
                                   class="reg-control-input"
                                   value="{{ old('address_line1', $correctionRestaurant->address_line1) }}"
                                   placeholder="128 Atlantic Avenue"
                                   maxlength="100"
                                   @required($stepEditable)
                                   @disabled(!$stepEditable)>
                        </div>

                        <!-- Apartment, suite or floor (optional) -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="reg-address2">
                                Apartment, suite or floor (optional)
                            </label>
                            <input type="text"
                                   id="reg-address2"
                                   name="address_line2"
                                   class="reg-control-input"
                                   value="{{ old('address_line2', $correctionRestaurant->address_line2) }}"
                                   placeholder="Ground floor"
                                   maxlength="100"
                                   @disabled(!$stepEditable)>
                        </div>

                        <!-- Country -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="correction-country_id">
                                Country <span class="reg-req">*</span>
                            </label>
                            @if($stepEditable)
                                <select name="country_id"
                                        id="correction-country_id"
                                        class="reg-control-input reg-select"
                                        required>
                                    <option value="">Select country</option>
                                    @foreach($countryList as $country)
                                        <option value="{{ $country->id }}" @selected((string)$currentCountryId === (string)$country->id)>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="reg-control-input" value="{{ $correctionRestaurant->country }}" readonly disabled>
                            @endif
                        </div>

                        <!-- State -->
                        <div class="reg-field-group">
                            <label class="reg-label" for="correction-state_id">
                                State <span class="reg-req">*</span>
                            </label>
                            @if($stepEditable)
                                <select name="state_id"
                                        id="correction-state_id"
                                        class="reg-control-input reg-select"
                                        required>
                                    <option value="">Select state</option>
                                    @foreach($stateList as $state)
                                        <option value="{{ $state->id }}" @selected((string)$currentStateId === (string)$state->id)>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="reg-control-input" value="{{ $correctionRestaurant->state }}" readonly disabled>
                            @endif
                        </div>

                        <!-- City & ZIP code in 2-col -->
                        <div class="reg-row-2col">
                            <div class="reg-field-group">
                                <label class="reg-label" for="correction-city_id">
                                    City
                                </label>
                                @if($stepEditable)
                                    <select name="city_id"
                                            id="correction-city_id"
                                            class="reg-control-input reg-select">
                                        <option value="">Select city</option>
                                        @foreach($cityList as $city)
                                            <option value="{{ $city->id }}" @selected((string)$currentCityId === (string)$city->id)>
                                                {{ $city->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="reg-control-input" value="{{ $correctionRestaurant->city }}" readonly disabled>
                                @endif
                            </div>

                            <div class="reg-field-group">
                                <label class="reg-label" for="reg-postal">
                                    ZIP code <span class="reg-req">*</span>
                                </label>
                                <input type="text"
                                       id="reg-postal"
                                       name="postal_code" minlength="5" maxlength="9"
                                       class="reg-control-input"
                                       value="{{ old('postal_code', $correctionRestaurant->postal_code) }}"
                                       placeholder="11201"
                                       maxlength="80"
                                       @disabled(!$stepEditable)>
                            </div>
                        </div>

                      
                    </div>

                    <!-- Need a hand? Support Card -->
                    <a href="mailto:support@dawat.com?subject={{ rawurlencode('Restaurant Registration Assistance') }}" class="reg-support-card-link">
                        <div class="reg-support-left">
                            <div aria-hidden="true">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="reg-support-title">Need a help?</h4>
                                <p class="reg-support-sub">Chat with our partner support team.</p>
                            </div>
                        </div>
                        <div aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Bottom CTA / Navigation -->
            <!-- @if($stepEditable)
                <div class="reg-actions-row">
                    <button type="submit" class="reg-submit-btn" id="reg-submit-btn">
                        <span>{{ $stepStatus === 'draft' ? 'Submit registration for review' : 'Save restaurant profile' }}</span>
                    </button>
                    <p class="reg-subnote">
                        Finish setup, then submit for review.
                    </p>
                </div>
            @else
                <div class="reg-nav-between">
                    <a href="{{ route('restaurant.onboarding.kyc') }}" class="reg-back-link">
                        ← Back to onboarding steps
                    </a>
                    <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'banking']) }}" class="reg-back-link" style="color: #FF6210; font-weight: 600;">
                        Next: Banking information →
                    </a>
                </div>
            @endif -->

        @if($stepEditable)
            </form>
            <script src="{{ asset('js/onboarding-location-selects.js') }}" defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const regForm = document.querySelector('form[action="{{ route('restaurant.onboarding.corrections.registration') }}"]');
                    if (regForm) {
                        regForm.addEventListener('submit', async (e) => {
                            if (window.confirmOnboardingSubmission) {
                                e.preventDefault();
                                const isDraft = @json($stepStatus === 'draft');
                                const confirmed = await window.confirmOnboardingSubmission(isDraft
                                    ? 'Submit your restaurant profile for review?'
                                    : 'Update your restaurant profile and submit it for review?');
                                if (confirmed) {
                                    regForm.submit();
                                }
                            }
                        });
                    }
                });
            </script>
        @else
            </div>
        @endif
    </div>
</div>
