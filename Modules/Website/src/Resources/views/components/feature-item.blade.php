@props([
    'title' => 'Lorem Ipsum is',
    'desc' => '',
    'icon' => 'mentor',
    'delay' => 0,
])

<div data-aos="fade-right" data-aos-delay="{{ $delay }}" class="flex items-start gap-2.5 md:gap-4">
    {{-- Circular Icon Box (40px x 40px) --}}
    <div class="w-8 h-8 md:w-10 md:h-10 rounded-full border border-white/10 flex items-center justify-center flex-shrink-0 bg-white/[0.02]">
        @if($icon === 'mentor')
            {{-- Mentor / Strategy icon --}}
            <svg class="w-4 h-4 fill-none stroke-white" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        @elseif($icon === 'integration')
            {{-- Integration / Lightning / Growth icon --}}
            <svg class="w-4 h-4 fill-none stroke-white" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
        @elseif($icon === 'document')
            {{-- Document / Content icon --}}
            <svg class="w-4 h-4 fill-none stroke-white" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        @else
            {{-- Loudspeaker / Campaign icon --}}
            <svg class="w-4 h-4 fill-none stroke-white" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
        @endif
    </div>

    {{-- Text Content --}}
    <div class="flex flex-col">
        <h4 class="font-urbanist font-bold text-[12px] md:text-[18px] text-white leading-4 md:leading-[22px]">
            {{ $title }}
        </h4>
        <p class="font-urbanist font-normal text-[10px] md:text-[15px] text-white/70 leading-[13px] md:leading-[18px] mt-1 md:mt-1.5 max-w-[260px]">
            {{ $desc }}
        </p>
    </div>
</div>

