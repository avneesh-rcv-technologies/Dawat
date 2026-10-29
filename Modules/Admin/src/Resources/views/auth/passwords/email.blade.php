<!DOCTYPE html>
<html class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        body { margin: 0; }
        .login-bg { position: relative; isolation: isolate; min-height: 100vh; overflow: hidden; background: radial-gradient(ellipse 42% 62% at 50% 48%, rgba(133,143,148,.16), rgba(67,73,76,.09) 42%, transparent 100%), radial-gradient(ellipse at 12% 18%, rgba(255,255,255,.035), transparent 34%), radial-gradient(ellipse at 88% 82%, rgba(170,179,183,.045), transparent 38%), linear-gradient(145deg,#141617 0%,#202325 48%,#0b0c0d 100%); background-size: 120% 120%; animation: forgot-bg-drift 32s ease-in-out infinite alternate; }
        .login-bg::before, .login-bg::after { position: absolute; z-index: -1; content: ''; border: 1px solid rgba(220,228,231,.045); border-radius: 50%; pointer-events: none; }
        .login-bg::before { width: min(68vw,760px); aspect-ratio: 1; top: -38%; left: -17%; box-shadow: 0 0 0 55px rgba(220,228,231,.012), 0 0 0 110px rgba(220,228,231,.008); animation: forgot-orbit 38s ease-in-out infinite alternate; }
        .login-bg::after { width: min(58vw,620px); aspect-ratio: 1; right: -16%; bottom: -42%; box-shadow: 0 0 0 48px rgba(220,228,231,.012), 0 0 0 96px rgba(220,228,231,.007); animation: forgot-orbit 44s ease-in-out -8s infinite alternate-reverse; }
        .login-bg > div { position: relative; z-index: 1; width: 100%; max-width: 500px; }
        .login-form { overflow: hidden; border: 0; border-radius: 0; background: #191b1d; box-shadow: 18px 18px 42px rgba(0,0,0,.52), -10px -10px 30px rgba(255,255,255,.035), inset 1px 1px 0 rgba(255,255,255,.035); animation: forgot-card-enter .65s cubic-bezier(.2,.75,.25,1) both; }
        .login-form .logo { min-height: 116px; padding: 25px 32px; border: 0; background: #111213; box-shadow: inset 0 -1px 0 rgba(255,255,255,.045); }
        .login-form .logo img { max-width: min(100%,230px); max-height: 66px; object-fit: contain; mix-blend-mode: screen; }
        .login-form .logo span { color: #fff; font-family: Georgia,serif; font-size: 28px; }
        .login-form form { gap: 22px; padding: 34px 44px 38px; background: linear-gradient(145deg,#1d1f21 0%,#17191b 100%); }
        .login-form .welcome { margin-bottom: 4px; }
        .login-form .welcome h3 { margin: 0; color: #f2f5f6; font-size: 28px; line-height: 1.2; text-align: center; }
        .login-form .welcome p { margin: 8px 0 0; color: #a5afb4; font-size: 13px; line-height: 1.5; text-align: center; }
        .login-form form label { color: #d6dcdf; font-size: 13px; font-weight: 650; }
        .glass-input { height: 50px; padding: 0 16px 0 48px; border: 1px solid rgba(255,255,255,.035); border-radius: 10px; background: #1b1d1f; box-shadow: inset 5px 5px 10px rgba(0,0,0,.38), inset -4px -4px 9px rgba(255,255,255,.035); transition: border-color .18s ease,box-shadow .18s ease; }
        .glass-input:focus-within { border-color: #00a86b; box-shadow: inset 4px 4px 8px rgba(0,0,0,.35), inset -3px -3px 7px rgba(255,255,255,.035), 0 0 0 3px rgba(0,168,107,.13); }
        .icon-wrapper { position: absolute; left: 15px; display: flex; width: 20px; align-items: center; justify-content: center; }
        .icon-wrapper i { color: #a2adb2; font-size: 14px; }
        .login-form .login-input,.login-form .login-input:focus { width: 100%; min-width: 0; border: 0 !important; outline: 0 !important; box-shadow: none !important; background: transparent !important; color: #f2f5f6; font-size: 14px; }
        .login-input::placeholder { color: #899499; }
        .login-input:-webkit-autofill,.login-input:-webkit-autofill:focus { -webkit-box-shadow: 0 0 0 1000px #1b1d1f inset !important; -webkit-text-fill-color: #f2f5f6 !important; }
        .login-form .reset-status { margin: 0; padding: 11px 13px; border: 1px solid rgba(69,199,151,.22); border-radius: 8px; background: rgba(22,132,91,.12); color: #8de0bd; font-size: 13px; line-height: 1.5; }
        .login-form .reset-submit { min-height: 48px; border: 1px solid rgba(255,255,255,.045); border-radius: 10px; background: #1c1e20; color: #f4f6f7; font-size: 14px; font-weight: 650; box-shadow: 6px 6px 12px rgba(0,0,0,.42), -4px -4px 10px rgba(255,255,255,.045); transition: transform .16s ease,color .16s ease,box-shadow .16s ease; }
        .login-form .reset-submit:hover { transform: translateY(-1px); color: #78e2b8; box-shadow: 8px 8px 16px rgba(0,0,0,.45), -5px -5px 12px rgba(255,255,255,.05); }
        .login-form .back-login { color: #45c797; text-decoration: none; }
        .login-form .back-login:hover { color: #78e2b8; text-decoration: underline; }
        .login-form :is(button,a,input):focus-visible { outline: 3px solid rgba(0,168,107,.35); outline-offset: 3px; }
        @keyframes forgot-bg-drift { from { background-position: 0% 0%; } to { background-position: 100% 100%; } }
        @keyframes forgot-orbit { from { transform: translate3d(-10px,-8px,0) scale(.98); opacity: .7; } to { transform: translate3d(18px,14px,0) scale(1.04); opacity: 1; } }
        @keyframes forgot-card-enter { from { opacity: 0; transform: translateY(14px) scale(.99); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @media(max-width:520px) { .login-bg { padding: 20px; } .login-form .logo { min-height: 96px; padding: 20px 24px; } .login-form form { gap: 19px; padding: 28px 24px 30px; } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
    </style>
</head>

<body class="h-full font-sans antialiased">
    <div id="toast-container" class="toast-container"></div>
    <div class="login-bg min-h-full flex items-center justify-center p-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-lg">

            <div class="login-form">
                <div class="logo flex justify-center items-center w-full border-b-1 border-[#fff] py-8 px-8 md:px-16">
                    @php
                        $logo = fn_get_image('company_logo', 0)['url'] ?? '';
                    @endphp
                    @if($logo)
                        <img src="{{ $logo }}" alt="{{ fn_get_setting('general.company.name') }}" class="h-16 w-auto object-cover">
                    @else
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>{{ fn_get_setting('general.company.name') ? fn_get_setting('general.company.name') : "Admin Panel" }}</span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.password.email') }}" class="form-ajax space-y-6">
                    @csrf

                    <div class="welcome mb-8">
                        <h3>Forgot password?</h3>
                        <p>Enter your registered email and we’ll send you a secure reset link.</p>
                    </div>

                    @if (session('status'))
                        <div class="reset-status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="space-y-2">
                        <label for="reset-email" class="block text-sm font-medium text-[var(--color-white)]">
                            Email Address
                        </label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input id="reset-email" name="email" type="email" required autocomplete="email"
                                class="login-input" placeholder="Enter registered email" value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="flex items-center justify-center flex-col gap-4 w-full">
                        <button type="submit" class="reset-submit px-4 py-2 w-full" name="button">
                            Send Reset Link
                        </button>
                        <a href="{{ route('admin.login.form') }}" class="back-login text-xs font-medium">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/toast.js') }}"></script>

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
