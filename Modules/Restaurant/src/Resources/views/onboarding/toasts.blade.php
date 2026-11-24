<style>
    /* Onboarding toasts (dark theme). Shared by every onboarding page. */
    .onboarding-toast-container .toast {
        background: #141418;
        border: 1px solid #232328;
        border-left: 4px solid var(--toast-accent, #FF5500);
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5);
        color: #FFFFFF;
    }
    .onboarding-toast-container .toast-title { color: #FFFFFF; font-size: 15px; font-weight: 600; }
    .onboarding-toast-container .toast-message { color: #A1A1AA; font-size: 13px; line-height: 1.45; white-space: pre-line; }

    /* Errors: clearly red */
    .onboarding-toast-container .toast-error {
        --toast-accent: #EF4444;
        background: linear-gradient(0deg, rgba(239, 68, 68, 0.16), rgba(239, 68, 68, 0.16)), #141418;
        border-color: rgba(239, 68, 68, 0.55);
        border-left-color: #EF4444;
    }
    .onboarding-toast-container .toast-error .toast-left { border-color: rgba(239, 68, 68, 0.35); }
    .onboarding-toast-container .toast-error .toast-inner { background: #EF4444; }
    .onboarding-toast-container .toast-error .toast-title { color: #FCA5A5; }
    .onboarding-toast-container .toast-error .toast-message { color: #FECACA; }

    /* Close (×): icon-only. Overrides the page-wide button styles on onboarding pages. */
    .onboarding-toast-container .toast-close {
        min-width: 28px; padding: 0; margin: 0;
        border: 0; border-radius: 8px;
        background: transparent; box-shadow: none;
        color: #A1A1AA; font-size: 22px; line-height: 28px; font-weight: 400;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .onboarding-toast-container .toast-close:hover,
    .onboarding-toast-container .toast-close:focus-visible {
        background: rgba(255, 255, 255, 0.08);
        border: 0;
        color: #FFFFFF;
    }
    .onboarding-toast-container .toast-error .toast-close { color: #FCA5A5; }
    .onboarding-toast-container .toast-error .toast-close:hover,
    .onboarding-toast-container .toast-error .toast-close:focus-visible { background: rgba(239, 68, 68, 0.2); color: #FFFFFF; }
    .onboarding-toast-container .toast-close:focus-visible { outline: 2px solid rgba(255, 255, 255, 0.5); outline-offset: 1px; }
</style>
<div id="toast-container" class="toast-container onboarding-toast-container" aria-label="Notifications"></div>
@php
    $onboardingToastData = [
    'success' => array_values(array_filter([session('success'), session('banking_success'), session('tax_success'), session('kyc_success')])),
    'errors' => array_values(array_unique(array_filter(array_merge($errors->all(), [session('error')])))),
    ];
@endphp
<script id="onboarding-toast-data" type="application/json" data-overview-url="{{ route('restaurant.onboarding.kyc') }}">@json($onboardingToastData)</script>
<script src="{{ asset('js/toast.js') }}"></script>
