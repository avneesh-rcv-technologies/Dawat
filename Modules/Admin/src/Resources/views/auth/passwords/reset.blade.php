<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    @include('partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ substr(hash_file('sha256', public_path('css/toast.css')), 0, 12) }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    @include('admin::auth.partials.dark-styles')
    <style>
        .password-help { color: rgba(255,255,255,.55); font-size: 13px; line-height: 1.5; margin-top: 8px; }
        .password-error { color: #fca5a5; font-size: 13px; margin-top: 6px; }
        .password-error:empty { display: none; }
        .login-form .glass-input:has(.login-input[aria-invalid="true"]) { border-color: #f87171; }
        .login-form .glass-input:has(.login-input[aria-invalid="true"]):focus-within { box-shadow: inset 4px 4px 8px rgba(0,0,0,.35), 0 0 0 3px rgba(248,113,113,.15); }
        .login-form .login-input:disabled { opacity: .75; cursor: default; -webkit-text-fill-color: #c5cccf; }
        .login-form .login-submit:disabled { opacity: .6; cursor: wait; }
        .reset-link-error { padding: 0; color: #fff; text-align: left; background: transparent; }
        .reset-link-error h3 { margin: 0 0 12px; font-family: 'NewYork', Georgia, serif; font-size: 34px; font-weight: 400; }
        .reset-link-error p { color: rgba(255,255,255,.62); line-height: 1.5; }
        .reset-link-error a { display: inline-flex; margin-top: 20px; padding: 14px 24px; border-radius: 999px; background: #FF6210; color: #fff; font-weight: 700; text-decoration: none; }
        
    </style>
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

                @if($linkValid)
                <form id="admin-reset-password-form" class="form-ajax space-y-6 py-8 px-8 md:px-16" method="POST" action="{{ route('admin.password.update') }}"
                    data-password-challenge-url="{{ route('admin.password.challenge') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

                    <div class="welcome mb-8">
                        <h3>Reset password</h3>
                        <p>Choose a strong password to secure your Dawat account.</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 text-sm font-medium text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label for="reset-account-email" class="block text-sm font-medium text-[var(--color-white)]">
                            Email Address
                        </label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input id="reset-account-email" type="email" class="login-input" placeholder="email@example.com"
                                value="{{ $email ?? old('email') }}" readonly disabled>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-[var(--color-white)]">
                            New Password
                        </label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input id="password" name="password" type="password" required minlength="8" maxlength="16" autocomplete="new-password"
                                aria-describedby="reset-password-help reset-password-error" aria-invalid="false"
                                class="login-input" placeholder="Enter new password">
                            <button type="button" class="toggle-password" data-password-toggle="password" aria-label="Show new password" aria-pressed="false"><i class="fas fa-eye" aria-hidden="true"></i></button>
                        </div>
                        <p id="reset-password-help" class="password-help">Use 8–16 characters with uppercase and lowercase letters, a number, and a special character.</p>
                        <p id="reset-password-error" class="password-error" aria-live="polite">@error('password'){{ $message }}@enderror</p>
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-[var(--color-white)]">
                            Confirm Password
                        </label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" maxlength="16" autocomplete="new-password"
                                aria-describedby="reset-confirmation-error" aria-invalid="false"
                                class="login-input" placeholder="Confirm new password">
                            <button type="button" class="toggle-password" data-password-toggle="password_confirmation" aria-label="Show confirmed password" aria-pressed="false"><i class="fas fa-eye" aria-hidden="true"></i></button>
                        </div>
                        <p id="reset-confirmation-error" class="password-error" aria-live="polite"></p>
                    </div>

                    <div class="flex items-center justify-center flex-col gap-4 w-full">
                        <button type="submit" class="login-submit" name="button">
                            <span>Reset password</span>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m0 0-6-6m6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </button>
                        <a href="{{ route('admin.login.form') }}" class="forgot-link text-xs font-medium">Back to Login</a>
                        <a href="{{ route('admin.password.request') }}" class="forgot-link text-xs font-medium">Request a new reset link</a>
                    </div>
                </form>
                @else
                    <div class="reset-link-error" role="alert">
                        <h3 class="text-xl font-semibold">Reset link unavailable</h3>
                        <p>This link is invalid, expired, or has already been used.</p>
                        <a href="{{ route('admin.password.request') }}">Request a new reset link</a>
                    </div>
                @endif
            </div>
        </div>
        </main>
    </div>
    <script src="{{ asset('js/toast.js') }}"></script>
    <script src="{{ asset('js/password-transport.js') }}?v={{ substr(hash_file('sha256', public_path('js/password-transport.js')), 0, 12) }}" defer></script>
    <script src="{{ asset('js/admin-password-reset.js') }}?v={{ substr(hash_file('sha256', public_path('js/admin-password-reset.js')), 0, 12) }}" defer></script>

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
