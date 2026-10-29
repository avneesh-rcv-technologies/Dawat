{{--
    Navbar Component — Dawat Website
    Matches Figma Frame 2121449829
--}}
<nav class="flex items-center justify-between w-full">

    {{-- Logo (Upright Roman Serif matching Figma) --}}
    <a href="{{ url('/') }}" class="flex items-center group flex-shrink-0">
        <img src="{{ asset('website/images/dawat-wordmark.png') }}" alt="Dawat" class="block w-[94px] lg:w-[132px] h-auto" />
    </a>

    {{-- Center: Desktop Nav Links (Frame 1116607449) --}}
    @php
        $navLinks = [
            ['label' => 'Home',           'url' => url('/'),                        'active' => request()->is('/')],
            ['label' => 'About us',        'url' => route('website.about'),          'active' => request()->routeIs('website.about')],
            ['label' => 'How it works',    'url' => route('website.how-it-works'),   'active' => request()->routeIs('website.how-it-works')],
            ['label' => 'Blogs',           'url' => route('website.blogs'),          'active' => request()->routeIs('website.blogs')],
            ['label' => 'Contact',         'url' => route('website.contact'),        'active' => request()->routeIs('website.contact')],
            ['label' => 'Partner with us', 'url' => route('website.partner'),        'active' => request()->routeIs('website.partner')],
        ];
    @endphp

    <div class="hidden lg:flex items-center gap-9 px-4 py-2">
        @foreach($navLinks as $link)
            <a href="{{ $link['url'] }}"
               class="font-urbanist font-medium text-[17px] text-white leading-5 whitespace-nowrap opacity-90 hover:opacity-100 transition-opacity">
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Right: App Store & Play Store Buttons (Frame 2121449830) --}}
    {{-- <div class="hidden lg:flex items-center gap-[14px]">

        <a href="#" target="_blank"
           class="flex items-center gap-2 px-3 py-1.5 rounded-[9px] border border-white/60 bg-white/5 hover:bg-white/15 transition-all w-[140px] h-[44px]">
            <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                <path d="M3.18 1.6A2 2 0 0 0 2 3.38v17.24a2 2 0 0 0 1.18 1.78l9.37-10.4zm1.44 20.3 9.37-5.41-2.22-2.47zM20.35 10.5l-2.52-1.45-2.7 3 2.7 3 2.55-1.47A2.01 2.01 0 0 0 20.35 10.5zM4.62 2.1l11.07 6.39-2.22 2.47z"/>
            </svg>
            <div class="flex flex-col text-left leading-tight">
                <span class="font-outfit text-[7.5px] text-white/80 uppercase tracking-widest font-normal">GET IT ON</span>
                <span class="font-outfit text-[11px] text-white font-semibold">Google Play</span>
            </div>
        </a>


        <a href="#" target="_blank"
           class="flex items-center gap-2 px-3 py-1.5 rounded-[9px] border border-white/60 bg-white/5 hover:bg-white/15 transition-all w-[140px] h-[44px]">
            <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
            </svg>
            <div class="flex flex-col text-left leading-tight">
                <span class="font-outfit text-[7.5px] text-white/80 uppercase tracking-widest font-normal">Download on</span>
                <span class="font-outfit text-[11px] text-white font-semibold">App Store</span>
            </div>
        </a>
    </div> --}}

    {{-- Mobile Menu Trigger --}}
    <button id="mobile-menu-btn" class="lg:hidden p-2 text-white hover:text-[#FF6210] transition-colors" aria-label="Open Navigation">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
        </svg>
    </button>
</nav>

{{-- Mobile Drawer Overlay --}}
<div id="mobile-drawer" class="hidden fixed inset-0 z-[100] bg-[#000000] flex-col justify-between p-8 overscroll-contain">
    <div>
        <div class="flex items-center justify-between pb-6 border-b border-white/10">
            <img src="{{ asset('website/images/dawat-wordmark.png') }}" alt="Dawat" class="block w-[132px] h-auto" />
            <button id="close-mobile-btn" class="p-2 text-white hover:text-[#FF6210] transition-colors" aria-label="Close Navigation">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex flex-col gap-5 mt-8">
            @foreach($navLinks as $link)
                <a href="{{ $link['url'] }}"
                   class="font-urbanist font-medium text-2xl text-white hover:text-[#FF6210] transition-colors {{ $link['active'] ? 'text-[#FF6210]' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
