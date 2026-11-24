<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
    <div class="auth-shell">
        @include('admin::auth.partials.brand-panel')

        <main class="auth-main">
            <div class="auth-main-inner">
                <a href="{{ route('admin.login.form') }}" class="login-home-link">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span>Back to sign in</span>
                </a>

                <div class="login-form">
                    <a href="{{ route('website.home') }}" class="logo" aria-label="Dawat home">
                        <img src="{{ asset('website/images/dawat-wordmark.png') }}" alt="{{ fn_get_setting('general.company.name') ?: 'Dawat' }}">
                    </a>

                    <form id="admin-forgot-password-form" data-admin-auth-form data-auth-mode="forgot"
                        data-captcha-enabled="{{ app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled() ? 'true' : 'false' }}"
                        method="POST" action="{{ route('admin.password.email') }}">
                        @csrf

                        <div class="welcome">
                            <h3>Forgot password?</h3>
                            <p>Enter your registered email and we&rsquo;ll send you a secure reset link.</p>
                        </div>

                        @if (session('status'))
                            <div class="reset-status">{{ session('status') }}</div>
                        @endif

                        <div class="field">
                            <label for="reset-email">Email address</label>
                            <div class="glass-input">
                                <div class="icon-wrapper"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                                <input id="reset-email" name="email" type="email" required autocomplete="email"
                                    class="login-input" placeholder="you@company.com" value="{{ old('email') }}" autofocus>
                            </div>
                        </div>

                        @if (app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
                            <div class="auth-captcha">
                                <div class="g-recaptcha" data-sitekey="{{ app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->siteKey() }}" data-theme="dark"></div>
                            </div>
                            <p id="admin-forgot-captcha-error" data-captcha-error class="partner-auth-error" role="alert"></p>
                        @endif

                        <button type="submit" class="reset-submit" name="button">
                            <span>Send reset link</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m0 0-6-6m6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <p id="admin-forgot-status" data-auth-status class="auth-status" role="status" aria-live="polite"></p>

                        <p class="auth-secure-note">
                            Remembered it? <a href="{{ route('admin.login.form') }}" class="back-login">Back to sign in</a>
                        </p>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/partner-auth.js') }}" defer></script>
    @if (app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script>
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
</body>

</html>
