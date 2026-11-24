<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ request()->routeIs('restaurant.login*') ? 'Restaurant Login' : 'Admin Login' }}</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ substr(hash_file('sha256', public_path('css/toast.css')), 0, 12) }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    @include('admin::auth.partials.dark-styles')


</head>

<body class="h-full antialiased">
    <div id="toast-container" class="toast-container"></div>
    @php
        $isRestaurantLogin = request()->routeIs('restaurant.login*');
    @endphp
    <div class="auth-shell">
        @include('admin::auth.partials.brand-panel')

        <main class="auth-main">
            <div class="auth-main-inner">
                <a href="{{ route('website.home') }}" class="login-home-link">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span>Back to website</span>
                </a>

                <div class="login-form">
                    <a href="{{ route('website.home') }}" class="logo" aria-label="Dawat home">
                        <img src="{{ asset('website/images/dawat-wordmark.png') }}" alt="{{ fn_get_setting('general.company.name') ?: 'Dawat' }}">
                    </a>

                    <form id="admin-login-form" data-admin-auth-form data-auth-mode="login"
                        data-password-challenge-url="{{ route('admin.password.challenge') }}"
                        data-captcha-enabled="{{ app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled() ? 'true' : 'false' }}"
                        method="POST" action="{{ route($isRestaurantLogin ? 'restaurant.login' : 'admin.login') }}">
                        @csrf

                        <div class="welcome">
                            <h3>Welcome back</h3>
                            <p>{{ $isRestaurantLogin ? 'Sign in to manage your restaurant on Dawat.' : 'Sign in to manage your Dawat workspace.' }}</p>
                        </div>

                        <div class="field">
                            <label for="email">Email address</label>
                            <div class="glass-input">
                                <div class="icon-wrapper"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                                <input id="email" name="email" type="email" autocomplete="email" class="login-input"
                                    placeholder="you@company.com" required autofocus>
                            </div>
                        </div>

                        <div class="field">
                            <label for="admin-login-password">Password</label>
                            <div class="glass-input">
                                <div class="icon-wrapper"><i class="fas fa-lock" aria-hidden="true"></i></div>
                                <input id="admin-login-password" name="password" type="password" autocomplete="off"
                                    class="login-input" style="padding-right: 40px;" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password" aria-label="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="remember-row">
                            <label for="remember-me" class="flex items-center gap-2">
                                <input id="remember-me" name="remember-me" type="checkbox" value="1">
                                <span>Keep me signed in</span>
                            </label>
                            <a href="{{ route('admin.password.request') }}" class="forgot-link">
                                @lang('admin::app.admin.login.forget-password-link')
                            </a>
                        </div>

                        @if (app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
                            <div class="auth-captcha">
                                <div class="g-recaptcha" data-sitekey="{{ app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->siteKey() }}" data-theme="dark"></div>
                            </div>
                            <p id="admin-login-captcha-error" data-captcha-error class="partner-auth-error" role="alert"></p>
                        @endif

                        <button type="submit" class="login-submit" name="button">
                            <span>Sign in</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m0 0-6-6m6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <p id="admin-login-status" data-auth-status class="auth-status" role="status" aria-live="polite"></p>

                        <p class="auth-secure-note">
                            <span class="material-icons-outlined" aria-hidden="true">lock</span>
                            Secure area &middot; sign-in activity is monitored
                        </p>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/password-transport.js') }}?v={{ substr(hash_file('sha256', public_path('js/password-transport.js')), 0, 12) }}" defer></script>
    <script src="{{ asset('js/partner-auth.js') }}?v={{ substr(hash_file('sha256', public_path('js/partner-auth.js')), 0, 12) }}" defer></script>
    @if (app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleButton = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('admin-login-password');

            if (toggleButton && passwordInput) {
                toggleButton.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';

                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye', !isPassword);
                    icon.classList.toggle('fa-eye-slash', isPassword);
                    this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
                });
            }
        });

        @if (session('success'))
            showToast(@json(session('success')), 'success', 'Success');
        @elseif (session('error'))
            showToast(@json(session('error')), 'error', 'Error');
        @elseif (session('info'))
            showToast(@json(session('info')), 'info', 'Information');
        @elseif (session('warning'))
            showToast(@json(session('warning')), 'warning', 'Warning');
        @endif
    </script>

    {{-- toast modal throgh sessions --}}
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const toast = sessionStorage.getItem('toastMessage');
            if (toast) {
                const {
                    message,
                    type,
                    title
                } = JSON.parse(toast);
                showToast(message, type, title); // Your existing toast function
                sessionStorage.removeItem('toastMessage'); // Clear after showing
            }
        });
    </script>
</body>

</html>
