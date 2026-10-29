<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Dawat — Where Every Celebration Becomes a Feast')</title>
    <meta name="description" content="@yield('meta_description', 'Discover restaurants, explore delicious cuisines, and order your favourite meals with Dawat — fast, simple, and made for every craving.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Urbanist:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF6210',
                        'primary-dark': '#e0530b',
                        'dawat-orange': '#FF6210',
                        'dawat-black': '#000000',
                    },
                    fontFamily: {
                        'newyork': ['NewYork', 'Georgia', 'serif'],
                        'urbanist': ['"Urbanist"', 'sans-serif'],
                        'outfit': ['"Outfit"', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow': '0 0 50px rgba(255, 98, 16, 0.35)',
                        'glow-lg': '0 0 100px rgba(255, 98, 16, 0.45)',
                        'card': '0 10px 30px rgba(0, 0, 0, 0.4)',
                    }
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }

        @font-face {
            font-family: 'NewYork';
            src: url("{{ asset('website/fonts/NewYork PERSONAL USE.otf') }}") format('opentype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        body {
            font-family: 'Urbanist', sans-serif;
            background-color: #000000;
            color: #FFFFFF;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Dawat's dark surfaces with the brand orange as the scroll accent. */
        :root {
            scrollbar-color: #FF6210 #141414;
            scrollbar-width: thin;
        }

        *::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        *::-webkit-scrollbar-track {
            background: #141414;
        }

        *::-webkit-scrollbar-thumb {
            background: #FF6210;
            border: 2px solid #141414;
            border-radius: 999px;
        }

        *::-webkit-scrollbar-thumb:hover {
            background: #e0530b;
        }

        *::-webkit-scrollbar-corner {
            background: #141414;
        }

        .font-newyork {
            font-family: 'NewYork', Georgia, serif;
            font-style: normal !important;
        }

        /* Hide scrollbars but allow scrolling */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Glassmorphism effects */
        .glass-pill {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .glass-card {
            background: radial-gradient(97.57% 210.75% at 0.9% 2.98%, rgba(199, 199, 199, 0.12) 0%, rgba(255, 255, 255, 0.18) 100%);
            backdrop-filter: blur(21px);
            -webkit-backdrop-filter: blur(21px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-category {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-top-gradient {
            background: linear-gradient(180deg, rgba(4, 4, 4, 0.35) 0%, rgba(4, 4, 4, 0) 65%);
        }

        .hero-bottom-gradient {
            background: linear-gradient(0deg, #000000 0%, rgba(4, 4, 4, 0.4) 40%, rgba(4, 4, 4, 0) 100%);
        }

        /* Clean Nav link hover */
        .nav-link {
            transition: color 0.2s ease, opacity 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: #FFFFFF;
            opacity: 1;
        }

        /* Interactive card hover */
        .restaurant-hover {
            transition: translate 0.45s cubic-bezier(0.22, 1, 0.36, 1),
                        scale 0.45s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.45s ease;
        }
        .restaurant-hover:hover {
            translate: 0 -6px;
            scale: 1.015;
            box-shadow: 0 14px 26px rgba(0, 0, 0, 0.32);
            z-index: 20;
        }

        /* A tap stands in for hover on touchscreens and in mobile emulation. */
        .restaurant-hover.touch-hover {
            translate: 0 -6px;
            scale: 1.015;
            box-shadow: 0 14px 26px rgba(0, 0, 0, 0.32);
            z-index: 20;
        }

        @media (hover: none) and (pointer: coarse) {
            .restaurant-hover:active {
                translate: 0 -6px;
                scale: 1.015;
                box-shadow: 0 14px 26px rgba(0, 0, 0, 0.32);
                z-index: 20;
            }
        }
    </style>

    @yield('styles')
</head>
<body class="bg-black text-white selection:bg-[#FF6210] selection:text-white @yield('page_class')">

    {{-- Header / Navbar --}}
    @include('website::components.header')

    {{-- Main Body --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('website::components.footer')

    {{-- Scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        if (window.AOS) {
            AOS.init({
                duration: 700,
                easing: 'ease-out-cubic',
                offset: 60,
                once: true,
                mirror: false,
                disable: () => window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            });
        }

        // Scroll header effect
        const mainHeader = document.getElementById('main-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                mainHeader.classList.add('bg-black/95', 'backdrop-blur-md', 'shadow-2xl', 'py-3');
                mainHeader.classList.remove('bg-transparent', 'py-5');
            } else {
                mainHeader.classList.remove('bg-black/95', 'backdrop-blur-md', 'shadow-2xl', 'py-3');
                mainHeader.classList.add('bg-transparent', 'py-5');
            }
        });

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
        const clearRestaurantCardHighlights = (except = null) => {
            document.querySelectorAll('.restaurant-hover.touch-hover').forEach((card) => {
                if (card !== except) card.classList.remove('touch-hover');
            });
        };

        document.addEventListener('pointerdown', (event) => {
            const card = restaurantCardAt(event.target);
            clearRestaurantCardHighlights(card);
            card?.classList.add('touch-hover');
        });

        document.addEventListener('pointerover', (event) => {
            if (event.pointerType !== 'mouse') return;
            const card = restaurantCardAt(event.target);
            clearRestaurantCardHighlights(card);
            card?.classList.add('touch-hover');
        });

        document.addEventListener('pointerout', (event) => {
            if (event.pointerType !== 'mouse') return;
            const card = restaurantCardAt(event.target);
            if (card && !(event.relatedTarget instanceof Node && card.contains(event.relatedTarget))) {
                card.classList.remove('touch-hover');
            }
        });

        document.addEventListener('pointerup', (event) => {
            if (event.pointerType !== 'touch') return;
            const card = restaurantCardAt(event.target);
            clearRestaurantCardHighlights(card);
            card?.classList.add('touch-hover');
        });
    </script>

    @yield('scripts')
</body>
</html>

