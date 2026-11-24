<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OTP Verification')</title>
    @include('partials.favicon')

    <script src="/js/jquery-3.7.1.min.js"></script>
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js', 'Modules/Backend/src/Resources/assets/js/toast.js', 'Modules/Backend/src/Resources/assets/css/toast.css'])
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta')
    @yield('styles')

</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-sky-50 text-slate-800">
    <main class="mx-auto flex min-h-screen w-full max-w-2xl items-center justify-center px-6 py-10">
        <div class="w-full rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.12)] backdrop-blur">
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                OTP VERIFICATION REQUIRED
            </span>

            <div class="mt-4 flex h-12 w-12 items-center justify-center rounded-xl border border-blue-200 bg-blue-50">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2Z" stroke="#2563EB" stroke-width="1.6"/>
                    <path d="M9.5 12L11.5 14L15 10" stroke="#2563EB" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h1 class="mt-4 text-2xl font-bold text-slate-900">Enter Access OTP</h1>
            <p class="mt-3 text-slate-600">An OTP was sent to administrator role users for your access request.</p>

            <div class="mt-4 space-y-1 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p><span class="font-semibold text-slate-700">IP:</span> {{ $ipAddress }}</p>
                <p><span class="font-semibold text-slate-700">Expires at:</span> {{ optional($expiresAt)->format('Y-m-d H:i:s') }}</p>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-md border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form class="mt-6" method="POST" action="{{ route('activitylog.access-request.otp.verify') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="otp" class="custom-label text-slate-700 required">OTP</label>
                    <input id="otp" name="otp" type="text" inputmode="numeric" maxlength="6"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800 placeholder-slate-400 focus:border-[var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-blue-200"
                        placeholder="Enter 6-digit OTP" autocomplete="one-time-code">
                    <p class="mt-2 text-xs text-slate-500">Use the 6-digit code received by the administrator.</p>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 shadow-sm transition">
                        Verify OTP
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>