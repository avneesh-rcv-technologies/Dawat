@extends('website::layouts.app')

@section('title', 'About Us — Dawat')
@section('meta_description', 'Good food brings people together. Discover the story behind Dawat and a simpler way to find, order, and enjoy the food you love.')
@section('page_class', 'about-page')

@section('styles')
    @vite(['resources/css/website/pages/about.css'], 'build-website')
@endsection

@section('content')
@php
    // SVG paths are static artwork defined in this template.
    $icons = [
        'discover' => '<path d="M13 3H5v18h7M13 3v6h6l-6-6ZM8 8h2M8 12h3M8 16h2"/><circle cx="16" cy="16" r="4"/><path d="m19 19 3 3"/>',
        'order' => '<path d="m10 5 3 3 6-6M3 14h4v8H3zM7 20l6 2 8-4c1-1 0-3-2-2l-4 1M7 15h5c3 0 3 3 1 3h-3"/>',
        'cookie' => '<path d="M21 11a5 5 0 0 1-6-6 4 4 0 0 1-3-3 10 10 0 1 0 9 9Z"/><path d="M8 7h.01M7 13h.01M12 17h.01M14 11h.01" stroke-width="3"/>',
        'smile' => '<circle cx="12" cy="12" r="10"/><path d="M7 8c1-2 3-2 4 0M14 8c1-2 3-2 4 0M7 12h10a5 5 0 0 1-10 0Z"/>',
        'people' => '<circle cx="12" cy="5" r="2"/><circle cx="5" cy="15" r="2"/><circle cx="19" cy="15" r="2"/><path d="M8 11V9h8v2M12 7v5M2 22v-3h6v3M16 22v-3h6v3M9 22v-5h6v5"/>',
        'link' => '<path d="m10 14 4-4M8 16l-1 1a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0M16 8l1-1a4 4 0 0 1 6 6l-5 5a4 4 0 0 1-6 0" transform="translate(2 0) scale(.85 1)"/>',
        'document' => '<rect x="5" y="3" width="14" height="18" rx="3"/><path d="M9 7h6M9 11h6M9 15h4"/>',
        'megaphone' => '<path d="m4 9 15-5v16L4 15V9ZM4 15l3 6h3l-2-5M19 9l3 1v4l-3 1M4 10H2v4h2"/>',
    ];
    $features = [
    [
        'title' => 'Browse Menus',
        'copy' => 'Explore restaurants, cuisines, and catering menus across New York City and Long Island.',
        'icon' => 'discover',
    ],
    [
        'title' => 'Order Easily',
        'copy' => 'Choose your dishes, set your quantities, and place your bulk order in a few simple steps.',
        'icon' => 'order',
    ],
    [
        'title' => 'Cater Your Way',
        'copy' => 'From office lunches to events, pick the menu that suits your crowd and your occasion.',
        'icon' => 'cookie',
    ],
    [
        'title' => 'Made for Celebrations',
        'copy' => 'Bring people together with great food for every gathering, celebration, and special occasion.',
        'icon' => 'smile',
    ],
];
    $reasons = [
        ['copy' => 'We shape your brand’s voice and vision using insights and creativity', 'icon' => 'people'],
        ['copy' => 'ROI-focused campaigns that turn clicks into conversions', 'icon' => 'link'],
        ['copy' => 'Content that engages, builds loyalty, and grows reach', 'icon' => 'document'],
        ['copy' => 'Integrated campaigns designed to spark action and results', 'icon' => 'megaphone'],
    ];
    $restaurants = [
        ['image' => 'website/73964bf77c75b2eba31ec165f554afb485d18dad.jpg', 'badgeType' => 'rating', 'badgeText' => '4.9'],
        ['image' => 'website/images/restaurants/royal-feast-comp.jpg', 'badgeType' => 'rating', 'badgeText' => '4.9'],
        ['image' => 'website/images/restaurants/spice-palace-comp.jpg', 'badgeType' => 'sale', 'badgeText' => 'SALE'],
        ['image' => 'website/images/restaurants/flavor-garden-comp.jpg', 'badgeType' => 'sale', 'badgeText' => 'SALE'],
        ['image' => 'website/images/restaurants/pexels-vladimirsrajber-29789555.jpg', 'badgeType' => 'sale', 'badgeText' => 'SALE'],
    ];
@endphp

<section class="about-hero" aria-labelledby="about-title">
    <img class="about-hero-image" {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/about/enjoying-street-food-fest 1.png', '(max-width: 1023px) 1600px, 100vw', 1600) !!} alt="Friends enjoying a meal together" fetchpriority="high" loading="eager" decoding="async" fetchpriority="high">
    <div>
        <h1 data-aos="fade-up" id="about-title" class="about-serif">Good Food Has the Power to<br class="hidden sm:block"> Bring People Together</h1>
        <p data-aos="fade-up" data-aos-delay="150" class="about-copy">At Dawat, we believe every gathering deserves great food. We connect restaurants across New
York City and Long Island with customers who need food in bulk, and we deliver every order
right where you need it.</p>
    </div>
    {{-- <div data-aos="fade" data-aos-delay="300" class="about-hero-rating">
        <span class="about-stars" aria-label="4.9 out of 5 stars">★★★★☆</span>
        <span>4.9 rating Trusted by 100+ Happy Clients</span>
    </div> --}}
</section>

<section class="about-story" aria-labelledby="about-story-title">
            <img {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/OBJECTS (2).png', '300px', 320) !!} alt="" aria-hidden="true" class="pointer-events-none absolute  top-[350px] hidden h-[650px] w-auto opacity-70 md:block" loading="lazy" decoding="async">
            <img {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/OBJECTS (3).png', '300px', 320) !!} alt="" aria-hidden="true" class="pointer-events-none absolute  right-[0vw] top-14 hidden h-[650px] w-auto opacity-70 md:block" loading="lazy" decoding="async">
    <div class="about-shell about-story-grid">
        <div data-aos="zoom-in" class="about-plate">
            <svg viewBox="0 0 560 560" aria-hidden="true">
                <defs><path id="about-flavour-circle" d="M 136.6,484.8 A 250,250 0 0,1 393.5,57.2"/></defs>
                <path d="M 356,29 A 262,262 0 1,1 118,487" fill="none" stroke="#ccc" stroke-width="1"/>
                <text fill="white" font-family="NewYork, Georgia, serif" font-size="43"><textPath href="#about-flavour-circle" textLength="720" lengthAdjust="spacing">● More Flavour. More Dawat. ●</textPath></text>
            </svg>
            <img {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/about/plate-complete.png', '(min-width: 768px) 50vw, 90vw', 960) !!} alt="A colourful platter of rice, grilled chicken, vegetables, and chutney" loading="lazy" decoding="async">
        </div>
        <div class="about-story-content">
            <h2 data-aos="fade-up" id="about-story-title" class="about-serif">Born From a Love <span>for Good Food</span></h2>
            <div data-aos="fade-up" data-aos-delay="100" class="about-copy">
                <p>Dawat was created with one simple idea: feeding a crowd well should be effortless.</p>

<p>Planning a get together, an office lunch, or a big family celebration usually means calling around, comparing menus, and hoping everything arrives on time. Dawat brings local restaurants and bulk orders together on one platform, so great catering is only a few clicks away.</p>

<p>We’re building a food experience that connects people, restaurants, and the celebrations they share.</p>  </div>
            {{-- <div data-aos="fade-up" data-aos-delay="200" class="about-stats">
                <div class="about-stat"><strong>10M+</strong><span>App Downloads</span></div>
                <div class="about-stat"><strong>150+</strong><span>Countries</span></div>
                <div class="about-stat"><strong><span class="about-rating-star">★</span> 4.9</strong><span>Rating &amp; Reviews</span></div>
            </div> --}}
        </div>
    </div>
</section>

<section class="about-cravings" aria-labelledby="about-cravings-title">
    <div data-aos="fade-up" class="about-cravings-copy">
        <h2 id="about-cravings-title">YOUR EVENT,<strong>YOUR MENU</strong></h2>
        <p class="about-copy">Whether you need biryani trays for a gala, sandwich platters for a team meeting, or desserts for
a party, Dawat puts a wide range of restaurant menus at your fingertips.</p>
<div class="about-cuisines">
    Indian | Chinese | Italian | Mughlai<br>
    Mexican | Desserts | Beverages &amp; More
</div>
    </div>
    <img data-aos="fade-up" data-aos-delay="150" class="about-cravings-image" {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/about/girl.png', '(min-width: 768px) 50vw, 90vw', 960) !!} alt="A food lover in a red sweater enjoying takeaway pasta" loading="lazy" decoding="async">
</section>

<section class="about-features about-shell" aria-labelledby="about-features-title">
    <div data-aos="fade-up" class="about-section-heading">
        <h2 id="about-features-title" class="about-serif">More Than Just Food Delivery</h2>
        <p class="about-copy">Dawat isn’t built for one-off meals. It’s a platform for large orders, where restaurants cook at
scale and we make sure every tray arrives on time.</p>
    </div>
    <div class="about-feature-grid">
        @foreach($features as $feature)
            <article data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}" class="about-feature">
                <div class="about-feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$feature['icon']] !!}</svg></div>
                <h3>{{ $feature['title'] }}</h3>
                <p class="about-copy">{{ $feature['copy'] }}</p>
            </article>
        @endforeach
    </div>
</section>

 <section class="relative mx-auto grid w-[90%] max-w-[1700px] items-center gap-10 py-10 md:grid-cols-[.9fr_1.1fr] md:gap-16 md:py-20">
        <div data-aos="fade-right" class="relative mx-auto w-full max-w-[470px]">
            <img {!! \Modules\Website\Support\WebsiteImages::attributes('website/images/9b78d4783ee0deb27fedc85b8cdedf832ebd87ae.jpg', '(min-width: 768px) 50vw, 90vw', 960) !!} alt="Happy Dawat customer enjoying a meal" class="h-[390px] w-full rounded-[18px] object-cover sm:h-[520px] md:h-[610px]" loading="lazy" decoding="async">
            {{-- <div class="absolute -right-4 top-5 rounded-xl bg-[#302d29]/90 px-3 py-2 text-xs text-white shadow-xl backdrop-blur-sm sm:-right-7 sm:px-4"><div class="text-[#FFC400]">★★★★★</div><div class="mt-1 text-[10px] text-white/80">5.0 rating based on<br>30K+ Customers</div></div>
            <div class="absolute -left-3 bottom-10 rounded-xl bg-[#b9824d] px-3 py-2 text-white shadow-xl sm:-left-5 sm:px-4"><div class="flex -space-x-2">@foreach(['A','B','C','D'] as $initial)<span class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-[#b9824d] bg-white text-[9px] font-bold text-[#FF6210]">{{ $initial }}</span>@endforeach</div><div class="mt-1 text-[11px] font-medium">1M+ Happy Users</div></div> --}}
        </div>
        <div data-aos="fade-left" class="relative z-10">
            <h2 class="font-newyork text-[40px] leading-[1.04] text-white sm:text-[52px] md:text-[62px]">Why Dawat?</h2>
            <p class="mt-5 max-w-[650px] font-urbanist text-sm leading-relaxed text-white/65 sm:text-base">Dawat helps restaurants reach more people and grow through meaningful catering and delivery orders. We create a simple path from discovery to repeat customers.</p>
            @php $benefits = [
                ['icon' => '<path d="M12 3a5 5 0 0 1 5 5v2a3 3 0 0 1 2 2v4H5v-4a3 3 0 0 1 2-2V8a5 5 0 0 1 5-5Z"/><path d="M9 16v2h6v-2M9 9h6"/>', 'title' => 'More visibility', 'text' => 'Show your restaurant to customers who are ready to order.'],
                ['icon' => '<path d="M4 19h16M6 15l3-4 3 2 5-6 2 8M17 7h2v2"/>', 'title' => 'Stronger growth', 'text' => 'Reach larger catering orders and repeat customers.'],
                ['icon' => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 8h8M8 12h8M8 16h4"/>', 'title' => 'Simple management', 'text' => 'Keep orders, menus, and updates organised in one place.'],
                ['icon' => '<path d="M4 7h11v11H4zM15 11h4l2 3v4h-6z"/><circle cx="8" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/>', 'title' => 'Reliable delivery', 'text' => 'Give every order a smooth experience from kitchen to doorstep.'],
            ]; @endphp
            <div class="mt-8 grid gap-x-8 gap-y-6 sm:grid-cols-2">
                @foreach($benefits as $benefit)
                    <article class="partner-benefit flex gap-3"><span class="partner-benefit-icon mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/15 text-white/80"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $benefit['icon'] !!}</svg></span><div><h3 class="font-urbanist font-semibold text-white">{{ $benefit['title'] }}</h3><p class="mt-1 font-urbanist text-xs leading-relaxed text-white/55">{{ $benefit['text'] }}</p></div></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="pb-20 pt-4 md:pb-28">
        <div data-aos="fade-up" class="mx-auto mb-8 w-[90%] max-w-[820px] text-center"><h2 class="font-newyork text-[36px] leading-tight text-white sm:text-[46px] md:text-[56px]">Restaurants in<br>New York City and Long Island</h2><p class="mt-3 font-urbanist text-sm leading-relaxed text-white/60">See the restaurants already growing with Dawat across New York City and Long Island.</p></div>
        <div id="featured-restaurants" class="overflow-x-auto scrollbar-hide pb-4" role="region" aria-label="Featured restaurants" tabindex="0"> <div class="flex w-max min-w-full gap-3 md:gap-5 px-2 md:px-0 [justify-content:safe_center]">

                {{-- Restaurant Card 1 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Wah Fung No.1 Fast Food',
                    'location' => 'Chinatown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.9',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/spice-palace-comp.jpg'),
                    'delay' => 0
                ])

                {{-- Restaurant Card 2 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Adel\'s Famous Halal Food',
                    'location' => 'Midtown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.9',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/curry-house-comp.jpg'),
                    'delay' => 100
                ])

                {{-- Restaurant Card 3 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Xi\'an Famous Foods',
                    'location' => 'Chinatown | Manhattan',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/royal-feast-comp.jpg'),
                    'delay' => 200
                ])

                {{-- Restaurant Card 4 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Spicy Home Tasty',
                    'location' => 'Commack | Long Island',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/taj-flavors-comp.jpg'),
                    'delay' => 300
                ])

                {{-- Restaurant Card 5 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'All American Hamburger Drive In',
                    'location' => 'Massapequa | Long Island',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/masala-kitchen-comp.jpg'),
                    'delay' => 400
                ])

                {{-- Restaurant Card 6 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Spice Symphony',
                    'location' => 'Midtown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.8',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/spice-symphony-comp.jpg'),
                    'delay' => 500
                ])

                {{-- Restaurant Card 7 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Flavor Garden',
                    'location' => 'Long Island City | Queens',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => \Modules\Website\Support\WebsiteImages::url('website/images/restaurants/flavor-garden-comp.jpg'),
                    'delay' => 600
                ])

        </div>
    </div>
    </section>

{{-- <section id="download-app" class="about-download" aria-labelledby="about-download-title">
    <div class="about-download-grid">
        <div>
            <h2 data-aos="fade-up" id="about-download-title">Download the app now!</h2>
            <p data-aos="fade-up" data-aos-delay="100" class="about-copy">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since 1966, when designers at Letraset and James Mosley.</p>
            <div data-aos="fade-up" data-aos-delay="200" class="about-store-badges" aria-label="App downloads coming soon">
                <span class="about-store-badge" title="Google Play download coming soon">
                    <svg viewBox="0 0 28 30" aria-hidden="true"><path fill="#32BBFF" d="M1 1.5v27L15 15Z"/><path fill="#00D26A" d="M1 1.5 19 11 15 15Z"/><path fill="#FFCE00" d="m19 11 7 3.7q.6.3 0 .6L19 19l-4-4Z"/><path fill="#FF4854" d="M1 28.5 19 19l-4-4Z"/></svg>
                    <span><small>COMING SOON ON</small>Google Play</span>
                </span>
                <span class="about-store-badge" title="App Store download coming soon">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                    <span><small>COMING SOON ON</small>App Store</span>
                </span>
            </div>
            <p class="about-download-note">App downloads are coming soon.</p>
        </div>
        <div data-aos="fade-up" class="about-download-art">
            <img {!! \Modules\Website\Support\WebsiteImages::attributes('website/9fb055111b8ba926a7cb6eef104950b4819e61ce.png', '(min-width: 768px) 50vw, 90vw', 960) !!} alt="Dawat app welcome screen with biryani, naan, and curries" loading="lazy" decoding="async">
        </div>
    </div>
</section> --}}
@endsection

@section('scripts')
    @include('website::components.restaurant-autoscroll')
<script>
    (() => {
        const section = document.querySelector('.about-story');
        const plate = section?.querySelector('.about-plate');
        if (!plate) return;

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let frame = null;
        let lastAngle = null;
        let visible = true;

        const updateRotation = () => {
            frame = null;
            if (reducedMotion.matches) {
                plate.style.removeProperty('--plate-rotation');
                lastAngle = null;
                return;
            }
            if (!visible) return;

            // Rotate up to 90 degrees while the story travels through the viewport.
            const bounds = section.getBoundingClientRect();
            const progress = Math.max(0, Math.min(1,
                (window.innerHeight - bounds.top) / (window.innerHeight + bounds.height)
            ));
            const angle = Math.round(progress * 9000) / 100;
            if (angle === lastAngle) return;
            lastAngle = angle;
            plate.style.setProperty('--plate-rotation', `${angle}deg`);
        };

        const scheduleUpdate = () => {
            if ((visible || reducedMotion.matches) && frame === null) frame = window.requestAnimationFrame(updateRotation);
        };

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(([entry]) => {
                visible = entry.isIntersecting;
                if (!visible && !reducedMotion.matches) {
                    // Set the clamped endpoint once; scrolling back restores the same rotation.
                    const angle = entry.boundingClientRect.bottom < 0 ? 90 : 0;
                    if (angle !== lastAngle) plate.style.setProperty('--plate-rotation', `${angle}deg`);
                    lastAngle = angle;
                }
                scheduleUpdate();
            }, { rootMargin: '100px' }).observe(section);
        }

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate);
        window.addEventListener('load', scheduleUpdate, { once: true });
        reducedMotion.addEventListener('change', scheduleUpdate);
        scheduleUpdate();
    })();
</script>
@endsection
