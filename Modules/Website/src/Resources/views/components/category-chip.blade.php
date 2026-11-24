@props([
    'title' => '',
    'subtitle' => 'Lorem Ipsum is simply dummy text',
    'image' => '',
    'alt' => '',
    'delay' => 0,
])

<div data-aos="fade-up" data-aos-delay="{{ $delay }}" class="glass-category flex h-full min-w-0 items-start gap-3 p-3 sm:p-4 rounded-[12px] cursor-pointer hover:border-[#FF6210]/60 hover:bg-white/[0.06] transition-all group">

    {{-- Food Plate Thumbnail --}}
    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg overflow-hidden flex-shrink-0 bg-white/5 flex items-center justify-center p-0.5">
        <img {!! \Modules\Website\Support\WebsiteImages::attributes($image, '(min-width: 640px) 56px, 48px', 160) !!}
             alt="{{ $alt ?: $title }}"
             class="w-full h-full object-cover rounded-md group-hover:scale-105 transition-transform duration-300"
             loading="lazy" decoding="async">
    </div>

    {{-- Text Details --}}
    <div class="flex min-w-0 flex-1 flex-col break-words">
        <h3 class="font-urbanist font-bold text-base text-white leading-5 group-hover:text-[#FF6210] transition-colors">
            {{ $title }}
        </h3>
        <p class="font-urbanist font-normal text-sm text-white/70 leading-5 mt-1">
            {{ $subtitle }}
        </p>
    </div>
</div>

