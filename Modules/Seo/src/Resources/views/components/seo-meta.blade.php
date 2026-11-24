@if ($payload->title)
    <title>{{ $payload->title }}</title>
@endif

@if ($payload->description)
    <meta name="description" content="{{ $payload->description }}">
@endif

@if ($payload->keywords)
    <meta name="keywords" content="{{ $payload->keywords }}">
@endif

@if ($payload->robots)
    <meta name="robots" content="{{ $payload->robots }}">
@endif

@if ($payload->canonical)
    <link rel="canonical" href="{{ $payload->canonical }}">
@endif

@if ($payload->title)
    <meta property="og:title" content="{{ $payload->title }}">
@endif

@if ($payload->description)
    <meta property="og:description" content="{{ $payload->description }}">
@endif

@if ($payload->canonical)
    <meta property="og:url" content="{{ $payload->canonical }}">
@endif

@if ($payload->ogImage)
    <meta property="og:image" content="{{ $payload->ogImage }}">
@endif

@if ($payload->ogType)
    <meta property="og:type" content="{{ $payload->ogType }}">
@endif

<meta property="og:site_name" content="{{ \Modules\Seo\Support\SeoSettings::get('defaults.site_name', config('seo.defaults.site_name')) }}">

<meta name="twitter:card" content="{{ data_get(config('seo.defaults.twitter'), 'card', 'summary_large_image') }}">

@if ($payload->title)
    <meta name="twitter:title" content="{{ $payload->title }}">
@endif

@if ($payload->description)
    <meta name="twitter:description" content="{{ $payload->description }}">
@endif

@if ($payload->twitterImage || $payload->ogImage)
    <meta name="twitter:image" content="{{ $payload->twitterImage ?? $payload->ogImage }}">
@endif

@if (! empty($payload->schema))
    <script type="application/ld+json">{!! json_encode($payload->schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif

@foreach ($payload->extraMeta as $name => $content)
    <meta name="{{ $name }}" content="{{ $content }}">
@endforeach
