@extends('website::layouts.app')

@section('title', 'Partner With Us — Dawat')
@section('meta_description', 'Partner with Dawat and grow your restaurant with more customers, orders, and support.')

@section('styles')
<style>
    .partner-hero::before { content: ''; position: absolute; inset: -8px; background: linear-gradient(180deg, rgba(0,0,0,.52), rgba(0,0,0,.68) 65%, #000), url("{{ asset('website/images/enjoying-street-food-fest 1 (1).png') }}") center 45% / cover; filter: blur(1.5px); }
    .partner-panel { border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.035); }
    .partner-input { border: 1px solid rgba(17,17,17,.15); transition: border-color .2s ease, box-shadow .2s ease; }
    .partner-input:focus { border-color: #FF6210; box-shadow: 0 0 0 3px rgba(255,98,16,.14); outline: none; }
    @media (prefers-reduced-motion: no-preference) {
        .partner-benefit { transition: transform .35s cubic-bezier(.22,1,.36,1); }
        .partner-benefit:hover { transform: translateY(-5px); }
        .partner-benefit-icon { transition: border-color .25s ease, color .25s ease, transform .35s cubic-bezier(.22,1,.36,1); }
        .partner-benefit:hover .partner-benefit-icon { border-color: rgba(255,98,16,.72); color: #FF6210; transform: scale(1.08); }
    }
</style>
@endsection

@section('content')
<div class="overflow-hidden bg-black">
    <section class="partner-hero relative isolate flex min-h-[390px] items-center justify-center overflow-hidden px-5 pb-12 pt-28 text-center sm:min-h-[460px] md:min-h-[540px]">
        <div class="relative z-10 mx-auto max-w-[850px]">
            <h1 data-aos="fade-down" class="font-newyork text-[42px] leading-tight text-white sm:text-5xl md:text-[65px]">Partner with us</h1>
            <p data-aos="fade-up" data-aos-delay="100" class="mx-auto mt-4 max-w-[760px] font-urbanist text-sm leading-relaxed text-white/85 sm:text-base md:text-[18px]">Join Dawat to connect your restaurant with more food lovers, grow your reach, and turn every order into a lasting relationship.</p>
        </div>
    </section>

    <section class="relative mx-auto w-[90%] max-w-[1700px] py-16 md:py-24">
        <img src="{{ asset('website/images/OBJECTS (3).png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute right-[-5.56vw] top-14 hidden h-[650px] w-auto opacity-70 md:block">
        <img src="{{ asset('website/images/OBJECTS (2).png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute left-[-5.56vw] top-[350px] hidden h-[650px] w-auto opacity-70 md:block">
        <div class="relative grid gap-10 md:grid-cols-3 md:gap-14">
            @foreach ([
                ['number' => '01', 'title' => 'Discover More', 'text' => 'Reach new customers who are looking for their next favourite restaurant.'],
                ['number' => '02', 'title' => 'Grow Faster', 'text' => 'Turn more orders into regular customers with a simple, reliable platform.'],
                ['number' => '03', 'title' => 'Partner Better', 'text' => 'Get the tools and support you need to build your business with confidence.'],
            ] as $index => $reason)
                <article data-aos="fade-up" data-aos-delay="{{ $index * 100 }}" class="text-center">
                    <span class="font-newyork text-[76px] leading-none text-white/35 sm:text-[94px] md:text-[112px]">{{ $reason['number'] }}</span>
                    <h2 class="mt-1 font-urbanist text-lg font-semibold text-white md:text-xl">{{ $reason['title'] }}</h2>
                    <p class="mx-auto mt-3 max-w-[290px] font-urbanist text-sm leading-relaxed text-white/60">{{ $reason['text'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="relative mx-auto grid w-[90%] max-w-[1700px] items-center gap-10 py-10 md:grid-cols-[.9fr_1.1fr] md:gap-16 md:py-20">
        <div data-aos="fade-right" class="relative mx-auto w-full max-w-[470px]">
            <img src="{{ asset('website/images/9b78d4783ee0deb27fedc85b8cdedf832ebd87ae.jpg') }}" alt="Happy Dawat customer enjoying a meal" class="h-[390px] w-full rounded-[18px] object-cover sm:h-[520px] md:h-[610px]">
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

    <section data-counter-group data-aos="fade-up" class="partner-panel mx-auto grid w-[90%] max-w-[1700px] grid-cols-2 overflow-hidden rounded-2xl sm:grid-cols-4">
        @foreach ([['250', '+', 0, 'Restaurants'], ['20', 'L+', 0, 'App Downloads'], ['52', 'k+', 0, 'Happy Customers'], ['7.2', 'k+', 1, 'Daily Delivery']] as $stat)
            <div class="border-b border-white/10 px-5 py-6 text-center last:border-b-0 sm:border-b-0 sm:border-r sm:last:border-r-0 md:px-8 md:py-7"><strong data-counter data-counter-target="{{ $stat[0] }}" data-counter-suffix="{{ $stat[1] }}" data-counter-decimals="{{ $stat[2] }}" class="block font-urbanist text-[30px] font-semibold text-white sm:text-[38px]">0{{ $stat[1] }}</strong><span class="mt-1 block text-xs text-white/55 sm:text-sm">{{ $stat[3] }}</span></div>
        @endforeach
    </section>

    <section class="relative mx-auto grid w-[90%] max-w-[1700px] gap-10 py-20 md:grid-cols-[.9fr_1.1fr] md:items-start md:gap-16 md:py-32">
        <img src="{{ asset('website/images/OBJECTS.png') }}" alt="" aria-hidden="true" class="pointer-events-none absolute left-[-5.20vw] bottom-[-116px] hidden h-[650px] w-auto opacity-70 lg:block">
        <div data-aos="fade-right" class="relative z-10">
            <h2 class="font-newyork text-[40px] leading-[1.04] text-white sm:text-[52px] md:text-[60px]">Born From a Love<br>for Good Food</h2>
            <div class="mt-6 max-w-[550px] space-y-4 font-urbanist text-sm leading-relaxed text-white/65 sm:text-base"><p>Dawat was created with one simple idea — finding and ordering great food should be effortless.</p><p>From local favourites to popular restaurants, we bring a wide variety of cuisines and dishes together in one platform. Whether you’re looking for a quick lunch, a family dinner, or a late-night craving, there’s something for everyone.</p><p>We’re building a food experience that connects people, restaurants, and the food they love.</p></div>
            <p class="mt-7 font-urbanist text-sm text-[#FF6210]">Drop a call or mail us if you need any help.</p>
            <div class="mt-4 grid max-w-[540px] gap-3 rounded-xl border border-white/15 p-3 sm:grid-cols-2 sm:gap-0 sm:divide-x sm:divide-white/15"><a href="mailto:hello@dawat.com" class="flex items-center gap-3 px-2 py-2 text-sm text-white/80"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10">✉</span><span><small class="block text-white/50">Email</small>hello@dawat.com</span></a><a href="tel:+916232115122" class="flex items-center gap-3 px-2 py-2 text-sm text-white/80"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10">☎</span><span><small class="block text-white/50">Phone</small>6232 1151 2211</span></a></div>
        </div>
        <form data-aos="fade-left" class="w-full rounded-2xl bg-white p-5 text-[#202020] shadow-2xl sm:p-7 md:max-w-[720px] md:justify-self-end" onsubmit="event.preventDefault(); this.querySelector('[data-form-message]').classList.remove('hidden');">
            <h2 class="font-urbanist text-xl font-semibold">Share your interest</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="text-[11px] font-medium tracking-wide text-black/45">FIRST NAME<input required name="first_name" placeholder="Jonnie" class="partner-input mt-2 h-11 w-full rounded-lg px-3 text-sm text-black placeholder:text-black/35"></label><label class="text-[11px] font-medium tracking-wide text-black/45">LAST NAME<input required name="last_name" placeholder="Jason" class="partner-input mt-2 h-11 w-full rounded-lg px-3 text-sm text-black placeholder:text-black/35"></label><label class="text-[11px] font-medium tracking-wide text-black/45 sm:col-span-2">EMAIL ADDRESS<input required type="email" name="email" placeholder="hello@dawat.com" class="partner-input mt-2 h-11 w-full rounded-lg px-3 text-sm text-black placeholder:text-black/35"></label><label class="text-[11px] font-medium tracking-wide text-black/45 sm:col-span-2">CONTACT NUMBER<input required type="tel" name="phone" placeholder="+91 - 9999999999" class="partner-input mt-2 h-11 w-full rounded-lg px-3 text-sm text-black placeholder:text-black/35"></label><label class="text-[11px] font-medium tracking-wide text-black/45 sm:col-span-2">LOCATION<input required name="location" placeholder="Delhi, India" class="partner-input mt-2 h-11 w-full rounded-lg px-3 text-sm text-black placeholder:text-black/35"></label></div>
            <label class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-black/70"><input required type="checkbox" class="h-4 w-4 accent-[#FF6210]"><span>I agree to the Terms and Condition</span></label><button type="submit" class="mt-5 h-11 w-full rounded-full bg-[#FF6210] font-urbanist text-sm font-semibold text-white transition-colors hover:bg-[#e0530b]">Submit</button><p data-form-message class="mt-3 hidden text-center text-sm text-green-700" role="status">Thanks. We’ll be in touch soon.</p>
        </form>
    </section>

    <section class="pb-20 pt-4 md:pb-28">
        <div data-aos="fade-up" class="mx-auto mb-8 w-[90%] max-w-[820px] text-center"><h2 class="font-newyork text-[36px] leading-tight text-white sm:text-[46px] md:text-[56px]">Restaurants in<br>New York City and Long Island</h2><p class="mt-3 font-urbanist text-sm leading-relaxed text-white/60">Discover great restaurants and find your next favourite meal with Dawat.</p></div>
        <div class="overflow-x-auto scrollbar-hide pb-4"> <div class="flex w-max min-w-full gap-3 md:gap-5 px-2 md:px-0 [justify-content:safe_center]">

                {{-- Restaurant Card 1 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Wah Fung No.1 Fast Food',
                    'location' => 'Chinatown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.9',
                    'image' => asset('website/images/restaurants/spice-palace-comp.jpg'),
                    'delay' => 0
                ])

                {{-- Restaurant Card 2 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Adel\'s Famous Halal Food',
                    'location' => 'Midtown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.9',
                    'image' => asset('website/images/restaurants/curry-house-comp.jpg'),
                    'delay' => 100
                ])

                {{-- Restaurant Card 3 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Xi\'an Famous Foods',
                    'location' => 'Chinatown | Manhattan',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => asset('website/images/restaurants/royal-feast-comp.jpg'),
                    'delay' => 200
                ])

                {{-- Restaurant Card 4 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Spicy Home Tasty',
                    'location' => 'Commack | Long Island',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => asset('website/images/restaurants/taj-flavors-comp.jpg'),
                    'delay' => 300
                ])

                {{-- Restaurant Card 5 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'All American Hamburger Drive In',
                    'location' => 'Massapequa | Long Island',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => asset('website/images/restaurants/masala-kitchen-comp.jpg'),
                    'delay' => 400
                ])

                {{-- Restaurant Card 6 (Rating Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Spice Symphony',
                    'location' => 'Midtown | Manhattan',
                    'badgeType' => 'rating',
                    'badgeText' => '4.8',
                    'image' => asset('website/images/restaurants/spice-symphony-comp.jpg'),
                    'delay' => 500
                ])

                {{-- Restaurant Card 7 (SALE Badge) --}}
                @include('website::components.restaurant-card', [
                    'name' => 'Flavor Garden',
                    'location' => 'Long Island City | Queens',
                    'badgeType' => 'sale',
                    'badgeText' => 'SALE',
                    'image' => asset('website/images/restaurants/flavor-garden-comp.jpg'),
                    'delay' => 600
                ])

        </div>
    </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    (() => {
        const startCounters = (group) => {
            if (!group || group.dataset.counted === 'true') return;
            group.dataset.counted = 'true';

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            group.querySelectorAll('[data-counter]').forEach((counter) => {
                const target = Number(counter.dataset.counterTarget);
                const suffix = counter.dataset.counterSuffix || '';
                const decimals = Number(counter.dataset.counterDecimals || 0);

                if (reducedMotion) {
                    counter.textContent = target.toFixed(decimals) + suffix;
                    return;
                }

                const duration = 1400;
                const startedAt = performance.now();
                const tick = (now) => {
                    const progress = Math.min((now - startedAt) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = (target * eased).toFixed(decimals) + suffix;
                    if (progress < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        };

        document.addEventListener('aos:in', ({ detail }) => {
            if (detail instanceof Element && detail.matches('[data-counter-group]')) startCounters(detail);
        });

        const counterGroups = document.querySelectorAll('[data-counter-group]');
        const revealVisibleCounters = () => {
            counterGroups.forEach((group) => {
                const bounds = group.getBoundingClientRect();
                if (bounds.top < window.innerHeight && bounds.bottom > 0) startCounters(group);
            });
        };

        // AOS handles the reveal. The observer covers sections that AOS has already
        // revealed before this page-specific script is evaluated.
        if ('IntersectionObserver' in window) {
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    startCounters(entry.target);
                    counterObserver.unobserve(entry.target);
                });
            }, { threshold: 0.25 });
            counterGroups.forEach((group) => counterObserver.observe(group));
        } else {
            window.addEventListener('scroll', revealVisibleCounters, { passive: true });
        }

        requestAnimationFrame(revealVisibleCounters);
    })();
</script>
@endsection
