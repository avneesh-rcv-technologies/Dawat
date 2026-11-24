@extends('website::layouts.app')

@section('title', e($author->name).' — Dawat')
@section('meta_description', e(\Illuminate\Support\Str::limit(strip_tags($author->bio ?? ($author->designation ? $author->name.' · '.$author->designation : 'Articles by '.$author->name.' on Dawat')), 160)))

@section('content')
@php
    $avatar = $author->avatar_url;
    $socialLinks = $author->socialLinks();
    $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($author->name, 0, 1));
@endphp
<div class="bg-black">
    <section class="website-blog-article mx-auto pb-10 pt-28 md:pb-14 md:pt-[180px]">
        <div class="mb-8">
            <a href="{{ route('website.blogs') }}" aria-label="Back to blogs" class="inline-flex items-center gap-2 rounded-full bg-[#251103] px-5 py-2 font-urbanist text-sm font-semibold text-[#FF6210] transition-colors hover:bg-[#351804]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Blogs
            </a>
        </div>

        <header class="flex flex-col items-start gap-6 sm:flex-row sm:items-center sm:gap-8">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $author->name }}" class="h-28 w-28 rounded-full object-cover object-center md:h-36 md:w-36" loading="eager" decoding="async" fetchpriority="high">
            @else
                <span aria-hidden="true" class="flex h-28 w-28 items-center justify-center rounded-full bg-white/10 font-urbanist text-4xl font-semibold text-white md:h-36 md:w-36 md:text-5xl">{{ $initial }}</span>
            @endif

            <div class="min-w-0 flex-1">
                <h1 class="font-urbanist text-[32px] font-semibold leading-tight text-white md:text-[48px] md:leading-[58px]">{{ $author->name }}</h1>
                @if($author->designation)
                    <p class="mt-2 font-outfit text-base text-[#FF6210] md:text-lg">{{ $author->designation }}</p>
                @endif
                @if($author->bio)
                    <p class="mt-4 max-w-3xl font-urbanist text-[15px] leading-relaxed text-white/75 md:text-base md:leading-6">{{ $author->bio }}</p>
                @endif
                {{-- @if($socialLinks)
                    <ul class="mt-5 flex flex-wrap items-center gap-3" aria-label="{{ $author->name }} social links">
                        @foreach($socialLinks as $network => $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full border border-white/15 px-4 py-1.5 font-urbanist text-sm text-white/80 transition-colors hover:border-[#FF6210]/50 hover:text-[#FF6210]">
                                    {{ \Illuminate\Support\Str::ucfirst($network) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif --}}
            </div>
        </header>
    </section>

    <section class="relative z-10 mx-auto w-[90%] max-w-[1700px] pb-16 md:pb-[122px]" aria-labelledby="author-blogs-heading">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-3 md:mb-12">
            <h2 id="author-blogs-heading" class="font-urbanist text-[28px] font-semibold text-white md:text-[40px]">
                Articles by {{ $author->name }}
            </h2>
            <p class="font-urbanist text-sm text-white/50 md:text-base">
                {{ $blogs->total() }} {{ \Illuminate\Support\Str::plural('article', $blogs->total()) }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-[38.6px] xl:gap-y-[67px]">
            @forelse($blogs as $blog)
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
            @empty
                <p class="col-span-full py-12 text-center font-urbanist text-white/75">No published articles from this author yet.</p>
            @endforelse
        </div>

        @if($blogs->hasPages())
            <nav aria-label="Author articles pagination" class="mt-12 flex items-center justify-center gap-6 font-urbanist text-white">
                @if($blogs->onFirstPage())
                    <span class="text-white/50">Previous</span>
                @else
                    <a href="{{ $blogs->previousPageUrl() }}" rel="prev" class="text-[#FF6210]">Previous</a>
                @endif
                <span>Page {{ $blogs->currentPage() }} of {{ $blogs->lastPage() }}</span>
                @if($blogs->hasMorePages())
                    <a href="{{ $blogs->nextPageUrl() }}" rel="next" class="text-[#FF6210]">Next</a>
                @else
                    <span class="text-white/50">Next</span>
                @endif
            </nav>
        @endif
    </section>
</div>
@endsection
