<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Access Denied')</title>
    @include('partials.favicon')

    <script src="/js/jquery-3.7.1.min.js"></script>
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js', 'Modules/Backend/src/Resources/assets/js/toast.js', 'Modules/Backend/src/Resources/assets/css/toast.css'])

    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body class="font-sans text-slate-800 bg-gradient-to-br from-slate-50 via-indigo-50 to-sky-50">

    <main class="mx-auto flex min-h-screen w-full max-w-2xl items-center justify-center px-6 py-10">
        <div class="w-full rounded-2xl border border-slate-200 bg-white/90 p-8 shadow-[0_20px_60px_rgba(15,23,42,0.12)] backdrop-blur">
            <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                ADMIN ACCESS RESTRICTED
            </span>

            <div class="mt-4 flex h-12 w-12 items-center justify-center rounded-xl border border-red-200 bg-red-50">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2Z" stroke="#DC2626" stroke-width="1.6"/>
                    <path d="M9 9L15 15M15 9L9 15" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="mt-4 text-2xl font-bold text-slate-900">Access Denied</h1>
            <p class="mt-3 text-slate-600">You do not have permission to access this route from your current IP address.</p>

            <div class="mt-4 space-y-1 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                <p><span class="font-semibold text-slate-700">IP:</span> {{ $ipAddress ?? request()->ip() }}</p>
                <p><span class="font-semibold text-slate-700">Route:</span> {{ $requestedPath ?? '/' }}</p>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <x-modal buttonText="{{ $actionText }}" type='link' modalTitle="{{ $modalTitle }}" id="block_ip"
                    ajaxUrl="{{ route('activitylog.access-request.form', ['requested_path' => $requestedPath ?? '/']) }}"
                    buttonClass="px-4 py-2 rounded-md {{ $buttonClass }} font-medium" modalSize="3xl" />

                <a href="{{ url('/') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Go to Homepage
                </a>
            </div>
        </div>
    </main>
    <script src="{{ asset('js/toast.js') }}"></script>
    @yield('scripts')

</body>

</html>