@extends('website::layouts.app')

@section('title', 'The Sweet Truth About Boxed Cake Mix — Dawat')
@section('meta_description', 'Discover how a thoughtful boxed cake mix can bring bakery-quality flavour and consistency to your kitchen.')

@section('content')
<div class="bg-black">
    @php
        $latestBlogs = [
            ['title' => 'Performance Marketing', 'description' => 'ROI-focused campaigns that turn clicks into conversions', 'image' => asset('website/images/Union.png')],
            ['title' => 'Brand Consulting', 'description' => 'We shape your brand’s voice and vision using insights and creativity', 'image' => asset('website/images/Union1.png')],
            ['title' => 'Content Marketing', 'description' => 'Content that engages, builds loyalty, and grows reach', 'image' => asset('website/images/Union2.png')],
        ];
        $detailUrl = route('website.blog-detail', ['slug' => 'the-sweet-truth-about-boxed-cake-mix']);
    @endphp

    <article class="mx-auto w-[calc(100%-36px)] max-w-[1238px] pb-14 pt-28 md:w-[calc(100%-64px)] md:pt-[156px]">
        <a href="{{ route('website.blogs') }}" class="mb-4 inline-flex items-center gap-2 rounded-full bg-[#251103] px-5 py-2 font-urbanist text-sm font-semibold text-[#FF6210] transition-colors hover:bg-[#351804]" aria-label="Back to blogs">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m14 6-6 6 6 6M8 12h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Prev
        </a>

        <header data-aos="fade-up" class="mb-7">
            <h1 class="font-urbanist text-[30px] font-semibold leading-tight text-white sm:text-4xl md:text-[46px]">The Sweet Truth About Boxed Cake Mix</h1>
            <div class="mt-4 space-y-3 font-urbanist text-[13px] leading-relaxed text-white/75 md:text-[15px]">
                <p>Once upon a time, boxed cake mix was treated like a baking shortcut that required secrecy. The box was hidden, the trash was buried, and compliments were accepted with suspicious humility. But modern bakers have evolved. And so has the mix.</p>
                <p>Today, boxed cake mix is no longer a compromise. It is a starting point for gourmet creativity, especially when the mix is designed with quality, balance, and artisan care in mind. That is exactly where Tres Artisan Collection enters the kitchen: proudly, confidently, and without apology.</p>
                <p>Because if a cake makes people close their eyes while chewing, no one is asking what aisle the ingredients came from.</p>
            </div>

            <div class="mt-5 flex items-center gap-3 font-urbanist text-xs text-white/60">
                <img src="{{ asset('website/images/Blog-details/Ellipse 58.png') }}" alt="" class="h-9 w-9 rounded-full object-cover object-center">
                <span class="font-medium text-white/90">Jennifer Mendy</span>
                <span class="h-5 border-l border-white/20"></span>
                <time datetime="2026-02-11">11-02-2026</time>
            </div>
        </header>

        <img data-aos="zoom-in" src="{{ asset('website/images/Blog-details/Frame 1116607254.png') }}" alt="A rich chocolate cake with cream filling" class="mb-7 aspect-[2.35] w-full rounded-[18px] object-cover md:rounded-[24px]">

        <div class="space-y-6 font-urbanist text-[13px] leading-relaxed text-white/75 md:text-[15px]">
            <section>
                <h2 class="mb-3 text-[24px] font-semibold leading-tight text-white md:text-[34px]">Why Gourmet Baking No Longer Requires a Full Weekend</h2>
                <p>In a world where time is the rarest ingredient, baking must adapt. Tres Artisan Collection was created for bakers who want bakery-style results without bakery-level exhaustion. The collective behind the brand understands that joy disappears quickly when a recipe requires twelve steps, three rest periods, and emotional resilience.</p>
                <p class="mt-4">Each mix is pre-measured, professionally balanced, and tested for consistency. The result is a cake that behaves exactly as expected: no surprises, no drama, and no last-minute Googling of “why did my cake sink in the middle.”</p>
                <p class="mt-4">The mix works quietly in the background while bakers take the credit. As it should.</p>
                <p class="mt-4">What Makes Tres Artisan Collection a Gourmet Cake Mix</p>
            </section>

            <section>
                <h2 class="mb-3 text-[23px] font-semibold leading-tight text-white md:text-[32px]">Crafted Like a Professional Ingredient, not a Shortcut</h2>
                <p>Unlike ordinary boxed mixes, Tres Artisan Collection is designed to mirror the structure and performance of bakery formulas. The flours, leavening, and sugar ratios are selected to produce an even crumb, stable rise, and rich mouthfeel, the three pillars of a truly gourmet cake.</p>
                <p class="mt-4">This is why the mix feels forgiving in the hands of home bakers and reliable in the kitchens of professionals. It behaves like a trusted assistant who never calls in sick.</p>
            </section>

            <section>
                <h2 class="mb-3 text-[23px] font-semibold leading-tight text-white md:text-[32px]">Consistency That Builds Confidence</h2>
                <p>Consistency is the hidden luxury of baking. When results repeat beautifully, creativity feels safe. Bakers stop worrying and start experimenting, adding fruit, filling layers, frosting generously, or transforming the cake into truffles, jars, or plated desserts.</p>
                <p class="mt-4">And this is how a simple boxed cake mix quietly becomes a signature dessert.</p>
            </section>

            <section class="grid items-start gap-6 py-2 md:grid-cols-[0.78fr_1.22fr] md:gap-8">
                <img src="{{ asset('website/images/Blog-details/Rectangle 161125084.png') }}" alt="Freshly baked chocolate brownies" class="h-full max-h-[560px] min-h-[280px] w-full self-start rounded-[20px] object-cover">
                <div class="space-y-5 self-start">
                    <div>
                        <h2 class="mb-3 text-[23px] font-semibold leading-tight text-white md:text-[32px]">From Pantry Staple<br class="hidden md:block"> to Boutique Dessert</h2>
                        <p>We don’t just make cake easier; we make it better. With minimal effort, this mix transforms into desserts that belong behind glass displays or on celebration tables. Add whipped cream and berries for a light finish.</p>
                        <p class="mt-3">Layer with ganache for drama. Bake in a loaf pan and call it rustic. The mix adapts, supports, and elevates like good ingredients always do.</p>
                        <p class="mt-3">The box may look simple, but the results never are.</p>
                    </div>
                    <div>
                        <h2 class="mb-2 text-[23px] font-semibold leading-tight text-white md:text-[32px]">Why Bakers Trust<br><span class="text-[#FF6210]">Tres Artisan Collection</span></h2>
                        <p>Kitchens operate on precision and speed. Every minute saved is a win. Tres Artisan Collection is trusted by professionals because it delivers predictable outcomes, batch after batch.</p>
                        <p class="mt-3">Cafés use it to scale production. Catering teams use it for reliability. Pastry chefs use it because it gives them freedom to focus on creativity rather than corrections.</p>
                        <p class="mt-3">In short, the mix behaves. And in a kitchen, that is everything.</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-3 text-[24px] font-semibold leading-tight text-white md:text-[34px]">Why Gourmet Baking No Longer Requires a Full Weekend</h2>
                <p>In a world where time is the rarest ingredient, baking must adapt. Tres Artisan Collection was created for bakers who want bakery-style results without bakery-level exhaustion. The collective behind the brand understands that joy disappears quickly when a recipe requires twelve steps, three rest periods, and emotional resilience.</p>
                <p class="mt-4">Each mix is pre-measured, professionally balanced, and tested for consistency. The result is a cake that behaves exactly as expected: no surprises, no drama, and no last-minute Googling of “why did my cake sink in the middle.”</p>
                <p class="mt-4">The mix works quietly in the background while bakers take the credit. As it should.</p>
                <p class="mt-4">What Makes Tres Artisan Collection a Gourmet Cake Mix</p>
            </section>

            <section>
                <h2 class="mb-3 text-[23px] font-semibold leading-tight text-white md:text-[32px]">Crafted Like a Professional Ingredient, not a Shortcut</h2>
                <p>Unlike ordinary boxed mixes, Tres Artisan Collection is designed to mirror the structure and performance of bakery formulas. The flours, leavening, and sugar ratios are selected to produce an even crumb, stable rise, and rich mouthfeel, the three pillars of a truly gourmet cake.</p>
                <p class="mt-4">This is why the mix feels forgiving in the hands of home bakers and reliable in the kitchens of professionals. It behaves like a trusted assistant who never calls in sick.</p>
            </section>

            <section>
                <h2 class="mb-3 text-[23px] font-semibold leading-tight text-white md:text-[32px]">Consistency That Builds Confidence</h2>
                <p>Consistency is the hidden luxury of baking. When results repeat beautifully, creativity feels safe. Bakers stop worrying and start experimenting, adding fruit, filling layers, frosting generously, or transforming the cake into truffles, jars, or plated desserts.</p>
                <p class="mt-4">And this is how a simple boxed cake mix quietly becomes a signature dessert.</p>
            </section>
        </div>

        <div class="mt-10 flex items-center gap-4 pb-6 font-urbanist text-sm md:gap-6">
            <span class="mr-1 text-white/80">Share on</span>
            <a href="#" aria-label="Share on Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#666] transition-all duration-300 hover:scale-105 hover:border-[#FF6210] hover:shadow-[0_0_18px_rgba(255,98,16,0.25)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#FF6210] md:h-12 md:w-12">
                <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 32 32" aria-hidden="true"><path fill="#FF6210" d="M7 0h18a7 7 0 0 1 7 7v18a7 7 0 0 1-7 7H7a7 7 0 0 1-7-7V7a7 7 0 0 1 7-7Z"/><path fill="#000" d="M18.4 27V17.4h3.2l.5-3.8h-3.7v-2.4c0-1.1.3-1.8 1.9-1.8h2V6c-.4-.1-1.7-.2-3.2-.2-3.2 0-5.4 1.9-5.4 5.5v2.3h-3.6v3.8h3.6V27h4.7Z"/></svg>
            </a>
            <a href="#" aria-label="Share on Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#666] transition-all duration-300 hover:scale-105 hover:border-[#FF6210] hover:shadow-[0_0_18px_rgba(255,98,16,0.25)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#FF6210] md:h-12 md:w-12">
                <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 32 32" aria-hidden="true"><path fill="#FF6210" d="M9 0h14a9 9 0 0 1 9 9v14a9 9 0 0 1-9 9H9a9 9 0 0 1-9-9V9a9 9 0 0 1 9-9Z"/><path fill="#000" d="M16 7.4a8.6 8.6 0 1 0 0 17.2 8.6 8.6 0 0 0 0-17.2Zm0 13.9a5.3 5.3 0 1 1 0-10.6 5.3 5.3 0 0 1 0 10.6ZM25 5.5a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg>
            </a>
            <a href="#" aria-label="Share on LinkedIn" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#666] transition-all duration-300 hover:scale-105 hover:border-[#FF6210] hover:shadow-[0_0_18px_rgba(255,98,16,0.25)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#FF6210] md:h-12 md:w-12">
                <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 32 32" aria-hidden="true"><path fill="#FF6210" d="M7 0h18a7 7 0 0 1 7 7v18a7 7 0 0 1-7 7H7a7 7 0 0 1-7-7V7a7 7 0 0 1 7-7Z"/><path fill="#000" d="M7.5 12.2h4.2V26H7.5V12.2Zm2.1-6.8a2.4 2.4 0 1 1 0 4.8 2.4 2.4 0 0 1 0-4.8ZM14 12.2h4v1.9h.1c.6-1.1 1.9-2.3 3.9-2.3 4.2 0 5 2.8 5 6.4V26h-4.2v-6.9c0-1.6 0-3.7-2.3-3.7s-2.6 1.8-2.6 3.6V26H14V12.2Z"/></svg>
            </a>
        </div>
    </article>

    <section class="mx-auto w-[calc(100%-36px)] max-w-[1238px] border-t border-white/20 pb-20 pt-8 md:w-[calc(100%-64px)] md:pt-10">
        <h2 class="mb-6 font-urbanist text-[25px] font-semibold text-white md:mb-8 md:text-[34px]">Latest Blogs</h2>
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 xl:grid-cols-3 xl:gap-[38px]">
            @foreach($latestBlogs as $index => $card)
                <article data-aos="fade-up" data-aos-delay="{{ $index * 100 }}" class="group min-w-0">
                    <a href="{{ $detailUrl }}" aria-label="Read {{ $card['title'] }} article" class="relative mb-4 block aspect-[1.2344]">
                        <div class="absolute inset-0 overflow-hidden rounded-[20px] md:rounded-[24px]">
                            <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" loading="lazy">
                            <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 font-urbanist text-[10px] font-semibold text-black md:text-xs">26,Jan,2026</span>
                        </div>
                        <span class="absolute -right-2 -top-2 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#FF6210] shadow-lg transition-transform duration-300 group-hover:rotate-45 md:-right-3 md:-top-3 md:h-[69px] md:w-[69px]" aria-hidden="true">
                            <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M7 17 17 7M7 7h10v10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </a>
                    <h3 class="font-urbanist text-lg font-semibold text-white md:text-[23px]">{{ $card['title'] }}</h3>
                    <p class="mt-1.5 max-w-[380px] font-urbanist text-sm leading-relaxed text-[#777] md:text-[17px]">{{ $card['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
</div>
@endsection
