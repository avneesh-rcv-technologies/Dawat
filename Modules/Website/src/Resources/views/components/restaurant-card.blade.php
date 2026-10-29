@props([
    'name' => 'Motimahal Resturent',
    'location' => 'Noida | India',
    'image' => '',
    'badgeType' => 'rating', // 'rating' or 'sale'
    'badgeText' => '4.9',
    'delay' => 0,
])

<div data-aos="fade-up" data-aos-delay="{{ $delay }}" class="restaurant-hover w-[42vw] max-w-[292px] sm:w-[292px] flex-shrink-0 bg-white rounded-[12px] sm:rounded-[22px] p-1.5 sm:p-2.5 relative flex flex-col justify-between cursor-pointer select-none h-[190px] sm:h-[364px]">

    {{-- Image Container (272px x 270px) --}}
    <div class="relative w-full h-[128px] sm:h-[270px] rounded-[9px] sm:rounded-[16px] overflow-hidden bg-[#EDE8DF]">
        <img src="{{ $image }}"
             alt="{{ $name }}"
             class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
             loading="lazy"
             decoding="async">

        {{-- Top Floating Overlay Bar --}}
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-10 pointer-events-none">

            {{-- Badge (Rating or SALE) --}}
            @if($badgeType === 'rating')
                <div class="bg-white rounded-full px-2.5 py-1 flex items-center gap-1 shadow-sm">
                    <svg class="w-3 h-3 fill-[#FFC400]" viewBox="0 0 20 20">
                        <path d="M10 1l2.4 6.6H19l-5.5 4 2.1 6.8L10 14.3l-5.6 4.1 2.1-6.8L1 7.6h6.6z"/>
                    </svg>
                    <span class="font-outfit text-[12px] font-normal text-black leading-none pt-0.5">
                        {{ $badgeText }}
                    </span>
                </div>
            @else
                <div class="bg-white rounded-full px-3.5 py-1 shadow-sm">
                    <span class="font-outfit text-[12px] font-normal text-black leading-none tracking-wide">
                        {{ $badgeText }}
                    </span>
                </div>
            @endif

            {{-- Favorite / Heart Button --}}
            <button type="button"
                    class="w-8 h-8 rounded-full bg-white/30 backdrop-blur-md flex items-center justify-center hover:bg-white/50 transition-colors pointer-events-auto"
                    aria-label="Add to wishlist">
                <svg class="w-4 h-4 fill-[#FF6161]" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Bottom Text Area --}}
    <div class="px-1.5 pt-2 pb-1">
        <h4 class="font-urbanist font-semibold text-[14px] sm:text-[20px] text-black leading-5 sm:leading-6 truncate">
            {{ $name }}
        </h4>
        <p class="font-urbanist font-normal text-[11px] sm:text-[16px] text-black/60 leading-4 sm:leading-5 mt-0.5 sm:mt-1 truncate">
            {{ $location }}
        </p>
    </div>
</div>

