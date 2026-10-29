@extends('backend::layouts.app')

@section('title', 'Dashboard | Settings')

@section('breadcrumbs')
    @php
        $title = 'System Settings';
        $breadcrumbs = [['label' => 'Dashboard', 'url' => route('dashboard.index')], ['label' => $title]];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    <style>
        .settings-hub { --settings-ink: #102f40; --settings-muted: #64748b; --settings-line: #dce5eb; max-width: 1600px; margin: 0 auto; padding-bottom: 32px; }
        .settings-hub-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin: 8px 0 34px; padding: 18px 20px; border: 1px solid #e4ebf0; border-radius: 14px; background: linear-gradient(110deg, #f7fbfa, #fff 72%); }
        .settings-hub-intro { margin: 0; color: var(--settings-muted); font-size: 14px; line-height: 1.6; }
        .settings-cache-button { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; min-height: 42px; padding: 0 17px !important; border-radius: 9px !important; box-shadow: 0 2px 5px rgb(153 27 27 / 12%); transition: background-color .18s ease, transform .18s ease, box-shadow .18s ease; }
        .settings-cache-button:hover { transform: translateY(-1px); box-shadow: 0 5px 12px rgb(153 27 27 / 18%); }
        .settings-cache-button:focus-visible, .settings-card:focus-visible { outline: 3px solid #6ee7b7; outline-offset: 3px; }
        .settings-category { margin-top: 34px; }
        .settings-category:first-child { margin-top: 0; }
        .settings-category-heading { display: flex; align-items: center; gap: 12px; margin: 0 0 16px; }
        .settings-category-heading::before { width: 4px; height: 28px; border-radius: 4px; background: #31be91; content: ''; }
        .settings-category-heading h2 { margin: 0; color: var(--settings-ink); font-size: 18px; font-weight: 700; letter-spacing: -.01em; }
        .settings-category-heading p { margin: 2px 0 0; color: var(--settings-muted); font-size: 12px; }
        .settings-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .settings-card { position: relative; display: flex; min-width: 0; min-height: 142px; gap: 15px; padding: 20px; overflow: hidden; border: 1px solid var(--settings-line); border-radius: 13px; background: #fff; color: inherit; text-decoration: none; box-shadow: 0 2px 5px rgb(15 23 42 / 3%); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease; }
        .settings-card::after { position: absolute; right: 0; bottom: 0; left: 0; height: 3px; background: #31be91; content: ''; opacity: 0; transform: scaleX(.25); transform-origin: left; transition: opacity .18s ease, transform .18s ease; }
        .settings-card:hover { transform: translateY(-3px); border-color: #a8d9c8; background: #fcfffd; box-shadow: 0 10px 24px rgb(16 47 64 / 8%); }
        .settings-card:hover::after { opacity: 1; transform: scaleX(1); }
        .settings-card-icon { display: grid; flex: 0 0 42px; place-items: center; width: 42px; height: 42px; border: 1px solid #d7eee5; border-radius: 12px; background: #effaf5; color: #147957; }
        .settings-card-icon .material-icons-outlined { font-size: 21px; }
        .settings-card-copy { min-width: 0; padding: 1px 18px 0 0; }
        .settings-card-copy h3 { margin: 0; color: #102f40; font-size: 16px; font-weight: 700; line-height: 1.35; }
        .settings-card-copy p { margin: 7px 0 0; color: #64748b; font-size: 12px; line-height: 1.55; }
        .settings-card-arrow { position: absolute; top: 17px; right: 15px; display: grid; width: 28px; height: 28px; place-items: center; border-radius: 8px; color: #94a3b8; transition: color .18s ease, background-color .18s ease, transform .18s ease; }
        .settings-card:hover .settings-card-arrow { transform: translateX(2px); background: #effaf5; color: #147957; }
        @media (max-width: 1200px) { .settings-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 900px) { .settings-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } }
        @media (max-width: 560px) { .settings-hub-toolbar { align-items: flex-start; flex-direction: column; margin-bottom: 28px; padding: 16px; } .settings-card-grid { grid-template-columns: minmax(0, 1fr); } .settings-card { min-height: 118px; padding: 17px; } .settings-category { margin-top: 28px; } }
        @media (prefers-reduced-motion: reduce) { .settings-card, .settings-card::after, .settings-card-arrow, .settings-cache-button { transition: none; } .settings-card:hover, .settings-cache-button:hover { transform: none; } }
    </style>

    @php
        $groups = Modules\Core\Events\RegisterSettingsMenu::getCachedGroupedMenu();
    @endphp
    <div class="settings-hub">
    <div class="settings-hub-toolbar">
        <p class="settings-hub-intro">Manage the preferences, services, and integrations available to your workspace.</p>
        <form method="POST" action="{{ route('settings.clear_cache') }}">
            @csrf
            <button type="submit" class="settings-cache-button inline-flex items-center bg-red-600 border border-transparent font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-25">
                <span class="material-icons-outlined mr-2">delete_sweep</span>
                Clear Cache
            </button>
        </form>
    </div>
    <div class="settings-groups">
    @foreach ($groups as $group)
        @php
            $category = $group['category'];
            $items = $group['items'];
        @endphp

        @if (collect($items)->some(fn($item) => bouncer($item['guard'])->hasPermission($item['permission'])))
            <section class="settings-category" aria-label="{{ $category['name'] }}">
                <div class="settings-category-heading">
                    <div>
                        <h2>{{ $category['name'] }}</h2>
                        <p>{{ $category['description'] }}</p>
                    </div>
                </div>
                <div class="settings-card-grid">
                    @foreach ($items as $item)
                        @if (bouncer($item['guard'])->hasPermission($item['permission']))
                            <a href="{{ $item['route'] }}" class="settings-card">
                                <span class="settings-card-icon" aria-hidden="true">
                                    <span class="material-icons-outlined">{{ $item['icon'] }}</span>
                                </span>
                                <span class="settings-card-copy">
                                    <h3>{{ $item['title'] }}</h3>
                                    <p>{{ $item['description'] }}</p>
                                </span>
                                <span class="settings-card-arrow" aria-hidden="true">
                                    <span class="material-icons-outlined">arrow_forward</span>
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
    </div>
    </div>
@endsection
