@php
    $scriptType = 'text/javascript';
@endphp

@if ($gscVerificationCode)
    <meta name="google-site-verification" content="{{ $gscVerificationCode }}">
@endif

@if ($gtmEnabled && $gtmContainerId)
    @include('seo::components.gtm-head', [
        'gtmContainerId' => $gtmContainerId,
    ])
@endif

@if ($gaEnabled && $gaMeasurementId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($gaMeasurementId)@if($gaAnonymizeIp), { 'anonymize_ip': true }@endif);
       
    </script>
@endif

@if ($facebookPixelId)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', @js($facebookPixelId));
        fbq('track', 'PageView');
    </script>
@endif

@if ($customHeadScripts)
    {!! $customHeadScripts !!}
@endif
