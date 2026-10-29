@extends('backend::layouts.app')

@section('title', 'System Activity Log')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index') ],
    ['label' => 'System Activity Log', 'url' => null ],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'System Activity Log'" />
@endsection

@section('content')

<div class="activity-log-page" x-data="{ currentView: 'cards', expandedCard: null }" @view-changed.window="currentView = $event.detail">
    <div class="activity-log-layout space-y-6">
        <!-- Header Component -->
        @include('activitylog::components.activity.activity-header', ['stats' => $stats])

        <!-- Stats Cards Component -->
        @include('activitylog::components.activity.activity-stats', ['stats' => $stats])

        <!-- View Controls -->
        @include('activitylog::components.activity.activity-controls')

        <!-- Activity Log -->
        <div class="activity-results-panel bg-white rounded-lg shadow-sm overflow-hidden">
            @include('activitylog::components.activity.activity-log-header', [
            'total' => $activities->total(),
            'currentPage' => $activities->currentPage(),
            'lastPage' => $activities->lastPage(),
            ])

            <!-- Cards View -->
            <div x-show="currentView === 'cards'" class="divide-y divide-gray-200" id="activity-cards-container">
                @forelse($activities as $activity)
                    @include('activitylog::components.activity.activity-card', ['activity' => $activity])
                @empty
                    @include('activitylog::components.activity.empty-state')
                @endforelse
            </div>

            <!-- Pagination -->
            @if($activities->hasPages())
                @include('activitylog::components.activity.activity-pagination', ['activities' => $activities])
            @endif
        </div>
    </div>
</div>
<style>
    .activity-log-page { --activity-ink: #102f40; --activity-muted: #64748b; --activity-line: #e2e8f0; max-width: 1600px; margin: 0 auto; padding-bottom: 28px; }
    .activity-log-layout { display: grid; gap: 18px; }
    .activity-log-layout > * { min-width: 0; margin: 0 !important; }
    .activity-log-hero { position: relative; padding: 24px 26px !important; overflow: hidden; border: 1px solid #dce8e5 !important; border-radius: 15px !important; background: linear-gradient(115deg, #f3faf7 0%, #fff 70%) !important; box-shadow: 0 3px 10px rgb(15 23 42 / 4%) !important; }
    .activity-log-hero::before { position: absolute; inset: 0 auto 0 0; width: 4px; background: #31be91; content: ''; }
    .activity-log-hero h1 { color: var(--activity-ink) !important; font-size: 23px !important; letter-spacing: -.02em; }
    .activity-log-hero p { max-width: 720px; color: var(--activity-muted) !important; font-size: 13px; }
    .activity-log-hero button { min-height: 40px; border: 0 !important; border-radius: 9px !important; background: #103647 !important; color: #fff !important; box-shadow: 0 3px 7px rgb(16 54 71 / 15%); }
    .activity-log-hero button:hover { background: #17506a !important; }
    .activity-log-hero button:focus-visible, .activity-log-controls button:focus-visible, .activity-log-card button:focus-visible { outline: 3px solid #6ee7b7; outline-offset: 2px; }
    .activity-stats-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px !important; }
    .activity-stat-card { min-width: 0; padding: 16px !important; border-radius: 12px !important; box-shadow: 0 2px 6px rgb(15 23 42 / 3%); transition: transform .16s ease, box-shadow .16s ease; }
    .activity-stat-card:hover { transform: translateY(-2px); box-shadow: 0 7px 16px rgb(15 23 42 / 7%); }
    .activity-stat-card > div:first-child > span { font-size: 12px !important; line-height: 1.35; }
    .activity-stat-card > div:nth-child(2) { margin: 8px 0 2px; font-size: 23px !important; line-height: 1.2; }
    .activity-log-controls { padding: 12px 14px !important; border: 1px solid var(--activity-line) !important; border-radius: 12px !important; background: #fff !important; box-shadow: 0 2px 6px rgb(15 23 42 / 3%) !important; }
    .activity-log-controls > div { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    .activity-log-controls > div > div:first-child { display: flex; flex-wrap: wrap; gap: 5px !important; }
    .activity-log-controls button { min-height: 38px; padding: 8px 12px !important; border: 1px solid transparent; border-radius: 8px !important; color: #64748b; transition: color .15s ease, background-color .15s ease, border-color .15s ease; }
    .activity-log-controls button:hover { background: #f1f7f6; color: #103647; }
    .activity-log-controls button.bg-gray-100 { border-color: #d7e9e2; background: #eef8f3 !important; color: #126746 !important; }
    .activity-log-controls > div > div:last-child { flex: 0 1 360px; max-width: 360px; margin-left: auto; }
    #activity-search-input { width: 100% !important; min-height: 40px; border-color: #d7e0e8; border-radius: 9px; color: #102f40; font-size: 13px; }
    #activity-search-input:focus { border-color: #31be91; outline: 0; box-shadow: 0 0 0 3px rgb(49 190 145 / 16%); }
    .activity-results-panel { overflow: hidden; border: 1px solid var(--activity-line); border-radius: 13px !important; background: #fff; box-shadow: 0 3px 10px rgb(15 23 42 / 4%) !important; }
    .activity-results-heading { padding: 15px 20px !important; border-bottom: 1px solid var(--activity-line) !important; background: #f8fafc !important; }
    .activity-results-heading h3 { color: var(--activity-ink) !important; font-size: 15px !important; font-weight: 700 !important; }
    .activity-results-heading span { color: var(--activity-muted) !important; font-size: 12px; }
    .activity-log-card { margin: 0 !important; }
    .activity-log-card-content { padding: 17px 20px !important; border: 0 !important; background: #fff !important; transition: background-color .15s ease; }
    .activity-log-card-content:hover { background: #fbfdfd !important; }
    .activity-log-card-content > div:first-child { gap: 14px !important; }
    .activity-log-card-content img, .activity-log-card-content .activity-avatar { width: 40px; height: 40px; }
    .activity-log-card-content h4 { color: #334155 !important; font-weight: 600 !important; }
    .activity-log-card-content p { color: #64748b !important; }
    .activity-log-card-content span.px-2.py-1 { border: 1px solid rgb(148 163 184 / 18%); border-radius: 999px !important; font-size: 10px; font-weight: 700; letter-spacing: .01em; }
    .activity-log-card-content > div[x-show] p { overflow-wrap: anywhere; white-space: pre-wrap; }
    .activity-log-card-content button { display: grid; width: 34px; height: 34px; place-items: center; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; }
    .activity-log-card-content button:hover { border-color: #cbd5e1; background: #f8fafc; }
    .activity-log-card-content > div[x-show] { border-color: #e8eef2 !important; }
    .activity-log-pagination { border-color: var(--activity-line) !important; background: #f8fafc !important; }
    .activity-log-pagination a, .activity-log-pagination span { min-height: 38px; border-radius: 8px !important; }
    .activity-log-pagination a:hover { border-color: #cbd5e1; background: #f1f5f9; }
    @media (max-width: 1200px) { .activity-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 700px) { .activity-log-hero { padding: 20px !important; } .activity-log-hero > div:first-child { flex-direction: column; gap: 16px; } .activity-log-controls > div { align-items: stretch; } .activity-log-controls > div > div:last-child { flex-basis: 100%; max-width: none; margin-left: 0; } }
    @media (max-width: 560px) { .activity-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .activity-stat-card { padding: 13px !important; } .activity-log-card-content { padding: 15px !important; } .activity-log-card-content > div:first-child { gap: 10px !important; } .activity-log-controls > div > div:first-child { width: 100%; justify-content: space-between; } .activity-log-controls button { padding: 7px 9px !important; font-size: 12px; } }
    @media (prefers-reduced-motion: reduce) { .activity-stat-card, .activity-log-card-content, .activity-log-controls button { transition: none; } .activity-stat-card:hover { transform: none; } }
</style>
@push('scripts')
<script>
    window.activityLogSearchRoute = "{{ route('activity-log.agency-search') }}";
</script>
@endpush
@endsection
