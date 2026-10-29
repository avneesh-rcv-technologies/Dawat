@extends('website::layouts.app')

@section('title', 'Contact Us — Dawat')

@section('content')
<div class="bg-black">
    {{-- Contact hero --}}
    <section class="relative flex min-h-[460px] items-center justify-center overflow-hidden px-5 pb-24 pt-32 text-center md:min-h-[clamp(510px,36vw,700px)] md:pb-28">
        <img src="{{ asset('website/images/e4c902957ed634404076ae416d26b967b43bc0f4.jpg') }}"
             alt="A table filled with Dawat dishes"
             class="absolute inset-0 h-full w-full object-cover object-center"
             loading="eager">
        <div class="absolute inset-0 bg-black/65"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/40 to-black"></div>

        <div class="relative z-10 mx-auto max-w-[900px] -translate-y-6 md:-translate-y-20">
            <h1 data-aos="fade-down" class="font-newyork text-[44px] font-normal leading-tight text-white sm:text-5xl md:text-[64px]">Contact us</h1>
            <p data-aos="fade-up" data-aos-delay="200" class="mx-auto mt-5 max-w-[850px] font-urbanist text-[15px] leading-relaxed text-white/90 sm:text-lg md:text-[20px]">
                At Dawat, we believe every craving deserves great food. We make it easy to discover restaurants, explore new flavours, and order your favourite meals right to your doorstep.
            </p>
        </div>
    </section>

    <section class="relative z-10 mx-auto -mt-28 w-[92%] max-w-[1096px] md:-mt-52">
        {{-- Contact form and dining photo --}}
        <div class="grid overflow-hidden rounded-[22px] bg-white p-3 text-[#171717] shadow-2xl sm:p-6 md:grid-cols-2 md:gap-8 md:rounded-[24px] md:p-10">
            <form class="flex flex-col gap-3 sm:gap-5" action="#" method="get">
                <div data-aos="fade-right" class="grid grid-cols-2 gap-2 sm:gap-5">
                    <label class="block">
                        <span class="mb-1.5 block font-urbanist text-[10px] font-semibold uppercase tracking-wide text-black/45 sm:mb-2 sm:text-sm">First name</span>
                        <input type="text" name="first_name" autocomplete="given-name" placeholder="Your first name" required
                               class="h-10 w-full rounded-lg border border-black/15 bg-white px-3 font-urbanist text-[12px] text-black outline-none transition placeholder:text-black/35 focus:border-[#FF6210] focus:ring-2 focus:ring-[#FF6210]/15 sm:h-12 sm:rounded-xl sm:px-4 sm:text-[15px]">
                    </label>
                    <label class="block">
                        <span class="mb-1.5 block font-urbanist text-[10px] font-semibold uppercase tracking-wide text-black/45 sm:mb-2 sm:text-sm">Last name</span>
                        <input type="text" name="last_name" autocomplete="family-name" placeholder="Your last name" required
                               class="h-10 w-full rounded-lg border border-black/15 bg-white px-3 font-urbanist text-[12px] text-black outline-none transition placeholder:text-black/35 focus:border-[#FF6210] focus:ring-2 focus:ring-[#FF6210]/15 sm:h-12 sm:rounded-xl sm:px-4 sm:text-[15px]">
                    </label>
                </div>

                <label data-aos="fade-right" data-aos-delay="100" class="block">
                    <span class="mb-1.5 block font-urbanist text-[10px] font-semibold uppercase tracking-wide text-black/45 sm:mb-2 sm:text-sm">Email address</span>
                    <input type="email" name="email" autocomplete="email" placeholder="you@example.com" required
                           class="h-10 w-full rounded-lg border border-black/15 bg-white px-3 font-urbanist text-[12px] text-black outline-none transition placeholder:text-black/35 focus:border-[#FF6210] focus:ring-2 focus:ring-[#FF6210]/15 sm:h-12 sm:rounded-xl sm:px-4 sm:text-[15px]">
                </label>

                <label data-aos="fade-right" data-aos-delay="200" class="block">
                    <span class="mb-1.5 block font-urbanist text-[10px] font-semibold uppercase tracking-wide text-black/45 sm:mb-2 sm:text-sm">Message</span>
                    <textarea name="message" rows="4" placeholder="Tell us how we can help" required
                              class="min-h-[100px] w-full resize-y rounded-lg border border-black/15 bg-white px-3 py-2 font-urbanist text-[12px] text-black outline-none transition placeholder:text-black/35 focus:border-[#FF6210] focus:ring-2 focus:ring-[#FF6210]/15 sm:min-h-[140px] sm:rounded-xl sm:px-4 sm:py-3 sm:text-[15px]"></textarea>
                </label>

                <label data-aos="fade-up" data-aos-delay="300" class="flex cursor-pointer items-start gap-2 font-urbanist text-[12px] leading-snug text-black sm:gap-3 sm:text-[16px]">
                    <span class="relative mt-0.5 h-4 w-4 shrink-0 sm:h-5 sm:w-5">
                        <input type="checkbox" name="terms" required class="peer h-4 w-4 appearance-none rounded-[4px] border border-black/25 bg-white checked:border-[#FF6210] checked:bg-[#FF6210] focus:outline-none focus:ring-2 focus:ring-[#FF6210]/30 sm:h-5 sm:w-5">
                        <svg class="pointer-events-none absolute inset-0 hidden h-4 w-4 text-white peer-checked:block sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="m5 10 3.2 3.2L15 6.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span>I agree to the Terms and Conditions</span>
                </label>

                <button data-aos="zoom-in" data-aos-delay="400" type="submit" class="h-10 rounded-full bg-[#FF6210] px-6 font-urbanist text-sm font-semibold text-white transition hover:bg-[#e9550b] focus:outline-none focus:ring-4 focus:ring-[#FF6210]/25 sm:h-12 sm:text-base">
                    Submit
                </button>
            </form>

            <div data-aos="zoom-in" data-aos-delay="250" class="mt-6 aspect-[504/584] overflow-hidden rounded-[18px] md:mt-0 md:h-auto md:min-h-0">
                <img src="{{ asset('website/images/young-people-enjoying-street-food 1.png') }}"
                     alt="Friends enjoying street food together"
                     class="h-full w-full object-cover object-center"
                     loading="lazy">
            </div>
        </div>

        {{-- Contact details strip --}}
        <div class="mt-8 grid gap-5 rounded-2xl border border-white/20 px-5 py-6 sm:grid-cols-2 sm:px-7 md:mt-9 md:grid-cols-[1fr_1fr_auto] md:items-center md:gap-0 md:px-6 md:py-5">
            <a data-aos="fade-up" href="mailto:hello@uiwiki.co" class="flex items-center gap-4 md:border-r md:border-white/30 md:py-2 md:pr-8">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/[0.06] text-white/80" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="5" width="17" height="14" rx="2.5"/><path d="m4.5 7 7.5 6 7.5-6"/></svg>
                </span>
                <span class="flex flex-col gap-1 font-urbanist"><span class="text-base text-white">Email</span><span class="text-sm text-white/45">hello@uiwiki.co</span></span>
            </a>

            <a data-aos="fade-up" data-aos-delay="120" href="tel:+162321512211" class="flex items-center gap-4 md:border-r md:border-white/30 md:px-8 md:py-2">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/[0.06] text-white/80" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 3.5h3l1.5 4-2 1.5a15 15 0 0 0 5.5 5.5l1.5-2 4 1.5v3c0 1.1-.9 2-2 2C10.5 21 3 13.5 3 5.5c0-1.1.9-2 2-2h2Z"/></svg>
                </span>
                <span class="flex flex-col gap-1 font-urbanist"><span class="text-base text-white">Phone</span><span class="text-sm text-white/45">6232 1151 2211</span></span>
            </a>

            <div data-aos="fade-up" data-aos-delay="240" class="flex items-center justify-between gap-4 border-t border-white/15 pt-5 sm:col-span-2 md:col-span-1 md:justify-end md:border-0 md:pl-8 md:pt-0">
                <span class="font-urbanist text-sm text-white">Follow us</span>
                <div class="flex items-center gap-3">
                    <a href="#" aria-label="Follow Dawat on X" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-white transition hover:border-white/40">
                        <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63zM17.7 19.77h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="#" aria-label="Follow Dawat on Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 text-white transition hover:border-white/40">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="18" cy="6" r=".8" fill="currentColor"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="h-24 md:h-28"></div>
</div>
@endsection
