@if(($latestBlogs ?? collect())->isNotEmpty())
<section class="website-blog-latest mx-auto border-t border-white/20 pb-20 pt-12 md:pb-28">
    <h2 class="mb-10 font-urbanist text-[30px] font-semibold text-white md:text-[40px]">Latest Blogs</h2>
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($latestBlogs as $blog)
                @php
                    $articleUrl = route('website.blog-detail', ['slug' => $blog->post_url]);
                    $cover = $blog->outer_image_url ?: \Modules\Website\Support\WebsiteImages::url('website/73964bf77c75b2eba31ec165f554afb485d18dad.jpg');
                    $summary = html_entity_decode(strip_tags($blog->short_description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                @endphp
                    <article data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 100 }}" class="group min-w-0">
                        <a href="{{ $articleUrl }}" aria-label="Read {{ $blog->title }} article" class="relative mb-[15.5px] block aspect-[1.2344] overflow-visible">
                            <div class="absolute inset-0 overflow-hidden rounded-[20px] md:rounded-[24px]">
                                <img src="{{ $cover }}"
                                     alt="{{ $blog->title }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                                     loading="lazy"
                                     decoding="async">
                                <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 font-urbanist text-[10px] font-semibold text-black md:left-4 md:top-4 md:text-[12px]">
                                    @if($blog->display_date)
                                        <time datetime="{{ $blog->display_date->toDateString() }}">{{ $blog->display_date->format('d M Y') }}</time>
                                    @endif
                                </span>
                            </div>
                            <span class="absolute -right-2 -top-2 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white text-[#FF6210] shadow-lg transition-transform duration-300 group-hover:rotate-45 md:-right-3 md:-top-3 md:h-[69px] md:w-[69px]" aria-hidden="true">
                                <svg class="h-5 w-5 md:h-6 md:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M7 17 17 7M7 7h10v10" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </a>
                        <h3 class="font-urbanist text-[17px] font-semibold leading-snug text-white md:text-[23px] md:leading-[28px]"><a href="{{ $articleUrl }}">{{ $blog->title }}</a></h3>
                        <p class="mt-1.5 max-w-[380px] font-urbanist text-[13px] leading-relaxed text-[#5A5A59] md:mt-[7.74px] md:text-[17px] md:leading-[21px]">{{ \Illuminate\Support\Str::limit($summary, 160) }}</p>
                    </article>
        @endforeach
    </div>
</section>
@endif
