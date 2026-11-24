@php
    $companyFavicon = fn_get_image('company_favicon', 0);
@endphp
@if(is_array($companyFavicon) && ($companyFavicon['object_type'] ?? null) === 'company_favicon' && !empty($companyFavicon['url']))
    <link rel="icon" href="{{ $companyFavicon['url'] }}" @if(!empty($companyFavicon['mime_type'])) type="{{ $companyFavicon['mime_type'] }}" @endif>
@endif
