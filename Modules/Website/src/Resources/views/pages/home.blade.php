@extends('website::layouts.app')

@section('title', 'Dawat — Where Every Celebration Becomes a Feast')

@section('page_class', 'home-page')

@section('styles')
    <style>
        .home-page [data-aos][data-aos][data-aos] {
            transition-duration: 1100ms !important;
        }
    </style>
@endsection

@section('content')

{{-- Exported Figma botanical artwork; the PNG already includes its rotation and opacity. --}}
<img src="{{ asset('website/images/OBJECTS.png') }}"
     alt=""
     aria-hidden="true"
     class="absolute hidden pointer-events-none z-[1] md:block"
     style="width: 250px; height: auto; top: 1348.11px; left: 0; opacity: 1;">

{{-- =========================================================================
     1. HERO SECTION (Desktop - 3 / Frame 2121449829)
     Full-screen gourmet food background with dark top & bottom gradient overlays
     ========================================================================= --}}

<section data-aos="fade" data-aos-duration="900" class="relative min-h-[740px] lg:h-[857px] lg:min-h-0 flex flex-col justify-between overflow-hidden bg-black">

    {{-- Background Food Image --}}
    <div class="absolute inset-0 z-0">
        <img
            src="{{ asset('website/73964bf77c75b2eba31ec165f554afb485d18dad.jpg') }}"
            alt="Dawat gourmet meal feast"
            class="w-full h-full object-cover object-center opacity-60 scale-105"
            loading="eager"
        />

        {{-- Existing Desktop Top Gradient --}}
        <div class="absolute inset-0 hero-top-gradient pointer-events-none"></div>

        {{-- Desktop Bottom Gradient --}}
        <div class="absolute inset-0 hidden hero-bottom-gradient pointer-events-none lg:block"></div>

        {{-- Mobile Background Gradient: transparent to solid black --}}
        <div
            class="absolute inset-0 pointer-events-none lg:hidden"
            style="background: linear-gradient(
                to bottom,
                transparent 40%,
                rgba(0, 0, 0, 0.85) 60%,
                #000000 76%,
                #000000 100%
            );"
        ></div>

    </div>

    {{-- Hero Content Wrapper --}}
    <div class="relative z-10 w-[90%] max-w-[1700px] mx-auto flex-1 flex items-center pt-28 lg:pt-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center w-full">

            {{-- Hero Copy --}}
            <div class="flex flex-col justify-center items-center text-center lg:items-start lg:text-left lg:col-span-7">

                {{-- Hero Title Lockup --}}
                <div data-aos="fade-down" class="relative mx-auto lg:mx-0 flex w-fit flex-col items-center lg:items-start pt-[22px] lg:pt-[30px]">

                    <p class="absolute top-[7px] left-[51.4%] -translate-x-1/2 lg:translate-x-0 lg:top-[9px] lg:left-[115px] font-urbanist font-medium text-[8px] sm:text-[12px] lg:text-[20px] text-white leading-tight tracking-wide whitespace-nowrap">
                        Where Every Celebration Becomes a
                    </p>

                    <h1 class="select-none leading-none lg:-translate-x-[9.35%] w-[74vw] max-w-[286px] lg:w-[42.36vw] lg:max-w-[610px] mt-[-12px] lg:mt-[-40px]">
                        <img
                            src="{{ asset('website/images/dawat-wordmark.png') }}"
                            alt="Dawat"
                            class="block w-full h-auto"
                        />
                    </h1>
                </div>

                {{-- Description Paragraph --}}
                <p data-aos="fade-up" data-aos-delay="200" class="font-urbanist border-t border-white/40 py-2 lg:py-4 z-20 font-light text-[10px] sm:text-[13px] lg:text-[20px] text-white leading-[143.4%] max-w-[360px] lg:max-w-[760px] mt-2 lg:mt-3 mb-3 lg:mb-8">
                    Discover restaurants, explore delicious cuisines, and order your favourite meals with Dawat — fast, simple, and made for every craving.
                </p>

                {{-- Star Rating Row --}}
                {{-- Keep your existing rating markup here if required. --}}

            </div>

            {{-- Phone Artwork --}}
            <div data-aos="fade" data-aos-delay="250" class="relative flex h-[565px] md:h-auto lg:col-span-5 justify-center items-center pointer-events-none -mt-2 lg:mt-0 overflow-hidden lg:overflow-visible">

                <img
                    src="{{ asset('website/9fb055111b8ba926a7cb6eef104950b4819e61ce.png') }}"
                    alt="Dawat app preview on a smartphone"
                    class="block absolute left-1/2 top-[-20px] -translate-x-1/2 w-[125vw] md:static md:w-[min(75vw,560px)] md:translate-x-0 max-w-none h-auto object-contain lg:w-[700px] lg:translate-y-48"
                    loading="eager"
                />

                {{-- Mobile Phone Gradient --}}
                <div
                    class="absolute inset-x-0 bottom-0 h-full lg:hidden"
                    style="background: linear-gradient(
                        to bottom,
                        transparent 40%,
                        rgba(0, 0, 0, 0.85) 60%,
                        #000000 76%,
                        #000000 100%
                    );"
                ></div>

            </div>

        </div>
    </div>

</section>


{{-- =========================================================================
     2. CATEGORY BAR SECTION (Figma: Frame 2121453669 & Line 2)
     Horizontal chips row right below the hero banner
     ========================================================================= --}}
<section data-aos="fade-up" class="relative bg-black pb-4 z-20">
    <div class="w-[90%] max-w-[1700px] mx-auto">
        <div class="mb-4 hidden w-full border-t border-white/20 md:block"></div>
        <div class="overflow-x-auto scrollbar-hide pb-2">
            <div class="flex w-full mt-4 min-w-[1106px] items-center gap-3.5">

                {{-- 1. Breakfast --}}
                @include('website::components.category-chip', [
                    'title' => 'Breakfast',
                    'subtitle' => 'Lorem Ipsum is simply dummy text',
                    'image' => asset('website/images/Frames/7e71ba0e8dfe7cabd816d0b74f7d9d39a1e09712.png'),
                    'alt' => 'Breakfast Plate',
                    'delay' => 0
                ])

                {{-- 2. Mexican --}}
                @include('website::components.category-chip', [
                    'title' => 'Mexican',
                    'subtitle' => 'Lorem Ipsum is simply dummy text',
                    'image' => asset('website/images/Frames/eca1011c9ab7e87855b5231f7c8f5c44a60fe8e0.png'),
                    'alt' => 'Mexican Taco Pizza',
                    'delay' => 100
                ])

                {{-- 3. Sandwiches --}}
                @include('website::components.category-chip', [
                    'title' => 'Sandwiches',
                    'subtitle' => 'Lorem Ipsum is simply dummy text',
                    'image' => asset('website/images/Frames/09f5c0334805df864a95c3fbd49314ca3d5bdf7f.png'),
                    'alt' => 'Artisan Sandwiches',
                    'delay' => 200
                ])

                {{-- 4. Gluten-free --}}
                @include('website::components.category-chip', [
                    'title' => 'Gluten-free',
                    'subtitle' => 'Lorem Ipsum is simply dummy text',
                    'image' => asset('website/images/Frames/c7f3505b4dd05596862621bb8aac977b1d93d17a.png'),
                    'alt' => 'Gluten Free Bowl',
                    'delay' => 300
                ])

                {{-- 5. Individual --}}
                @include('website::components.category-chip', [
                    'title' => 'Individual',
                    'subtitle' => 'Lorem Ipsum is simply dummy text',
                    'image' => asset('website/images/Frames/da12cbdd41962b8137096d6f6e60746a9c586f60.png'),
                    'alt' => 'Individual Platter',
                    'delay' => 400
                ])

            </div>
        </div>
    </div>
</section>


{{-- =========================================================================
     3. "EVERYTHING YOU CRAVE, DELIVERED." SECTION
     Figma: Title, Stats, solid orange circle backdrop, 2 phone mockups, red pepper
     ========================================================================= --}}
<section data-aos="fade-up" class="relative bg-black pt-12 pb-8 md:pt-32 md:pb-16 overflow-hidden">

    {{-- Botanical artwork accent beside the headline (Figma: OBJECTS) --}}
    <img src="{{ asset('website/images/OBJECTS (1).png') }}"
         alt=""
         aria-hidden="true"
         class="absolute right-[-45px] top-0 z-0 w-[200px] pointer-events-none opacity-60 sm:w-[240px] md:right-[-25px] md:top-8 md:w-[280px] md:hidden">

    {{-- Decorative floating red pepper on far left (Figma: two-vibrant-red-pepper-slices) --}}
    <div class="absolute -left-[70px] top-10 hidden w-32 pointer-events-none opacity-85 z-10 md:block md:-left-[18px] md:-top-3 md:w-56">
        <img src="{{ asset('website/two-vibrant-red-pepper-slices-revealing-seeds-and-a-fleshy-interior-against-white-photo 2.png') }}"
             alt="Red pepper slice decoration"
             class="w-full h-auto object-contain filter drop-shadow-[0_10px_20px_rgba(255,0,0,0.3)]">
    </div>

    <div class="w-[90%] max-w-[1700px] mx-auto relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-12 lg:gap-16 items-center">

            {{-- Left Column: Headline, Copy, Stats (Col 6) --}}
            <div class="lg:col-span-6 flex flex-col justify-center">

                {{-- Heading: Everything you crave, delivered. (Upright NewYork) --}}
                <h2 data-aos="fade-right" class="font-newyork font-normal text-white leading-[115%] mb-3 md:mb-6"
                    style="font-size: clamp(28px, 5.5vw, 88px);">
                    {{-- Everything you<br>crave, delivered. --}}
                    Where big orders meet great kitchens.
                </h2>

                {{-- Body Paragraph --}}
                <p data-aos="fade-up" data-aos-delay="150" class="font-urbanist font-light text-[11px] md:text-[16px] text-white/90 leading-[143.4%] max-w-[650px] mb-0 md:mb-12">
                  Dawat connects NYC and Long Island restaurants with customers who need food in bulk. From office lunches to weddings and large events, we bring the catering orders to your kitchen and get them delivered on time, every time.
                </p>

                {{-- Stats Row (Frame 1116607173) --}}
                {{-- <div class="flex items-center gap-0 max-w-[500px]">

                    
                    <div class="flex flex-col pr-8 border-r border-white/20">
                        <span class="font-urbanist font-bold text-[32px] text-white leading-none">10M+</span>
                        <span class="font-urbanist font-medium text-[16px] text-white/90 mt-2">App Downloads</span>
                    </div>

                    
                    <div class="flex flex-col px-8 border-r border-white/20">
                        <span class="font-urbanist font-bold text-[32px] text-white leading-none">150+</span>
                        <span class="font-urbanist font-medium text-[16px] text-white/90 mt-2">Countries</span>
                    </div>

                   
                    <div class="flex flex-col pl-8">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-6 h-6 fill-[#FFC400]" viewBox="0 0 20 20">
                                <path d="M10 1l2.4 6.6H19l-5.5 4 2.1 6.8L10 14.3l-5.6 4.1 2.1-6.8L1 7.6h6.6z"/>
                            </svg>
                            <span class="font-urbanist font-extrabold text-[32px] text-white leading-none">4.9</span>
                        </div>
                        <span class="font-urbanist font-medium text-[16px] text-white/90 mt-2">Rating &amp; Reviews</span>
                    </div>

                </div> --}}

            </div>

            {{-- Right Column: Orange Circle & Overlapping Phone Mockups (Col 6) --}}
            <div class="lg:col-span-6 flex justify-center items-center relative min-h-[245px] md:min-h-[400px] lg:min-h-[560px]">

                {{-- Solid orange backdrop circle behind the phone artwork --}}
                <div class="absolute w-[300px] h-[300px] sm:w-[350px] sm:h-[350px] lg:w-[540px] lg:h-[540px] rounded-full bg-[#FF6210] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-0 shadow-glow"></div>

                {{-- Supplied Figma phone mockup artwork --}}
                <div data-aos="fade" data-aos-delay="250" class="relative z-10 mx-auto w-full max-w-[420px] md:max-w-[420px] lg:max-w-[620px] translate-y-0 lg:translate-y-5">
                    <img src="{{ asset('website/images/4647d78a91cd2c0c1d9eda99c2429ba4d36e4e7f.png') }}"
                         alt="Dawat food ordering app displayed on two phones"
                         class="block w-full h-auto translate-x-[10.65%] object-contain"
                         loading="lazy">
                </div>

            </div>

        </div>
    </div>
</section>


{{-- =========================================================================
     4. RESTAURANTS SECTION (Figma: Restaurants in New York City and Long Island)
     Horizontally scrollable row of white restaurant cards with rating & sale badges
     ========================================================================= --}}
<section class="bg-black pt-5 md:pt-8 pb-6 md:pb-8">
    <div class="w-[90%] max-w-[1700px] mx-auto">

        {{-- Section Header (Centered) --}}
        <div class="text-center max-w-[960px] mx-auto mb-7 md:mb-14">
            <h2 data-aos="fade-down" class="font-newyork font-normal text-white leading-[123.4%] mb-4"
                style="font-size: clamp(24px, 3.8vw, 48px);">
                Restaurants in <br> York City and Long Island
            </h2>
            <p data-aos="fade-up" data-aos-delay="150" class="font-urbanist font-light text-[10px] md:text-[16px] text-white leading-[143.4%] max-w-[960px] mx-auto">
                Discover great restaurants and find your next favourite meal with Dawat.
            </p>
        </div>
    </div>

    {{-- Full-bleed horizontal scroller; card appearance and sizing stay unchanged. --}}
    <div class="relative left-1/2 -mt-1.5 md:-mt-8 w-screen -translate-x-1/2 overflow-x-auto scrollbar-hide pt-4 md:pt-8 pb-5 md:pb-6"
         role="region" aria-label="Featured restaurants" tabindex="0">
        <div class="flex w-max min-w-full gap-3 md:gap-5 px-2 md:px-0 [justify-content:safe_center]">

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


{{-- =========================================================================
     5. "WHY DAWAT?" SECTION
     Left: Hero portrait eating biryani + floating cards (ratings, happy users)
     Right: "Why Dawat?" heading, description, 2x2 feature items
     ========================================================================= --}}
<section data-aos="fade-up" class="bg-black pt-8 pb-12 md:pt-16 md:pb-32 overflow-hidden">
    <div class="w-[90%] max-w-[1700px] mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-14 lg:gap-16 items-center">

            {{-- Left Column: Portrait Photo with 2 Floating Cards (Col 6) --}}
            <div class="lg:col-span-5 relative w-full max-w-[530px] mx-auto lg:mx-0">

                {{-- Main Photo: Young man enjoying biryani (Figma: Rectangle 161125245, 529px x 575px, rounded-20px) --}}
                <div class="w-[88%] mx-auto lg:mx-0 lg:w-full h-[240px] sm:h-[320px] lg:h-[540px] rounded-[16px] lg:rounded-[20px] overflow-hidden shadow-2xl relative">
                    <img src="{{ asset('website/images/9b78d4783ee0deb27fedc85b8cdedf832ebd87ae.jpg') }}"
                         alt="Happy customer enjoying delicious biryani at Dawat"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                </div>

                {{-- Floating Card 1: Top Right (Group 1000009441: 5.0 rating based on 30K+ Customers) --}}
                {{-- <div class="glass-card absolute -top-5 -right-3 sm:-right-8 p-3.5 rounded-[12px] shadow-2xl w-[156px] select-none">
                    <div class="flex items-center gap-0.5">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 fill-[#FCD240]" viewBox="0 0 20 20">
                                <path d="M10 1l2.4 6.6H19l-5.5 4 2.1 6.8L10 14.3l-5.6 4.1 2.1-6.8L1 7.6h6.6z"/>
                            </svg>
                        @endfor
                    </div>
                    <p class="font-urbanist font-medium text-[13px] text-white leading-[18px] mt-2">
                        5.0 rating based on 30K+ Customers
                    </p>
                </div> --}}

                {{-- Floating Card 2: Bottom Left (Group 1000009440: 1M+ Happy Users & Avatars) --}}
                {{-- <div class="glass-card absolute -bottom-6 -left-3 sm:-left-8 px-4 py-3.5 rounded-[12px] shadow-2xl min-w-[180px] select-none"> --}}
                    {{-- 5 Overlapping User Avatars --}}
                    {{-- <div class="flex items-center -space-x-2.5 mb-2">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&fit=crop&q=80" class="w-8 h-8 rounded-full border-2 border-[#F5F5FF] object-cover" alt="User">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&fit=crop&q=80" class="w-8 h-8 rounded-full border-2 border-[#F5F5FF] object-cover" alt="User">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&fit=crop&q=80" class="w-8 h-8 rounded-full border-2 border-[#F5F5FF] object-cover" alt="User">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&fit=crop&q=80" class="w-8 h-8 rounded-full border-2 border-[#F5F5FF] object-cover" alt="User">
                        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=80&fit=crop&q=80" class="w-8 h-8 rounded-full border-2 border-[#F5F5FF] object-cover" alt="User">
                    </div>
                    <p class="font-outfit font-medium text-[16px] text-white leading-tight">
                        1M+ Happy Users
                    </p> --}}
                {{-- </div> --}}

            </div>

            {{-- Right Column: Typography & 4 Feature Items (Col 6) --}}
            <div class="lg:col-span-7 flex flex-col justify-center">

                {{-- Heading: Why Dawat? (Upright NewYork) --}}
                <h2 data-aos="fade-right" class="font-newyork font-normal text-white leading-[104.4%] mb-5"
                    style="font-size: clamp(32px, 5.2vw, 75px);">
                    Why Dawat?
                </h2>

                {{-- Description --}}
                <p data-aos="fade-up" data-aos-delay="150" class="font-urbanist font-light text-[11px] md:text-[16px] text-white leading-[143.4%] max-w-[700px] mb-6 md:mb-12">
                    We built Dawat for restaurants that want to grow through catering. No small tickets and no wasted effort, just larger orders from customers who plan ahead and order in volume.
                </p>

                {{-- 2x2 Feature Grid (Frame 2121453556) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 md:gap-y-10">

                    {{-- Feature 1: Mentor --}}
                    @include('website::components.feature-item', [
                        'title' => 'Bulk orders only',
                        'desc' => 'Every order is catering-sized, so your kitchen’s time goes toward bigger revenue.',
                        'icon' => 'mentor',
                        'delay' => 0
                    ])

                    {{-- Feature 2: Integration --}}
                    @include('website::components.feature-item', [
                        'title' => 'Local to NYC & Long Island',
                        'desc' => 'We know the area and the customers looking for great food nearby.',
                        'icon' => 'integration',
                        'delay' => 100
                    ])

                    {{-- Feature 3: Document --}}
                    @include('website::components.feature-item', [
                        'title' => 'Reliable delivery',
                        'desc' => 'Large orders arrive hot, on time, and handled with care.',
                        'icon' => 'document',
                        'delay' => 200
                    ])

                    {{-- Feature 4: Loudspeaker --}}
                    @include('website::components.feature-item', [
                        'title' => 'Clear, fast payouts',
                        'desc' => 'Transparent pricing and quick payments, so you always know what you earn.',
                        'icon' => 'loudspeaker',
                        'delay' => 300
                    ])

                </div>

            </div>

        </div>
    </div>
</section>


{{-- =========================================================================
     6. DOWNLOAD APP BANNER SECTION (Figma: Frame 1116607470 / Frame 1116606965)
     Vibrant orange container (1120px x 470px), QR code, download buttons, phone mockup
     ========================================================================= --}}
{{-- <section class="bg-black py-8 md:py-24">
    <div class="w-[90%] max-w-[1120px] mx-auto">

        <div class="relative bg-[#FF6210] rounded-[14px] md:rounded-[24px] overflow-hidden shadow-2xl min-h-[430px] md:min-h-[460px] flex items-center">

          
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute left-1/3 -bottom-16 w-60 h-60 rounded-full bg-black/10 pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-8 items-center w-full px-4 md:px-14 py-6 md:py-12">

       
                <div class="lg:col-span-7 flex flex-col gap-6">

                    <div>
                        <h2 class="font-urbanist font-bold text-[22px] md:text-[48px] text-white leading-tight">
                            Download the app now!
                        </h2>
                        <p class="font-urbanist font-light text-[10px] md:text-[16px] text-white/95 leading-[143.4%] mt-2 md:mt-3 max-w-[500px]">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since 1966, when designers at Letraset and James Mosley.
                        </p>
                    </div>

                  
                    <div class="hidden md:flex items-center gap-3.5 flex-wrap">
                   
                        <a href="#" target="_blank"
                           class="flex items-center gap-2.5 px-3 py-1.5 rounded-[9px] border border-white/60 bg-black/15 hover:bg-black/30 transition-all w-[140px] h-[44px]">
                            <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                                <path d="M3.18 1.6A2 2 0 0 0 2 3.38v17.24a2 2 0 0 0 1.18 1.78l9.37-10.4zm1.44 20.3 9.37-5.41-2.22-2.47zM20.35 10.5l-2.52-1.45-2.7 3 2.7 3 2.55-1.47A2.01 2.01 0 0 0 20.35 10.5zM4.62 2.1l11.07 6.39-2.22 2.47z"/>
                            </svg>
                            <div class="flex flex-col text-left leading-tight">
                                <span class="font-outfit text-[7.5px] text-white/80 uppercase tracking-widest font-normal">GET IT ON</span>
                                <span class="font-outfit text-[11px] text-white font-semibold">Google Play</span>
                            </div>
                        </a>

                 
                        <a href="#" target="_blank"
                           class="flex items-center gap-2.5 px-3 py-1.5 rounded-[9px] border border-white/60 bg-black/15 hover:bg-black/30 transition-all w-[140px] h-[44px]">
                            <svg class="w-5 h-5 fill-white flex-shrink-0" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            <div class="flex flex-col text-left leading-tight">
                                <span class="font-outfit text-[7.5px] text-white/80 uppercase tracking-widest font-normal">Download on</span>
                                <span class="font-outfit text-[11px] text-white font-semibold">App Store</span>
                            </div>
                        </a>
                    </div>


                    <div class="hidden md:flex items-center gap-4 pt-2">
                       
                        <div class="w-28 h-28 bg-white rounded-xl p-2.5 flex items-center justify-center flex-shrink-0 shadow-md">
                            <svg class="w-full h-full fill-black" viewBox="0 0 100 100">
                            
                                <rect x="5" y="5" width="28" height="28" rx="4" fill="none" stroke="black" stroke-width="4"/>
                                <rect x="11" y="11" width="16" height="16" rx="2"/>
                     
                                <rect x="67" y="5" width="28" height="28" rx="4" fill="none" stroke="black" stroke-width="4"/>
                                <rect x="73" y="11" width="16" height="16" rx="2"/>
                      
                                <rect x="5" y="67" width="28" height="28" rx="4" fill="none" stroke="black" stroke-width="4"/>
                                <rect x="11" y="73" width="16" height="16" rx="2"/>
                     
                                <rect x="38" y="8" width="6" height="6"/>
                                <rect x="48" y="8" width="6" height="6"/>
                                <rect x="38" y="18" width="6" height="6"/>
                                <rect x="56" y="18" width="6" height="6"/>
                                <rect x="38" y="28" width="6" height="6"/>
                                <rect x="48" y="28" width="6" height="6"/>
                                <rect x="56" y="28" width="6" height="6"/>
                                <rect x="8" y="38" width="6" height="6"/>
                                <rect x="18" y="38" width="6" height="6"/>
                                <rect x="28" y="38" width="6" height="6"/>
                                <rect x="38" y="38" width="6" height="6"/>
                                <rect x="48" y="38" width="6" height="6"/>
                                <rect x="67" y="38" width="6" height="6"/>
                                <rect x="77" y="38" width="6" height="6"/>
                                <rect x="87" y="38" width="6" height="6"/>
                                <rect x="18" y="48" width="6" height="6"/>
                                <rect x="28" y="48" width="6" height="6"/>
                                <rect x="48" y="48" width="6" height="6"/>
                                <rect x="56" y="48" width="6" height="6"/>
                                <rect x="77" y="48" width="6" height="6"/>
                                <rect x="38" y="56" width="6" height="6"/>
                                <rect x="48" y="56" width="6" height="6"/>
                                <rect x="67" y="56" width="6" height="6"/>
                                <rect x="87" y="56" width="6" height="6"/>
                                <rect x="38" y="67" width="6" height="6"/>
                                <rect x="56" y="67" width="6" height="6"/>
                                <rect x="77" y="67" width="6" height="6"/>
                                <rect x="48" y="77" width="6" height="6"/>
                                <rect x="67" y="77" width="6" height="6"/>
                                <rect x="87" y="77" width="6" height="6"/>
                                <rect x="38" y="87" width="6" height="6"/>
                                <rect x="56" y="87" width="6" height="6"/>
                                <rect x="67" y="87" width="6" height="6"/>
                            </svg>
                        </div>

                        
                        <p class="font-urbanist font-medium text-[18px] sm:text-[20px] text-white leading-tight max-w-[190px]">
                            Scan QR code for download the app
                        </p>
                    </div>

                </div>

         
                <div class="flex lg:col-span-5 justify-center items-end relative h-[245px] md:h-[380px]">
                    <img src="{{ asset('website/9fb055111b8ba926a7cb6eef104950b4819e61ce.png') }}" alt="Dawat app displayed on a phone" class="block md:hidden w-[125vw] max-w-[450px] h-auto absolute left-1/2 -translate-x-1/2 -bottom-4">
                    <div class="hidden md:flex w-[280px] h-[480px] bg-[#111114] rounded-t-[40px] border-t-[5px] border-x-[5px] border-[#2c2c34] shadow-2xl overflow-hidden flex-col justify-start absolute -bottom-12">
                        <div class="px-3 md:px-5 pt-3 md:pt-5 pb-2 md:pb-3 text-center bg-black/40">
                            <span class="font-newyork text-[12px] md:text-[20px] font-bold text-white tracking-widest">DAWAT</span>
                            <p class="text-[7px] md:text-[10px] text-white/70">Welcome to Dawat</p>
                        </div>
                        <div class="p-2 md:p-3">
                            <div class="w-full h-24 md:h-44 rounded-xl md:rounded-2xl overflow-hidden shadow-md">
                                <img src="https://images.unsplash.com/photo-1543353071-10c8ba85a904?w=500&auto=format&fit=crop&q=80"
                                     class="w-full h-full object-cover" alt="App Preview">
                            </div>
                            <div class="mt-3 flex flex-col gap-1.5">
                                <div class="w-3/4 h-2.5 bg-white/20 rounded-full"></div>
                                <div class="w-1/2 h-2.5 bg-white/10 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section> --}}

@endsection
