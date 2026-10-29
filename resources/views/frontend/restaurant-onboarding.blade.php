<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restaurant Onboarding - Dawat</title>
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Material Icons --}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    {{-- Toast CSS --}}
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">

    <style>
        html,
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000000;
            color: #ffffff;
        }
        #onboarding-submit:disabled, #onboarding-verification button:disabled { opacity: .5; cursor: wait; }
        #onboarding-verification { position: fixed; inset: 0; margin: auto; width: calc(100% - 2rem); max-width: 28rem; max-height: calc(100dvh - 2rem); overflow-y: auto; background: #181818; color: #fff; }
        #onboarding-verification::backdrop { background: rgb(0 0 0 / 75%); }
        #onboarding-verification [data-error="true"], #onboarding-otp-status[data-error="true"] { color: #fca5a5; }
        #onboarding-open-otp[hidden] { display: none; }
        #onboarding-form input[aria-invalid="true"] { border-color: #f87171; }
        .email-dns-status { display: flex; align-items: flex-start; gap: 6px; margin-top: 8px; font-size: 12px; color: #b3b3b3; }
        .email-dns-status svg { flex-shrink: 0; margin-top: 1px; }
        .email-dns-status svg[hidden] { display: none; }
        .email-dns-status[data-state="valid"] { color: #86efac; }
        .email-dns-status[data-state="invalid"], .email-dns-status[data-state="error"] { color: #fca5a5; }
        .partner-location-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .partner-location-select { width: 100%; min-height: 52px; padding: 12px 36px 12px 14px; border: 1px solid rgb(255 255 255 / 20%); border-radius: 12px; background-color: #242424; color: #fff; color-scheme: dark; font: inherit; transition: border-color .2s, box-shadow .2s; }
        .partner-location-select:focus-visible { outline: none; border-color: #fff; box-shadow: 0 0 0 3px rgb(255 255 255 / 20%); }
        .partner-location-select:disabled { color: #a3a3a3; background-color: #191919; cursor: not-allowed; }
        .partner-location-select[aria-invalid="true"] { border-color: #f87171; }
        .location-error { color: #fca5a5; font-size: 12px; margin-top: 8px; }
        .location-error:empty { display: none; }
        .location-feedback { display: flex; align-items: center; gap: 12px; margin-top: 10px; font-size: 12px; color: #b3b3b3; }
        #location-status[data-error="true"] { color: #fca5a5; }
        #location-retry { text-decoration: underline; color: #fff; }
        #location-retry[hidden] { display: none; }
        @media (max-width: 640px) { .partner-location-grid { grid-template-columns: 1fr; } }
        #onboarding-form .password-error { margin-top: 8px; color: #fca5a5; font-size: 12px; }
    </style>
</head>

<body class="bg-black text-white min-h-screen">
    {{-- Toast Container --}}
    <div id="toast-container" class="toast-container"></div>

    {{-- ===== SECTION 1: HEADER ===== --}}
    <header class="w-full border-b border-white/10  backdrop-blur-sm sticky top-0 z-50 animate-fade-down">
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

            {{-- Logo --}}
            <a href="#"
                class="text-2xl font-bold tracking-widest uppercase text-white hover:text-white/70 transition-colors duration-300">
                <img src="{{asset('dawat.png')}}" alt="Dawat Logo" class="w-32 ">
            </a>

            {{-- Download Links --}}
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('restaurant.onboarding.form') }}"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="font-medium">Partner with Us</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M3.18 23.76c.31.17.67.19 1.01.06l11.93-6.9-2.58-2.58-10.36 9.42zm-1.15-21.4C1.75 2.7 2 3.1 2 3.62v16.76c0 .52.25.92.63 1.16l.1.06 9.39-9.39v-.22L2.13 2.3l-.1.06zM20.65 10.2l-2.55-1.47-2.87 2.87 2.87 2.87 2.58-1.49c.74-.43.74-1.35-.03-1.78zm-18.47 12l10.36-9.42 2.58 2.58-11.93 6.9c-.34.19-.71.2-1.01.06z" />
                    </svg>
                    <span class="font-medium">Android</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98l-.09.06c-.22.15-2.19 1.28-2.17 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.77M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                    </svg>
                    <span class="font-medium">iOS</span>
                </a>
            </div>

            {{-- Mobile Hamburger --}}
            <button class="md:hidden flex flex-col gap-1.5 group" aria-label="Menu">
                <span class="block w-6 h-0.5 bg-white transition-all duration-300 group-hover:w-4"></span>
                <span class="block w-6 h-0.5 bg-white"></span>
                <span class="block w-4 h-0.5 bg-white transition-all duration-300 group-hover:w-6"></span>
            </button>

        </div>
    </header>

    {{-- Onboarding Form --}}
    <section class="py-10 px-6">
        <div class="max-w-4xl mx-auto">

            {{-- Form Header --}}
            <div class="text-center mb-8">
                <div class="inline-block mb-4">
                    <div
                        class="w-16 h-16 mx-auto bg-gradient-to-br from-white/20 to-white/5 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/10">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <h1
                    class="text-4xl md:text-5xl font-bold mb-3 bg-gradient-to-r from-white via-white/90 to-white/70 bg-clip-text text-transparent">
                    Partner with Dawat
                </h1>
                <p class="text-base text-white/60 max-w-2xl mx-auto">
                    Join thousands of restaurants serving customers through our platform
                </p>
            </div>

            {{-- Form --}}
            <form id="onboarding-form" action="{{ route('restaurant.onboarding.submit') }}" method="POST"
                data-email-check-url="{{ route('restaurant.onboarding.check-email') }}"
                data-send-otp-url="{{ route('api.restaurant.auth.send-otp') }}"
                data-resend-otp-url="{{ route('api.restaurant.auth.resend-otp') }}"
                data-verify-otp-url="{{ route('api.restaurant.auth.verify-otp') }}"
                data-otp-type="{{ \Modules\Restaurant\Services\RestaurantOnboardingOtpService::TYPE }}"
                class="space-y-6 bg-gradient-to-b from-white/10 to-white/5 border border-white/10 rounded-3xl p-6 md:p-8 backdrop-blur-xl shadow-2xl animate-fade-up">
                @csrf
                <input type="hidden" name="email_verification_token" value="{{ old('email_verification_token') }}">

                {{-- Owner Information --}}
                <div class="space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white">Your Information</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="group">
                            <label
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                Your Name <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="John Doe">
                            </div>
                        </div>

                        <div class="group">
                            <label for="partner-email"
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                Email Address <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input id="partner-email" type="email" name="email" aria-describedby="email-dns-status" aria-invalid="false" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="john@example.com">
                            </div>
                            <p id="email-dns-status" class="email-dns-status" role="status" aria-live="polite">
                                <svg data-email-icon="valid" hidden width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                                <svg data-email-icon="invalid" hidden width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6" /></svg>
                                <span>We'll check the email format, domain and availability before sending an OTP.</span>
                            </p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="group">
                            <label
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                <span id="onboarding-password-label">Password <span class="text-red-400">*</span></span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="onboarding-password" type="password" name="password" required minlength="8" maxlength="16" autocomplete="new-password"
                                    aria-labelledby="onboarding-password-label" aria-describedby="password-requirements password-error" aria-invalid="false"
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="••••••••">
                            </div>
                            <p id="password-requirements" class="mt-2 text-xs text-white/60">Use 8–16 characters with uppercase and lowercase letters, a number, and a special character.</p>
                            <p id="password-error" class="password-error" aria-live="polite"></p>
                        </div>

                        <div class="group">
                            <label
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                <span id="onboarding-confirm-password-label">Confirm Password <span class="text-red-400">*</span></span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input id="onboarding-confirm-password" type="password" name="password_confirmation" required minlength="8" maxlength="16" autocomplete="new-password"
                                    aria-labelledby="onboarding-confirm-password-label" aria-describedby="password-confirmation-error" aria-invalid="false"
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="••••••••">
                            </div>
                            <p id="password-confirmation-error" class="password-error" aria-live="polite"></p>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/10"></div>
                    </div>
                </div>

                {{-- Restaurant Information --}}
                <div class="space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white">Restaurant Information</h2>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="group">
                            <label
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                Restaurant Name <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <input type="text" name="restaurant_name" value="{{ old('restaurant_name') }}" required
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="My Restaurant">
                            </div>
                        </div>

                        <div class="group">
                            <label
                                class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                                Phone Number <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required
                                    class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10"
                                    placeholder="+1 (555) 123-4567">
                            </div>
                        </div>
                    </div>

                    <div id="partner-location"
                        data-states-url="{{ route('api.countries.states-by-country', ['countryId' => '__id__']) }}"
                        data-cities-url="{{ route('api.countries.cities-by-state', ['stateId' => '__id__']) }}">
                        <p id="location-help" class="text-sm text-white/60 mb-3">Choose your country, then state and city to locate your restaurant.</p>
                        <div class="partner-location-grid">
                            @foreach (['country' => ['Country', $countries], 'state' => ['State / Province', $states], 'city' => ['City', $cities]] as $key => [$label, $locations])
                                <div class="group">
                                    <label for="partner-{{ $key }}" class="block text-sm font-medium mb-2 text-white/80">
                                        {{ $label }} <span class="text-red-400" aria-hidden="true">*</span>
                                    </label>
                                    <select id="partner-{{ $key }}" name="{{ $key }}_id" required
                                        autocomplete="{{ ['country' => 'country', 'state' => 'address-level1', 'city' => 'address-level2'][$key] }}"
                                        aria-describedby="location-help {{ $key }}-error" aria-invalid="{{ $errors->has($key.'_id') ? 'true' : 'false' }}"
                                        class="partner-location-select" @disabled($key !== 'country' && $locations->isEmpty())>
                                        <option value="">{{ $key === 'country' ? 'Select country' : ($locations->isEmpty() ? ($key === 'state' ? 'Choose country first' : 'Choose state first') : 'Select '.$key) }}</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" @selected(is_scalar(old($key.'_id')) && (string) old($key.'_id') === (string) $location->id)>{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                    <p id="{{ $key }}-error" class="location-error">@error($key.'_id'){{ $message }}@enderror</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="location-feedback">
                            <p id="location-status" role="status" aria-live="polite"></p>
                            <button id="location-retry" type="button" hidden>Try again</button>
                        </div>
                        <noscript><p class="location-error">Please enable JavaScript to load states and cities for your selected country.</p></noscript>
                    </div>

                    <div class="group">
                        <label
                            class="block text-sm font-medium mb-2 text-white/80 group-focus-within:text-white transition-colors">
                            Full Address <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute top-4 left-0 pl-4 pointer-events-none">
                                <svg class="w-5 h-5 text-white/40 group-focus-within:text-white/60 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <textarea name="address_line1" rows="3" required
                                class="w-full pl-12 pr-4 py-3.5 bg-white/5 border border-white/20 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent text-white placeholder-white/40 transition-all duration-200 hover:bg-white/10 resize-none"
                                placeholder="123 Main Street, Suite 100">{{ old('address_line1') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-6">
                    <button id="onboarding-submit" type="submit"
                        class="w-full py-4 bg-gradient-to-r from-white via-white to-white/90 text-black font-bold text-lg rounded-xl hover:shadow-2xl hover:shadow-white/20 transition-all duration-300 hover:scale-[1.02] active:scale-95 relative overflow-hidden group">
                        <span class="relative z-10">Submit Application</span>
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/50 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                        </div>
                    </button>
                    <p id="onboarding-otp-status" role="status" aria-live="polite" class="mt-3 text-center text-sm text-white/60">We'll verify your email with an OTP before submitting.</p>
                    <button id="onboarding-open-otp" type="button" hidden class="mt-3 w-full text-center text-sm text-white underline">Enter OTP / Resend OTP</button>
                    <div class="mt-4 p-4 bg-white/5 border border-white/10 rounded-xl">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-white/60 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-white/60 leading-relaxed">
                                Your application will be carefully reviewed by our team. You'll receive an email
                                notification once your restaurant has been approved to join our platform.
                            </p>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </section>

    <dialog id="onboarding-verification" aria-labelledby="onboarding-verification-title" aria-describedby="onboarding-verification-description" class="rounded-2xl border border-white/20 p-6 shadow-2xl">
        <h2 id="onboarding-verification-title" class="text-xl font-bold">Verify your email</h2>
        <p id="onboarding-verification-description" class="mt-2 text-sm text-white/60">Enter the 6-digit code sent to <strong id="onboarding-verification-email" class="break-words text-white"></strong>.</p>
        <label for="onboarding-otp" class="mt-5 block text-sm font-medium">Verification code</label>
        <input id="onboarding-otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="6-digit code" class="mt-2 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white">
        <p id="onboarding-verification-status" role="status" aria-live="polite" class="mt-3 text-sm text-white/60"></p>
        <p id="onboarding-otp-expiry" class="mt-2 text-xs text-white/60"></p>
        <div class="mt-5 flex flex-wrap gap-3">
            <button id="onboarding-verify" type="button" disabled class="flex-1 rounded-xl bg-white px-4 py-3 text-sm font-semibold text-black">Verify &amp; Submit</button>
            <button id="onboarding-resend" type="button" disabled class="rounded-xl border border-white/20 px-4 py-3 text-sm">Resend OTP</button>
        </div>
        <button id="onboarding-cancel" type="button" class="mt-4 text-sm text-white/60 hover:text-white">Back to application</button>
    </dialog>

    @include('frontend.partials.onboarding-confirmation', ['confirmationTheme' => 'dark'])

    {{-- Toast JavaScript --}}
    @if(session('success'))
        <script>
            // Clear browser-restored values before email and OTP checks initialize.
            document.getElementById('onboarding-form').reset();
            document.getElementById('onboarding-otp').value = '';
        </script>
    @endif
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/partner-email-check.js') }}" defer></script>
    <script src="{{ asset('js/partner-location.js') }}" defer></script>
    <script src="{{ asset('js/restaurant-onboarding.js') }}" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show success message
            @if(session('success'))
                toastSystem.show(
                    "{{ session('success') }}", 
                    'success', 
                    'Success', 
                    5000
                );
            @endif

            // Show error messages
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastSystem.show(
                        "{{ $error }}", 
                        'error', 
                        'Error', 
                        5000
                    );
                @endforeach
            @endif
        });
    </script>

</body>

</html>
