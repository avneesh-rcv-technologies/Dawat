<!DOCTYPE html>
<html class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst('customer') }} Register</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        .login-form { border-radius: 16px; border-image-slice: 1; backdrop-filter: blur(23.5px); background: linear-gradient(130.72deg, rgba(249, 251, 255, 0.63) 26.87%, rgba(66, 99, 170, 0.63) 68.04%); }
        .glass-input { background: #A7B0C4; border-radius: 40px; padding: 12px 18px 12px 56px; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); height: 52px; }
        .icon-wrapper { position: absolute; left: 12px; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .icon-wrapper i { color: #ffffff; opacity: 0.9; font-size: 14px; }
        .login-input { width: 100%; background: transparent; border: none; outline: none; color: #1A1737; font-size: 15px; }
        .login-input::placeholder { color: rgba(255, 255, 255, 0.85); }
    </style>
</head>
<body class="h-full font-sans antialiased">
    <div class="login-bg min-h-full flex items-center justify-center p-4 sm:px-6 lg:px-8 bg-gradient-to-br from-[var(--color-primary-light)] to-[var(--color-primary-lighter)]">
        <div class="w-full max-w-lg space-y-8">
            <div class="login-form bg-[var(--color-bg)] rounded-2xl shadow-xl ">
                <div class="logo flex justify-center items-center w-full border-b-1 border-[#FFFFFF] py-8  px-8 md:px-16">
                    <span class="text-lg font-semibold text-white">Register</span>
                </div>
                <form class="form-ajax space-y-6 py-8 px-8 md:px-16" method="POST" action="{{ route('customer.register') }}">
                    @csrf
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-white/90">Name</label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper"><i class="fas fa-user"></i></div>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" class="login-input" required autofocus placeholder="Enter your name">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-white/90">Email Address</label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper"><i class="fas fa-envelope"></i></div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="login-input" required placeholder="Enter your email address">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-white/90">Password</label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper"><i class="fas fa-lock"></i></div>
                            <input id="password" type="password" name="password" class="login-input" required placeholder="Enter password">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-white/90">Confirm Password</label>
                        <div class="relative flex items-center glass-input">
                            <div class="icon-wrapper"><i class="fas fa-lock"></i></div>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="login-input" required placeholder="Confirm password">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>