@props(['status'])

@php
    $labels = [
        'pending' => 'Pending',
        'under_review' => 'Under review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'refill_requested' => 'Refill requested',
    ];
    $classes = [
        'pending' => 'kyc-badge kyc-badge-pending',
        'under_review' => 'kyc-badge kyc-badge-review',
        'approved' => 'kyc-badge kyc-badge-approved',
        'rejected' => 'kyc-badge kyc-badge-rejected',
        'refill_requested' => 'kyc-badge kyc-badge-pending',
    ];
@endphp

<span class="{{ $classes[$status] ?? 'kyc-badge' }}">
    {{ $labels[$status] ?? ucfirst(str_replace('_', ' ', (string) $status)) }}
</span>
