@php
    if (in_array($imageUrl, ['/dawat.png', asset('dawat.png')], true)) {
        $imageUrl = null;
    }
@endphp
<div class="h-14 w-14 overflow-hidden rounded-lg border border-gray-200 bg-gray-50" style="width:56px;height:56px">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $name }}" width="56" height="56" loading="lazy" decoding="async"
            class="h-full w-full object-cover" style="width:56px;height:56px;object-fit:cover"
            onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
    @endif
    <span @if ($imageUrl) hidden @endif role="img" aria-label="No image" title="No image uploaded">
        <span class="material-icons-outlined text-gray-400" aria-hidden="true" style="padding:15px">restaurant</span>
    </span>
</div>
