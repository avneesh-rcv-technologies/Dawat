@extends('backend::layouts.app')

{{-- Menu Management inside the restaurant panel: the onboarding menu screens in the light theme. --}}

@section('breadcrumbs')
    @php
        $menuBreadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard.index')]];
        if (bouncer()->hasPermission('admin.restaurant.index')) {
            $menuBreadcrumbs[] = ['label' => 'Restaurants', 'url' => route('admin.restaurant.index')];
        }
        $menuBreadcrumbs[] = ['label' => $menu->restaurant->name, 'url' => route('admin.restaurant.edit', $menu->restaurant->id)];
        // The breadcrumb bar shows the page title, so the page body doesn't repeat it.
        $menuTitle = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('heading')))) ?: 'Menu Management';
        $menuBreadcrumbs[] = ['label' => 'Menu Management', 'url' => $menu->route('index')];
        if ($menuTitle !== 'Menu Management') {
            $menuBreadcrumbs[] = ['label' => $menuTitle];
        }
    @endphp
    <x-breadcrumbs :breadcrumbs="$menuBreadcrumbs" :name="$menuTitle" />
@endsection

@push('styles')
    @include('restaurant::onboarding.menu.styles')
    <style>
        .menu-wrap .menu-page-head { margin-bottom: 24px; }
        .menu-wrap .menu-page-back { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px; color: var(--m-muted); font-size: 14px; font-weight: 500; text-decoration: none; }
        .menu-wrap .menu-page-back:hover { color: var(--m-text); }
        .menu-wrap .menu-page-title { margin: 0 0 6px; font-size: 24px; line-height: 1.25; font-weight: 700; color: var(--m-text); }
        .menu-wrap .menu-page-sub { margin: 0; max-width: 760px; color: var(--m-muted); font-size: 14px; line-height: 1.6; }
    </style>
@endpush

@section('content')
    <div class="menu-wrap menu-light @yield('menu_page_class')">
        <div class="menu-page-head">
            @hasSection('back_url')
                <a href="@yield('back_url')" class="menu-page-back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>@yield('back_label', 'Back')</span>
                </a>
            @endif
            <p class="menu-page-sub">@yield('subheading')</p>
        </div>

        @yield('menu_content')
    </div>
@endsection

{{-- Forms with data-confirm are confirmed by the shared popup (public/js/app-popup.js). --}}
