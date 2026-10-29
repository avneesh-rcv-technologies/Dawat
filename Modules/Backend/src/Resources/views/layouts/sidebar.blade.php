@php
    $logo = fn_get_image('company_logo', 0)['url'] ?? '';
    $sidebarCompany = fn_get_setting('general.company.name') ?: 'Admin Panel';
    $event = new \Modules\Core\Events\RegisterMenu();
    event($event);
    $groups = collect($event->getMenu())->sortBy('position')->map(function ($item) {
        if (isset($item['children'])) {
            $item['children'] = array_values(array_filter($item['children'], fn ($child) => \Modules\Core\Helpers\MenuVisibility::visible($child)));
            return count($item['children']) ? $item : null;
        }
        return \Modules\Core\Helpers\MenuVisibility::visible($item) ? $item : null;
    })->filter()->values();
    // Prefer the most specific visible route so Restaurant and Approvals do not both highlight.
    $currentMenuRoute = request()->route()?->getName() ?? '';
    $activeMenuRoute = $groups->flatMap(fn ($item) => $item['children'] ?? [$item])
        ->filter(function ($item) use ($currentMenuRoute) {
            $route = $item['route'] ?? '';
            return $route !== '' && ($currentMenuRoute === $route
                || (str_contains($route, '.') && \Illuminate\Support\Str::is(\Illuminate\Support\Str::beforeLast($route, '.').'.*', $currentMenuRoute)));
        })->sortByDesc(fn ($item) => $item['route'] === $currentMenuRoute ? PHP_INT_MAX : strlen(\Illuminate\Support\Str::beforeLast($item['route'], '.')))
        ->first()['route'] ?? null;
@endphp
<aside id="sidebar" class="admin-sidebar" aria-label="Main navigation">
    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $sidebarCompany }}">
            @else
                <span class="sidebar-brand-name">{{ $sidebarCompany }}</span>
            @endif
        </div>
        <span class="sidebar-brand-initial" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sidebarCompany, 0, 1)) }}</span>
        <button type="button" id="sidebar-mobile-close" aria-label="Close navigation menu"><span class="material-icons-outlined" aria-hidden="true">close</span></button>
    </div>
    <nav id="sidebarMenu" class="sidebar-navigation" aria-label="Administration">
        <p class="sidebar-section-label">Workspace</p>
        @forelse ($groups as $item)
            @if (!isset($item['children']))
                <x-sidebar.link href="{{ route($item['route']) }}" label="{{ $item['label'] }}"
                    icon="{{ $item['icon'] ?? 'dashboard' }}" :badge="$item['badge'] ?? null" :active="$activeMenuRoute === $item['route']" />
            @else
                @php
                    $menuId = 'sidebar-group-'.$loop->index.'-'.\Illuminate\Support\Str::slug($item['label']);
                    $groupActive = collect($item['children'])->contains(fn ($child) => $child['route'] === $activeMenuRoute);
                @endphp
                <div class="sidebar-parent-group {{ $groupActive ? 'has-active-child' : '' }}">
                    <button type="button" id="{{ $menuId }}-button" class="sidebar-toggle-btn sidebar-nav-link"
                        title="{{ $item['label'] }}" aria-controls="{{ $menuId }}" aria-expanded="{{ $groupActive ? 'true' : 'false' }}" data-target="{{ $menuId }}">
                        <span class="material-icons-outlined sidebar-nav-icon" aria-hidden="true">{{ $item['icon'] ?? 'folder' }}</span>
                        <span class="sidebar-collapse-text sidebar-link-label">{{ $item['label'] }}</span>
                        <span class="material-icons-outlined toggle-icon sidebar-collapse-text" aria-hidden="true">chevron_right</span>
                    </button>
                    <div id="{{ $menuId }}" class="submenu" @if (!$groupActive) hidden @endif>
                        @foreach ($item['children'] as $child)
                            <a href="{{ route($child['route']) }}" title="{{ $child['label'] }}"
                                class="sidebar-child-link {{ $activeMenuRoute === $child['route'] ? 'active' : '' }}" @if ($activeMenuRoute === $child['route']) aria-current="page" @endif>
                                <span class="sidebar-child-dot" aria-hidden="true"></span><span>{{ $child['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div id="{{ $menuId }}-popover" class="sidebar-popover" aria-labelledby="{{ $menuId }}-button" hidden>
                        <p class="sidebar-popover-heading">{{ $item['label'] }}</p>
                        @foreach ($item['children'] as $child)
                            <a href="{{ route($child['route']) }}" class="sidebar-child-link {{ $activeMenuRoute === $child['route'] ? 'active' : '' }}" @if ($activeMenuRoute === $child['route']) aria-current="page" @endif>{{ $child['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <p class="sidebar-empty">No navigation available.</p>
        @endforelse
    </nav>
    <div class="sidebar-footer">
        @hook('backend:profile-sidebar-bottom.pre')
        <form method="POST" action="{{ route('admin.logout') }}" class="form-ajax sidebar-mobile-logout">
            @csrf
            <button type="submit" name="button" class="sidebar-nav-link" aria-label="Sign out">
                <span class="material-icons-outlined sidebar-nav-icon" aria-hidden="true">logout</span>
                <span>Sign out</span>
            </button>
        </form>
        <button id="super-sidebar-collapse-button" data-testid="super-sidebar-collapse-button" type="button" class="sidebar-nav-link"
            aria-label="Collapse sidebar" title="Collapse sidebar" aria-controls="sidebarMenu" aria-expanded="true">
            <span class="material-icons-outlined sidebar-nav-icon" id="super-sidebar-collapse-icon" aria-hidden="true">first_page</span>
            <span class="sidebar-collapse-text" id="sidebar-collapse-label">Collapse sidebar</span>
        </button>
        @hook('backend:profile-sidebar-bottom.post')
    </div>
</aside>
<button id="sidebar-mobile-overlay" class="sidebar-mobile-overlay" type="button" aria-label="Close navigation menu" tabindex="-1" hidden></button>
<style>
    #sidebar { position: fixed; inset: 0 auto 0 0; width: min(290px, calc(100vw - 48px)); flex: 0 0 auto; height: 100vh; height: 100dvh; min-height: 0; display: flex; flex-direction: column; background: var(--color-sidebar-bg, #101010); color: var(--sidebar-text-color, #fff); border-right: 1px solid #ffffff12; transform: translateX(-100%); visibility: hidden; transition: width 180ms ease, transform 180ms ease; z-index: 70; }
    #sidebar.is-mobile-open { transform: translateX(0); visibility: visible; }
    #mobileSidebarToggle { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; touch-action: manipulation; }
    #sidebar [hidden], .sidebar-popover[hidden], .sidebar-mobile-overlay[hidden] { display: none !important; }
    .sidebar-brand { min-height: 102px; padding: 20px 24px; display: flex; align-items: center; border-bottom: 1px solid #ffffff14; position: relative; flex-shrink: 0; }
    .sidebar-brand-logo { min-width: 0; width: 100%; }
    .sidebar-brand-logo img { max-width: 100%; width: 170px; height: 60px; object-fit: contain; object-position: left center; }
    .sidebar-brand-name { display: block; font-size: 23px; font-weight: 600; overflow-wrap: anywhere; }
    .sidebar-brand-initial { display: none; width: 40px; height: 40px; place-items: center; font-size: 23px; font-family: Georgia, serif; background: #ffffff14; border: 1px solid #ffffff24; border-radius: 12px; }
    .sidebar-navigation { min-height: 0; flex: 1; overflow-y: auto; overflow-x: hidden; overscroll-behavior: contain; padding: 20px 12px; scrollbar-width: thin; scrollbar-color: #ffffff30 transparent; }
    .sidebar-section-label { margin: 0 12px 14px; color: #a3a8b0; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .14em; }
    #sidebar .sidebar-nav-link { display: flex; align-items: center; gap: 11px; width: 100%; min-height: 44px; margin-bottom: 4px; padding: 10px 12px; border: 0; border-radius: 9px; background: transparent; color: #d4d7dd; text-align: left; text-decoration: none; font-size: 13px; font-weight: 500; line-height: 1.4; cursor: pointer; transition: background-color 150ms, color 150ms; }
    #sidebar .sidebar-nav-link:hover { color: #fff; background: var(--sidebar-bg-hover, #272b32); }
    #sidebar .sidebar-nav-link.active, #sidebar .has-active-child > .sidebar-nav-link { color: #fff; background: var(--sidebar-bg-active, #252a33); }
    .sidebar-link-label { flex: 1; min-width: 0; overflow-wrap: anywhere; }
    #sidebar .sidebar-nav-icon { font-size: 21px; width: 22px; flex-shrink: 0; text-align: center; color: #aeb5c0; }
    #sidebar .active .sidebar-nav-icon, #sidebar .has-active-child > button .sidebar-nav-icon { color: #fff; }
    #sidebar .toggle-icon { margin-left: auto; font-size: 18px; transition: transform 150ms; }
    #sidebar .sidebar-toggle-btn[aria-expanded="true"] .toggle-icon { transform: rotate(90deg); }
    #sidebar .submenu { margin: 6px 0 12px 23px; padding-left: 13px; border-left: 1px solid #ffffff20; }
    .sidebar-child-link { display: flex; align-items: center; gap: 9px; padding: 10px; margin: 3px 0; border-radius: 7px; color: #b7bec9; font-size: 12px; line-height: 1.5; text-decoration: none; overflow-wrap: anywhere; }
    .sidebar-child-link:hover { background: var(--sidebar-submenu-hover, #252a33); color: #fff; }
    .sidebar-child-link.active { background: var(--sidebar-bg-active, #252a33); color: #fff; font-weight: 600; }
    .sidebar-child-dot { width: 4px; height: 4px; border-radius: 50%; background: currentColor; flex-shrink: 0; opacity: .7; }
    .sidebar-child-link.active .sidebar-child-dot { background: #d3b48d; opacity: 1; }
    .sidebar-footer { padding: 12px; border-top: 1px solid #ffffff14; flex-shrink: 0; }
    .sidebar-footer .sidebar-nav-link { margin-bottom: 0 !important; color: #b7bec9 !important; font-size: 12px !important; }
    .sidebar-footer .sidebar-mobile-logout .sidebar-nav-link { color: #fca5a5 !important; min-height: 44px; }
    .sidebar-mobile-logout .sidebar-nav-icon { color: inherit !important; }
    .sidebar-mobile-logout { margin: 0; padding-bottom: env(safe-area-inset-bottom); }
    .sidebar-badge { margin-left: auto; padding: 2px 6px; border-radius: 5px; background: #ffffff20; font-size: 10px; }
    .sidebar-empty { color: #a3a8b0; padding: 12px; font-size: 12px; }
    #sidebar :is(a, button):focus-visible, .sidebar-popover a:focus-visible { outline: 2px solid #d3b48d; outline-offset: -2px; }
    .sidebar-popover { position: fixed; width: 240px; max-width: calc(100vw - 24px); max-height: calc(100vh - 24px); overflow-y: auto; padding: 8px; background: var(--sidebar-submenu-bg, #161a21); color: var(--sidebar-submenu-text, #fff); border: 1px solid #ffffff20; border-radius: 12px; box-shadow: 0 16px 40px #0004; z-index: 1000; }
    .sidebar-popover-heading { margin: 0 0 6px; padding: 8px 10px 12px; border-bottom: 1px solid #ffffff14; font-size: 12px; font-weight: 600; }
    #sidebar-mobile-close { display: flex; flex-shrink: 0; align-items: center; justify-content: center; width: 44px; height: 44px; border: 0; border-radius: 8px; background: #ffffff14; color: #fff; cursor: pointer; }
    #super-sidebar-collapse-button { display: none !important; }
    .sidebar-mobile-overlay { position: fixed; inset: 0; background: #0b122080; border: 0; z-index: 60; touch-action: none; }
    @media (min-width: 1024px) {
        #sidebar { position: relative; inset: auto; width: 248px; height: 100%; transform: none; visibility: visible; z-index: 20; }
        #mobileSidebarToggle, #sidebar-mobile-close { display: none; }
        .sidebar-mobile-logout { display: none; }
        #super-sidebar-collapse-button { display: flex !important; }
        #sidebar.collapsed { width: 76px; }
        #sidebar.collapsed .sidebar-collapse-text, #sidebar.collapsed .sidebar-section-label, #sidebar.collapsed .sidebar-badge, #sidebar.collapsed .sidebar-brand-logo, #sidebar.collapsed .submenu { display: none; }
        #sidebar.collapsed .sidebar-brand { padding: 20px 18px; justify-content: center; }
        #sidebar.collapsed .sidebar-brand-initial { display: grid; }
        #sidebar.collapsed .sidebar-nav-link { justify-content: center; padding: 12px; }
        #sidebar.collapsed .sidebar-nav-icon { color: inherit; }
    }
    @media (prefers-reduced-motion: reduce) { #sidebar, #sidebar * { transition: none !important; } }
</style>
<script src="{{ asset('js/admin-sidebar.js') }}?v={{ filemtime(public_path('js/admin-sidebar.js')) }}" defer></script>
