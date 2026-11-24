@php
    $kycAllSteps = collect($timeline['steps'] ?? []);
    $kycStepRecord = $kycAllSteps->firstWhere('key', 'kyc');
    $kycStepStatus = $kycStepRecord['status'] ?? 'draft';
    $kycStepApproved = ($kycStepStatus === 'approved');
    $kycStepRejected = in_array($kycStepStatus, ['rejected', 'action_required'], true);
    $kycStepUnderReview = in_array($kycStepStatus, ['submitted', 'under_review'], true);
    $kycStepRejectionReason = $kycStepRecord['rejection_reason'] ?? null;

    $requiredOtherSteps = $kycAllSteps->filter(fn($s) => !($s['can_skip'] ?? false) && ($s['key'] ?? '') !== 'kyc');
    $totalOtherRequired = $requiredOtherSteps->count();
    $otherRequiredDone = $requiredOtherSteps->filter(fn($s) => in_array($s['status'] ?? '', ['approved', 'submitted', 'under_review'], true))->count();
    $allOthersDoneExceptKyc = ($totalOtherRequired > 0) && ($otherRequiredDone >= $totalOtherRequired);
@endphp

<style>
    /* KYC Web Form Container */
    .kyc-web-wrapper {
        min-height: 100vh;
        width: 100%;
        background-color: #0A0A0C;
        color: #FFFFFF;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        box-sizing: border-box;
        padding: 0 20px 60px;
    }

    .kyc-web-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Top Web Navigation */
    .kyc-web-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }

    .kyc-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #A1A1AA;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        margin-bottom: 24px;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .kyc-back-link:hover {
        color: #FFFFFF;
        transform: translateX(-3px);
    }

    .kyc-header-center {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #71717A;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .kyc-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .kyc-store-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #141418;
        border: 1px solid #272730;
        border-radius: 9999px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 500;
        color: #E4E4E7;
    }

    .kyc-store-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #FF6210;
    }

    .kyc-logout-link {
        background: transparent;
        border: none;
        color: #71717A;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    .kyc-logout-link:hover {
        color: #F43F5E;
        background: rgba(244, 63, 94, 0.08);
    }

    /* Hero Section */
    .kyc-web-hero {
        margin-bottom: 32px;
    }

    .kyc-web-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(32px, 4vw, 46px);
        line-height: 1.15;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 12px 0;
        letter-spacing: -0.5px;
    }

    .kyc-web-subtitle {
        color: #A1A1AA;
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
        max-width: 720px;
    }

    .kyc-highlight-name {
        color: #FFFFFF;
        font-weight: 600;
    }

    /* Progress Widget */
    .kyc-web-progress-box {
        background-color: #121215;
        border: 1px solid #202026;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 32px;
    }

    .kyc-progress-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .kyc-progress-label {
        font-size: 15px;
        font-weight: 600;
        color: #E4E4E7;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .kyc-web-chip {
        font-size: 12.5px;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 9999px;
        border: 1px solid rgba(249, 115, 22, 0.4);
        background: rgba(249, 115, 22, 0.1);
        color: #F97316;
    }

    .kyc-web-chip[data-status="approved"] {
        border-color: rgba(52, 211, 153, 0.4);
        background: rgba(52, 211, 153, 0.1);
        color: #34D399;
    }

    .kyc-progress-bar-track {
        height: 6px;
        background-color: #232328;
        border-radius: 9999px;
        overflow: hidden;
    }

    .kyc-progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #F97316, #FF5500);
        border-radius: 9999px;
        transition: width 0.3s ease;
    }

    /* 2-Column Responsive Cards Grid */
    .kyc-web-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        margin-bottom: 28px;
    }

    @media (max-width: 768px) {
        .kyc-web-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    /* Individual Card */
    .kyc-web-card {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
    }

    .kyc-web-card:hover {
        border-color: #383842;
        background-color: #17171B;
    }

    .kyc-web-card.is-dragging {
        border-color: #F97316 !important;
        background-color: #1C1917 !important;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
    }

    .kyc-card-top {
        margin-bottom: 16px;
    }

    .kyc-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .kyc-doc-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 98, 16, 0.08);
        border: 1px solid rgba(255, 98, 16, 0.15);
        color: #FF6210;
        transition: all 0.2s ease;
    }

    .kyc-doc-icon.is-approved {
        background: rgba(52, 211, 153, 0.1);
        border-color: rgba(52, 211, 153, 0.25);
        color: #34D399;
    }

    .kyc-doc-icon.is-under-review {
        background: rgba(96, 165, 250, 0.1);
        border-color: rgba(96, 165, 250, 0.25);
        color: #60A5FA;
    }

    .kyc-doc-icon.is-rejected {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #F87171;
    }

    .kyc-doc-icon.is-pending {
        background: rgba(255, 98, 16, 0.08);
        border-color: rgba(255, 98, 16, 0.2);
        color: #FF6210;
    }

    .kyc-doc-title {
        margin: 0;
        font-size: 17px;
        font-weight: 600;
        color: #FFFFFF;
        letter-spacing: -0.2px;
    }

    .kyc-doc-desc {
        color: #8E8E93;
        font-size: 13.5px;
        line-height: 1.5;
        margin: 0;
        padding-left: 38px;
    }

    /* File Area / Dropzone inside Card */
    .kyc-dropzone-box {
        margin: 16px 0 16px 38px;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px dashed #2E2E36;
        background-color: #18181D;
        transition: border-color 0.2s ease, background-color 0.2s ease;
        cursor: pointer;
    }

    .kyc-dropzone-box:hover {
        border-color: #3E3E4A;
        background-color: #1B1B20;
    }

    .kyc-dropzone-empty {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #71717A;
        font-size: 13px;
    }

    .kyc-dropzone-empty-icon {
        color: #F97316;
        flex-shrink: 0;
    }

    .kyc-file-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .kyc-file-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .kyc-file-tag {
        flex-shrink: 0;
        background-color: rgba(249, 115, 22, 0.15);
        color: #F97316;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .kyc-file-meta {
        min-width: 0;
    }

    .kyc-filename {
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        word-break: break-all;
        margin: 0;
    }

    .kyc-filesize {
        color: #71717A;
        font-size: 12px;
        margin: 2px 0 0 0;
    }

    .kyc-file-preview-btn {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        background: rgba(255, 98, 16, 0.08);
        border: 1px solid rgba(255, 98, 16, 0.2);
        color: #FF6210;
        cursor: pointer;
        padding: 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
    }

    .kyc-file-preview-btn:hover {
        background: rgba(255, 98, 16, 0.18);
        border-color: #FF6210;
        color: #FFFFFF;
        transform: translateY(-1px);
    }

    .kyc-file-preview-btn:focus-visible,
    .kyc-modal-close:focus-visible {
        outline: 3px solid rgba(255, 98, 16, 0.45);
        outline-offset: 3px;
    }

    /* Card Footer */
    .kyc-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 14px;
        border-top: 1px solid #1E1E24;
        margin-top: 4px;
        padding-left: 38px;
    }

    .kyc-pill {
        font-size: 11.5px;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 9999px;
        background-color: #202024;
        color: #9CA3AF;
        display: inline-block;
    }

    .kyc-pill.is-required {
        color: #F97316;
        background-color: rgba(249, 115, 22, 0.1);
        border: 1px solid rgba(249, 115, 22, 0.2);
    }

    .kyc-pill.is-verified {
        color: #34D399;
        background-color: rgba(52, 211, 153, 0.1);
    }

    .kyc-pill.is-refill {
        color: #FBBF24;
        background-color: rgba(251, 191, 36, 0.1);
    }

    .kyc-pill.is-rejected {
        color: #F87171;
        background-color: rgba(248, 113, 113, 0.1);
    }

    .kyc-action-btn {
        background: transparent;
        border: none;
        color: #F97316;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        font-family: inherit;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    .kyc-action-btn:hover {
        color: #FF7A1A;
        background-color: rgba(249, 115, 22, 0.08);
    }

    .kyc-native-input {
        display: none;
    }

    /* Rejection / Feedback Alert */
    .kyc-web-card.is-rejected-card {
        border-color: rgba(239, 68, 68, 0.45);
        background: #141113;
    }
    .kyc-web-card.is-rejected-card:hover {
        border-color: rgba(239, 68, 68, 0.65);
    }
    .kyc-dropzone-box.is-rejected-dropzone {
        border-color: rgba(239, 68, 68, 0.45);
        background: rgba(239, 68, 68, 0.04);
    }
    .kyc-file-tag.is-rejected-tag {
        background: rgba(239, 68, 68, 0.2);
        color: #F87171;
    }
    .kyc-file-tag.is-ready-tag {
        background: rgba(16, 185, 129, 0.2);
        color: #34D399;
    }
    .kyc-replace-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #F87171;
        margin-top: 8px;
        font-weight: 500;
    }
    .kyc-feedback-note {
        margin: 12px 0 0 38px;
        padding: 10px 14px;
        border-radius: 10px;
        background: rgba(248, 113, 113, 0.1);
        border-left: 3px solid #EF4444;
        color: #FCA5A5;
        font-size: 13px;
        line-height: 1.45;
    }

    /* Extra non-file fields */
    .kyc-extra-container {
        margin: 14px 0 0 38px;
    }

    .kyc-input-group {
        margin-bottom: 12px;
    }

    .kyc-input-group label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #A1A1AA;
        margin-bottom: 6px;
    }

    .kyc-input-group input, .kyc-input-group select {
        width: 100%;
        padding: 10px 14px;
        font-size: 13.5px;
        background: #1C1C22;
        border: 1px solid #2C2C34;
        border-radius: 10px;
        color: #FFFFFF;
        box-sizing: border-box;
    }

    .kyc-input-group input:focus, .kyc-input-group select:focus {
        outline: none;
        border-color: #F97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.15);
    }

    .kyc-card-error {
        color: #EF4444;
        font-size: 12.5px;
        margin-top: 8px;
        padding-left: 38px;
    }

    /* Guidance & Support 2-Column Row */
    .kyc-web-guidance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        margin-bottom: 36px;
    }

    @media (max-width: 768px) {
        .kyc-web-guidance-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    .kyc-guidance-card {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 20px 22px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .kyc-guidance-icon {
        flex-shrink: 0;
        margin-top: 2px;
    }

    .kyc-guidance-title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #F97316;
    }

    .kyc-guidance-desc {
        margin: 4px 0 0 0;
        color: #8E8E93;
        font-size: 13px;
        line-height: 1.5;
    }

    .kyc-support-card-link {
        text-decoration: none;
        justify-content: space-between;
        align-items: center;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .kyc-support-card-link:hover {
        border-color: #383842;
        background-color: #17171B;
    }

    .kyc-support-card-left {
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .kyc-support-card-title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #FFFFFF;
    }

    .kyc-support-card-sub {
        margin: 3px 0 0 0;
        color: #8E8E93;
        font-size: 13px;
    }

    .kyc-support-card-arrow {
        flex-shrink: 0;
    }

    /* Web Action Section */
    .kyc-web-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid #1E1E24;
    }

    .kyc-web-submit-btn {
        max-width: 440px;
        width: 100%;
        height: 52px;
        background-color: #FF5500;
        color: #FFFFFF;
        border: none;
        border-radius: 9999px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background-color 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
        box-shadow: 0 4px 18px rgba(255, 85, 0, 0.25);
        font-family: inherit;
    }

    .kyc-web-submit-btn:hover:not(:disabled) {
        background-color: #FF6611;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(255, 85, 0, 0.35);
    }

    .kyc-web-submit-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .kyc-web-submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .kyc-web-cancel-link {
        margin-top: 16px;
        color: #F97316;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: opacity 0.15s ease;
    }

    .kyc-web-cancel-link:hover {
        opacity: 0.85;
    }

    .kyc-web-note {
        margin: 12px 0 0 0;
        font-size: 13px;
        color: #71717A;
        text-align: center;
    }

    /* Alert Banner */
    .kyc-web-alert {
        padding: 14px 18px;
        border-radius: 12px;
        background-color: #141418;
        border: 1px solid #272730;
        margin-bottom: 24px;
        font-size: 14px;
        color: #E4E4E7;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .kyc-web-alert[data-type="error"] {
        background-color: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }

    .kyc-web-alert[data-type="success"] {
        background-color: rgba(52, 211, 153, 0.1);
        border-color: rgba(52, 211, 153, 0.3);
        color: #6EE7B7;
    }

    /* Modal Dialog */
    .kyc-preview-modal {
        max-width: min(92vw, 680px);
        width: 100%;
        max-height: min(88dvh, 760px);
        border: 1px solid #27272A;
        background: #121215;
        color: #FFFFFF;
        border-radius: 20px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
    }

    .kyc-preview-modal::backdrop {
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
    }

    .kyc-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 24px;
        border-bottom: 1px solid #232328;
        background: #141418;
    }

    .kyc-modal-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .kyc-modal-close {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        background: #1F1F26;
        border: 1px solid #383842;
        color: #A1A1AA;
        cursor: pointer;
        padding: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;
    }

    .kyc-modal-close:hover {
        background: rgba(239, 68, 68, 0.14);
        border-color: rgba(239, 68, 68, 0.45);
        color: #FCA5A5;
    }

    .kyc-modal-body {
        padding: 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 180px;
        max-height: calc(min(88dvh, 760px) - 73px);
        overflow: auto;
        background: #0A0A0C;
    }

    .kyc-modal-body img {
        max-width: 100%;
        max-height: 64vh;
        object-fit: contain;
        border-radius: 12px;
        border: 1px solid #27272A;
    }

    @media (max-width: 500px) {
        .kyc-modal-header,
        .kyc-modal-body { padding: 16px; }
    }
</style>

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

        <!-- Web Hero Section -->
        <section class="kyc-web-hero">
            <a href="{{ route('restaurant.onboarding.kyc') }}" class="kyc-back-link" aria-label="Back to onboarding steps">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to onboarding steps</span>
            </a>

            <h1 class="kyc-web-title">A trusted place to dine.</h1>
            <p class="kyc-web-subtitle">
                Upload documents to verify your business. We'll review them to confirm
                <span class="kyc-highlight-name">{{ $timeline['restaurant']['name'] ?? 'your restaurant' }}</span> can operate on Dawat.
            </p>
        </section>

        <!-- Web Progress Box -->
        <section class="kyc-web-progress-box">
            <div class="kyc-progress-meta">
                <div class="kyc-progress-label">
                    <span id="kyc-counter-label">0 of 4 documents uploaded</span>
                </div>
                <span id="kyc-status-chip" class="kyc-web-chip">In progress</span>
            </div>
            <div class="kyc-progress-bar-track">
                <div id="kyc-progress-bar-fill" class="kyc-progress-bar-fill" style="width: 0%;"></div>
            </div>
        </section>

        

        <!-- Feedback / Error Banner -->
        <div id="kyc-alert-notice" class="kyc-web-alert" style="display: none;">
            <span id="kyc-alert-text"></span>
            <button type="button" id="kyc-retry-btn" style="display: none; background: transparent; border: 1px solid currentColor; color: inherit; border-radius: 6px; padding: 4px 10px; font-size: 12px; cursor: pointer; margin-left: 12px;">Retry</button>
        </div>

        <!-- The KYC Form -->
        <form id="onboarding-kyc-form" enctype="multipart/form-data">
            @csrf

            <!-- 2-Column Responsive Web Grid -->
            <div id="kyc-cards-grid" class="kyc-web-grid"></div>

            <!-- Web Guidance & Support Grid -->
            <div class="kyc-web-guidance-grid">
                <!-- Clear documents, faster review -->
                <div class="kyc-guidance-card">
                    <div class="kyc-guidance-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                            <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                            <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                            <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="kyc-guidance-title">Clear documents, faster review</h4>
                        <p class="kyc-guidance-desc">PDF, JPG or PNG · Up to 10 MB per file. Keep all corners visible and make sure every detail is readable.</p>
                    </div>
                </div>

                <!-- Need a hand? -->
                <a href="mailto:support@dawat.com?subject={{ rawurlencode('KYC Document Assistance') }}" class="kyc-guidance-card kyc-support-card-link">
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
            </div>

            <!-- Web Bottom Actions -->
            <div class="kyc-web-actions">
                <button type="submit" id="kyc-main-action-btn" class="kyc-web-submit-btn">
                    <span id="kyc-btn-text">Upload documents</span>
                </button>

                <a href="{{ route('restaurant.onboarding.kyc') }}" class="kyc-web-cancel-link">
                    Save and finish later
                </a>

                <p id="kyc-bottom-note" class="kyc-web-note">
                    All 4 documents are required before review.
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Modal image preview dialog -->
<dialog id="kyc-image-dialog" class="kyc-preview-modal" aria-labelledby="kyc-modal-title">
    <div class="kyc-modal-header">
        <h4 id="kyc-modal-title">Document preview</h4>
        <form method="dialog" style="margin: 0;">
            <button type="submit" class="kyc-modal-close" aria-label="Close document preview">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18"></path>
                </svg>
            </button>
        </form>
    </div>
    <div class="kyc-modal-body">
        <img id="kyc-image-full" alt="Document preview">
    </div>
</dialog>

<script>
(() => {
    const urls = {
        get: @json(route('api.restaurant.onboarding.documents')),
        upload: @json(route('api.restaurant.onboarding.documents.upload')),
        reupload: @json(route('api.restaurant.onboarding.documents.reupload')),
    };

    const stepStatus = @json($kycStepStatus);
    const stepApproved = @json($kycStepApproved);
    const stepLocked = @json(!\Modules\Restaurant\Services\RestaurantOnboardingAccessService::isStepEditable($kycStepStatus));
    const allOthersDoneExceptKyc = @json($allOthersDoneExceptKyc);

    const form = document.getElementById('onboarding-kyc-form');
    const cardsGrid = document.getElementById('kyc-cards-grid');
    const counterLabel = document.getElementById('kyc-counter-label');
    const statusChip = document.getElementById('kyc-status-chip');
    const progressBarFill = document.getElementById('kyc-progress-bar-fill');
    const alertNotice = document.getElementById('kyc-alert-notice');
    const alertText = document.getElementById('kyc-alert-text');
    const retryBtn = document.getElementById('kyc-retry-btn');
    const mainActionBtn = document.getElementById('kyc-main-action-btn');
    const btnText = document.getElementById('kyc-btn-text');
    const bottomNote = document.getElementById('kyc-bottom-note');
    const previewDialog = document.getElementById('kyc-image-dialog');
    const previewTitle = document.getElementById('kyc-modal-title');
    const previewImage = document.getElementById('kyc-image-full');

    previewDialog.addEventListener('close', () => previewImage.removeAttribute('src'));

    const figmaDescriptions = {
        'Business Registration': 'Certificate of formation or incorporation.',
        'Food License': 'Current food service establishment permit.',
        'Tax Certificate': 'State sales tax Certificate of Authority.',
        'Proof of Address': 'A utility bill or bank statement dated within the last 90 days, showing your legal business name and address.'
    };

    let documentRows = [];

    function showAlert(text, type = 'info', canRetry = false) {
        if (!text) {
            alertNotice.style.display = 'none';
            return;
        }
        alertText.textContent = text;
        alertNotice.dataset.type = type;
        alertNotice.style.display = 'flex';
        retryBtn.style.display = canRetry ? 'inline-block' : 'none';
        if (type === 'error' && window.onboardingToast) {
            window.onboardingToast(text, 'error');
        }
    }

    retryBtn.addEventListener('click', loadDocuments);

    function connectionError(isUpload) {
        const error = new Error(isUpload
            ? 'Document upload failed due to a network connection issue. Please check your internet connection and try again.'
            : 'Unable to load KYC documents due to a network connection issue. Please check your internet connection and try again.');
        error.isNetworkError = true;
        return error;
    }

    async function apiCall(url, body) {
        let response;
        try {
            response = await fetch(url, {
                method: body ? 'POST' : 'GET',
                credentials: 'same-origin',
                body,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.elements.namedItem('_token').value
                },
            });
        } catch (_) {
            throw connectionError(!!body);
        }

        let result;
        try {
            result = await response.json();
        } catch (_) {
            throw connectionError(!!body);
        }
        if (!response.ok || result.success !== true) {
            const validationMessages = [...new Set(Object.values(result.errors || {}).flat().filter(Boolean))];
            const error = new Error([401, 419].includes(response.status)
                ? 'Your session expired. Refresh the page and sign in again.'
                : (validationMessages.join('\n') || result.message || 'Unable to process documents. Please try again.'));
            error.errors = result.errors || {};
            throw error;
        }
        return result;
    }

    function formatBytes(bytes) {
        if (!bytes || isNaN(bytes)) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const size = bytes / Math.pow(1024, i);
        return `${size.toFixed(size >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
    }

    function getDocIconSvgByType(name, status) {
        // Status color & styling
        const isApproved = status === 'approved';
        const isUnderReview = status === 'under_review';
        const isRejected = status === 'rejected';

        // Custom path based on the document type
        const lower = (name || '').toLowerCase();
        let iconPath = '';

        if (lower.includes('registration') || lower.includes('incorporation') || lower.includes('business')) {
            // Building / Business Certificate icon
            iconPath = `<rect x="4" y="2" width="16" height="20" rx="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path><path d="M16 10h.01"></path><path d="M16 14h.01"></path><path d="M8 10h.01"></path><path d="M8 14h.01"></path>`;
        } else if (lower.includes('license') || lower.includes('food') || lower.includes('permit')) {
            // Food hygiene / award ribbon certificate icon
            iconPath = `<circle cx="12" cy="8" r="6"></circle><path d="M15.4 12.8 17 22l-5-3-5 3 1.6-9.2"></path>`;
        } else if (lower.includes('tax')) {
            // Tax / receipt calculation statement icon
            iconPath = `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>`;
        } else if (lower.includes('address') || lower.includes('utility') || lower.includes('lease') || lower.includes('proof')) {
            // Location map pin / address document icon
            iconPath = `<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>`;
        } else {
            // Default verified document
            iconPath = `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><path d="m9 15 2 2 4-4"></path>`;
        }

        // Alert badge overlay for rejected
        if (isRejected) {
            return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>`;
        }

        // Check badge overlay for approved
        if (isApproved) {
            return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>`;
        }

        return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">${iconPath}</svg>`;
    }

    function getGreenDocIconSvg() {
        return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>`;
    }

    function getOrangeUploadIconSvg() {
        return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9"></path>
            <polyline points="16 16 12 12 8 16"></polyline>
            <line x1="12" y1="12" x2="12" y2="21"></line>
        </svg>`;
    }

    function getRedAlertIconSvg() {
        return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>`;
    }

    function areAllKycDocumentsCompleted() {
        if (!documentRows.length) return false;
        return documentRows.every(row => {
            const saved = row.item.current_submission;
            const isApproved = stepApproved || saved?.status === 'approved';
            const isUnderReview = saved?.status === 'under_review' || saved?.status === 'pending';
            return isApproved || isUnderReview;
        });
    }

    function updateOverallState() {
        const total = documentRows.length;
        let satisfiedCount = 0;
        let rejectedCount = 0;
        let missingCount = 0;
        let hasNewSelection = false;
        let firstActionNeeded = null;

        documentRows.forEach(row => {
            const item = row.item;
            const saved = item.current_submission;
            const hasSelectedFile = !!(row.fileControl?.files?.length);
            const isApproved = stepApproved || saved?.status === 'approved';
            const isUnderReview = saved?.status === 'under_review' || saved?.status === 'pending';
            const isRejected = saved?.status === 'rejected';
            const isRefill = !!saved?.refill_requested;

            if (hasSelectedFile) {
                hasNewSelection = true;
                satisfiedCount++;
            } else if (isApproved || isUnderReview) {
                satisfiedCount++;
            } else if (isRejected || isRefill) {
                rejectedCount++;
                if (!firstActionNeeded) firstActionNeeded = { row, type: 'rejected' };
            } else {
                missingCount++;
                if (!firstActionNeeded) firstActionNeeded = { row, type: 'missing' };
            }
        });

        // Progress bar percentage
        const percentage = total > 0 ? Math.round((satisfiedCount / total) * 100) : 0;
        progressBarFill.style.width = `${percentage}%`;

        if (rejectedCount > 0) {
            statusChip.textContent = 'Action required';
            statusChip.dataset.status = 'rejected';
            progressBarFill.style.backgroundColor = '#EF4444';
            counterLabel.textContent = `${satisfiedCount} of ${total} documents ready · ${rejectedCount} need replacement`;
            bottomNote.textContent = `${rejectedCount} rejected document(s) must be replaced before review can proceed.`;
        } else if (missingCount > 0) {
            statusChip.textContent = 'In progress';
            statusChip.dataset.status = 'progress';
            progressBarFill.style.backgroundColor = '#FF6210';
            counterLabel.textContent = `${satisfiedCount} of ${total} documents uploaded`;
            bottomNote.textContent = `All ${total} documents are required before review.`;
        } else {
            progressBarFill.style.backgroundColor = '#10B981';
            if (hasNewSelection) {
                statusChip.textContent = 'Ready to submit';
                statusChip.dataset.status = 'ready';
                counterLabel.textContent = `All ${total} documents ready`;
                bottomNote.textContent = 'Click below to submit all documents for review.';
            } else {
                statusChip.textContent = stepApproved ? 'Approved' : 'Under review';
                statusChip.dataset.status = 'approved';
                counterLabel.textContent = `All ${total} documents submitted`;
                bottomNote.textContent = 'Documents submitted for compliance verification.';
            }
        }

        if (hasNewSelection) {
            btnText.textContent = 'Submit documents for review';
            mainActionBtn.dataset.action = 'submit';
            mainActionBtn.disabled = false;
        } else if (firstActionNeeded) {
            const targetRow = firstActionNeeded.row;
            const targetName = targetRow.item.kyc_type.name;
            if (firstActionNeeded.type === 'rejected') {
                btnText.textContent = `Replace ${targetName}`;
            } else {
                btnText.textContent = `Upload ${targetName}`;
            }
            mainActionBtn.dataset.action = 'choose-file';
            mainActionBtn.dataset.targetId = targetRow.item.kyc_type.id;
            mainActionBtn.disabled = false;
        } else {
            btnText.textContent = 'All documents submitted · View review status';
            mainActionBtn.dataset.action = 'back-overview';
            mainActionBtn.disabled = false;
        }
    }

    function fileFormat(name) {
        const extension = String(name || '').match(/\.([a-z0-9]+)$/i)?.[1]?.toLowerCase();
        return ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'].includes(extension) ? extension.toUpperCase() : 'FILE';
    }

    function renderCards(items) {
        cardsGrid.innerHTML = '';
        documentRows = [];

        items.forEach((item, index) => {
            const type = item.kyc_type;
            const saved = item.current_submission;
            const savedFile = saved?.files?.[0] || null;
            const isApproved = stepApproved || saved?.status === 'approved';
            const isRefill = !!saved?.refill_requested;
            const isRejected = saved?.status === 'rejected';
            const isUnderReview = saved?.status === 'under_review' || saved?.status === 'pending';
            const needsReplacement = isRejected || isRefill;

            const card = document.createElement('div');
            card.className = `kyc-web-card ${needsReplacement ? 'is-rejected-card' : ''}`;
            card.id = `kyc-card-${type.id}`;

            const row = {
                item,
                card,
                fileControl: null,
                selectedFile: null,
                controls: [],
            };

            const description = figmaDescriptions[type.name] || type.description || 'Required document for restaurant verification.';

            // Determine document status for theming
            const statusKey = needsReplacement ? 'rejected' : (isApproved ? 'approved' : (isUnderReview ? 'under_review' : (savedFile ? 'uploaded' : 'pending')));
            const iconSvg = getDocIconSvgByType(type.name, statusKey);

            card.innerHTML = `
                <div class="kyc-card-top">
                    <div class="kyc-card-header">
                        <div class="kyc-doc-icon is-${statusKey}" id="kyc-icon-${type.id}">
                            ${iconSvg}
                        </div>
                        <h3 class="kyc-doc-title">${type.name} *</h3>
                    </div>
                    <p class="kyc-doc-desc">${description}</p>

                    <!-- File Dropzone Box inside Card -->
                    <div class="kyc-dropzone-box ${needsReplacement ? 'is-rejected-dropzone' : ''}" id="kyc-dropzone-${type.id}">
                        ${savedFile ? `
                            <div class="kyc-file-selected" id="kyc-file-selected-${type.id}">
                                <div class="kyc-file-left">
                                    ${needsReplacement ? `
                                        <span class="kyc-file-tag is-rejected-tag">REJECTED · ACTION REQUIRED</span>
                                    ` : (!isApproved ? `
                                        <span class="kyc-file-tag">UPLOADED</span>
                                    ` : '')}
                                    <div class="kyc-file-meta">
                                        <p class="kyc-filename" id="kyc-filename-${type.id}"></p>
                                        <p class="kyc-filesize" id="kyc-filesize-${type.id}">${fileFormat(savedFile.name)} · Document</p>
                                    </div>
                                </div>
                                ${savedFile.url ? `
                                    <button type="button" class="kyc-file-preview-btn" id="kyc-preview-btn-${type.id}" title="Preview ${type.name}" aria-label="Preview ${type.name}" aria-haspopup="dialog" aria-controls="kyc-image-dialog">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                ` : ''}
                            </div>
                            ${needsReplacement ? `
                                <div class="kyc-replace-hint">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                    <span>Click here or drop a new file to upload replacement</span>
                                </div>
                            ` : ''}
                        ` : `
                            <div class="kyc-dropzone-empty" id="kyc-dropzone-empty-${type.id}">
                                <div class="kyc-dropzone-empty-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                </div>
                                <span id="kyc-dropzone-text-${type.id}">Drop file here, or click to browse</span>
                            </div>
                            <div class="kyc-file-selected" id="kyc-file-selected-${type.id}" style="display: none;">
                                <div class="kyc-file-left">
                                    <span class="kyc-file-tag" id="kyc-file-tag-${type.id}">READY</span>
                                    <div class="kyc-file-meta">
                                        <p class="kyc-filename" id="kyc-filename-${type.id}"></p>
                                        <p class="kyc-filesize" id="kyc-filesize-${type.id}"></p>
                                    </div>
                                </div>
                            </div>
                        `}
                    </div>

                    ${saved?.rejection_reason ? `<div class="kyc-feedback-note"><strong>Reviewer Feedback:</strong> ${saved.rejection_reason}</div>` : ''}

                    <!-- Extra non-file fields if defined -->
                    <div class="kyc-extra-container" id="kyc-extra-${type.id}"></div>
                </div>

                <!-- Footer row with status badge and action -->
                <div class="kyc-card-footer">
                    <span class="kyc-pill ${!savedFile ? 'is-required' : (isApproved ? 'is-verified' : (isRefill ? 'is-refill' : (isRejected ? 'is-rejected' : '')))}" id="kyc-pill-${type.id}">
                        ${isApproved ? 'Approved' : (isUnderReview ? 'Under review' : (isRefill ? 'Refill requested' : (isRejected ? 'Rejected · Re-upload required' : (savedFile ? 'Uploaded · Not yet verified' : 'Required'))))}
                    </span>
                    ${isApproved ? '' : `
                        <button type="button" class="kyc-action-btn" id="kyc-action-${type.id}">
                            ${needsReplacement ? 'Replace file ↻' : (savedFile ? 'Replace' : 'Choose file +')}
                        </button>
                    `}
                </div>
                <div class="kyc-card-error" id="kyc-error-${type.id}" style="display: none;"></div>
            `;

            if (savedFile) {
                card.querySelector(`#kyc-filename-${type.id}`).textContent = savedFile.name || 'Uploaded document';
            }

            // Hidden file input
            const fileField = type.fields?.find(f => f.field_type === 'file') || { field_key: 'document_file', id: 0 };
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.className = 'kyc-native-input';
            fileInput.id = `kyc-input-${type.id}`;
            fileInput.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx';
            fileInput.disabled = isApproved;
            card.appendChild(fileInput);
            row.fileControl = fileInput;
            row.controls.push({ field: fileField, control: fileInput });

            // File select listener
            fileInput.addEventListener('change', () => {
                const file = fileInput.files?.[0];
                if (file) {
                    row.selectedFile = file;
                    card.classList.remove('is-rejected-card');
                    const dropzoneEl = document.getElementById(`kyc-dropzone-${type.id}`);
                    if (dropzoneEl) dropzoneEl.classList.remove('is-rejected-dropzone');

                    const iconEl = document.getElementById(`kyc-icon-${type.id}`);
                    if (iconEl) {
                        iconEl.className = 'kyc-doc-icon is-approved';
                        iconEl.innerHTML = getGreenDocIconSvg();
                    }
                    
                    const emptyZone = document.getElementById(`kyc-dropzone-empty-${type.id}`);
                    if (emptyZone) emptyZone.style.display = 'none';

                    const selectedZone = document.getElementById(`kyc-file-selected-${type.id}`);
                    if (selectedZone) {
                        selectedZone.style.display = 'flex';
                        const tagEl = selectedZone.querySelector('.kyc-file-tag');
                        if (tagEl) {
                            tagEl.className = 'kyc-file-tag is-ready-tag';
                            tagEl.textContent = 'READY TO RESUBMIT';
                        }
                    }

                    const hintEl = card.querySelector('.kyc-replace-hint');
                    if (hintEl) hintEl.style.display = 'none';

                    const nameEl = document.getElementById(`kyc-filename-${type.id}`);
                    if (nameEl) nameEl.textContent = file.name;

                    const ext = fileFormat(file.name);
                    const sizeEl = document.getElementById(`kyc-filesize-${type.id}`);
                    if (sizeEl) sizeEl.textContent = `${ext} · ${formatBytes(file.size)}`;

                    const pill = document.getElementById(`kyc-pill-${type.id}`);
                    pill.className = 'kyc-pill is-verified';
                    pill.textContent = 'Replacement selected · Ready';

                    document.getElementById(`kyc-action-${type.id}`).textContent = 'Change file';
                    document.getElementById(`kyc-error-${type.id}`).style.display = 'none';
                }
                updateOverallState();
            });

            // Clicking Dropzone or Action Button triggers file selector
            const dropzone = card.querySelector(`#kyc-dropzone-${type.id}`);
            const actionBtn = card.querySelector(`#kyc-action-${type.id}`);
            if (!isApproved) {
                const triggerFile = (e) => {
                    e.stopPropagation();
                    if (stepLocked && !isRefill && !isRejected) {
                        showAlert('This step is locked while documents are under review.', 'info');
                        return;
                    }
                    fileInput.click();
                };

                dropzone.addEventListener('click', triggerFile);
                actionBtn?.addEventListener('click', triggerFile);

                // Drag and Drop
                ['dragenter', 'dragover'].forEach(name => {
                    card.addEventListener(name, (e) => {
                        e.preventDefault();
                        card.classList.add('is-dragging');
                    });
                });
                ['dragleave', 'drop'].forEach(name => {
                    card.addEventListener(name, (e) => {
                        e.preventDefault();
                        card.classList.remove('is-dragging');
                    });
                });
                card.addEventListener('drop', (e) => {
                    if (!e.dataTransfer?.files?.length) return;
                    try {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch (_) {}
                });
            }

            // Preview modal trigger
            if (savedFile?.url) {
                const previewBtn = card.querySelector(`#kyc-preview-btn-${type.id}`);
                if (previewBtn) {
                    previewBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (savedFile.is_image) {
                            previewImage.src = savedFile.url;
                            previewImage.alt = savedFile.name || type.name;
                            previewTitle.textContent = savedFile.name || `${type.name} preview`;
                            previewDialog.showModal();
                        } else {
                            window.open(savedFile.url, '_blank', 'noopener,noreferrer');
                        }
                    });
                }
            }

            // Non-file fields
            const extraContainer = card.querySelector(`#kyc-extra-${type.id}`);
            (type.fields || []).filter(f => f.field_type !== 'file').forEach(field => {
                const group = document.createElement('div');
                group.className = 'kyc-input-group';
                const label = document.createElement('label');
                label.textContent = field.field_name + (field.is_required ? ' *' : '');
                
                const input = document.createElement(field.field_type === 'select' ? 'select' : 'input');
                input.value = saved?.values?.[field.field_key] || '';
                if (field.field_type === 'select') {
                    input.add(new Option('Select an option', ''));
                    for (const [val, lbl] of Object.entries(field.options || {})) {
                        input.add(new Option(lbl, val));
                    }
                }
                group.appendChild(label);
                group.appendChild(input);
                extraContainer.appendChild(group);
                row.controls.push({ field, control: input });
            });

            cardsGrid.appendChild(card);
            documentRows.push(row);
        });

        updateOverallState();
    }

    async function loadDocuments() {
        showAlert('Loading document requirements…', 'info');
        try {
            const result = await apiCall(urls.get);
            showAlert('', 'info');
            renderCards(result.data || []);
            return true;
        } catch (error) {
            showAlert(error.message, 'error', true);
            return false;
        }
    }

    mainActionBtn.addEventListener('click', (e) => {
        const action = mainActionBtn.dataset.action;
        if (action === 'choose-file') {
            e.preventDefault();
            const targetId = mainActionBtn.dataset.targetId;
            const targetRow = documentRows.find(r => String(r.item.kyc_type.id) === String(targetId));
            if (targetRow) {
                targetRow.fileControl.click();
            }
        } else if (action === 'back-overview') {
            e.preventDefault();
            const canGoToSubmitted = areAllKycDocumentsCompleted() && allOthersDoneExceptKyc;
            window.location.assign(
                canGoToSubmitted
                    ? @json(route('restaurant.onboarding.kyc', ['step' => 'submitted']))
                    : @json(route('restaurant.onboarding.kyc'))
            );
        }
    });

    function displayValidationErrors(errors, batch) {
        let firstCardError = null;
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const msg = Array.isArray(messages) ? messages.join(' ') : String(messages);
            const match = key.match(/^documents\.(\d+)/);
            if (match && batch) {
                const index = parseInt(match[1], 10);
                const row = batch[index];
                if (row) {
                    const typeId = row.item.kyc_type.id;
                    const errEl = document.getElementById(`kyc-error-${typeId}`);
                    if (errEl) {
                        errEl.textContent = msg;
                        errEl.style.display = 'block';
                        if (!firstCardError) firstCardError = errEl;
                    }
                }
            }
        });
        if (firstCardError) {
            firstCardError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (stepLocked && !documentRows.some(r => r.item.current_submission?.status === 'rejected' || r.item.current_submission?.refill_requested)) {
            return;
        }

        const modifiedRows = documentRows.filter(r => !!r.selectedFile || (r.controls.some(c => c.field.field_type !== 'file' && c.control.value)));

        if (!modifiedRows.length) {
            showAlert('Please select documents to upload.', 'info');
            return;
        }

        for (const row of modifiedRows) {
            const errEl = document.getElementById(`kyc-error-${row.item.kyc_type.id}`);
            if (errEl) {
                errEl.style.display = 'none';
                errEl.textContent = '';
            }

            if (row.selectedFile) {
                const maxBytes = 10 * 1024 * 1024;
                if (row.selectedFile.size > maxBytes) {
                    if (errEl) {
                        errEl.textContent = 'This file exceeds the 10 MB maximum allowed size.';
                        errEl.style.display = 'block';
                    }
                    showAlert('One or more files exceed the 10 MB limit.', 'error');
                    return;
                }
            }
        }

        if (window.confirmOnboardingSubmission) {
            const confirmed = await window.confirmOnboardingSubmission('Submit your verification documents for review?');
            if (!confirmed) return;
        }

        mainActionBtn.disabled = true;
        btnText.textContent = 'Submitting documents…';
        showAlert('Submitting documents…', 'info');

        const batches = [false, true].map(reupload => ({
            reupload,
            rows: modifiedRows.filter(row => !!row.item.current_submission === reupload),
        }));

        let savedCount = 0;
        let currentBatch = null;
        try {
            for (const { reupload, rows: batch } of batches) {
                if (!batch.length) continue;
                currentBatch = batch;
                const body = new FormData();
                batch.forEach((row, index) => {
                    body.append(`documents[${index}][kyc_type_id]`, row.item.kyc_type.id);
                    for (const { field, control } of row.controls) {
                        if (field.field_type === 'file') {
                            if (control.files[0]) body.append(`documents[${index}][${field.field_key}]`, control.files[0]);
                        } else {
                            body.append(`documents[${index}][${field.field_key}]`, control.value);
                        }
                    }
                });

                await apiCall(reupload ? urls.reupload : urls.upload, body);
                savedCount += batch.length;
            }

            await loadDocuments();

            if (areAllKycDocumentsCompleted()) {
                showAlert(`${savedCount} document(s) submitted successfully. All documents under review.`, 'success');
                const targetUrl = allOthersDoneExceptKyc
                    ? @json(route('restaurant.onboarding.kyc', ['step' => 'submitted']))
                    : @json(route('restaurant.onboarding.kyc'));
                setTimeout(() => {
                    window.location.assign(targetUrl);
                }, 700);
            } else {
                showAlert(`${savedCount} document(s) uploaded successfully. Please replace the remaining rejected document(s) to complete your submission.`, 'info');
                mainActionBtn.disabled = false;
                updateOverallState();
            }
        } catch (error) {
            if (error.errors && currentBatch) {
                displayValidationErrors(error.errors, currentBatch);
            }
            showAlert(error.message, 'error');
            mainActionBtn.disabled = false;
            updateOverallState();
        }
    });

    loadDocuments();
})();
</script>
