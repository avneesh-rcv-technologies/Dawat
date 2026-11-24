@php
    $color = match ($status) {
        'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800',
        'changes_requested' => 'bg-orange-100 text-orange-800',
        'submitted', 'under_review' => 'bg-blue-100 text-blue-800', default => 'bg-gray-100 text-gray-700',
    };
@endphp
<span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $color }}">{{ \Modules\DietaryManagment\Services\OnboardingCertificateService::STATUS_LABELS[$status] ?? $status }}</span>
