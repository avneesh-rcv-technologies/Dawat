@php
    $topProfileUser = Illuminate\Support\Facades\Auth::user();
    $topProfileRoleName = $topProfileUser?->role?->name ?? null;
    $topProfileIsRestaurantOwner = strcasecmp(trim((string) $topProfileRoleName), 'Restaurant Owner') === 0;
@endphp
@if(!$topProfileIsRestaurantOwner && bouncer(Illuminate\Support\Facades\Auth::getDefaultDriver())->hasPermission('settings.index'))
<a href="{{route('settings.index')}}" class="flex items-center rounded-lg px-3 py-2 text-sm transition hover:bg-neutral-100">
    <span class="material-icons-outlined mr-2 text-base text-neutral-500">settings</span>
    Setting
</a>
@endif
