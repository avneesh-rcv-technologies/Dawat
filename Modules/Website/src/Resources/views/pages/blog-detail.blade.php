@extends('website::layouts.app')

@section('title', e($blog->meta_title ?: $blog->title).' — Dawat')
@section('meta_description', e($blog->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($blog->short_description ?? ''), 160)))

@section('content')
@php
    $content = \Modules\Website\Support\BlogContent::split($blog->long_description);
@endphp
<article class="website-blog-article mx-auto pb-14 pt-28 md:pt-[180px]">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('website.blogs') }}" aria-label="Back to blogs" class="inline-flex items-center gap-2 rounded-full bg-[#251103] px-5 py-2 font-urbanist text-sm font-semibold text-[#FF6210] transition-colors hover:bg-[#351804]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Prev
        </a>
        @if($isPreview ?? false)
        <span class="rounded-full border border-[#FF6210]/40 px-4 py-2 font-urbanist text-xs text-[#FF6210]">Preview &middot; {{ ucfirst($blog->status) }}</span>
        @endif
    </div>
    <header class="mb-10">
        <h1 class="website-blog-title">{{ $blog->title }}</h1>
        @if($content['introduction'])
            <div class="website-blog-intro mt-6 font-urbanist">{!! $content['introduction'] !!}</div>
        @elseif($blog->short_description)
            <div class="website-blog-intro mt-6 font-urbanist">
                @foreach(preg_split('/<\/p\s*>|<br\s*\/?\s*>|\r?\n/i', $blog->short_description) as $paragraph)
                    @if(trim(strip_tags($paragraph)) !== '')
                        <p>{{ html_entity_decode(strip_tags($paragraph), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</p>
                    @endif
                @endforeach
            </div>
        @endif
        @php
            $avatar = $blog->author?->avatar_url;
            $authorUrl = ($blog->author?->status && filled($blog->author?->slug))
                ? route('website.author-detail', ['slug' => $blog->author->slug])
                : null;
        @endphp
        <div class="mt-6 flex flex-wrap items-center gap-3 font-outfit text-base leading-5 text-white/60">
            @if($authorUrl)
                <a href="{{ $authorUrl }}" class="inline-flex items-center gap-3 transition-opacity hover:opacity-90" aria-label="View author {{ $blog->author_display_name }}">
                    @if($avatar)
                        <img src="{{ $avatar }}" alt="" class="h-10 w-10 rounded-full object-cover object-center" loading="lazy" decoding="async">
                    @else
                        <span aria-hidden="true" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 font-semibold text-white/90">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($blog->author_display_name, 0, 1)) }}</span>
                    @endif
                    <span class="font-medium text-white/90">{{ $blog->author_display_name }}</span>
                </a>
            @else
                @if($avatar)
                    <img src="{{ $avatar }}" alt="" class="h-10 w-10 rounded-full object-cover object-center" loading="lazy" decoding="async">
                @else
                    <span aria-hidden="true" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 font-semibold text-white/90">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($blog->author_display_name, 0, 1)) }}</span>
                @endif
                <span class="font-medium text-white/90">{{ $blog->author_display_name }}</span>
            @endif
            @if($blog->display_date)
                <span aria-hidden="true" class="h-5 border-l border-white/20"></span>
                <time datetime="{{ $blog->display_date->toDateString() }}">{{ $blog->display_date->format('d-m-Y') }}</time>
            @endif
        </div>
    </header>
    @php
        $innerImage = $blog->inner_image_url;
        $outerImage = $blog->outer_image_url;
        $cover = $innerImage ?: $outerImage;
    @endphp
    @if($cover)
        <img src="{{ $cover }}" alt="{{ $blog->title }}" class="website-blog-cover" loading="eager" decoding="async" fetchpriority="high">
    @endif
    <div class="website-blog-content font-urbanist">
        {!! $content['body'] !!}
    </div>
    @include('website::pages.partials.blog-faqs')
</article>
@include('website::pages.partials.blog-latest')
@endsection
