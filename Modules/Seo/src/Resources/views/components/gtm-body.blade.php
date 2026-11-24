@php
    $gtmContainerId = $gtmContainerId ?? fn_get_seo_setting('analytics.gtm_container_id');
@endphp

@if ($gtmContainerId)
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmContainerId }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
@endif
