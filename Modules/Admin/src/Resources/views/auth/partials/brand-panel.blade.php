@php
    // Shared left panel for the admin / restaurant auth screens, styled like the public website.
    $isRestaurantAuth = request()->routeIs('restaurant.*');
    $authFeatures = $isRestaurantAuth
        ? [
            ['icon' => 'restaurant_menu', 'title' => 'Menu & items', 'text' => 'Keep dishes, prices and availability up to date.'],
            ['icon' => 'receipt_long', 'title' => 'Live orders', 'text' => 'Accept, prepare and track every order in one place.'],
            ['icon' => 'payments', 'title' => 'Payouts', 'text' => 'Follow earnings and settlements as they happen.'],
        ]
        : [
            ['icon' => 'storefront', 'title' => 'Restaurant partners', 'text' => 'Review onboarding, KYC and approvals.'],
            ['icon' => 'receipt_long', 'title' => 'Orders & payments', 'text' => 'Monitor every order from checkout to delivery.'],
            ['icon' => 'tune', 'title' => 'Platform settings', 'text' => 'Configure the website, mobile app and integrations.'],
        ];
@endphp

<aside class="auth-visual">
    <img class="auth-visual-photo" alt=""
        {!! \Modules\Website\Support\WebsiteImages::attributes('website/73964bf77c75b2eba31ec165f554afb485d18dad.jpg', '(min-width: 1024px) 55vw, 1px', 1600) !!}
        decoding="async" fetchpriority="high">

    <div class="auth-visual-top">
        <a href="{{ route('website.home') }}" aria-label="Dawat home">
            <img class="auth-wordmark" src="{{ asset('website/images/dawat-wordmark.png') }}" alt="Dawat">
        </a>
    </div>

    <div class="auth-visual-copy">
        <span class="auth-eyebrow"><span class="auth-eyebrow-dot"></span>{{ $isRestaurantAuth ? 'Restaurant partner portal' : 'Admin workspace' }}</span>
        <h1>
            @if ($isRestaurantAuth)
                Your kitchen,<br><span>one dashboard away.</span>
            @else
                Tasty meals,<br><span>run from one place.</span>
            @endif
        </h1>
        <p>{{ $isRestaurantAuth ? 'Manage your restaurant on Dawat — menus, orders and payouts, all in one workspace.' : 'Everything that powers Dawat — partners, orders, content and settings — in a single workspace.' }}</p>

        <ul class="auth-features">
            @foreach ($authFeatures as $feature)
                <li>
                    <span class="auth-feature-icon"><span class="material-icons-outlined">{{ $feature['icon'] }}</span></span>
                    <span>
                        <strong>{{ $feature['title'] }}</strong>
                        <small>{{ $feature['text'] }}</small>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</aside>
