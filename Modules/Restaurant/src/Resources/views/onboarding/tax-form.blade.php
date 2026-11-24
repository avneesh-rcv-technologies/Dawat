<style>
    /* Tax Web Form Wrapper */
    .tax-web-wrapper {
        min-height: 100vh;
        width: 100%;
        background-color: #0A0A0C;
        color: #FFFFFF;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        box-sizing: border-box;
        padding: 0 20px 60px;
    }

    .tax-web-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Top Web Header */
    .tax-web-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }

    .tax-back-link {
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

    .tax-back-link:hover {
        color: #FFFFFF;
        transform: translateX(-3px);
    }

    .tax-header-center {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #71717A;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .tax-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .tax-store-badge {
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

    .tax-store-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #FF6210;
    }

    .tax-logout-link {
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

    .tax-logout-link:hover {
        color: #F43F5E;
        background: rgba(244, 63, 94, 0.08);
    }

    /* Hero Section */
    .tax-web-hero {
        margin-bottom: 28px;
    }

    .tax-web-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(32px, 4vw, 44px);
        line-height: 1.15;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 10px 0;
        letter-spacing: -0.5px;
    }

    .tax-web-subtitle {
        color: #A1A1AA;
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
    }

    /* Explainer Box with Clipboard Icon */
    .tax-intro-box {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 22px 24px;
        margin-bottom: 32px;
        display: flex;
        gap: 18px;
        align-items: flex-start;
    }

    .tax-intro-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(249, 115, 22, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #F97316;
    }

    .tax-intro-text {
        color: #D4D4D8;
        font-size: 14.5px;
        line-height: 1.6;
        margin: 0;
    }

    /* Section Headings */
    .tax-section-heading {
        font-size: 20px;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 18px 0;
        letter-spacing: -0.3px;
    }

    .tax-section-subheading {
        color: #8E8E93;
        font-size: 13.5px;
        margin: -10px 0 20px 0;
    }

    /* Screen 1: Entity Choice Cards (2 Columns on Web) */
    .tax-choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 28px;
    }

    @media (max-width: 680px) {
        .tax-choice-grid {
            grid-template-columns: 1fr;
        }
    }

    .tax-choice-card {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.15s ease;
    }

    .tax-choice-card:hover {
        border-color: #383842;
        background-color: #17171B;
    }

    .tax-choice-card.is-selected {
        border-color: #F97316;
        background-color: #161414;
        box-shadow: 0 0 0 1px #F97316, 0 4px 20px rgba(249, 115, 22, 0.12);
    }

    .tax-choice-left {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .tax-choice-icon {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }

    .tax-choice-title {
        margin: 0 0 6px 0;
        font-size: 17px;
        font-weight: 600;
        color: #FFFFFF;
    }

    .tax-choice-desc {
        margin: 0;
        font-size: 13.5px;
        color: #8E8E93;
        line-height: 1.5;
    }

    .tax-radio-indicator {
        flex-shrink: 0;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #3F3F46;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.15s ease, background-color 0.15s ease;
        margin-top: 4px;
    }

    .tax-choice-card.is-selected .tax-radio-indicator {
        border-color: #F97316;
        background-color: #F97316;
    }

    .tax-radio-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #FFFFFF;
        display: none;
    }

    .tax-choice-card.is-selected .tax-radio-dot {
        display: block;
    }

    /* Screen 2: Entity Summary Badge Box */
    .tax-entity-summary {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 18px;
        padding: 18px 24px;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .tax-summary-left {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tax-summary-label {
        font-size: 12.5px;
        color: #71717A;
        font-weight: 500;
    }

    .tax-summary-val {
        font-size: 17px;
        font-weight: 600;
        color: #FFFFFF;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tax-summary-alt-pill {
        font-size: 11.5px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 9999px;
        background: rgba(249, 115, 22, 0.12);
        color: #F97316;
    }

    .tax-change-btn {
        background: transparent;
        border: none;
        color: #F97316;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: 8px;
        transition: background-color 0.15s ease;
    }

    .tax-change-btn:hover {
        background: rgba(249, 115, 22, 0.08);
    }

    /* Input Form Grids */
    .tax-form-block {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 24px;
    }

    .tax-inputs-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    @media (max-width: 768px) {
        .tax-inputs-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    .tax-input-col-full {
        grid-column: 1 / -1;
    }

    .tax-field-wrap {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .tax-field-wrap label {
        font-size: 13.5px;
        font-weight: 600;
        color: #E4E4E7;
    }

    .tax-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .tax-control-input {
        width: 100%;
        background-color: #1A1A1F;
        border: 1px solid #2D2D36;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        color: #FFFFFF;
        box-sizing: border-box;
        font-family: inherit;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .tax-control-input:focus {
        outline: none;
        border-color: #F97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.15);
    }

    .tax-control-input[aria-invalid="true"],
    .tax-control-input[aria-invalid="true"]:focus {
        border-color: #EF4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
    }

    .tax-control-input:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .tax-input-toggle-eye {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: #71717A;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s ease;
    }

    /* Keep the icon-only look: override the page-wide button:hover background. */
    .tax-input-toggle-eye:hover,
    .tax-input-toggle-eye:focus-visible {
        color: #FFFFFF;
        background: transparent;
        border-color: transparent;
    }

    .tax-field-hint {
        color: #71717A;
        font-size: 12px;
        margin: 4px 0 0 0;
    }

    .tax-field-error-msg {
        color: #EF4444;
        font-size: 12.5px;
        margin: 4px 0 0 0;
    }

    /* Checkbox Rows */
    .tax-checkbox-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 18px;
        cursor: pointer;
    }

    .tax-checkbox-input {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 1.5px solid #3F3F46;
        background-color: #1A1A1F;
        margin-top: 2px;
        accent-color: #F97316;
        cursor: pointer;
    }

    .tax-checkbox-label {
        font-size: 13.5px;
        color: #D4D4D8;
        line-height: 1.5;
        cursor: pointer;
    }

    /* Tip / Guidance Cards */
    .tax-info-banner {
        background-color: #141416;
        border: 1px solid #232328;
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 28px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .tax-info-banner-icon {
        flex-shrink: 0;
        color: #F97316;
        margin-top: 2px;
    }

    .tax-info-banner-title {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #F97316;
    }

    .tax-info-banner-desc {
        margin: 4px 0 0 0;
        color: #8E8E93;
        font-size: 13px;
        line-height: 1.5;
    }

    .tax-irs-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #F97316;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        margin-top: 8px;
        margin-bottom: 32px;
        transition: opacity 0.15s ease;
    }

    .tax-irs-link:hover {
        opacity: 0.85;
    }

    /* Bottom Web Actions */
    .tax-web-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid #1E1E24;
    }

    .tax-web-cta-btn {
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

    .tax-web-cta-btn:hover:not(:disabled) {
        background-color: #FF6611;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(255, 85, 0, 0.35);
    }

    .tax-web-cta-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .tax-web-subnote {
        margin: 12px 0 0 0;
        font-size: 13px;
        color: #71717A;
        text-align: center;
    }

    /* Feedback Alert */
    .tax-alert-box {
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

    .tax-alert-box[data-type="error"] {
        background-color: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }

    .tax-alert-box[data-type="success"] {
        background-color: rgba(52, 211, 153, 0.1);
        border-color: rgba(52, 211, 153, 0.3);
        color: #6EE7B7;
    }

    .tax-review-feedback {
        padding: 14px 18px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 12px;
        margin-bottom: 24px;
        background: rgba(239, 68, 68, 0.1);
        color: #FCA5A5;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<div class="tax-web-wrapper">
    <div class="tax-web-container">
        <!-- Top Web Navigation Header -->
        <header class="tax-web-header">
            <div class="tax-header-center">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
            </div>

            <div class="tax-header-right">
              
                <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="tax-logout-link" title="Logout">
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

        <a href="{{ route('restaurant.onboarding.kyc') }}" class="tax-back-link" aria-label="Back to onboarding steps">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back to onboarding steps</span>
        </a>

        <!-- Feedback Alert Banner -->
        <div id="tax-alert-box" class="tax-alert-box" style="display: none;">
            <span id="tax-alert-message"></span>
            <button type="button" id="tax-retry" style="display: none; background: transparent; border: 1px solid currentColor; color: inherit; border-radius: 6px; padding: 4px 10px; font-size: 12px; cursor: pointer; margin-left: 12px;">Retry</button>
        </div>

        @if($stepDetails && !empty($stepDetails['rejection_reason']))
            <div class="tax-review-feedback" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><strong>Review feedback:</strong> {{ $stepDetails['rejection_reason'] }}</span>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- SCREEN 1: Entity Choice Screen (Figma 1)    -->
        <!-- ========================================== -->
        <section id="tax-choice-section" style="display: none;">
            <!-- Hero -->
            <div class="tax-web-hero">
                <h1 class="tax-web-title">Let's get tax-ready.</h1>
                <p class="tax-web-subtitle">Welcome to IRS Substitute W-9 Form</p>
            </div>

            <!-- Explainer Box -->
            <div class="tax-intro-box">
                <div class="tax-intro-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        <line x1="9" y1="11" x2="15" y2="11"></line>
                        <line x1="9" y1="15" x2="13" y2="15"></line>
                    </svg>
                </div>
                <p class="tax-intro-text">
                    We collect your W-9 details to ensure we have accurate taxpayer information. Please select your tax classification to continue.
                </p>
            </div>

            <h2 class="tax-section-heading">How do you file taxes?</h2>

            <!-- 2 Choice Cards -->
            <div class="tax-choice-grid">
                <!-- Business Card -->
                <div class="tax-choice-card is-selected" id="tax-card-business" data-type="business">
                    <div class="tax-choice-left">
                        <div class="tax-choice-icon" aria-hidden="true">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="6" x2="9" y2="6.01"></line>
                                <line x1="15" y1="6" x2="15" y2="6.01"></line>
                                <line x1="9" y1="10" x2="9" y2="10.01"></line>
                                <line x1="15" y1="10" x2="15" y2="10.01"></line>
                                <line x1="9" y1="14" x2="9" y2="14.01"></line>
                                <line x1="15" y1="14" x2="15" y2="14.01"></line>
                                <path d="M10 22v-4h4v4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="tax-choice-title">I am a business</h3>
                            <p class="tax-choice-desc">A corporation, partnership or LLC using an Employer Identification Number (EIN).</p>
                        </div>
                    </div>
                    <div class="tax-radio-indicator">
                        <div class="tax-radio-dot"></div>
                    </div>
                </div>

                <!-- Individual Card -->
                <div class="tax-choice-card" id="tax-card-individual" data-type="individual">
                    <div class="tax-choice-left">
                        <div class="tax-choice-icon" aria-hidden="true">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#A1A1AA" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="tax-choice-title">I am an individual</h3>
                            <p class="tax-choice-desc">An individual or sole proprietor using a Social Security number (SSN).</p>
                        </div>
                    </div>
                    <div class="tax-radio-indicator">
                        <div class="tax-radio-dot"></div>
                    </div>
                </div>
            </div>

            <!-- Tip: One choice, one tax form -->
            <div class="tax-info-banner">
                <div class="tax-info-banner-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </div>
                <div>
                    <h4 class="tax-info-banner-title">One choice, one tax form</h4>
                    <p class="tax-info-banner-desc">Business → business W-9. Individual → individual W-9. Both paths return to business verification.</p>
                </div>
            </div>

            <!-- IRS instructions link -->
            <a href="https://www.irs.gov/forms-pubs/about-form-w-9" target="_blank" rel="noopener noreferrer" class="tax-irs-link">
                <span>Not sure? View IRS W-9 instructions</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </a>

            <!-- Action Button -->
            <div class="tax-web-actions">
                <button type="button" id="tax-choice-continue-btn" class="tax-web-cta-btn">
                    <span>Continue as a business</span>
                </button>
                <p class="tax-web-subnote">Complete only the form for your selected entity type.</p>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- SCREEN 2: Tax Details Form (Figma 2)        -->
        <!-- ========================================== -->
        <section id="tax-form-section" style="display: none;">
            <!-- Hero -->
            <div class="tax-web-hero">
                <h1 class="tax-web-title" id="tax-details-hero-title">Business tax details.</h1>
                <p class="tax-web-subtitle">IRS Substitute W-9 · Enter details exactly as they appear on your federal tax return.</p>
            </div>

            <!-- Preferred Entity Type Summary Card with Change link -->
            <div class="tax-entity-summary">
                <div class="tax-summary-left">
                    <span class="tax-summary-label">Preferred entity type</span>
                    <div class="tax-summary-val">
                        <span id="tax-summary-type-name">Business</span>
                        <span class="tax-summary-alt-pill" id="tax-summary-alt-pill">Alternative to individual form</span>
                    </div>
                </div>
                <button type="button" class="tax-change-btn" id="tax-change-entity-btn">Change</button>
            </div>

            <form id="onboarding-tax-form" novalidate>
                @csrf
                <input type="hidden" name="tax_type" id="tax-tax_type" value="business">

                <!-- Block 1: Taxpayer Information -->
                <div class="tax-form-block">
                    <h2 class="tax-section-heading">Taxpayer information</h2>

                    <div class="tax-inputs-grid">
                        <!-- Business Fields -->
                        <div class="tax-field-wrap tax-input-col-full" data-tax-entity="business">
                            <label for="tax-company_legal_name">Legal business name *</label>
                            <input class="tax-control-input" id="tax-company_legal_name" name="company_legal_name" maxlength="150" autocomplete="organization" placeholder="e.g. Saffron Table LLC" required>
                            <p class="tax-field-hint">Use the name shown on IRS records.</p>
                            <p class="tax-field-error-msg" id="tax-error-company_legal_name" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap" data-tax-entity="business">
                            <label for="tax-doing_business_as">Business / DBA name (optional)</label>
                            <input class="tax-control-input" id="tax-doing_business_as" name="doing_business_as" maxlength="150" placeholder="e.g. Saffron Table">
                            <p class="tax-field-error-msg" id="tax-error-doing_business_as" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap" data-tax-entity="business">
                            <label for="tax-ein">Employer Identification Number (EIN) *</label>
                            <div class="tax-input-container">
                                <input class="tax-control-input" id="tax-ein" name="ein" type="password" maxlength="10" autocomplete="off" inputmode="numeric" placeholder="9 digits (12-3456789)" required>
                                <button type="button" class="tax-input-toggle-eye" data-target="tax-ein" title="Toggle visibility">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="tax-field-hint" id="tax-ein-help">9-digit taxpayer identification number.</p>
                            <p class="tax-field-error-msg" id="tax-error-ein" style="display: none;"></p>
                        </div>

                        <!-- Individual Fields -->
                        <div class="tax-field-wrap" data-tax-entity="individual" style="display: none;">
                            <label for="tax-full_name">Full name *</label>
                            <input class="tax-control-input" id="tax-full_name" name="full_name" maxlength="100" autocomplete="name" placeholder="Legal full name" required>
                            <p class="tax-field-hint">Use the name shown on your tax return.</p>
                            <p class="tax-field-error-msg" id="tax-error-full_name" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap" data-tax-entity="individual" style="display: none;">
                            <label for="tax-company_legal_name_ind">Business / Trade name *</label>
                            <input class="tax-control-input" id="tax-company_legal_name_ind" name="company_legal_name_ind" maxlength="150" placeholder="Business name" required>
                            <p class="tax-field-hint">Business or trade name for sole proprietorship.</p>
                            <p class="tax-field-error-msg" id="tax-error-company_legal_name_ind" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap tax-input-col-full" data-tax-entity="individual" style="display: none;">
                            <label for="tax-ssn">Social Security Number (SSN) *</label>
                            <div class="tax-input-container">
                                <input class="tax-control-input" id="tax-ssn" name="ssn" type="password" maxlength="11" pattern="[0-9]{3}-[0-9]{2}-[0-9]{4}" autocomplete="off" inputmode="numeric" placeholder="123-45-6789" required>
                                <button type="button" class="tax-input-toggle-eye" data-target="tax-ssn" title="Toggle visibility">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="tax-field-hint" id="tax-ssn-help">9-digit Social Security number.</p>
                            <p class="tax-field-error-msg" id="tax-error-ssn" style="display: none;"></p>
                        </div>
                    </div>
                </div>

                <!-- Block 2: Mailing Address -->
                <div class="tax-form-block">
                    <h2 class="tax-section-heading">Mailing address</h2>
                    <p class="tax-section-subheading">Your tax documents will be sent to this address.</p>

                    <!-- Same Mailing Address Checkbox -->
                    <label class="tax-checkbox-row" for="tax-same_mailing_address">
                        <input type="checkbox" class="tax-checkbox-input" id="tax-same_mailing_address" name="same_mailing_address" checked>
                        <span class="tax-checkbox-label">My mailing address is the same as the restaurant address.</span>
                    </label>

                    <fieldset id="tax-restaurant-mailing-box" class="tax-inputs-grid" disabled aria-label="Restaurant mailing address" style="margin: 20px 0 0; padding: 0; border: 0; min-width: 0;">
                        <div class="tax-field-wrap tax-input-col-full">
                            <label for="tax-address">Mailing address *</label>
                            <input class="tax-control-input" id="tax-address" name="address" maxlength="150" autocomplete="street-address" placeholder="e.g. 12B Atlantic Avenue" required>
                            <p class="tax-field-error-msg" id="tax-error-address" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap">
                            <label for="tax-country_id">Country *</label>
                            <select class="tax-control-input" id="tax-country_id" name="country_id" autocomplete="country" required>
                                <option value="">Select country</option>
                            </select>
                            <p class="tax-field-error-msg" id="tax-error-country_id" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap">
                            <label for="tax-state_id">State *</label>
                            <select class="tax-control-input" id="tax-state_id" name="state_id" autocomplete="address-level1" required disabled>
                                <option value="">Select state</option>
                            </select>
                            <p class="tax-field-error-msg" id="tax-error-state_id" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap">
                            <label for="tax-city_id">City *</label>
                            <select class="tax-control-input" id="tax-city_id" name="city_id" autocomplete="address-level2" required disabled>
                                <option value="">Select city</option>
                            </select>
                            <p class="tax-field-error-msg" id="tax-error-city_id" style="display: none;"></p>
                        </div>

                        <div class="tax-field-wrap">
                            <label for="tax-zip_code">ZIP code *</label>
                            <input class="tax-control-input" id="tax-zip_code" name="zip_code" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="postal-code" placeholder="e.g. 11201" required>
                            <p class="tax-field-error-msg" id="tax-error-zip_code" style="display: none;"></p>
                        </div>
                    </fieldset>

                    <!-- Alternate mailing address fields if unchecked -->
                    <div id="tax-alt-mailing-box" style="display: none; margin-top: 24px; padding-top: 20px; border-top: 1px solid #232328;">
                        <h3 class="tax-section-heading" style="font-size: 16px;">Alternate recipient mailing address</h3>
                        <div class="tax-inputs-grid" style="margin-top: 16px;">
                            <div class="tax-field-wrap tax-input-col-full">
                                <label for="tax-mailing_address">Alternate Mailing Address *</label>
                                <input class="tax-control-input" id="tax-mailing_address" name="mailing_address" maxlength="150">
                                <p class="tax-field-error-msg" id="tax-error-mailing_address" style="display: none;"></p>
                            </div>
                            <div class="tax-field-wrap">
                                <label for="tax-mailing_city">Mailing City *</label>
                                <input class="tax-control-input" id="tax-mailing_city" name="mailing_city" maxlength="100">
                                <p class="tax-field-error-msg" id="tax-error-mailing_city" style="display: none;"></p>
                            </div>
                            <div class="tax-field-wrap">
                                <label for="tax-mailing_state">Mailing State *</label>
                                <input class="tax-control-input" id="tax-mailing_state" name="mailing_state" maxlength="100">
                                <p class="tax-field-error-msg" id="tax-error-mailing_state" style="display: none;"></p>
                            </div>
                            <div class="tax-field-wrap">
                                <label for="tax-mailing_zip_code">Mailing ZIP code *</label>
                                <input class="tax-control-input" id="tax-mailing_zip_code" name="mailing_zip_code" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="postal-code">
                                <p class="tax-field-error-msg" id="tax-error-mailing_zip_code" style="display: none;"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Certifications & Checkboxes -->
                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #1E1E24;">
                        <label class="tax-checkbox-row" for="tax-receive_1099_electronically">
                            <input type="checkbox" class="tax-checkbox-input" id="tax-receive_1099_electronically" name="receive_1099_electronically">
                            <span class="tax-checkbox-label">I want to receive my 1099s electronically</span>
                        </label>

                        <div data-tax-entity="business">
                            <label class="tax-checkbox-row" for="tax-backup_tax_withholding">
                                <input type="checkbox" class="tax-checkbox-input" id="tax-backup_tax_withholding" name="backup_tax_withholding">
                                <span class="tax-checkbox-label">I am subject to backup tax withholding</span>
                            </label>

                            <label class="tax-checkbox-row" for="tax-tax_certification">
                                <input type="checkbox" class="tax-checkbox-input" id="tax-tax_certification" name="tax_certification" required>
                                <span class="tax-checkbox-label">I certify that the tax information provided is correct *</span>
                            </label>
                            <p class="tax-field-error-msg" id="tax-error-tax_certification" style="display: none; margin-left: 30px;"></p>
                        </div>
                    </div>
                </div>

                <!-- Warning / Before you save Banner (Figma 2) -->
                <div class="tax-info-banner">
                    <div class="tax-info-banner-icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h4 class="tax-info-banner-title">Before you save</h4>
                        <p class="tax-info-banner-desc">Your legal name and EIN must match. Incorrect details may delay verification or payouts.</p>
                    </div>
                </div>

                <!-- Web Save Action Button -->
                <div class="tax-web-actions">
                    <button type="submit" id="tax-submit-btn" class="tax-web-cta-btn">
                        <span id="tax-btn-text">Save business tax details</span>
                    </button>
                    <p class="tax-web-subnote">Finish setup, then submit for review.</p>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
(() => {
    const urls = {
        get: @json(route('api.restaurant.me.tax-information.show')),
        store: @json(route('api.restaurant.me.tax-information.store')),
        update: @json(route('api.restaurant.me.tax-information.update')),
        countries: @json(route('api.countries.country')),
        states: @json(route('api.countries.states-by-country', ['countryId' => '__ID__'])),
        cities: @json(route('api.countries.cities-by-state', ['stateId' => '__ID__'])),
    };

    const choiceSection = document.getElementById('tax-choice-section');
    const formSection = document.getElementById('tax-form-section');
    const cardBusiness = document.getElementById('tax-card-business');
    const cardIndividual = document.getElementById('tax-card-individual');
    const choiceContinueBtn = document.getElementById('tax-choice-continue-btn');
    const changeEntityBtn = document.getElementById('tax-change-entity-btn');

    const heroTitle = document.getElementById('tax-details-hero-title');
    const summaryTypeName = document.getElementById('tax-summary-type-name');
    const summaryAltPill = document.getElementById('tax-summary-alt-pill');
    const form = document.getElementById('onboarding-tax-form');
    const submitBtn = document.getElementById('tax-submit-btn');
    const btnText = document.getElementById('tax-btn-text');

    const alertBox = document.getElementById('tax-alert-box');
    const alertMessage = document.getElementById('tax-alert-message');
    const retryBtn = document.getElementById('tax-retry');

    const countrySelect = document.getElementById('tax-country_id');
    const stateSelect = document.getElementById('tax-state_id');
    const citySelect = document.getElementById('tax-city_id');
    const sameMailingCheckbox = document.getElementById('tax-same_mailing_address');
    const altMailingBox = document.getElementById('tax-alt-mailing-box');
    const restaurantMailingBox = document.getElementById('tax-restaurant-mailing-box');

    let records = {};
    let drafts = {};
    let selectedType = 'business';
    let readOnly = false;
    let locationVersion = 0;
    let hasLoadedExisting = false;
    let restaurantAddress = null;

    function showAlert(text, type = 'info', canRetry = false) {
        if (!text) {
            alertBox.style.display = 'none';
            return;
        }
        alertMessage.textContent = text;
        alertBox.dataset.type = type;
        alertBox.style.display = 'flex';
        retryBtn.style.display = canRetry ? 'inline-block' : 'none';
        if (type === 'error' && window.onboardingToast) {
            window.onboardingToast(text, 'error');
        }
    }

    const errorField = field => field === 'company_legal_name' && selectedType === 'individual'
        ? 'company_legal_name_ind'
        : field;

    function clearTaxFieldError(field) {
        const error = document.getElementById(`tax-error-${errorField(field)}`);
        if (!error) return;
        error.textContent = '';
        error.style.display = 'none';
        document.getElementById(`tax-${errorField(field)}`)?.removeAttribute('aria-invalid');
    }

    function showTaxFieldError(field, text, focus = false) {
        const resolvedField = errorField(field);
        const error = document.getElementById(`tax-error-${resolvedField}`);
        if (!error) return false;
        error.textContent = text;
        error.style.display = 'block';
        const control = document.getElementById(`tax-${resolvedField}`);
        control?.setAttribute('aria-invalid', 'true');
        if (focus) {
            control?.focus();
            control?.scrollIntoView({ block: 'center' });
        }
        return true;
    }

    function validationErrors(payload, isBusiness) {
        const identifier = isBusiness ? 'ein' : 'ssn';
        const identifierValue = Object.hasOwn(payload, identifier) ? payload[identifier] : 'saved';
        const identifierPattern = isBusiness
            ? /^(?:[0-9]{9}|[0-9]{2}-[0-9]{7})$/
            : /^(?:[0-9]{9}|[0-9]{3}-[0-9]{2}-[0-9]{4})$/;
        const required = [
            [isBusiness ? 'company_legal_name' : 'full_name', isBusiness ? payload.company_legal_name : payload.full_name,
                isBusiness ? 'Please enter the business legal entity name.' : 'Please enter your full name.'],
            [isBusiness ? 'ein' : 'company_legal_name', isBusiness ? identifierValue : payload.company_legal_name,
                isBusiness ? 'Please enter your EIN.' : 'Please enter the business legal entity name.'],
            [isBusiness ? null : 'ssn', isBusiness ? null : identifierValue,
                'Please enter your SSN.'],
            ['address', payload.address, 'Please enter your mailing address.'],
            ['country_id', payload.country_id, 'Please select a country.'],
            ['state_id', payload.state_id, 'Please select a state.'],
            ['city_id', payload.city_id, 'Please select a city.'],
            ['zip_code', payload.zip_code, 'Please enter your zip or postal code.'],
        ];

        if (!payload.same_mailing_address) {
            required.push(
                ['mailing_address', payload.mailing_address, 'Please enter the alternate mailing address.'],
                ['mailing_city', payload.mailing_city, 'Please enter the mailing city.'],
                ['mailing_state', payload.mailing_state, 'Please enter the mailing state.'],
                ['mailing_zip_code', payload.mailing_zip_code, 'Please enter the mailing zip or postal code.'],
            );
        }

        const errors = required
            .filter(([field, value]) => field && !value)
            .map(([field, , message]) => ({ field, message }));
        for (const field of ['zip_code', ...(!payload.same_mailing_address ? ['mailing_zip_code'] : [])]) {
            if (payload[field] && !/^[0-9]{5}$/.test(payload[field])) {
                errors.push({ field, message: 'Please enter a valid 5-digit ZIP code.' });
            }
        }
        if (Object.hasOwn(payload, identifier) && payload[identifier] && !identifierPattern.test(payload[identifier])) {
            errors.push({
                field: identifier,
                message: isBusiness
                    ? 'Enter a 9-digit EIN, with or without a hyphen (12-3456789).'
                    : 'Enter a 9-digit SSN, with or without hyphens (123-45-6789).',
            });
        }
        if (isBusiness && !payload.tax_certification) {
            errors.push({ field: 'tax_certification', message: 'Please certify that the tax information provided is correct.' });
        }
        return errors;
    }

    function showValidationErrors(errors) {
        showAlert('', 'info');
        if (window.onboardingToasts) window.onboardingToasts(errors.map(error => error.message), 'error');
        else if (window.onboardingToast) window.onboardingToast(errors.map(error => error.message).join('\n'), 'error');
    }

    document.querySelectorAll('.tax-control-input, .tax-checkbox-input').forEach(control => {
        const field = control.id.replace(/^tax-/, '');
        control.addEventListener('input', () => {
            if (['zip_code', 'mailing_zip_code'].includes(field)) {
                control.value = control.value.replace(/[^0-9]/g, '').slice(0, 5);
            } else if (field === 'ssn') {
                const caret = control.selectionStart;
                const digitsBeforeCaret = control.value.slice(0, caret ?? control.value.length).replace(/[^0-9]/g, '').length;
                const digits = control.value.replace(/[^0-9]/g, '').slice(0, 9);
                control.value = [digits.slice(0, 3), digits.slice(3, 5), digits.slice(5, 9)].filter(Boolean).join('-');
                if (caret !== null) {
                    const position = Math.min(digitsBeforeCaret, digits.length);
                    const formattedPosition = position + (position > 3 ? 1 : 0) + (position > 5 ? 1 : 0);
                    control.setSelectionRange(formattedPosition, formattedPosition);
                }
            }
            clearTaxFieldError(field);
        });
        control.addEventListener('change', () => clearTaxFieldError(field));
    });

    retryBtn.addEventListener('click', initialize);

    async function apiCall(url, options = {}) {
        let response;
        try {
            response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.elements.namedItem('_token').value,
                    ...options.headers
                }
            });
        } catch {
            throw new Error('Unable to connect. Please check your internet connection and try again.');
        }
        const result = await response.json().catch(() => ({}));
        if (!response.ok || result.success !== true) {
            const error = new Error([401, 419].includes(response.status)
                ? 'Your session expired. Refresh the page and sign in again.'
                : (response.status >= 500 ? 'Tax service is temporarily unavailable.' : result.message || 'Unable to load or save tax information.'));
            error.errors = result.errors || {};
            throw error;
        }
        return result;
    }

    function populateOptions(select, list, placeholder, selectedVal = '') {
        select.innerHTML = '';
        select.appendChild(new Option(placeholder, ''));
        (list || []).forEach(item => {
            select.appendChild(new Option(item.name, String(item.id)));
        });
        select.value = String(selectedVal ?? '');
        select.disabled = !list || !list.length;
    }

    // Toggle Eye on Password Inputs
    document.querySelectorAll('.tax-input-toggle-eye').forEach(btn => {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        const syncVisibility = () => {
            const visible = input.type !== 'password';
            btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
                ${visible ? '<path d="M3 3l18 18"></path>' : ''}
            </svg>`;
        };
        btn.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            syncVisibility();
        });
        syncVisibility();
    });

    // Screen 1: Choice Card selection
    function selectChoice(type) {
        selectedType = type;
        cardBusiness.classList.toggle('is-selected', type === 'business');
        cardIndividual.classList.toggle('is-selected', type === 'individual');

        choiceContinueBtn.querySelector('span').textContent = type === 'business'
            ? 'Continue as a business'
            : 'Continue as an individual';
    }

    cardBusiness.addEventListener('click', () => selectChoice('business'));
    cardIndividual.addEventListener('click', () => selectChoice('individual'));

    choiceContinueBtn.addEventListener('click', () => {
        showFormScreen(selectedType);
    });

    changeEntityBtn.addEventListener('click', () => {
        if (readOnly) return;
        showChoiceScreen();
    });

    function showChoiceScreen() {
        choiceSection.style.display = 'block';
        formSection.style.display = 'none';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function showFormScreen(type) {
        selectedType = type;
        choiceSection.style.display = 'none';
        formSection.style.display = 'block';

        heroTitle.textContent = type === 'business' ? 'Business tax details.' : 'Individual tax details.';
        summaryTypeName.textContent = type === 'business' ? 'Business' : 'Individual';
        summaryAltPill.textContent = type === 'business' ? 'Alternative to individual form' : 'Alternative to business form';
        btnText.textContent = type === 'business' ? 'Save business tax details' : 'Save individual tax details';

        document.getElementById('tax-tax_type').value = type;

        // Toggle field groups visibility
        document.querySelectorAll('[data-tax-entity]').forEach(group => {
            const isMatch = group.dataset.taxEntity === type;
            group.style.display = isMatch ? '' : 'none';
            group.querySelectorAll('input, select').forEach(ctl => {
                ctl.disabled = !isMatch || readOnly;
            });
        });

        await populateFormData(type);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function applyRestaurantAddress() {
        document.getElementById('tax-address').value = restaurantAddress?.address || '';
        document.getElementById('tax-zip_code').value = restaurantAddress?.zip_code || '';
        countrySelect.value = String(restaurantAddress?.country_id || '');
        populateOptions(stateSelect, [], 'Select state');
        populateOptions(citySelect, [], 'Select city');

        if (!restaurantAddress?.address || !restaurantAddress?.country_id || !restaurantAddress?.state_id || !restaurantAddress?.city_id || !restaurantAddress?.zip_code) {
            showAlert('Your restaurant address is incomplete. Please update it in your restaurant details before saving tax information.', 'error');
            return;
        }

        const currentVersion = ++locationVersion;
        try {
            const stateRes = await apiCall(urls.states.replace('__ID__', encodeURIComponent(countrySelect.value)));
            if (currentVersion !== locationVersion) return;
            populateOptions(stateSelect, stateRes.states, 'Select state', restaurantAddress.state_id);

            const cityRes = await apiCall(urls.cities.replace('__ID__', encodeURIComponent(stateSelect.value)));
            if (currentVersion !== locationVersion) return;
            populateOptions(citySelect, cityRes.cities, 'Select city', restaurantAddress.city_id);
        } catch (error) {
            if (currentVersion === locationVersion) showAlert(error.message, 'error');
        }
    }

    function syncMailingAddressMode() {
        const isSame = sameMailingCheckbox.checked;
        restaurantMailingBox.style.display = isSame ? '' : 'none';
        altMailingBox.style.display = isSame ? 'none' : 'block';
        sameMailingCheckbox.disabled = readOnly;
        altMailingBox.querySelectorAll('input').forEach(input => {
            input.required = !isSame;
            input.disabled = isSame || readOnly;
        });
        for (const field of ['address', 'country_id', 'state_id', 'city_id', 'zip_code',
            'mailing_address', 'mailing_city', 'mailing_state', 'mailing_zip_code']) {
            clearTaxFieldError(field);
        }
    }

    // Preserve an alternate address when switching back and forth.
    sameMailingCheckbox.addEventListener('change', syncMailingAddressMode);

    // Country / State / City cascading
    async function handleLocationCascade(parentSelect, targetSelect, urlTemplate, listKey, placeholder) {
        if (readOnly) return;
        const currentVersion = ++locationVersion;
        populateOptions(targetSelect, [], placeholder);
        if (parentSelect === countrySelect) {
            populateOptions(citySelect, [], 'Select city');
        }
        submitBtn.disabled = true;

        try {
            if (parentSelect.value) {
                const res = await apiCall(urlTemplate.replace('__ID__', encodeURIComponent(parentSelect.value)));
                if (currentVersion === locationVersion) {
                    populateOptions(targetSelect, res[listKey], placeholder);
                }
            }
        } catch (e) {
            if (currentVersion === locationVersion) {
                showAlert(e.message, 'error');
            }
        } finally {
            if (currentVersion === locationVersion) {
                submitBtn.disabled = readOnly;
            }
        }
    }

    countrySelect.addEventListener('change', () => {
        handleLocationCascade(countrySelect, stateSelect, urls.states, 'states', 'Select state');
    });

    stateSelect.addEventListener('change', () => {
        handleLocationCascade(stateSelect, citySelect, urls.cities, 'cities', 'Select city');
    });

    async function populateFormData(type) {
        const data = drafts[type] || records[type] || {};

        if (type === 'business') {
            document.getElementById('tax-company_legal_name').value = data.company_legal_name || '';
            document.getElementById('tax-doing_business_as').value = data.doing_business_as || '';
            const einInput = document.getElementById('tax-ein');
            einInput.value = data.ein || '';
            document.getElementById('tax-ein-help').textContent = data.tax_identifier_masked
                ? `Saved EIN: ${data.tax_identifier_masked}. Leave blank to keep current.`
                : '9-digit taxpayer identification number.';
            document.getElementById('tax-backup_tax_withholding').checked = !!data.backup_tax_withholding;
            document.getElementById('tax-tax_certification').checked = !!data.tax_certification;
        } else {
            document.getElementById('tax-full_name').value = data.full_name || '';
            document.getElementById('tax-company_legal_name_ind').value = data.company_legal_name || '';
            const ssnInput = document.getElementById('tax-ssn');
            ssnInput.value = data.ssn || '';
            document.getElementById('tax-ssn-help').textContent = data.tax_identifier_masked
                ? `Saved SSN: ${data.tax_identifier_masked}. Leave blank to keep current.`
                : '9-digit Social Security number.';
        }

        document.getElementById('tax-address').value = data.address || '';
        document.getElementById('tax-zip_code').value = data.zip_code || '';
        document.getElementById('tax-receive_1099_electronically').checked = !!data.receive_1099_electronically;

        // Mailing
        const sameMailing = data.same_mailing_address !== false;
        sameMailingCheckbox.checked = sameMailing;
        syncMailingAddressMode();
        document.getElementById('tax-mailing_address').value = data.mailing_address || '';
        document.getElementById('tax-mailing_city').value = data.mailing_city || '';
        document.getElementById('tax-mailing_state').value = data.mailing_state || '';
        document.getElementById('tax-mailing_zip_code').value = data.mailing_zip_code || '';

        // Restaurant address fields are a read-only reference in both mailing modes.
        // Always use the current restaurant address, including for existing tax records.
        await applyRestaurantAddress();
    }

    async function initialize() {
        showAlert('Loading tax information…', 'info');
        submitBtn.disabled = true;

        try {
            const [taxRes, countriesRes] = await Promise.all([
                apiCall(urls.get),
                apiCall(urls.countries)
            ]);

            records = taxRes.data || {};
            restaurantAddress = taxRes.restaurant_address || null;
            const savedType = taxRes.tax_type || 'business';
            readOnly = ['approved', 'under_review'].includes(taxRes.onboarding_step?.status);

            populateOptions(countrySelect, countriesRes.data, 'Select country');

            // Always let the partner confirm the entity type before opening its form.
            selectedType = savedType;
            selectChoice(savedType);
            showChoiceScreen();

            showAlert('', 'info');
            submitBtn.disabled = readOnly;
        } catch (err) {
            showAlert(err.message, 'error', true);
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (readOnly || submitBtn.disabled) return;

        document.querySelectorAll('.tax-field-error-msg').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });

        const isBusiness = selectedType === 'business';
        const payload = {
            tax_type: selectedType,
            company_legal_name: (isBusiness ? document.getElementById('tax-company_legal_name') : document.getElementById('tax-company_legal_name_ind')).value.trim(),
            address: document.getElementById('tax-address').value.trim(),
            country_id: countrySelect.value ? parseInt(countrySelect.value, 10) : null,
            state_id: stateSelect.value ? parseInt(stateSelect.value, 10) : null,
            city_id: citySelect.value ? parseInt(citySelect.value, 10) : null,
            zip_code: document.getElementById('tax-zip_code').value.trim(),
            same_mailing_address: sameMailingCheckbox.checked,
            receive_1099_electronically: document.getElementById('tax-receive_1099_electronically').checked,
        };

        if (isBusiness) {
            payload.doing_business_as = document.getElementById('tax-doing_business_as').value.trim() || null;
            const einVal = document.getElementById('tax-ein').value.trim();
            if (einVal || !records[selectedType]?.tax_identifier_masked) {
                payload.ein = einVal;
            }
            payload.backup_tax_withholding = document.getElementById('tax-backup_tax_withholding').checked;
            payload.tax_certification = document.getElementById('tax-tax_certification').checked;
        } else {
            payload.full_name = document.getElementById('tax-full_name').value.trim();
            const ssnVal = document.getElementById('tax-ssn').value.trim();
            if (ssnVal || !records[selectedType]?.tax_identifier_masked) {
                payload.ssn = ssnVal;
            }
        }

        if (!sameMailingCheckbox.checked) {
            payload.mailing_address = document.getElementById('tax-mailing_address').value.trim();
            payload.mailing_city = document.getElementById('tax-mailing_city').value.trim();
            payload.mailing_state = document.getElementById('tax-mailing_state').value.trim();
            payload.mailing_zip_code = document.getElementById('tax-mailing_zip_code').value.trim();
        }

        const validationErrorsList = validationErrors(payload, isBusiness);
        if (validationErrorsList.length) {
            validationErrorsList.forEach(error => showTaxFieldError(error.field, error.message));
            const firstError = validationErrorsList[0];
            showTaxFieldError(firstError.field, firstError.message, true);
            showValidationErrors(validationErrorsList);
            return;
        }

        const isUpdate = !!(records && records[selectedType]);

        if (window.confirmOnboardingSubmission) {
            const taxProfileLabel = isBusiness ? 'business' : 'individual';
            const confirmed = await window.confirmOnboardingSubmission(isUpdate
                ? `Update your ${taxProfileLabel} tax information and submit it for review?`
                : `Submit your ${taxProfileLabel} tax information for review?`);
            if (!confirmed) return;
        }

        submitBtn.disabled = true;
        const originalBtnText = btnText.textContent;
        btnText.textContent = 'Saving tax details…';
        showAlert('Saving tax details…', 'info');

        try {
            const result = await apiCall(isUpdate ? urls.update : urls.store, {
                method: isUpdate ? 'PATCH' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            showAlert(result.message || 'Tax details saved successfully.', 'success');
            const nextStepUrl = @json(route('restaurant.onboarding.kyc', ['step' => 'dietary_certificates']));
            setTimeout(() => {
                window.location.assign(nextStepUrl);
            }, 600);
            records[selectedType] = result.data || payload;
            btnText.textContent = originalBtnText;
            submitBtn.disabled = false;
        } catch (err) {
            btnText.textContent = originalBtnText;
            submitBtn.disabled = false;

            const serverErrors = [];
            if (err.errors) {
                for (const [field, messages] of Object.entries(err.errors)) {
                    const message = Array.isArray(messages) ? messages.join(' ') : String(messages);
                    if (showTaxFieldError(field, message)) serverErrors.push({ field, message });
                }
            }
            if (serverErrors.length) {
                const firstError = serverErrors[0];
                showTaxFieldError(firstError.field, firstError.message, true);
                showValidationErrors(serverErrors);
            }
            else showAlert(err.message || 'Unable to save tax information. Please try again.', 'error');
        }
    });

    initialize();
})();
</script>
