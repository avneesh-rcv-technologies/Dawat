@extends('website::layouts.app')

@section('title', 'Blog & Articles — Dawat')

@section('content')
<div class="bg-black">
    @php
        $blogCards = [
            [
                'title' => 'Performance Marketing',
                'description' => 'ROI-focused campaigns that turn clicks into conversions',
                'image' => asset('website/images/Union.png'),
                'alt' => 'Marketing team planning a campaign together',
            ],
            [
                'title' => 'Brand Consulting',
                'description' => 'We shape your brand’s voice and vision using insights and creativity',
                'image' => asset('website/images/Union1.png'),
                'alt' => 'Creative team collaborating on a brand strategy',
            ],
            [
                'title' => 'Content Marketing',
                'description' => 'Content that engages, builds loyalty, and grows reach',
                'image' => asset('website/images/Union2.png'),
                'alt' => 'Camera recording content in a studio',
            ],
        ];
    @endphp

    {{-- Blog hero --}}
    <section class="relative flex min-h-[390px] items-center justify-center overflow-hidden px-5 pb-16 pt-28 text-center md:min-h-[520px] md:pb-14 md:pt-[152px]">
        <img src="{{ asset('website/73964bf77c75b2eba31ec165f554afb485d18dad.jpg') }}"
             alt="A spread of Dawat dishes"
             class="absolute inset-0 h-full w-full object-cover object-center"
             loading="eager">
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/45 to-black"></div>

        <div class="relative z-10 mx-auto max-w-[900px]">
            <h1 data-aos="fade-down" class="font-newyork text-[42px] font-normal leading-tight text-white sm:text-5xl md:text-[65px] md:leading-[113.4%]">Blog &amp; Articles</h1>
            <p data-aos="fade-up" data-aos-delay="150" class="mx-auto mt-4 max-w-[850px] font-urbanist text-[14px] leading-relaxed text-white/90 sm:text-lg md:max-w-[871px] md:text-[20px]">
                At Dawat, we believe every craving deserves great food. We make it easy to discover restaurants, explore new flavours, and order your favourite meals right to your doorstep.
            </p>
        </div>
    </section>

    {{-- Article cards --}}
    <section class="relative z-10 mx-auto w-[calc(100%-32px)] max-w-[1238px] pb-16 pt-10 md:pb-[122px] md:pt-[85px]">
        <div class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 xl:grid-cols-3 xl:gap-x-[38.6px] xl:gap-y-[67px]">
            @for($row = 0; $row < 3; $row++)
                @foreach($blogCards as $index => $card)
                    <article data-aos="fade-up" data-aos-delay="{{ (($row * 3 + $index) % 3) * 100 }}" class="group min-w-0">
                        <a href="{{ route('website.blog-detail', ['slug' => 'the-sweet-truth-about-boxed-cake-mix']) }}" aria-label="Read {{ $card['title'] }} article" class="relative mb-[15.5px] block aspect-[1.2344] overflow-visible">
                            <div class="absolute inset-0 overflow-hidden rounded-[20px] md:rounded-[24px]">
                                <img src="{{ $card['image'] }}"
                                     alt="{{ $card['alt'] }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                     loading="lazy"
                                     decoding="async">
                                <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 font-urbanist text-[10px] font-semibold text-black md:left-4 md:top-4 md:text-[12px]">26,Jan,2026</span>
                            </div>
                            <span class="absolute -right-2 -top-2 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#FF6210] shadow-lg transition-transform duration-300 group-hover:rotate-45 md:-right-3 md:-top-3 md:h-[69px] md:w-[69px]" aria-hidden="true">
                                <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M7 17 17 7M7 7h10v10" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </a>
                        <h2 class="font-urbanist text-[17px] font-semibold leading-snug text-white md:text-[23px] md:leading-[28px]">{{ $card['title'] }}</h2>
                        <p class="mt-1.5 max-w-[380px] font-urbanist text-[13px] leading-relaxed text-[#5A5A59] md:mt-[7.74px] md:text-[17px] md:leading-[21px]">{{ $card['description'] }}</p>
                    </article>
                @endforeach
            @endfor
        </div>
    </section>
</div>
@endsection
