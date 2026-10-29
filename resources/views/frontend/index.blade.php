<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dawat - Home</title>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @vite(['Modules/Backend/src/Resources/assets/css/app.css', 'Modules/Backend/src/Resources/assets/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --color-bg: #000000 !important;
            --color-text-primary: #ffffff !important;
            --color-sidebar-bg: #000000 !important;
            --color-topbar-search-bg: #000000 !important;
            --color-input-bg: #000000 !important;
            --color-white: #ffffff !important;
            --color-black: #000000 !important;
        }

        html,
        body {
            background-color: #000000 !important;
            color: #ffffff !important;
            background: #000000 !important;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        [class*="animate-"] {
            animation-fill-mode: both;
        }

        @keyframes fadeDown {
            0% {
                opacity: 0;
                transform: translateY(-30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideLeft {
            0% {
                opacity: 0;
                transform: translateX(60px);
            }

            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-18px);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        @keyframes glowPulse {

            0%,
            100% {
                opacity: 0.15;
            }

            50% {
                opacity: 0.35;
            }
        }

        .animate-fade-down {
            animation: fadeDown 0.8s ease both;
        }

        .animate-fade-up {
            animation: fadeUp 0.8s ease both;
        }

        .animate-slide-left {
            animation: slideLeft 0.9s ease both;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-spin-slow {
            animation: spin 12s linear infinite;
        }

        .animate-pulse-slow {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .animate-glow-pulse {
            animation: glowPulse 3s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-black text-white overflow-hidden h-screen w-screen">

    {{-- ===== SECTION 1: HEADER ===== --}}
    <header class="w-full border-b border-white/10 bg-black/80 backdrop-blur-sm sticky top-0 z-50 animate-fade-down">
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">

            {{-- Logo --}}
            <a href="#"
                class="text-2xl font-bold tracking-widest uppercase text-white hover:text-white/70 transition-colors duration-300">
               <img src="{{asset('dawat.png')}}" alt="Dawat Logo" class="w-32 ">
            </a>

            {{-- Download Links --}}
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ route('restaurant.onboarding.form') }}"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-medium">Partner with Us</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M3.18 23.76c.31.17.67.19 1.01.06l11.93-6.9-2.58-2.58-10.36 9.42zm-1.15-21.4C1.75 2.7 2 3.1 2 3.62v16.76c0 .52.25.92.63 1.16l.1.06 9.39-9.39v-.22L2.13 2.3l-.1.06zM20.65 10.2l-2.55-1.47-2.87 2.87 2.87 2.87 2.58-1.49c.74-.43.74-1.35-.03-1.78zm-18.47 12l10.36-9.42 2.58 2.58-11.93 6.9c-.34.19-.71.2-1.01.06z" />
                    </svg>
                    <span class="font-medium">Android</span>
                </a>
                <a href="#"
                    class="flex items-center gap-2 text-xs text-white/60 hover:text-white transition-colors duration-200 border border-white/20 px-4 py-2 rounded-lg hover:border-white/40 hover:bg-white/5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98l-.09.06c-.22.15-2.19 1.28-2.17 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.77M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                    </svg>
                    <span class="font-medium">iOS</span>
                </a>
            </div>

            {{-- Mobile Hamburger --}}
            <button class="md:hidden flex flex-col gap-1.5 group" aria-label="Menu">
                <span class="block w-6 h-0.5 bg-white transition-all duration-300 group-hover:w-4"></span>
                <span class="block w-6 h-0.5 bg-white"></span>
                <span class="block w-4 h-0.5 bg-white transition-all duration-300 group-hover:w-6"></span>
            </button>

        </div>
    </header>

    {{-- ===== SECTION 2: HERO ===== --}}
    <section class="min-h-screen flex items-center bg-black relative overflow-hidden">

        {{-- Background decorative rings --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-[600px] h-[600px] rounded-full border border-white/5 animate-spin-slow"></div>
            <div class="absolute w-[400px] h-[400px] rounded-full border border-white/5 animate-spin-slow"
                style="animation-direction:reverse; animation-duration:18s;"></div>
            <div class="absolute w-[800px] h-[800px] rounded-full border border-white/[0.03] animate-spin-slow"
                style="animation-duration:25s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 py-20 grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">

            {{-- Left: Text --}}
            <div class="space-y-8">

                {{-- Welcome badge --}}
                <div class="animate-fade-up delay-100">
                    <span
                        class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.3em] uppercase text-white/50 border border-white/20 px-4 py-2 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse-slow"></span>
                        Welcome to Dawat
                    </span>
                </div>

                {{-- Main heading --}}
                <div class="animate-fade-up delay-200">
                    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight tracking-tight text-white">
                        Welcome to Dawat

                    </h1>
                </div>

                {{-- Subtext --}}
                <p class="text-white/40 text-base leading-relaxed max-w-md animate-fade-up delay-300">
                    Dawat has been trusted and loved by thousands of customers. Order the finest catering experience on
                    your Android or iOS device — reviews and ratings are much appreciated.
                </p>

                {{-- App Buttons --}}
                <div class="flex flex-wrap gap-4 animate-fade-up delay-400">
                    <a href="#"
                        class="flex items-center gap-3 border border-white/20 bg-white/5 hover:bg-white hover:text-black transition-all duration-300 px-5 py-3 rounded-xl group hover:scale-105 active:scale-95">
                        <svg class="w-6 h-6 group-hover:text-black transition-colors duration-300" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M3.18 23.76c.31.17.67.19 1.01.06l11.93-6.9-2.58-2.58-10.36 9.42zm-1.15-21.4C1.75 2.7 2 3.1 2 3.62v16.76c0 .52.25.92.63 1.16l.1.06 9.39-9.39v-.22L2.13 2.3l-.1.06zM20.65 10.2l-2.55-1.47-2.87 2.87 2.87 2.87 2.58-1.49c.74-.43.74-1.35-.03-1.78zm-18.47 12l10.36-9.42 2.58 2.58-11.93 6.9c-.34.19-.71.2-1.01.06z" />
                        </svg>
                        <div>
                            <div
                                class="text-white/40 group-hover:text-black/50 text-[10px] leading-none transition-colors duration-300">
                                Get it on</div>
                            <div class="text-sm font-semibold">Google Play</div>
                        </div>
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 border border-white/20 bg-white/5 hover:bg-white hover:text-black transition-all duration-300 px-5 py-3 rounded-xl group hover:scale-105 active:scale-95">
                        <svg class="w-6 h-6 group-hover:text-black transition-colors duration-300" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98l-.09.06c-.22.15-2.19 1.28-2.17 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.77M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                        </svg>
                        <div>
                            <div
                                class="text-white/40 group-hover:text-black/50 text-[10px] leading-none transition-colors duration-300">
                                Get it on</div>
                            <div class="text-sm font-semibold">App Store</div>
                        </div>
                    </a>
                </div>

                {{-- Stats row --}}
                <div class="flex gap-8 pt-2 animate-fade-up delay-500">
                    <div>
                        <p class="text-2xl font-bold text-white">10M+</p>
                        <p class="text-xs text-white/40 mt-1">Downloads</p>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">150+</p>
                        <p class="text-xs text-white/40 mt-1">Countries</p>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">4.9★</p>
                        <p class="text-xs text-white/40 mt-1">Rating</p>
                    </div>
                </div>

            </div>

            {{-- Right: Image --}}
            <div class="flex justify-center animate-slide-left delay-300">
                <div class="relative w-full max-w-md">
                    {{-- Glow blob behind image --}}
                    <div class="absolute inset-0 bg-white/10 rounded-3xl blur-3xl scale-75 animate-glow-pulse"></div>

                    {{-- Image container --}}
                    <div class="relative animate-float">
                        <img src="{{asset('banner.png')}}" alt="Dawat Catering"
                            class="w-full h-auto rounded-3xl border-2 border-white/20 shadow-2xl object-cover">

                        {{-- Decorative overlay --}}
                        <div
                            class="absolute inset-0 rounded-3xl bg-gradient-to-t from-black/40 via-transparent to-transparent">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</body>

</html>