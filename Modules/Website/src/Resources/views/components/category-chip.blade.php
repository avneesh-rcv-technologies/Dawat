@props([
    'title' => '',
    'subtitle' => 'Lorem Ipsum is simply dummy text',
    'image' => '',
    'alt' => '',
    'delay' => 0,
])

<div data-aos="fade-up" data-aos-delay="{{ $delay }}" class="glass-category flex flex-none md:flex-1 w-[42vw] md:w-auto items-center gap-2 md:gap-3 px-2 md:px-3 py-1.5 md:py-2 rounded-[9px] md:rounded-[12px] cursor-pointer hover:border-[#FF6210]/60 hover:bg-white/[0.06] transition-all group h-[58px] md:h-[84px] md:min-w-[230px]">

    {{-- Food Plate Thumbnail --}}
    <div class="w-[42px] h-[42px] md:w-[66px] md:h-[66px] rounded-md md:rounded-lg overflow-hidden flex-shrink-0 bg-white/5 flex items-center justify-center p-0.5">
        <img src="{{ $image }}"
             alt="{{ $alt ?: $title }}"
             class="w-full h-full object-cover rounded-md group-hover:scale-105 transition-transform duration-300"
             loading="lazy">
    </div>

    {{-- Text Details --}}
    <div class="flex flex-col justify-center">
        <h3 class="font-urbanist font-bold text-[10px] md:text-[15px] text-white leading-4 md:leading-5 group-hover:text-[#FF6210] transition-colors">
            {{ $title }}
        </h3>
        <p class="font-urbanist font-normal text-[8px] md:text-[12px] text-white/70 leading-[10px] md:leading-4 mt-0.5 max-w-[125px]">
            {{ $subtitle }}
        </p>
    </div>
</div>

