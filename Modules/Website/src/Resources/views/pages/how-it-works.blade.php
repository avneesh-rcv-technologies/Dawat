@extends('website::layouts.app')

@section('title', 'How It Works — Dawat')
@section('meta_description', 'Discover how Dawat makes finding restaurants, choosing your favourite dishes, and ordering food simple.')

@section('styles')
    <style>
        .how-hero {
            isolation: isolate;
            overflow: hidden;
            background: #000;
        }

        .how-hero::before {
            content: '';
            position: absolute;
            inset: -14px;
            z-index: 0;
            background-image: linear-gradient(180deg, rgba(0, 0, 0, .68), rgba(0, 0, 0, .84) 66%, #000 100%),
                url("{{ asset('website/images/enjoying-street-food-fest1.png') }}");
            background-position: center 42%;
            background-size: cover;
            filter: blur(1px);
        }

        .how-step-card,
        .how-faq-item {
            background: rgba(255, 255, 255, .045);
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .how-faq-item summary { list-style: none; }
        .how-faq-item summary::-webkit-details-marker { display: none; }
        .how-faq-item[open] .how-faq-icon { transform: rotate(45deg); color: #FF6210; }

        @media (prefers-reduced-motion: no-preference) {
            .how-step-card { transition: transform .35s ease, border-color .35s ease, box-shadow .35s ease; }
            .how-step-card:hover { transform: translateY(-5px); border-color: rgba(255, 98, 16, .42); }

            .how-feature {
                transition: transform .4s cubic-bezier(.22, 1, .36, 1), filter .4s ease;
            }

            .how-feature:hover {
                transform: translateY(-9px);
                filter: drop-shadow(0 12px 18px rgba(255, 98, 16, .12));
            }

            .how-feature .how-feature-line,
            .how-feature .how-feature-icon,
            .how-feature h3,
            .how-feature p {
                transition: width .4s cubic-bezier(.22, 1, .36, 1), transform .4s cubic-bezier(.22, 1, .36, 1), color .3s ease;
            }

            .how-feature:hover .how-feature-line { width: 100%; }
            .how-feature:hover .how-feature-icon { transform: scale(1.14) rotate(-5deg); }
            .how-feature:hover h3 { color: #FF7A35; }
            .how-feature:hover p { color: rgba(255, 255, 255, .82); }
        }
    </style>
@endsection

@section('content')
<div class="overflow-hidden bg-black">
    {{-- Intro hero --}}
    <section class="how-hero relative flex min-h-[390px] items-center justify-center px-5 pb-12 pt-28 text-center sm:min-h-[440px] md:min-h-[500px] md:pt-32">
        <div class="relative z-10 mx-auto max-w-[850px]">
            <h1 data-aos="fade-down" class="font-newyork text-[42px] leading-tight text-white sm:text-5xl md:text-[65px]">How it works</h1>
            <p data-aos="fade-up" data-aos-delay="120" class="mx-auto mt-4 max-w-[760px] font-urbanist text-sm leading-relaxed text-white/80 sm:text-base md:text-[18px]">
                At Dawat, we believe every craving deserves great food. We make it easy to discover restaurants, explore new flavours, and order your favourite meals right to your doorstep.
            </p>
        </div>
    </section>

    {{-- Brand story and app preview --}}
    <section class="relative mx-auto grid w-[90%] max-w-[1700px] items-center gap-8 pb-14 pt-8 md:grid-cols-2 md:gap-12 md:pb-20 md:pt-12">
        <img src="{{ asset('website/images/OBJECTS (3).png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute right-[-5.56vw] top-0 hidden h-[650px] w-auto opacity-70 md:block">
        <div data-aos="fade-right" class="relative mx-auto flex w-full max-w-[520px] items-center justify-center py-4 md:py-8">
            <img src="{{ asset('website/images/OBJECTS (4).png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute right-[-16%] top-1/2 z-0 h-[430px] w-auto -translate-y-1/2 opacity-90 md:hidden">
            <div class="absolute left-1/2 top-1/2 aspect-square w-[98%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#FF6210] sm:w-[88%]"></div>
            <img src="{{ asset('website/images/4647d78a91cd2c0c1d9eda99c2429ba4d36e4e7f.png') }}" alt="Dawat app screens showing restaurant discovery and ordering" class="how-phone-art relative left-[8%] z-10 w-[98%] drop-shadow-2xl md:left-10" loading="lazy">
        </div>
        <img src="{{ asset('website/images/OBJECTS (2).png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute left-[-5.56vw] top-[250px] hidden h-[650px] w-auto opacity-70 md:block">

        <div data-aos="fade-left" class="relative z-10">
            <p class="mb-1 font-newyork text-[24px] text-white/90 sm:text-[30px]">Born From a Love</p>
            <h2 class="font-newyork text-[38px] leading-[1.04] text-white sm:text-[48px] md:text-[54px]">For Good Food</h2>
            <div class="mt-5 space-y-3 font-urbanist text-[14px] leading-relaxed text-white/65 sm:text-[15px]">
                <p>Dawat was created with one simple idea — finding and ordering great food should be effortless.</p>
                <p>From local favourites to popular restaurants, we bring a wide variety of cuisines and dishes together in one platform. Whether you’re looking for a quick lunch, a family dinner, or a late-night craving, there’s something for everyone.</p>
                <p>We’re building a food experience that connects people, restaurants, and the food they love.</p>
            </div>
            <div class="mt-7 grid grid-cols-3 divide-x divide-white/15">
                <div class="pr-3"><strong class="block font-urbanist text-[24px] font-bold text-white sm:text-[30px]">10M+</strong><span class="mt-1 block text-xs text-white/65 sm:text-sm">App Downloads</span></div>
                <div class="px-3"><strong class="block font-urbanist text-[24px] font-bold text-white sm:text-[30px]">150+</strong><span class="mt-1 block text-xs text-white/65 sm:text-sm">Countries</span></div>
                <div class="pl-3"><strong class="block font-urbanist text-[23px] font-bold text-white sm:text-[30px]"><span class="text-[#FFC400]">★</span> 4.9</strong><span class="mt-1 block text-xs text-white/65 sm:text-sm">Rating &amp; Reviews</span></div>
            </div>
        </div>
    </section>

    {{-- Four ordering steps --}}
    <section class="mx-auto grid w-[90%] max-w-[1700px] grid-cols-1 gap-3 pb-16 sm:grid-cols-2 lg:grid-cols-4 md:pb-24">
@php
    $steps = [
        [
            'icon' => '<path d="M7 3h8l5 5v5"/><path d="M15 3v5h5"/><path d="M7 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10"/><path d="M7 11h7M7 14h5"/><circle cx="17" cy="17" r="3"/><path d="m19.2 19.2 2 2"/>',
            'title' => 'Step 1',
            'text' => 'Explore restaurants, cuisines, and hidden food favourites around you.',
        ],
        [
            'icon' => '<path d="M3 13h4l2 2h5a2 2 0 0 0 2-2v-1h-5"/><path d="M7 13V9h5l3 3"/><path d="m14 6 2 2 4-4"/><path d="M3 17h12a3 3 0 0 0 3-3v-1"/><path d="M5 13v4"/>',
            'title' => 'Step 2',
            'text' => 'Find what you want, customize your meal, and place your order in just a few taps.',
        ],
        [
            'icon' => '<path d="M20.5 12.5a8.5 8.5 0 1 1-8.5-8.5 3 3 0 0 0 3 3 3 3 0 0 0 3 3 2.5 2.5 0 0 1 2.5 2.5Z"/><circle cx="8" cy="10" r="1"/><circle cx="12" cy="15" r="1"/><circle cx="16" cy="11" r="1"/><path d="M5 5 3.5 3.5"/>',
            'title' => 'Step 3',
            'text' => 'From everyday meals to indulgent treats, choose something for every mood and craving.',
        ],
        [
            'icon' => '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="15" cy="10" r="1"/><path d="M8 14c.8 2 2.1 3 4 3s3.2-1 4-3"/><path d="M8 4.5 9 3M16 4.5 15 3"/>',
            'title' => 'Step 4',
            'text' => 'A simple way to discover, explore, and enjoy more of the food you love.',
        ],
    ];
@endphp 
        @foreach($steps as $index => $step)
            <article data-aos="fade-up" data-aos-delay="{{ $index * 80 }}" class="how-step-card rounded-[18px] p-5 sm:p-6">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-lg bg-[#FF6210] text-white">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $step['icon'] !!}</svg>
                </div>
                <h3 class="font-urbanist text-lg font-semibold text-white">{{ $step['title'] }}</h3>
                <p class="mt-2 font-urbanist text-sm leading-relaxed text-white/60">{{ $step['text'] }}</p>
            </article>
        @endforeach
    </section>

    {{-- Join / delivery features --}}
    <section class="mx-auto w-[90%] max-w-[1700px] pb-16 md:pb-24">
        <div data-aos="fade-up" class="mx-auto mb-8 max-w-[820px] text-center">
            <h2 class="font-newyork text-[34px] text-white sm:text-[42px] md:text-[48px]">Easy steps to join us</h2>
            <p class="mx-auto mt-3 max-w-[760px] font-urbanist text-sm leading-relaxed text-white/65 sm:text-base">Discover a smoother way to find the food you love. Browse nearby restaurants, pick your favourites, and let Dawat take care of the rest.</p>
        </div>

        <div data-aos="zoom-in" class="relative mb-8 h-[190px] overflow-hidden rounded-[18px] bg-[#FF6210] sm:h-[280px] md:h-[360px] md:rounded-[24px]">
            <img src="{{ asset('website/images/enjoying-street-food-fest 1.png') }}" alt="Friends sharing a meal together" class="absolute inset-0 h-full w-full object-cover opacity-40 mix-blend-multiply" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-br from-[#ff7a27]/70 via-[#ff6210]/55 to-[#9e2b00]/65"></div>
            <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(rgba(255,255,255,.6) .7px, transparent .7px); background-size: 5px 5px;"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full border border-white/60 bg-black/15 text-white backdrop-blur-sm sm:h-[72px] sm:w-[72px]" aria-hidden="true">
                    <svg class="ml-1 h-6 w-6 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.8c0-.8.9-1.3 1.6-.9l9.2 6.2c.6.4.6 1.3 0 1.8l-9.2 6.2c-.7.5-1.6 0-1.6-.9V5.8Z"/></svg>
                </div>
            </div>
        </div>

        @php
            $features = [
                ['icon' => '<path d="m12 2 8 4.5v9L12 20l-8-4.5v-9L12 2Z"/><path d="m4.5 6.8 7.5 4.3 7.5-4.3M12 11v9"/>', 'title' => 'Book Your Shipment', 'text' => 'Schedule shipments in minutes with our easy-to-use platform designed for speed and convenience.'],
                ['icon' => '<circle cx="6" cy="5" r="2"/><circle cx="18" cy="12" r="2"/><circle cx="6" cy="19" r="2"/><path d="m8 6 8 5M8 18l8-5"/>', 'title' => 'A Smarter Shipping', 'text' => 'Leverage intelligent tools and real-time insights to make shipping more efficient than ever.'],
                ['icon' => '<rect x="4" y="3" width="16" height="18" rx="4"/><path d="M9 10h6M9 14h6"/>', 'title' => 'Manage With Ease', 'text' => 'Track, update, and control your deliveries through one intuitive dashboard built for simplicity.'],
                ['icon' => '<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/><path d="M14 14h7"/>', 'title' => 'Fast & Secure Delivery', 'text' => 'Enjoy rapid, reliable, and safe delivery backed by trusted carriers and smart logistics.'],
            ];
        @endphp
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            @foreach($features as $index => $feature)
                <article data-aos="fade-up" data-aos-delay="{{ $index * 70 }}" class="how-feature bg-transparent px-0 pb-2 pt-4">
                    <div class="mb-5 h-1 w-full bg-white/10">
                        <div class="how-feature-line h-full {{ $index === 0 ? 'w-1/2 bg-[#FF6210]' : 'w-0 bg-[#FF6210]' }}"></div>
                    </div>
                    <svg class="how-feature-icon mb-4 h-6 w-6 text-[#FF6210]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $feature['icon'] !!}</svg>
                    <h3 class="font-urbanist text-xl font-semibold text-white md:text-[25px]">{{ $feature['title'] }}</h3>
                    <p class="mt-3 font-urbanist text-base leading-relaxed text-white/60 md:text-[18px]">{{ $feature['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Frequently asked questions --}}
    <section class="mx-auto grid w-[90%] max-w-[1700px] gap-8 pb-20 md:grid-cols-[.8fr_1.2fr] md:gap-12 md:pb-28">
        <div data-aos="fade-right">
            <h2 class="font-urbanist text-[34px] font-bold leading-[1.02] tracking-tight text-white sm:text-[42px] md:text-[48px]">Frequently<br>Asked Questions</h2>
            <p class="mt-4 max-w-[420px] font-urbanist text-sm leading-relaxed text-white/60 sm:text-base">Have questions? Our FAQ section has you covered with quick answers to the most common inquiries.</p>
        </div>
        <div class="space-y-3">
            @php
                $faqs = [
                    ['q' => 'What is Dawat?', 'a' => 'Dawat helps you discover local restaurants, explore menus, and order the meals you love in one simple place.'],
                    ['q' => 'How do I place an order?', 'a' => 'Choose a restaurant, add your dishes to the cart, customize your order, and follow the checkout steps to place it.'],
                    ['q' => 'Can I customize my meal?', 'a' => 'Yes. Available options and special instructions are shown on each dish so you can make your order your own.'],
                    ['q' => 'How can I track my delivery?', 'a' => 'After placing your order, use the order status in the app to follow its progress and delivery updates.'],
                    ['q' => 'Which restaurants are available?', 'a' => 'Dawat features participating restaurants near you, with availability depending on your delivery location.'],
                    ['q' => 'What if I need help with my order?', 'a' => 'Contact the Dawat support team from the app and include your order details so we can help promptly.'],
                ];
            @endphp
            @foreach($faqs as $index => $faq)
                <details data-faq-item data-aos="fade-up" data-aos-delay="{{ min($index * 50, 200) }}" class="how-faq-item rounded-xl px-4 py-4 sm:px-5">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 font-urbanist text-sm font-semibold text-white sm:text-base">
                        {{ $faq['q'] }}
                        <span class="how-faq-icon shrink-0 text-2xl font-light leading-none text-white transition-transform duration-300" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-3 pr-6 font-urbanist text-sm leading-relaxed text-white/60">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const faqItems = document.querySelectorAll('[data-faq-item]');

            faqItems.forEach((item) => {
                item.addEventListener('toggle', () => {
                    if (!item.open) return;

                    faqItems.forEach((otherItem) => {
                        if (otherItem !== item) otherItem.open = false;
                    });
                });
            });
        });
    </script>
@endsection
