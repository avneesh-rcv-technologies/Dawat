<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurant onboarding — Dawat</title>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body.kyc-web-mode { background: #0A0A0C !important; color: #ffffff !important; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; margin: 0 !important; padding: 0 !important; min-height: 100vh; }
        body { margin: 0; background: #fff; color: #141414; font-family: Arial, sans-serif; line-height: 1.5; }

        /* Store Setup Overview Dark Web Layout */
        .kyc-web-wrapper { min-height: 100vh; width: 100%; background-color: #0A0A0C; color: #FFFFFF; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; box-sizing: border-box; padding: 0 20px 60px; }
        .kyc-web-container { max-width: 1040px; margin: 0 auto; box-sizing: border-box; }
        .kyc-web-header { display: flex; align-items: center; justify-content: space-between; padding: 24px 0; border-bottom: 1px solid #1E1E24; margin-bottom: 36px; }
        .kyc-back-link { display: inline-flex; align-items: center; gap: 8px; color: #A1A1AA; font-size: 14px; font-weight: 500; text-decoration: none; margin-bottom: 24px; transition: color 0.15s ease, transform 0.15s ease; }
        .kyc-back-link:hover { color: #FFFFFF; transform: translateX(-3px); }
        .kyc-header-center { display: flex; align-items: center; gap: 8px; color: #71717A; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .kyc-header-right { display: flex; align-items: center; gap: 16px; }
        .kyc-store-badge { display: inline-flex; align-items: center; gap: 8px; background: #141418; border: 1px solid #272730; border-radius: 9999px; padding: 6px 14px; font-size: 13px; font-weight: 500; color: #E4E4E7; }
        .kyc-store-dot { width: 8px; height: 8px; border-radius: 50%; background-color: #FF6210; }
        .kyc-logout-link { background: transparent; border: none; color: #71717A; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 8px; transition: color 0.15s ease, background-color 0.15s ease; }
        .kyc-logout-link:hover { color: #F43F5E; background: rgba(244, 63, 94, 0.08); }
        .kyc-web-hero { margin-bottom: 32px; }
        .kyc-web-title { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(32px, 4vw, 46px); line-height: 1.15; font-weight: 600; color: #FFFFFF; margin: 0 0 12px 0; letter-spacing: -0.5px; }
        .kyc-web-subtitle { color: #A1A1AA; font-size: 15px; line-height: 1.6; margin: 0; max-width: 720px; }
        .kyc-highlight-name { color: #FFFFFF; font-weight: 600; }
        .kyc-store-overview-card { background-color: #141418; border: 1px solid #232328; border-radius: 20px; padding: 24px; margin-bottom: 28px; }
        .kyc-store-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .kyc-store-card-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .kyc-store-icon-box { width: 44px; height: 44px; border-radius: 12px; background: rgba(249, 115, 22, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kyc-store-card-name { margin: 0; font-size: 18px; font-weight: 600; color: #FFFFFF; }
        .kyc-store-card-loc { margin: 3px 0 0 0; font-size: 13px; color: #8E8E93; }
        .kyc-store-status-pill { font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 9999px; background: #202026; color: #A1A1AA; }
        .kyc-store-progress-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13.5px; }
        .kyc-store-progress-label { color: #D4D4D8; font-weight: 500; }
        .kyc-store-progress-pct { color: #F97316; font-weight: 600; }
        .kyc-store-progress-track { height: 6px; background: #232328; border-radius: 9999px; overflow: hidden; }
        .kyc-store-progress-fill { height: 100%; background: linear-gradient(90deg, #F97316, #FF5500); border-radius: 9999px; transition: width 0.3s ease; }
        .kyc-steps-list-wrapper { margin-bottom: 28px; }
        .kyc-step-row-card { background-color: #141416; border: 1px solid #232328; border-radius: 18px; padding: 20px 24px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.15s ease; }
        .kyc-step-row-card:hover { border-color: #383842; background-color: #17171B; transform: translateY(-1px); }
        .kyc-step-row-left { display: flex; align-items: center; gap: 16px; min-width: 0; }
        .kyc-step-icon { width: 40px; height: 40px; border-radius: 50%; background: #1C1C22; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background-color 0.2s ease; }
        .kyc-step-icon.is-complete { background: rgba(16, 185, 129, 0.12); }
        .kyc-step-texts { min-width: 0; }
        .kyc-step-header-line { display: flex; align-items: center; gap: 8px; }
        .kyc-step-title { margin: 0; font-size: 16px; font-weight: 600; color: #FFFFFF; }
        .kyc-optional-tag { font-size: 11px; font-weight: 600; color: #FF6210; background: #1C1C22; padding: 2px 8px; border-radius: 4px; }
        .kyc-step-sub { margin: 3px 0 6px 0; font-size: 13px; color: #8E8E93; line-height: 1.4; }
        .kyc-step-badge-line { display: flex; align-items: center; gap: 6px; }
        .kyc-step-chip { font-size: 11.5px; font-weight: 500; padding: 3px 10px; border-radius: 9999px; background: #202026; color: #9CA3AF; }
        .kyc-step-chip.is-complete { background: rgba(16, 185, 129, 0.12); color: #34D399; }
        .kyc-step-chip.is-action { background: rgba(249, 115, 22, 0.12); color: #F97316; }
        .kyc-step-arrow { color: #71717A; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px; }
        .kyc-guidance-card { background-color: #141416; border: 1px solid #232328; border-radius: 20px; padding: 20px 22px; display: flex; gap: 16px; align-items: flex-start; }
        .kyc-guidance-icon { flex-shrink: 0; margin-top: 2px; }
        .kyc-support-card-link { text-decoration: none; justify-content: space-between; align-items: center; transition: border-color 0.2s ease, background-color 0.2s ease; }
        .kyc-support-card-link:hover { border-color: #383842; background-color: #17171B; }
        .kyc-support-card-left { display: flex; gap: 16px; align-items: center; }
        .kyc-support-card-title { margin: 0; font-size: 15px; font-weight: 600; color: #FFFFFF; }
        .kyc-support-card-sub { margin: 3px 0 0 0; color: #8E8E93; font-size: 13px; }
        .kyc-web-actions { display: flex; flex-direction: column; align-items: center; padding-top: 24px; border-top: 1px solid #1E1E24; }
        .kyc-web-submit-btn { max-width: 440px; width: 100%; height: 52px; background-color: #FF5500; color: #FFFFFF; border: none; border-radius: 9999px; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease; box-shadow: 0 4px 18px rgba(255, 85, 0, 0.25); font-family: inherit; }
        .kyc-web-submit-btn:hover:not(:disabled) { background-color: #FF6611; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(255, 85, 0, 0.35); }
        .kyc-web-submit-btn.is-submitted-btn { background-color: #FF5500; color: #FFFFFF; cursor: pointer; box-shadow: 0 4px 18px rgba(255, 85, 0, 0.28); }
        .kyc-web-submit-btn.is-submitted-btn:hover { background-color: #FF6611; transform: translateY(-1px); box-shadow: 0 6px 22px rgba(255, 85, 0, 0.38); }
        .kyc-submission-complete-card { background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(16, 185, 129, 0.02)); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px; padding: 24px; margin-bottom: 28px; display: flex; gap: 20px; align-items: flex-start; }
        .kyc-submission-complete-icon { width: 48px; height: 48px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #34D399; }
        .kyc-submission-complete-body { flex: 1; min-width: 0; }
        .kyc-submission-complete-title { margin: 0 0 6px 0; font-size: 17px; font-weight: 700; color: #34D399; }
        .kyc-submission-complete-desc { margin: 0; font-size: 14px; line-height: 1.6; color: #D4D4D8; }
        .kyc-web-subnote { margin: 12px 0 0 0; font-size: 13px; color: #71717A; text-align: center; }
        .kyc-web-alert { padding: 14px 18px; border-radius: 12px; background-color: #141418; border: 1px solid #272730; margin-bottom: 24px; font-size: 14px; color: #E4E4E7; display: flex; align-items: center; justify-content: space-between; }
        .kyc-web-alert[data-type="error"] { background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #FCA5A5; }
        .kyc-step-inner-wrap { background: #141416; border: 1px solid #232328; border-radius: 20px; padding: 32px; margin-bottom: 24px; }
        .kyc-step-inner-wrap .panel-title { color: #FFFFFF; font-size: 24px; margin-top: 0; }
        .kyc-step-inner-wrap .step-message { color: #A1A1AA; font-size: 14px; margin-bottom: 20px; }
        .kyc-step-inner-wrap input, .kyc-step-inner-wrap select { background: #1A1A1F; border: 1px solid #2D2D36; border-radius: 10px; color: #FFFFFF; padding: 12px; }
        .kyc-step-inner-wrap label { color: #E4E4E7; }
        .kyc-step-inner-wrap button[type="submit"] { background: #FF5500; border: none; color: #FFFFFF; font-weight: 700; border-radius: 9999px; padding: 14px 28px; }
        main { max-width: 1120px; margin: 80px auto; padding: 0 32px; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        h1 { font-size: clamp(28px, 4vw, 44px); line-height: 1.2; margin: 0 0 20px; }
        .restaurant { display: flex; align-items: center; gap: 14px; font-size: 26px; margin: 0; }
        .store-icon { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 50%; background: #FF6210; color: white; flex-shrink: 0; }
        button { border: 1.5px solid #222; border-radius: 28px; padding: 10px 26px; background: white; color: #111; font: inherit; font-weight: 700; cursor: pointer; }
        button:hover { background: #f1f5f4; }
        button:focus-visible { outline: 3px solid #FF6210; outline-offset: 4px; }
        .progress { height: 8px; border-radius: 6px; background: #e2e8eb; margin: 24px 0 12px; overflow: hidden; }
        .progress span { display: block; height: 100%; background: #00c477; }
        .summary { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; color: #456471; font-size: 14px; }
        ol { list-style: none; padding: 20px 0; margin: 0; border-bottom: 6px solid #141414; }
        li { display: flex; align-items: flex-start; gap: 20px; padding: 24px 0; }
        .number { flex-shrink: 0; display: grid; place-items: center; width: 60px; height: 60px; border-radius: 50%; background: #e6e8e8; color: #111; font-size: 27px; }
        .step-row[data-tone="approved"] .number { background: #00c477; color: #111; }
        .step-row.current .number { background: #222; color: #fff; }
        .step { flex: 1; min-width: 0; }
        h2 { font-size: 21px; margin: 0 0 4px; }
        .badge { display: inline-block; font-size: 13px; padding: 3px 12px; border-radius: 20px; background: #edf1f2; color: #345564; }
        [data-tone="draft"] { --status-bg: #f1f5f9; --status-ink: #475569; --status-border: #d9e2ec; }
        [data-tone="pending"] { --status-bg: #fffbeb; --status-ink: #92400e; --status-border: #f5d78e; }
        [data-tone="review"] { --status-bg: #eff6ff; --status-ink: #1e40af; --status-border: #bfdbfe; }
        [data-tone="attention"] { --status-bg: #fff7ed; --status-ink: #9a3412; --status-border: #fdba74; }
        [data-tone="rejected"] { --status-bg: #fef2f2; --status-ink: #b91c1c; --status-border: #fecaca; }
        [data-tone="approved"] { --status-bg: #ecfdf5; --status-ink: #166534; --status-border: #a7e5c5; }
        [data-tone="suspended"] { --status-bg: #faf5ff; --status-ink: #6b21a8; --status-border: #e9d5ff; }
        .status-badge { background: var(--status-bg); color: var(--status-ink); border: 1px solid var(--status-border); font-weight: 600; }
        .status-notice { display: flex; gap: 14px; align-items: flex-start; background: var(--status-bg); color: var(--status-ink); border: 1px solid var(--status-border); border-radius: 12px; padding: 18px 20px; margin-top: 20px; }
        .status-notice-icon { flex-shrink: 0; display: grid; place-items: center; width: 26px; height: 26px; border-radius: 50%; border: 1px solid currentColor; font-weight: 700; }
        .status-notice h2 { font-size: 16px; margin: 0 0 4px; }
        .status-notice p { margin: 0; font-size: 14px; line-height: 1.6; }
        .status-notice-links { display: flex; flex-wrap: wrap; gap: 8px 18px; margin-top: 10px; }
        .status-notice-links a { color: inherit; font-size: 14px; font-weight: 600; line-height: 1.6; text-decoration: underline; text-underline-offset: 3px; }
        .status-notice .review-note { margin-top: 8px; white-space: pre-line; }
        .step-row { border-bottom: 1px solid #edf1f3; }
        .step-row:last-child { border-bottom: 0; }
        .step-message { font-size: 14px; color: #61727a; margin-top: 8px; }
        .step-row .reason { background: var(--status-bg); color: var(--status-ink); border-left: 3px solid var(--status-border); padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 14px; white-space: pre-line; }
        .step-row .reason strong { display: block; font-size: 12px; margin-bottom: 3px; }
        .step-row .step-arrow { border-color: var(--status-border); color: var(--status-ink); background: var(--status-bg); font-size: 15px; }
        .step-row .step-arrow:hover, .step-row .step-arrow:focus-visible { background: var(--status-ink); color: #fff; border-color: var(--status-ink); }
        p { margin: 8px 0 0; }
        .optional { font-size: 13px; font-weight: 400; color: #526873; margin-left: 8px; }
        .reason { color: #973b21; overflow-wrap: anywhere; }
        .step-panel .onboarding-form-message, .step-panel-messages .onboarding-form-message,
        .step-panel :is(.bank-message, .tax-message, .kyc-message) {
            width: 100%; margin: 16px 0; padding: 14px 16px; border: 1px solid #dbe5e8;
            border-radius: 10px; background: #f3f7f8; color: #345564; font-size: 14px; line-height: 1.6;
            overflow-wrap: anywhere; text-align: left;
        }
        .step-panel .onboarding-form-message-error, .step-panel-messages .onboarding-form-message-error,
        .step-panel :is(.bank-message, .tax-message, .kyc-message)[data-error="true"] {
            background: #fef2f2; border-color: #fecaca; color: #a52e25;
        }
        .step-panel .onboarding-form-message-success, .step-panel-messages .onboarding-form-message-success,
        .step-panel :is(.bank-message, .tax-message, .kyc-message)[data-success="true"] {
            background: #ecfdf5; border-color: #a7e5c5; color: #166534;
        }
        .onboarding-form-message p { margin: 0; }
        .onboarding-form-message p + p { margin-top: 6px; }
        .onboarding-form-message-title { font-weight: 700; }
        .onboarding-form-error-list { margin: 6px 0 0; padding: 0 0 0 18px; list-style: disc; }
        .onboarding-form-error-list li { display: list-item; padding: 0; margin: 4px 0 0; font-size: 14px; line-height: 1.6; }
        .step-panel :is(.bank-error, .tax-error, .kyc-error) { margin: 6px 0 0; color: #a52e25; font-size: 13px; line-height: 1.5; text-align: left; }
        .step-panel :is(input, select, textarea)[aria-invalid="true"] { border-color: #dc2626 !important; }
        .step-feedback { margin: 16px 0; padding: 14px 16px; border: 1px solid var(--status-border); border-radius: 10px; background: var(--status-bg); color: var(--status-ink); font-size: 14px; overflow-wrap: anywhere; }
        .step-feedback strong { display: block; margin-bottom: 4px; font-size: 12px; }
        .step-feedback p { margin: 0; white-space: pre-line; }
        main [hidden], main .hidden { display: none !important; }
        .notice { padding: 24px 0; color: #456471; }
        .onboarding-help { margin-top: 12px; padding: 28px 40px 36px; border: 2px solid #b8b8b8; border-radius: 32px; background: #fff; }
        .onboarding-help h2 { margin: 0 0 12px; text-align: right; color: #111; font-size: 26px; font-weight: 700; }
        .onboarding-help p { margin: 0; color: #456471; font-size: 16px; line-height: 1.6; }
        .onboarding-help .support-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; margin-top: 28px; padding: 8px 22px; border: 1.5px solid #222; border-radius: 28px; color: #111; background: #fff; font-size: 14px; font-weight: 700; text-decoration: none; }
        .onboarding-help .support-link:hover { background: #141414; border-color: #141414; color: #fff; }
        a { color: inherit; }
        a:focus-visible { outline: 3px solid #FF6210; outline-offset: 4px; }
        .step-arrow { flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 12px; min-height: 46px; margin-top: 6px; padding: 8px 22px; border: 1px solid #d4dfe3; border-radius: 28px; text-decoration: none; font-size: 18px; font-weight: 600; transition: background-color .2s, border-color .2s, color .2s; }
        .step-arrow span { display: inline-block; transition: transform .2s; }
        .step-arrow:hover, .step-arrow:focus-visible { background: #141414; border-color: #141414; color: #fff; }
        .step-arrow:hover span, .step-arrow:focus-visible span { transform: translateX(3px); }
        @media (prefers-reduced-motion: reduce) { .step-arrow, .step-arrow span { transition: none; } .step-arrow:hover span, .step-arrow:focus-visible span { transform: none; } }
        .step-panel { padding: 28px 0; border-bottom: 6px solid #141414; }
        .back-link { display: inline-block; margin-bottom: 24px; font-weight: 700; text-decoration: none; }
        .panel-title { font-size: 28px; }
        .read-only { color: #456471; font-size: 14px; margin: 12px 0 24px; }
        .details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin: 24px 0; }
        .detail-field { background: #f5f8f8; border: 1px solid #e3eaea; padding: 16px; border-radius: 12px; }
        .registration-correction-actions { display: flex; justify-content: flex-end; margin-top: 24px; }
        .registration-correction-actions button { max-width: 100%; }
        dt { font-size: 13px; color: #456471; font-weight: 700; }
        dd { margin: 8px 0 0; overflow-wrap: anywhere; white-space: pre-line; }
        .step-navigation { display: flex; justify-content: space-between; gap: 16px; margin-top: 28px; }
        .step-navigation a, .step-navigation button { display: inline-block; border: 1.5px solid #141414; border-radius: 28px; padding: 10px 24px; font-size: 15px; font-weight: 700; text-decoration: none; }
        .step-navigation a:hover { background: #edf7f3; }
        .step-navigation button:disabled { color: #8b979d; border-color: #d4dfe3; background: #f5f8f8; cursor: default; }
        @media (max-width: 600px) {
            main { margin: 32px auto; padding: 0 20px; }
            header { flex-direction: column-reverse; gap: 24px; }
            header form { align-self: flex-end; }
            .restaurant { font-size: 20px; }
            li { gap: 14px; }
            .number { width: 44px; height: 44px; font-size: 22px; }
            h2 { font-size: 18px; }
            .details-grid { grid-template-columns: 1fr; }
            .step-arrow { min-height: 40px; padding: 7px 14px; gap: 8px; font-size: 15px; }
            .step-row { flex-wrap: wrap; }
            .step-row .step { flex-basis: calc(100% - 58px); }
            .step-row .step-arrow { margin-left: 58px; margin-top: 0; }
            .onboarding-help { padding: 24px; border-radius: 24px; }
            .onboarding-help h2 { font-size: 23px; }
            .onboarding-help p { font-size: 15px; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/restaurant-onboarding-responsive.css') }}">
</head>
<body class="onboarding-page kyc-web-mode">
    @include('restaurant::onboarding.toasts')
    @php
        $steps = collect($timeline['steps']);
        $statusPresentation = \Modules\Restaurant\Services\OnboardingStatusPresentation::class;
        $notice = $statusPresentation::notice($timeline);
        $refillLinks = $steps->where('status', 'action_required')
            ->reject(fn ($step) => $step['key'] === $selectedStep?->value);
        $overallStyle = $statusPresentation::status($timeline['restaurant']['onboarding_status'] ?? 'draft');
        $attentionStep = $steps->where('can_skip', false)->first(fn ($step) => in_array($step['status'], ['rejected', 'action_required'], true));
        $bankingApproved = (bool) ($steps->firstWhere('key', 'banking')['is_approved'] ?? false);
        $taxApproved = (bool) ($steps->firstWhere('key', 'tax')['is_approved'] ?? false);
        $storedStep = $steps->firstWhere('number', (int) $timeline['restaurant']['onboarding_step']);
        $currentKey = $timeline['progress']['is_complete'] ? null : (
            $attentionStep['key'] ?? ($storedStep && !$storedStep['is_approved'] ? $storedStep['key'] : ($timeline['next_step']['key'] ?? null))
        );
        $selectedRecord = $selectedStep ? $steps->firstWhere('key', $selectedStep->value) : null;
    @endphp
    @include('frontend.partials.onboarding-confirmation', [
        'confirmationTheme' => 'banking',
    ])
    @if(!empty($isVerified))
        @include('restaurant::onboarding.verified')
    @elseif(!empty($isSubmitted))
        @include('restaurant::onboarding.submitted')
    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::KYC)
        @include('restaurant::onboarding.kyc-form')
    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)
        @include('restaurant::onboarding.tax-form')
    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::REGISTRATION)
        @include('restaurant::onboarding.registration-form')
    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING)
        @include('restaurant::onboarding.banking-form')
    @elseif($selectedStep === null)
        {{-- =================================================== --}}
        {{-- STORE SETUP OVERVIEW: Figma Reference Converted to Web --}}
        {{-- =================================================== --}}
        <div class="kyc-web-wrapper">
            <div class="kyc-web-container">
                <!-- Top Web Navigation Header -->
                <header class="kyc-web-header">
                    <div class="kyc-header-center">
                        <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                            <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                        </a>
                    </div>

                    <div class="kyc-header-right">
                        <!-- <div class="kyc-store-badge">
                            <span class="kyc-store-dot" aria-hidden="true"></span>
                            <span>{{ $timeline['restaurant']['name'] ?? 'Restaurant Partner' }}</span>
                        </div> -->
                        <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="kyc-logout-link" title="Logout">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </header>

                <!-- Hero Section -->
                <section class="kyc-web-hero">
                    <h1 class="kyc-web-title">Your kitchen.<br>A new chapter.</h1>
                    <p class="kyc-web-subtitle">
                        Let's set up your store and bring <span class="kyc-highlight-name">{{ $timeline['restaurant']['name'] }}</span> to Dawat.
                    </p>
                </section>

                <!-- Store Overview Card with Progress (Figma 2) -->
                <section class="kyc-store-overview-card">
                    @php
                        $requiredStepsCollection = $steps->where('can_skip', false);
                        $completedCount = $requiredStepsCollection->filter(fn($s) => in_array($s['status'], ['approved', 'submitted', 'under_review'], true))->count();
                        $totalRequiredCount = $requiredStepsCollection->count();
                        $userProgressPct = $totalRequiredCount > 0 ? (int) round(($completedCount / $totalRequiredCount) * 100) : 100;
                        $allRequiredDone = $completedCount >= $totalRequiredCount;
                        $isOverallApproved = ($timeline['restaurant']['onboarding_status'] ?? '') === 'approved';
                        $storeStatusLabel = $isOverallApproved ? 'Approved' : ($allRequiredDone ? 'Under review' : ($timeline['restaurant']['onboarding_status_label'] ?? 'Draft'));
                    @endphp
                    <div class="kyc-store-card-top">
                        <div class="kyc-store-card-left">
                            <div class="kyc-store-icon-box" aria-hidden="true">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </div>
                            <div>
                                <h2 class="kyc-store-card-name">{{ $timeline['restaurant']['name'] }}</h2>
                                <p class="kyc-store-card-loc">{{ $timeline['restaurant']['address'] ?? 'Store setup' }}</p>
                            </div>
                        </div>
                        <span class="kyc-store-status-pill" style="{{ $allRequiredDone ? 'background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3);' : '' }}">
                            {{ $storeStatusLabel }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="kyc-store-progress-row">
                        <span class="kyc-store-progress-label">{{ $completedCount }} of {{ $totalRequiredCount }} sections complete</span>
                        <span class="kyc-store-progress-pct">{{ $userProgressPct }}%</span>
                    </div>
                    <div class="kyc-store-progress-track">
                        <div class="kyc-store-progress-fill" style="width: {{ $userProgressPct }}%;"></div>
                    </div>
                </section>

                <!-- Review Status Banner when all sections are completed -->
                @if($allRequiredDone && !$isOverallApproved)
                <div class="kyc-submission-complete-card">
                    <div class="kyc-submission-complete-icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="kyc-submission-complete-body">
                        <h3 class="kyc-submission-complete-title">All sections submitted · Application under review</h3>
                        <p class="kyc-submission-complete-desc">
                            All required onboarding sections and business verification documents have been received. Our compliance team is actively reviewing your store details. You will be notified by email as soon as your store is approved.
                        </p>
                        <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'submitted']) }}" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; color: #34D399; font-weight: 600; font-size: 13.5px; text-decoration: none;">
                            <span>View review &amp; launch timeline</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Alert / Review feedback if applicable -->
                @if($timeline['restaurant']['rejection_reason'] || $notice['tone'] === 'attention')
                <div class="kyc-web-alert" data-type="error">
                    <span><strong>Notice:</strong> {{ $timeline['restaurant']['rejection_reason'] ?: $notice['message'] }}</span>
                </div>
                @endif

                <!-- Steps List (Real routes only, skipping routes not in our system) -->
                <div class="kyc-steps-list-wrapper">
                    @foreach($steps as $step)
                        @php
                            $stepStatus = $step['status'];
                            $stepKey = $step['key'];
                            $stepRoute = route('restaurant.onboarding.kyc', ['step' => $stepKey]);
                            $isStepDone = in_array($stepStatus, ['approved', 'submitted', 'under_review'], true);

                            $displayTitle = match($stepKey) {
                                'registration' => 'Restaurant profile',
                                'banking' => 'Banking information',
                                'tax' => 'Tax information',
                                'dietary_certificates' => 'Dietary certificates',
                                'kyc' => 'Business verification documents',
                                default => $step['label'],
                            };

                            $subtitle = match($stepKey) {
                                'registration' => $timeline['restaurant']['address'] ?? 'Restaurant details and address',
                                'banking' => 'Set up your payout account',
                                'tax' => 'Complete your Substitute W-9',
                                'dietary_certificates' => 'Upload optional food safety certificates',
                                'kyc' => 'Upload required verification documents',
                                default => $step['label'],
                            };
                        @endphp

                        <a href="{{ $stepRoute }}" class="kyc-step-row-card">
                            <div class="kyc-step-row-left">
                                <div class="kyc-step-icon {{ $isStepDone ? 'is-complete' : '' }}">
                                    @if($isStepDone)
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    @else
                                        {!! match($stepKey) {
                                            'registration' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M10 22v-4h4v4"/></svg>',
                                            'banking' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7H3zM5 10v9M9 10v9M15 10v9M19 10v9M3 21h18"/></svg>',
                                            'tax' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
                                            'dietary_certificates' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.4 12.8 17 22l-5-3-5 3 1.6-9.2"/></svg>',
                                            'kyc' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="14" x="3" y="5" rx="2"/><circle cx="9" cy="12" r="2"/><path d="m15 12 3 3-3 3"/></svg>',
                                            default => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>'
                                        } !!}
                                    @endif
                                </div>
                                <div class="kyc-step-texts">
                                    <div class="kyc-step-header-line">
                                        <h3 class="kyc-step-title">{{ $displayTitle }}</h3>
                                        @if($step['can_skip'] || $stepKey === 'dietary_certificates')
                                            <span class="kyc-optional-tag">Optional</span>
                                        @endif
                                    </div>
                                    <p class="kyc-step-sub">{{ $subtitle }}</p>
                                    <div class="kyc-step-badge-line">
                                        <span class="kyc-step-chip {{ $isStepDone ? 'is-complete' : (in_array($stepStatus, ['action_required', 'rejected']) ? 'is-action' : '') }}">
                                            {{ $isStepDone ? ($stepStatus === 'approved' || $stepStatus === 'submitted' ? 'Complete' : 'In review') : ($stepStatus === 'draft' || $stepStatus === 'not_started' ? 'Not started' : $step['status_label']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="kyc-step-arrow" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>
                        {{-- Optional menu details section, shown second (not part of the review steps). --}}
                        @if($loop->first)
                            @include('restaurant::onboarding.menu.step-card', ['restaurant' => $correctionRestaurant])
                        @endif
                    @endforeach
                </div>

                <!-- Guidance & Need a hand? Support Card -->
                <a href="mailto:support@dawat.com?subject={{ rawurlencode('Store Setup Assistance') }}" class="kyc-guidance-card kyc-support-card-link" style="margin-top: 28px; margin-bottom: 32px;">
                    <div class="kyc-support-card-left">
                        <div class="kyc-guidance-icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="kyc-support-card-title">Need a help?</h4>
                            <p class="kyc-support-card-sub">Chat with our partner support team.</p>
                        </div>
                    </div>
                    <div class="kyc-support-card-arrow" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"></line>
                            <polyline points="7 7 17 7 17 17"></polyline>
                        </svg>
                    </div>
                </a>

                <!-- Bottom CTA Action -->
                @php
                    $nextStepTarget = $steps->where('can_skip', false)->first(fn($s) => in_array($s['status'], ['action_required', 'rejected'], true))
                        ?? $steps->where('can_skip', false)->first(fn($s) => !in_array($s['status'], ['approved', 'submitted', 'under_review'], true));
                    if (!$nextStepTarget) {
                        $nextStepTarget = $steps->first(fn($s) => !in_array($s['status'], ['approved', 'submitted', 'under_review', 'skipped'], true));
                    }
                    $nextStepRoute = $nextStepTarget ? route('restaurant.onboarding.kyc', ['step' => $nextStepTarget['key']]) : '#';
                    $nextStepName = $nextStepTarget ? match($nextStepTarget['key']) {
                        'registration' => 'restaurant profile',
                        'banking' => 'banking information',
                        'tax' => 'tax information',
                        'dietary_certificates' => 'dietary certificates',
                        'kyc' => 'kyc documents',
                        default => strtolower($nextStepTarget['label']),
                    } : 'onboarding';
                    $allRequiredDone = $completedCount >= $totalRequiredCount;
                @endphp
                <div class="kyc-web-actions">
                    @if($isOverallApproved)
                        <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'approved']) }}" class="kyc-web-submit-btn is-submitted-btn" style="text-decoration: none;">
                            <span>Go live on Dawat →</span>
                        </a>
                        <p class="kyc-web-subnote" style="margin-top: 14px; color: #34D399; font-weight: 500;">
                            🎉 Your restaurant is fully approved! Click to review launch settings and go live.
                        </p>
                    @elseif($allRequiredDone)
                        <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'submitted']) }}" class="kyc-web-submit-btn is-submitted-btn" id="btn-all-submitted" style="text-decoration: none;">
                            <span>All sections submitted · In review</span>
                        </a>
                        <p class="kyc-web-subnote" style="margin-top: 14px; color: #34D399; font-weight: 500;">
                            ✓ All required sections are submitted. Click to view review status.
                        </p>
                    @elseif($nextStepTarget)
                        <a href="{{ $nextStepRoute }}" class="kyc-web-submit-btn" style="text-decoration: none;">
                            <span>Continue {{ $nextStepName }}</span>
                        </a>
                        <p class="kyc-web-subnote" style="margin-top: 14px;">
                            Finish setup, then submit for review.
                        </p>
                    @endif
                </div>
                <script>
                    document.getElementById('btn-all-submitted')?.addEventListener('click', () => {
                        if (window.onboardingToast) {
                            window.onboardingToast('All onboarding sections and documents are submitted. Our team is actively reviewing your application!', 'success');
                        }
                    });
                </script>
            </div>
        </div>
    @else
        {{-- =================================================== --}}
        {{-- OTHER STEPS (Banking, Dietary, Registration) in Web Shell --}}
        {{-- =================================================== --}}
        <div class="kyc-web-wrapper">
            <div class="kyc-web-container">
                <header class="kyc-web-header">
                    <div class="kyc-header-center">
                        <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                            <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                        </a>
                    </div>

                    <div class="kyc-header-right">
                        <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="kyc-logout-link" title="Logout">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </header>

                @php
                    $selectedRecord = $steps->firstWhere('key', $selectedStep->value);
                    $selectedStyle = $statusPresentation::status($selectedRecord['status'] ?? 'draft');
                    $orderedSteps = \Modules\Restaurant\Enums\OnboardingStep::ordered();
                    $selectedIndex = array_search($selectedStep, $orderedSteps, true);
                    $previousStep = $orderedSteps[$selectedIndex - 1] ?? null;
                    $nextStep = $orderedSteps[$selectedIndex + 1] ?? null;
                    $stepEditable = \Modules\Restaurant\Services\RestaurantOnboardingAccessService::isStepEditable($selectedRecord['status'] ?? 'draft');
                @endphp

                <div class="kyc-web-hero">
                    <a href="{{ route('restaurant.onboarding.kyc') }}" class="kyc-back-link" aria-label="Back to onboarding steps">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Back to onboarding steps</span>
                    </a>

                    <h1 class="kyc-web-title">{{ $selectedStep->label() }}</h1>
                    <p class="kyc-web-subtitle">{{ $selectedStyle['message'] }}</p>
                </div>

                @if(!$stepEditable && $selectedStep !== \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES)
                    <div class="kyc-web-alert">
                        <span>{{ ($selectedRecord['status'] ?? '') === 'under_review' ? 'Under review · Updates are disabled until the review is complete.' : 'Approved information · Read only' }}</span>
                    </div>
                @endif

                @if($stepDetails && $stepDetails['rejection_reason'])
                    <div class="kyc-web-alert" data-type="error">
                        <span><strong>Review feedback:</strong> {{ $stepDetails['rejection_reason'] }}</span>
                    </div>
                @endif

                <div class="kyc-step-inner-wrap">
                    @if($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::REGISTRATION && $stepEditable)
                        @include('restaurant::onboarding.registration-correction')
                    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING && $stepEditable)
                        @include('restaurant::onboarding.banking-form')
                    @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::DIETARY_CERTIFICATES)
                        @include('restaurant::onboarding.dietary-certificates')
                    @else
                        @if(isset($stepDetails['fields']) && count($stepDetails['fields']))
                            <div class="tax-inputs-grid">
                                @foreach($stepDetails['fields'] as $field)
                                    <div class="tax-field-wrap">
                                        <label>{{ $field['label'] }}</label>
                                        <input class="tax-control-input" value="{{ $field['value'] }}" readonly disabled>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p style="color: #8E8E93; margin: 0;">No information has been submitted for this step yet.</p>
                        @endif
                    @endif
                </div>

                <!-- Navigation between steps -->

            </div>
        </div>
    @endif
</body>
</html>
