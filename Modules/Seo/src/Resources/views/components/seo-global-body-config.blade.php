@if ($gtmEnabled && $gtmContainerId)
    @include('seo::components.gtm-body', ['gtmContainerId' => $gtmContainerId])
@endif

@if ($customBodyScripts)
    {!! $customBodyScripts !!}
@endif
