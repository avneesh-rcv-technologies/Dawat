<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    @include('partials.favicon')
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @if(\Modules\Seo\Support\SeoSettings::enabled())
        @php
            $seoPageConfigured = collect(config('seo.pages', []))->contains(fn ($page) => ($page['route'] ?? null) === request()->route()?->getName());
            $seoSource = null;
            $seoModel = null;
            if (request()->routeIs('website.blog-detail', 'website.blog-preview')) {
                $seoModel = $blog ?? null;
                $seoSource = $seoModel ? 'blog' : null;
            } elseif (request()->routeIs('website.author-detail')) {
                $seoModel = $author ?? null;
                $seoSource = $seoModel ? 'blog_author' : null;
            }
            $seoTitle = $seoPageConfigured || $seoModel ? null : html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES, 'UTF-8');
            $seoDescription = $seoPageConfigured || $seoModel ? null : html_entity_decode(trim($__env->yieldContent('meta_description')), ENT_QUOTES, 'UTF-8');
        @endphp
        <x-seo-meta :source="$seoSource" :model="$seoModel"
            :title="$seoTitle ?: null" :description="$seoDescription ?: null"
            :robots="request()->routeIs('website.blog-preview') ? 'noindex,nofollow' : null" />
        <x-seo-global-head-config />
    @else
        <title>@yield('title', 'Dawat — Where Every Celebration Becomes a Feast')</title>
        <meta name="description" content="@yield('meta_description', 'Bulk food ordering for events, office lunches, and every big gathering. Restaurants cook, Dawat
delivers, and everyone gets fed.')">
    @endif

    <link rel="preload" href="{{ Vite::asset('resources/fonts/NewYork.otf', 'build-website') }}" as="font" type="font/otf" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Urbanist:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @yield('styles')

    {{-- Website Vite build (manifest: public/build-website, hot: public/website.hot). --}}
    @vite(['resources/css/website.css', 'resources/js/website.js'], 'build-website')
</head>
<body class="bg-black text-white selection:bg-[#FF6210] selection:text-white @yield('page_class')">
    @if(\Modules\Seo\Support\SeoSettings::enabled())
        <x-seo-global-body-config />
    @endif

    {{-- Header / Navbar --}}
    @include('website::components.header')

    {{-- Main Body --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('website::components.footer')

    {{-- Scripts --}}
    <script>
        // Scroll header effect
        const mainHeader = document.getElementById('main-header');
        let headerScrolled = null;
        let headerFrame = null;
        const updateHeader = () => {
            headerFrame = null;
            const scrolled = window.scrollY > 40;
            if (!mainHeader || scrolled === headerScrolled) return;
            headerScrolled = scrolled;
            if (scrolled) {
                mainHeader.classList.add('bg-black/95', 'backdrop-blur-md', 'shadow-2xl', 'py-3');
                mainHeader.classList.remove('bg-transparent', 'py-5');
            } else {
                mainHeader.classList.remove('bg-black/95', 'backdrop-blur-md', 'shadow-2xl', 'py-3');
                mainHeader.classList.add('bg-transparent', 'py-5');
            }
        };
        window.addEventListener('scroll', () => {
            if (headerFrame === null) headerFrame = requestAnimationFrame(updateHeader);
        }, { passive: true });
        updateHeader();

        // Mobile drawer toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileDrawer = document.getElementById('mobile-drawer');
        const closeMobileBtn = document.getElementById('close-mobile-btn');

        // Keep the fixed drawer outside the fixed header's backdrop-filter stacking context.
        if (mobileDrawer) {
            document.body.appendChild(mobileDrawer);
        }

        if (mobileMenuBtn && mobileDrawer) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileDrawer.classList.toggle('hidden');
                mobileDrawer.classList.toggle('flex');
                const isOpen = mobileDrawer.classList.contains('flex');
                document.body.classList.toggle('overflow-hidden', isOpen);
                mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
            });
        }
        if (closeMobileBtn && mobileDrawer) {
            closeMobileBtn.addEventListener('click', () => {
                mobileDrawer.classList.add('hidden');
                mobileDrawer.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                mobileMenuBtn?.setAttribute('aria-expanded', 'false');
            });
        }

        // Emulated mobile browsers can disable CSS :hover; handle mouse hover and touch taps directly.
        const restaurantCardAt = (target) => target instanceof Element ? target.closest('.restaurant-hover') : null;
        let activeRestaurantCard = null;
        const highlightRestaurantCard = (card) => {
            if (card === activeRestaurantCard) return;
            activeRestaurantCard?.classList.remove('touch-hover');
            card?.classList.add('touch-hover');
            activeRestaurantCard = card;
        };

        document.addEventListener('pointerdown', (event) => {
            const card = restaurantCardAt(event.target);
            highlightRestaurantCard(card);
        });

        document.addEventListener('pointerover', (event) => {
            if (event.pointerType !== 'mouse') return;
            const card = restaurantCardAt(event.target);
            highlightRestaurantCard(card);
        });

        document.addEventListener('pointerout', (event) => {
            if (event.pointerType !== 'mouse') return;
            const card = restaurantCardAt(event.target);
            if (card && !(event.relatedTarget instanceof Node && card.contains(event.relatedTarget))) {
                highlightRestaurantCard(restaurantCardAt(event.relatedTarget));
            }
        });

        document.addEventListener('pointerup', (event) => {
            if (event.pointerType !== 'touch') return;
            const card = restaurantCardAt(event.target);
            highlightRestaurantCard(card);
        });
    </script>

    @yield('scripts')
</body>
</html>

