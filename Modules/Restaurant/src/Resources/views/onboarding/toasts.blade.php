<div id="toast-container" class="toast-container onboarding-toast-container" aria-label="Notifications"></div>
@php
    $onboardingToastData = [
    'success' => array_values(array_filter([session('success'), session('banking_success'), session('tax_success'), session('kyc_success')])),
    'errors' => array_values(array_unique(array_filter(array_merge($errors->all(), [session('error')])))),
    ];
@endphp
<script id="onboarding-toast-data" type="application/json">@json($onboardingToastData)</script>
<script src="{{ asset('js/toast.js') }}"></script>
<script src="{{ asset('js/onboarding-toasts.js') }}"></script>
