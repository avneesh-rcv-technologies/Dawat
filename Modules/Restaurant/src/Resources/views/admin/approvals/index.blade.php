@extends('backend::layouts.app')

@section('title', 'Onboarding Documents & Reviews')

@section('breadcrumbs')
    @php
        $title = 'Onboarding Documents & Reviews';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <section class="approval-counts-section" aria-label="Restaurant application summary">
        <p class="approval-counts-help">{{ $ownerOnly ? 'Your restaurant applications' : 'Across all restaurant applications' }}</p>
        <dl class="approval-counts">
            @foreach([
                'approved' => ['label' => 'Total Approved', 'help' => 'Registration approved'],
                'pending' => ['label' => 'Total Pending', 'help' => 'Awaiting registration review'],
                'rejected' => ['label' => 'Total Rejected', 'help' => 'Registration rejected'],
                'draft' => ['label' => 'Total Draft', 'help' => 'Registration not submitted'],
                'resubmitted' => ['label' => 'Total Resubmitted', 'help' => 'Resubmitted at least once'],
            ] as $key => $card)
                <div class="approval-count-card approval-count-{{ $key }}">
                    <dt><span class="approval-count-dot" aria-hidden="true"></span>{{ $card['label'] }}</dt>
                    <dd>{{ number_format($approvalCounts[$key] ?? 0) }}<span>{{ $card['help'] }}</span></dd>
                </div>
            @endforeach
        </dl>
    </section>
    <x-data-view :data="$lists" title="Restaurant Applications" />
    <style>
        .approval-pending-items { display: inline-flex; align-items: center; gap: 6px; max-width: 240px; vertical-align: middle; }
        .approval-pending-name { display: inline-flex; align-items: center; gap: 8px; min-width: 0; max-width: 210px; padding: 5px 9px 5px 0; border: 1px solid #dce5eb; border-radius: 5px; background: #fff; color: #475569; font-size: 12px; line-height: 1.2; }
        .approval-pending-accent { align-self: stretch; width: 4px; min-height: 20px; flex: 0 0 4px; border-radius: 0 3px 3px 0; background: #60a5fa; }
        .approval-pending-name-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .approval-pending-count { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 22px; padding: 0 5px; border-radius: 999px; background: #252525; color: #fff; font-size: 11px; font-weight: 700; line-height: 1; }
        .approval-counts-section { margin-bottom: 24px; }
        .approval-counts-help { margin: 0 0 10px; color: #64748b; font-size: 12px; }
        .approval-counts { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; }
        .approval-count-card { --count-color: #64748b; padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 1px 3px rgb(15 23 42 / 4%); }
        .approval-count-card dt { display: flex; align-items: center; gap: 8px; color: #475569; font-size: 12px; font-weight: 600; }
        .approval-count-dot { width: 8px; height: 8px; flex-shrink: 0; border-radius: 50%; background: var(--count-color); }
        .approval-count-card dd { margin: 12px 0 0; font-size: 28px; line-height: 1.2; font-weight: 700; color: var(--count-color); }
        .approval-count-card dd span { display: block; margin-top: 8px; font-size: 11px; line-height: 1.5; font-weight: 400; color: #64748b; }
        .approval-count-approved { --count-color: #047857; }
        .approval-count-pending { --count-color: #b45309; }
        .approval-count-rejected { --count-color: #b91c1c; }
        .approval-count-resubmitted { --count-color: #4f46e5; }
        @media (max-width: 1100px) { .approval-counts { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 640px) {
            .approval-counts { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .approval-count-card { padding: 14px; }
            .approval-count-card dd { font-size: 24px; }
        }
    </style>
@endsection
