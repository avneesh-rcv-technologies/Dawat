<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurant onboarding — Dawat</title>
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #103647; font-family: Arial, sans-serif; line-height: 1.5; }
        main { max-width: 1120px; margin: 80px auto; padding: 0 32px; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        h1 { font-size: clamp(28px, 4vw, 44px); line-height: 1.2; margin: 0 0 20px; }
        .restaurant { display: flex; align-items: center; gap: 14px; font-size: 26px; margin: 0; }
        .store-icon { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 50%; background: #31be91; color: white; flex-shrink: 0; }
        button { border: 1.5px solid #222; border-radius: 28px; padding: 10px 26px; background: white; color: #111; font: inherit; font-weight: 700; cursor: pointer; }
        button:hover { background: #f1f5f4; }
        button:focus-visible { outline: 3px solid #31be91; outline-offset: 4px; }
        .progress { height: 8px; border-radius: 6px; background: #e2e8eb; margin: 24px 0 12px; overflow: hidden; }
        .progress span { display: block; height: 100%; background: #00c477; }
        .summary { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; color: #456471; font-size: 14px; }
        ol { list-style: none; padding: 20px 0; margin: 0; border-bottom: 6px solid #103647; }
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
        .onboarding-toast-container .toast-message { white-space: pre-line; }
        .onboarding-toast-container .toast-close { min-width: 28px; padding: 0; border-radius: 5px; font-size: 24px; line-height: 28px; color: #64748b; }
        .notice { padding: 24px 0; color: #456471; }
        .onboarding-help { margin-top: 12px; padding: 28px 40px 36px; border: 2px solid #b8b8b8; border-radius: 32px; background: #fff; }
        .onboarding-help h2 { margin: 0 0 12px; text-align: right; color: #111; font-size: 26px; font-weight: 700; }
        .onboarding-help p { margin: 0; color: #456471; font-size: 16px; line-height: 1.6; }
        .onboarding-help .support-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; margin-top: 28px; padding: 8px 22px; border: 1.5px solid #222; border-radius: 28px; color: #111; background: #fff; font-size: 14px; font-weight: 700; text-decoration: none; }
        .onboarding-help .support-link:hover { background: #103647; border-color: #103647; color: #fff; }
        a { color: inherit; }
        a:focus-visible { outline: 3px solid #31be91; outline-offset: 4px; }
        .step-arrow { flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 12px; min-height: 46px; margin-top: 6px; padding: 8px 22px; border: 1px solid #d4dfe3; border-radius: 28px; text-decoration: none; font-size: 18px; font-weight: 600; transition: background-color .2s, border-color .2s, color .2s; }
        .step-arrow span { display: inline-block; transition: transform .2s; }
        .step-arrow:hover, .step-arrow:focus-visible { background: #103647; border-color: #103647; color: #fff; }
        .step-arrow:hover span, .step-arrow:focus-visible span { transform: translateX(3px); }
        @media (prefers-reduced-motion: reduce) { .step-arrow, .step-arrow span { transition: none; } .step-arrow:hover span, .step-arrow:focus-visible span { transform: none; } }
        .step-panel { padding: 28px 0; border-bottom: 6px solid #103647; }
        .back-link { display: inline-block; margin-bottom: 24px; font-weight: 700; text-decoration: none; }
        .panel-title { font-size: 28px; }
        .read-only { color: #456471; font-size: 14px; margin: 12px 0 24px; }
        .details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin: 24px 0; }
        .detail-field { background: #f5f8f8; border: 1px solid #e3eaea; padding: 16px; border-radius: 12px; }
        dt { font-size: 13px; color: #456471; font-weight: 700; }
        dd { margin: 8px 0 0; overflow-wrap: anywhere; white-space: pre-line; }
        .step-navigation { display: flex; justify-content: space-between; gap: 16px; margin-top: 28px; }
        .step-navigation a, .step-navigation button { display: inline-block; border: 1.5px solid #103647; border-radius: 28px; padding: 10px 24px; font-size: 15px; font-weight: 700; text-decoration: none; }
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
</head>
<body>
    @include('restaurant::onboarding.toasts')
    @php
        $steps = collect($timeline['steps']);
        $statusPresentation = \Modules\Restaurant\Services\OnboardingStatusPresentation::class;
        $notice = $statusPresentation::notice($timeline);
        $overallStyle = $statusPresentation::status($timeline['restaurant']['onboarding_status'] ?? 'draft');
        $attentionStep = $steps->first(fn ($step) => in_array($step['status'], ['rejected', 'action_required'], true));
        $bankingApproved = (bool) ($steps->firstWhere('key', 'banking')['is_approved'] ?? false);
        $taxApproved = (bool) ($steps->firstWhere('key', 'tax')['is_approved'] ?? false);
        $storedStep = $steps->firstWhere('number', (int) $timeline['restaurant']['onboarding_step']);
        $currentKey = $timeline['progress']['is_complete'] ? null : (
            $attentionStep['key'] ?? ($storedStep && !$storedStep['is_approved'] ? $storedStep['key'] : ($timeline['next_step']['key'] ?? null))
        );
    @endphp
    @include('frontend.partials.onboarding-confirmation')
    <main>
        @if($selectedStep)
            <nav aria-label="Back to onboarding">
                <a class="back-link" href="{{ route('restaurant.onboarding.kyc') }}">← Back to onboarding steps</a>
            </nav>
        @endif
        <header>
            <div>
                <h1>Let's set up your store</h1>
                <p class="restaurant">
                    <span class="store-icon" aria-hidden="true">{!! \Modules\Restaurant\Enums\OnboardingStep::REGISTRATION->icon() !!}</span>
                    {{ $timeline['restaurant']['name'] }}
                </p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </header>
        <div class="progress" role="progressbar" aria-label="Required onboarding steps approved"
            aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $timeline['progress']['percentage'] }}">
            <span style="width: {{ $timeline['progress']['percentage'] }}%"></span>
        </div>
        <div class="summary">
            <span>{{ $timeline['progress']['approved_required_steps'] }} of {{ $timeline['progress']['required_steps'] }} required steps approved</span>
            <span class="badge status-badge" data-tone="{{ $overallStyle['tone'] }}">{{ $timeline['restaurant']['onboarding_status_label'] }}</span>
        </div>
        <section class="status-notice" data-tone="{{ $notice['tone'] }}" aria-labelledby="onboarding-status-title" role="status">
            <span class="status-notice-icon" aria-hidden="true">{{ $notice['tone'] === 'approved' ? '✓' : (in_array($notice['tone'], ['attention', 'rejected'], true) ? '!' : 'i') }}</span>
            <div>
                <h2 id="onboarding-status-title">{{ $notice['title'] }}</h2>
                <p>{{ $notice['message'] }}</p>
                @if($timeline['restaurant']['rejection_reason'])
                    <p class="review-note"><strong>Review feedback:</strong> {{ $timeline['restaurant']['rejection_reason'] }}</p>
                @endif
            </div>
        </section>
        @if($selectedStep)
            @php
                $selectedRecord = $steps->firstWhere('key', $selectedStep->value);
                $selectedStyle = $statusPresentation::status($selectedRecord['status'] ?? 'draft');
                $orderedSteps = \Modules\Restaurant\Enums\OnboardingStep::ordered();
                $selectedIndex = array_search($selectedStep, $orderedSteps, true);
                $previousStep = $orderedSteps[$selectedIndex - 1] ?? null;
                $nextStep = $orderedSteps[$selectedIndex + 1] ?? null;
            @endphp
            <section class="step-panel" aria-labelledby="step-title">
                <h2 class="panel-title" id="step-title">{{ $selectedStep->label() }}</h2>
                <span class="badge status-badge" data-tone="{{ $selectedStyle['tone'] }}">{{ $stepDetails['status'] }}</span>
                <p class="step-message">{{ $selectedStyle['message'] }}</p>
                @if((!in_array($selectedRecord['status'] ?? '', ['action_required', 'rejected'], true) && !in_array($selectedStep, [\Modules\Restaurant\Enums\OnboardingStep::BANKING, \Modules\Restaurant\Enums\OnboardingStep::KYC, \Modules\Restaurant\Enums\OnboardingStep::TAX], true)) || ($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING && $bankingApproved))
                    <p class="read-only">Submitted information · Read only</p>
                @endif
                @if($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING && $bankingApproved)
                    <p class="read-only">Your banking information is approved and cannot be updated.</p>
                @endif
                @if($stepDetails['rejection_reason'])
                    <div class="step-feedback" data-tone="{{ $selectedStyle['tone'] }}">
                        <strong>Review feedback</strong>
                        <p>{{ $stepDetails['rejection_reason'] }}</p>
                    </div>
                @endif
                @include('restaurant::onboarding.form-messages')
                @if($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::REGISTRATION && in_array($selectedRecord['status'] ?? '', ['action_required', 'rejected'], true))
                    @include('restaurant::onboarding.registration-correction')
                @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::MENU && in_array($selectedRecord['status'] ?? '', ['action_required', 'rejected'], true))
                    @include('restaurant::onboarding.menu-correction')
                @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::BANKING && !$bankingApproved)
                    @include('restaurant::onboarding.banking-form')
                @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::KYC)
                    @include('restaurant::onboarding.kyc-form')
                @elseif($selectedStep === \Modules\Restaurant\Enums\OnboardingStep::TAX)
                    @if($taxApproved)<p class="read-only">Your tax information is approved. Changes will be submitted for review again.</p>@endif
                    @include('restaurant::onboarding.tax-form')
                @else
                @if(count($stepDetails['fields']))
                    <dl class="details-grid">
                        @foreach($stepDetails['fields'] as $field)
                            <div class="detail-field">
                                <dt>{{ $field['label'] }}</dt>
                                <dd>{{ $field['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="read-only">No information has been submitted for this step yet.</p>
                @endif
                <dl class="details-grid">
                    @foreach(['submitted_at' => 'Submitted at', 'reviewed_at' => 'Reviewed at'] as $key => $label)
                        @if($stepDetails[$key])
                            <div class="detail-field"><dt>{{ $label }}</dt><dd>{{ $stepDetails[$key] }}</dd></div>
                        @endif
                    @endforeach
                </dl>
                @endif
                <nav class="step-navigation" aria-label="Onboarding step navigation">
                    @if($previousStep)
                        <a href="{{ route('restaurant.onboarding.kyc', ['step' => $previousStep->value]) }}" rel="prev"
                            aria-label="Previous: {{ $previousStep->label() }}">← Previous</a>
                    @else
                        <button type="button" disabled>← Previous</button>
                    @endif
                    @if($nextStep)
                        <a href="{{ route('restaurant.onboarding.kyc', ['step' => $nextStep->value]) }}" rel="next"
                            aria-label="Next: {{ $nextStep->label() }}">Next →</a>
                    @else
                        <button type="button" disabled>Next →</button>
                    @endif
                </nav>
            </section>
        @else
        <div class="step-panel-messages">
            @include('restaurant::onboarding.form-messages')
        </div>
        <ol aria-label="Restaurant onboarding steps">
            @foreach($steps as $step)
                @php $stepStyle = $statusPresentation::status($step['status']); @endphp
                <li class="step-row {{ $currentKey === $step['key'] ? 'current' : '' }}" data-tone="{{ $stepStyle['tone'] }}"
                    @if($currentKey === $step['key']) aria-current="step" @endif>
                    <span class="number" aria-hidden="true">{{ $step['is_approved'] ? '✓' : $step['number'] }}</span>
                    <div class="step">
                        <h2>{{ $step['label'] }}@if($step['can_skip'])<span class="optional">Optional</span>@endif</h2>
                        <span class="badge status-badge">{{ $step['is_approved'] ? 'Completed' : $step['status_label'] }}</span>
                        <p class="step-message">{{ $step['can_skip'] && in_array($step['status'], ['draft', 'not_started'], true) ? 'This step is optional. You can complete it later.' : $stepStyle['message'] }}</p>
                        @if($step['rejection_reason'])<p class="reason"><strong>Review feedback</strong>{{ $step['rejection_reason'] }}</p>@endif
                    </div>
                    @if(!$step['is_approved'] || $step['key'] === 'tax')
                        @php $stepAction = $step['is_approved'] ? 'View / update' : ($stepStyle['action'] ?? 'View'); @endphp
                        <a class="step-arrow" href="{{ route('restaurant.onboarding.kyc', ['step' => $step['key']]) }}"
                            aria-label="{{ $stepAction }}: {{ $step['label'] }}">{{ $stepAction }} <span aria-hidden="true">→</span></a>
                    @endif
                </li>
            @endforeach
        </ol>
        @endif
        <p class="notice">Your restaurant onboarding is in progress. Access to the dashboard will be available once the required reviews are complete.</p>
        @php
            $supportEmail = config('mail.support_email') ?: config('mail.from.address');
        @endphp
        <section class="onboarding-help" aria-labelledby="onboarding-help-title">
            <h2 id="onboarding-help-title">Need Help?</h2>
            <p>Contact our support team to get answers to your questions.</p>
            <a class="support-link" href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('Restaurant onboarding support') }}">GET SUPPORT</a>
        </section>
    </main>
</body>
</html>
