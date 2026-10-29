<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - {{guard}}</title>
    
   <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gray-50">
    <div id="toast-container" class="toast-container"></div>
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Back to Login -->
            <div class="mb-6">
                <a href="{{ route('{{guard}}.login.form') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Login
                </a>
            </div>

            <!-- Reset Password Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border-t-4 border-indigo-500">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-indigo-500 to-violet-500 rounded-xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold gradient-text mb-2">Reset Password</h2>
                    <p class="text-gray-600">Enter your email to receive a password reset link</p>
                </div>

                <!-- Reset Password Form -->
                <form class="form-ajax space-y-6" method="POST" action="{{ route('{{guard}}.password.email') }}">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                               placeholder="{{guard}}@example.com">
                    </div>
                    <x-button
                        type="submit"
                        class="btn-primary w-full flex justify-center"
                        label="Send Reset Link"
                        icon=""
                        name="button"
                    />
                </form>

                <!-- Divider -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Remember your password? 
                        <a href="{{ route('{{guard}}.login.form') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/toast.js') }}"></script>
    <script>
        @if (session('success'))
            showToast(@json(session('success')), 'success', 'Success');
        @elseif (session('error'))
            showToast(@json(session('error')), 'error', 'Error');
        @elseif (session('status'))
            showToast(@json(session('status')), 'success', 'Success');
        @endif
    </script>
</body>
</html>
