<!DOCTYPE html>
<html class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    @include('admin::auth.partials.dark-styles')


</head>

<body class="h-full font-sans antialiased">
    <div id="toast-container" class="toast-container"></div>
    <div
        class="login-bg min-h-full flex items-center justify-center p-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-lg">
            <a href="{{ route('index') }}" class="login-home-link">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 12H5m0 0 6 6m-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <span>Return to home</span>
            </a>

            <div class="login-form">
                <div class="logo flex justify-center items-center w-full border-b-1 border-[#fff] py-8  px-8 md:px-16">
                    @php
                    $logo = fn_get_image('company_logo', 0)['url'] ?? '';
                    @endphp
                    @if($logo)
                    <img src="{{ $logo }}" alt="{{ fn_get_setting('general.company.name') }}"
                        class="h-16 w-auto object-cover">


                    @else
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span>{{fn_get_setting('general.company.name') ? fn_get_setting('general.company.name') : "Admin
                        Panel" }}</span>
                    @endif
                </div>
                <form class="form-ajax space-y-6 py-8 px-8 md:px-16" method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <div class="welcome mb-8">
                        <h3 class="text-[var(--color-white)] text-[27.89px] text-center font-semibold">Welcome Back!</h3>
                        <p>Sign in to manage your Dawat workspace.</p>
                    </div>


                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-[var(--color-white)]">
                            Email Address
                        </label>

                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-envelope"></i>
                            </div>

                            <input name="email" type="email" autocomplete="email" class="login-input"
                                placeholder="Enter registered email">
                        </div>
                    </div>


                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-[var(--color-white)]">
                            Password
                        </label>

                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-lock"></i>
                            </div>

                            <input id="admin-login-password" name="password" type="password" autocomplete="current-password" class="login-input pr-10"
                                placeholder="Enter password">

                            <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-black opacity-80 hover:opacity-100" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-row flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox" value="1"
                                class="h-4 w-4 text-[var(--color-primary)] focus:ring-[var(--color-primary)] border-[var(--color-primary)] rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-[var(--color-white)]">
                                Keep me logged in
                            </label>
                        </div>
                        <div class="">
                            <a href="{{ route('admin.password.request') }}" class="forgot-link text-xs font-medium">
                                @lang('admin::app.admin.login.forget-password-link')
                            </a>
                        </div>
                    </div>

                    {{-- <button type="submit" class=" padding: 0.5rem 1rem;
  border-radius: 0.5rem; bg-white w-full" name="button">
                        Sign In
                    </button> --}}
                    <button type="submit" class="login-submit px-4 py-2 w-full" name="button">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/toast.js') }}"></script>

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
