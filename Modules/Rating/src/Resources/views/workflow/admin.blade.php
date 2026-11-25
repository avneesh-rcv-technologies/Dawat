@extends('backend::layouts.app')

@section('title', 'Rating Management')

@push('styles')
<style>
    .rating-admin { --ra-ink:#182230; --ra-muted:#697586; --ra-line:#e8ebef; --ra-orange:#ff6210; color:var(--ra-ink); }
    .ra-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:22px; }
    .ra-eyebrow { margin:0 0 7px; color:var(--ra-orange); font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
    .ra-heading h1 { margin:0; color:var(--ra-ink); font-size:30px; font-weight:800; letter-spacing:-.035em; line-height:1.15; }
    .ra-heading p { margin:8px 0 0; color:var(--ra-muted); font-size:14px; }
    .ra-total { display:inline-flex; align-items:center; gap:8px; border:1px solid var(--ra-line); border-radius:999px; background:#fff; padding:9px 14px; color:#344054; font-size:12px; font-weight:700; white-space:nowrap; }
    .ra-total:before { width:7px; height:7px; border-radius:50%; background:var(--ra-orange); content:""; }
    .ra-flash { margin:0 0 18px; border:1px solid #b9ebd2; border-radius:12px; background:#effaf4; padding:12px 15px; color:#147343; font-size:13px; font-weight:600; }
    .ra-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:13px; margin-bottom:20px; }
    .ra-stat { display:flex; min-height:78px; align-items:center; justify-content:space-between; gap:12px; border:1px solid var(--ra-line); border-radius:13px; background:#fff; padding:14px 17px; }
    .ra-stat > div > span { display:block; color:var(--ra-muted); font-size:11px; font-weight:700; }
    .ra-stat strong { display:block; margin-top:5px; color:var(--ra-ink); font-size:23px; font-weight:800; line-height:1; }
    .ra-stat > .ra-icon { display:flex; width:36px; min-width:36px; height:36px; flex:0 0 36px; align-items:center; justify-content:center; align-self:center; margin-left:auto; border-radius:11px; background:#fff3eb; color:var(--ra-orange); font-family:Arial,sans-serif; font-size:18px; font-weight:700; line-height:1; }
    .ra-stat.pending .ra-icon { background:#fff7e8; color:#c98200; }
    .ra-stat.approved .ra-icon { background:#eaf8f0; color:#16824d; }
    .ra-stat.rejected .ra-icon { background:#fff0f0; color:#c23b42; }
    .ra-grid-shell { overflow:hidden; border:1px solid var(--ra-line); border-radius:15px; background:#fff; box-shadow:0 2px 8px #18223005; }
    .ra-grid-shell > .pb-1 { padding:0 16px 8px; }
    .ra-grid-shell .dataview-table { border:0; border-radius:11px; box-shadow:none; }
    .ra-grid-shell .dataview-table-scroll { border:1px solid #eee9e4; border-radius:11px; }
    .ra-grid-shell .dataview-table ~ div.flex.items-center.justify-between { padding:14px 4px 8px !important; }
    .ra-tabs { display:flex; align-items:center; gap:4px; overflow:auto; border-bottom:1px solid var(--ra-line); padding:0 12px; }
    .ra-tabs a { display:inline-flex; min-height:47px; align-items:center; gap:8px; border-bottom:2px solid transparent; padding:0 12px; color:#667085; font-size:12px; font-weight:700; text-decoration:none; white-space:nowrap; }
    .ra-tabs a:hover { color:var(--ra-orange); }
    .ra-tabs a.active { border-bottom-color:var(--ra-orange); color:#d94b00; }
    .ra-tabs b { display:inline-grid; min-width:21px; height:21px; place-items:center; border-radius:999px; background:#f2f4f7; padding:0 6px; color:#667085; font-size:10px; }
    .ra-tabs .active b { background:#fff0e8; color:#d94b00; }
    .ra-grid-shell .data-view-container, .ra-grid-shell [class*="data-view"] { max-width:100%; }
    .ra-grid-shell .data-view-container { padding-top:12px; }
    @media(max-width:720px) { .ra-heading { align-items:flex-start; flex-direction:column; gap:13px; } .ra-heading h1 { font-size:25px; } .ra-stats { grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; } .ra-stat { min-height:70px; padding:11px; } .ra-stat strong { font-size:20px; } .ra-grid-shell > .pb-1 { padding:0 10px 6px; } }
</style>
@endpush

@section('content')
@php
    $statusTabs = [
        'all' => ['label' => 'All restaurant reviews', 'count' => $counts['all']],
        'pending' => ['label' => 'Pending', 'count' => $counts['pending']],
        'approved' => ['label' => 'Approved', 'count' => $counts['approved']],
        'rejected' => ['label' => 'Rejected', 'count' => $counts['rejected']],
    ];
    $activeStatus = request('status', 'all');
@endphp
<div class="rating-admin">
    <header class="ra-heading">
        <div><p class="ra-eyebrow">Trust & feedback</p><h1>Restaurant rating management</h1><p>Review customer feedback for restaurants and decide what appears publicly.</p></div>
        <span class="ra-total">{{ number_format($counts['all']) }} total reviews</span>
    </header>
    @if(session('success'))<div class="ra-flash">{{ session('success') }}</div>@endif
    <section class="ra-stats" aria-label="Rating summary">
        @foreach([['all','Restaurant reviews','★'],['pending','Pending review','◷'],['approved','Approved','✓'],['rejected','Rejected','×']] as [$key,$label,$icon])
            <article class="ra-stat {{ $key }}"><div><span>{{ $label }}</span><strong>{{ number_format($counts[$key]) }}</strong></div><span class="ra-icon" aria-hidden="true">{{ $icon }}</span></article>
        @endforeach
    </section>
    <section class="ra-grid-shell" aria-label="Customer ratings">
        <nav class="ra-tabs" aria-label="Filter ratings by approval status">
            @foreach($statusTabs as $key => $tab)
                @php($tabQuery = request()->except('status', 'page'))
                @if($key !== 'all') @php($tabQuery['status'] = $key) @endif
                <a href="{{ route('ratings.workflow.admin.index', $tabQuery) }}" class="{{ $activeStatus === $key ? 'active' : '' }}">{{ $tab['label'] }}<b>{{ $tab['count'] }}</b></a>
            @endforeach
        </nav>
        <x-data-view :data="$lists" title="" url="" />
    </section>
</div>
@endsection
