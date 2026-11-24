<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Menu details') — Dawat</title>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #0A0A0C; color: #FFFFFF; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.5; }
        a { color: inherit; }
        a:focus-visible, button:focus-visible { outline: 3px solid rgba(255, 98, 16, 0.45); outline-offset: 3px; }
        /* Shell (same as onboarding status page) */
        .kyc-web-wrapper { min-height: 100vh; width: 100%; padding: 0 20px 60px; }
        .kyc-web-container { max-width: 1040px; margin: 0 auto; }
        .kyc-web-header { display: flex; align-items: center; justify-content: space-between; padding: 24px 0; border-bottom: 1px solid #1E1E24; margin-bottom: 36px; }
        .kyc-logout-link { background: transparent; border: none; color: #71717A; font: inherit; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; transition: color .15s, background-color .15s; }
        .kyc-logout-link:hover { color: #F43F5E; background: rgba(244, 63, 94, 0.08); }
        .kyc-back-link { display: inline-flex; align-items: center; gap: 8px; color: #A1A1AA; font-size: 14px; font-weight: 500; text-decoration: none; margin-bottom: 24px; transition: color .15s, transform .15s; }
        .kyc-back-link:hover { color: #FFFFFF; transform: translateX(-3px); }
        .kyc-web-hero { margin-bottom: 28px; }
        .kyc-web-title { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(30px, 4vw, 44px); line-height: 1.15; font-weight: 600; margin: 0 0 12px; letter-spacing: -0.5px; }
        .kyc-web-subtitle { color: #A1A1AA; font-size: 15px; line-height: 1.6; margin: 0; max-width: 720px; }
    </style>
    @include('restaurant::onboarding.menu.styles')
    <link rel="stylesheet" href="{{ asset('css/restaurant-onboarding-responsive.css') }}">
</head>
<body class="onboarding-page kyc-web-mode">
    @include('restaurant::onboarding.toasts')
    <div class="kyc-web-wrapper menu-wrap @yield('menu_page_class')">
        <div class="kyc-web-container">
            <header class="kyc-web-header">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
                <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="kyc-logout-link" title="Logout">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </header>

            <div class="kyc-web-hero">
                <a href="@yield('back_url', route('restaurant.onboarding.kyc'))" class="kyc-back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>@yield('back_label', 'Back to onboarding steps')</span>
                </a>
                <h1 class="kyc-web-title">@yield('heading')</h1>
                <p class="kyc-web-subtitle">@yield('subheading')</p>
            </div>

            @yield('menu_content')
        </div>
    </div>
    {{-- Forms with data-confirm are confirmed by the shared popup. --}}
    <script src="{{ asset('js/app-popup.js') }}?v={{ filemtime(public_path('js/app-popup.js')) }}"></script>
    @stack('scripts')
</body>
</html>
