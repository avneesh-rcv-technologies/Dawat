<style>
    /* Banking Information Web Form */
    .bank-web-wrapper {
        min-height: 100vh;
        width: 100%;
        background-color: #0A0A0C;
        color: #FFFFFF;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        box-sizing: border-box;
        padding: 0 20px 60px;
    }

    .bank-web-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    /* Top Web Header */
    .bank-web-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }

    .bank-back-link {
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

    .bank-back-link:hover {
        color: #FFFFFF;
        transform: translateX(-3px);
    }

    .bank-header-center {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #71717A;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .bank-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .bank-store-badge {
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

    .bank-store-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #FF6210;
    }

    .bank-logout-link {
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

    .bank-logout-link:hover {
        color: #F43F5E;
        background: rgba(244, 63, 94, 0.08);
    }

    /* Hero Section */
    .bank-web-hero {
        margin-bottom: 32px;
    }

    .bank-web-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(32px, 4vw, 46px);
        line-height: 1.15;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 12px 0;
        letter-spacing: -0.5px;
    }

    .bank-web-subtitle {
        color: #A1A1AA;
        font-size: 15px;
        line-height: 1.6;
        margin: 0;
        max-width: 640px;
    }

    /* Notice Banner Card (Figma reference) */
    .bank-notice-card {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 18px;
        padding: 20px 24px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .bank-notice-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(249, 115, 22, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .bank-notice-content {
        flex: 1;
        min-width: 0;
    }

    .bank-notice-title {
        font-size: 15.5px;
        font-weight: 600;
        color: #FF6210;
        margin: 0 0 4px 0;
    }

    .bank-notice-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 0;
        line-height: 1.5;
    }

    /* Status Alerts */
    .bank-alert-banner {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bank-alert-banner.is-approved {
        background: rgba(16, 185, 129, 0.08);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #34D399;
    }

    .bank-alert-banner.is-under-review {
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.25);
        color: #60A5FA;
    }

    .bank-alert-banner.is-error {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }

    /* Form Status / Feedback Message */
    .bank-message {
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 14px;
        background: #141418;
        border: 1px solid #272730;
        color: #E4E4E7;
    }

    .bank-message[data-error="true"] {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }

    .bank-message[data-success="true"] {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34D399;
    }

    .bank-retry-btn {
        background: #1F1F26;
        border: 1px solid #383842;
        color: #FFFFFF;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        margin-bottom: 20px;
    }

    /* Grid Layout for Sections */
    .bank-grid-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    @media (max-width: 900px) {
        .bank-grid-wrap {
            grid-template-columns: 1fr;
        }
    }

    .bank-card {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 28px;
    }

    .bank-card-title {
        font-size: 18px;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0 0 4px 0;
    }

    .bank-card-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 0 0 22px 0;
        line-height: 1.4;
    }

    /* Form Fields */
    .bank-field {
        margin-bottom: 20px;
        min-width: 0;
    }

    .bank-field:last-child {
        margin-bottom: 0;
    }

    .bank-field label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #D4D4D8;
        margin-bottom: 8px;
    }

    .bank-req {
        color: #FF6210;
        margin-left: 2px;
    }

    .bank-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .bank-control-input {
        width: 100%;
        height: 48px;
        background: #18181D;
        border: 1px solid #2D2D36;
        border-radius: 12px;
        padding: 0 16px;
        color: #FFFFFF;
        font-size: 14.5px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        box-sizing: border-box;
    }

    .bank-control-input:focus:not(:disabled) {
        border-color: #FF6210;
        box-shadow: 0 0 0 3px rgba(255, 98, 16, 0.15);
        background: #1C1C22;
    }

    .bank-control-input:disabled,
    .bank-control-input[readonly] {
        background: #121215;
        border-color: #202026;
        color: #E4E4E7;
        cursor: default;
    }

    .bank-control-input[aria-invalid="true"] {
        border-color: #EF4444 !important;
        background-color: rgba(239, 68, 68, 0.05);
    }

    .bank-input-with-eye .bank-control-input {
        padding-right: 48px;
    }

    .bank-toggle-eye-btn {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: #71717A;
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: color 0.15s ease;
    }

    /* Keep the icon-only look: override the page-wide button:hover background. */
    .bank-toggle-eye-btn:hover,
    .bank-toggle-eye-btn:focus-visible {
        color: #FFFFFF;
        background: transparent;
        border-color: transparent;
    }

    .bank-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%238E8E93' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        padding-right: 40px;
        cursor: pointer;
    }

    .bank-select:disabled {
        cursor: default;
    }

    .bank-help {
        font-size: 12px;
        color: #71717A;
        margin: 6px 0 0 0;
        line-height: 1.4;
    }

    .bank-help.is-match {
        color: #34D399;
    }

    .bank-error {
        color: #F87171;
        font-size: 12.5px;
        margin: 6px 0 0 0;
        line-height: 1.4;
    }

    .bank-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    @media (max-width: 500px) {
        .bank-row-2col {
            grid-template-columns: 1fr;
        }
    }

    /* Confirmation & Agreements Cards */
    .bank-agreement-card {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 24px 28px;
        margin-bottom: 24px;
    }

    .bank-checkbox-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        cursor: pointer;
        user-select: none;
    }

    .bank-checkbox-row + .bank-checkbox-row {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid #1E1E24;
    }

    .bank-checkbox-row input[type="checkbox"] {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        accent-color: #FF6210;
        margin-top: 2px;
        cursor: pointer;
    }

    .bank-checkbox-label {
        font-size: 13.5px;
        color: #D4D4D8;
        line-height: 1.5;
        font-weight: 500;
        cursor: pointer;
    }

    .bank-checkbox-sub {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #71717A;
        font-weight: 400;
    }

    /* Support Card */
    .bank-support-card-link {
        background-color: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-decoration: none;
        transition: border-color 0.2s ease, background-color 0.2s ease;
        margin-bottom: 28px;
    }

    .bank-support-card-link:hover {
        border-color: #383842;
        background-color: #17171B;
    }

    .bank-support-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .bank-support-title {
        font-size: 15px;
        font-weight: 600;
        color: #FFFFFF;
        margin: 0;
    }

    .bank-support-sub {
        font-size: 13px;
        color: #8E8E93;
        margin: 3px 0 0 0;
    }

    /* Bottom Actions */
    .bank-actions-row {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 36px;
        padding-top: 28px;
        border-top: 1px solid #1E1E24;
    }

    .bank-nav-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-top: 28px;
    }

    .bank-save-btn {
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

    .bank-save-btn:hover:not(:disabled) {
        background-color: #FF6611;
        transform: translateY(-1px);
        box-shadow: 0 6px 22px rgba(255, 85, 0, 0.35);
    }

    .bank-save-btn:disabled {
        opacity: 0.55;
        cursor: wait;
    }

    .bank-subnote {
        margin: 12px 0 0 0;
        font-size: 13px;
        color: #71717A;
        text-align: center;
    }
</style>

@php
    $selectedRecord = $steps->firstWhere('key', 'banking');
    $stepStatus = $selectedRecord['status'] ?? 'draft';
    $stepEditable = \Modules\Restaurant\Services\RestaurantOnboardingAccessService::isStepEditable($stepStatus);
    $isApproved = (bool) ($selectedRecord['is_approved'] ?? false);
    $isUnderReview = $stepStatus === 'under_review';
    $restaurantName = $timeline['restaurant']['name'] ?? 'Restaurant Partner';
@endphp

<div class="bank-web-wrapper">
    <div class="bank-web-container">
        <!-- Top Web Navigation Header -->
        <header class="bank-web-header">
            <div class="bank-header-center">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
            </div>

            <div class="bank-header-right">
             
                <form method="POST" action="{{ route('admin.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="bank-logout-link" title="Logout">
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

        <!-- Hero Section (Figma Reference) -->
        <section class="bank-web-hero">
            <a href="{{ route('restaurant.onboarding.kyc') }}" class="bank-back-link" aria-label="Back to onboarding steps">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to onboarding steps</span>
            </a>

            <h1 class="bank-web-title">A home for your earnings.</h1>
            <p class="bank-web-subtitle">
                Earnings will be deposited into the bank account you add below.
            </p>
        </section>

        <!-- Secure Payout Details Banner (Figma Reference) -->
        <div class="bank-notice-card">
            <div class="bank-notice-icon-box" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 10 9-7 9 7H3zM5 10v9M9 10v9M15 10v9M19 10v9M3 21h18"/>
                </svg>
            </div>
            <div class="bank-notice-content">
                <h3 class="bank-notice-title">Secure payout details</h3>
                <p class="bank-notice-sub">Use a US checking account held by your business. Sensitive values are hidden for your privacy.</p>
            </div>
        </div>

        <!-- Status Alerts -->
        @if($isApproved)
            <div class="bank-alert-banner is-approved">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span><strong>Approved information</strong> · This step is approved and locked for editing.</span>
            </div>
        @elseif($isUnderReview)
            <div class="bank-alert-banner is-under-review">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span><strong>Under review</strong> · Updates are disabled until our team completes verification.</span>
            </div>
        @endif

        @if($stepDetails && !empty($stepDetails['rejection_reason']))
            <div class="bank-alert-banner is-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><strong>Review feedback:</strong> {{ $stepDetails['rejection_reason'] }}</span>
            </div>
        @endif

        <!-- Status & Feedback Messages -->
        <p id="bank-message" class="bank-message" role="status" aria-live="polite">Loading banking information…</p>
        <button type="button" id="bank-retry" class="bank-retry-btn" hidden>Retry loading</button>

        <form id="onboarding-bank-form" novalidate>
            @csrf
            <fieldset id="bank-fields" style="border: 0; padding: 0; margin: 0; min-width: 0;" disabled>
                <!-- 2-Column Responsive Web Grid -->
                <div class="bank-grid-wrap">
                    <!-- Column 1: Account Details -->
                    <div class="bank-card">
                        <h2 class="bank-card-title">Account details</h2>
                        <p class="bank-card-sub">Enter the account that should receive your payouts.</p>

                        <!-- Name of Account Holder -->
                        <div class="bank-field">
                            <label for="bank-account_holder_name">
                                Name of account holder <span class="bank-req">*</span>
                            </label>
                            <input id="bank-account_holder_name"
                                   name="account_holder_name"
                                   class="bank-control-input"
                                   maxlength="150"
                                   autocomplete="name"
                                   placeholder="e.g. Saffron Table LLC"
                                   required>
                            <p class="bank-help">Must match the name registered with your bank.</p>
                        </div>

                        <!-- Account Type -->
                        <!-- <div class="bank-field">
                            <label for="bank-account_type">
                                Account type <span class="bank-req">*</span>
                            </label>
                            <select id="bank-account_type" class="bank-control-input bank-select">
                                <option value="business_checking" selected>Business checking</option>
                                <option value="business_savings">Business savings</option>
                                <option value="individual_checking">Individual checking</option>
                                <option value="individual_savings">Individual savings</option>
                            </select>
                        </div> -->

                        <!-- Routing Number -->
   <div class="bank-field">
    <label for="bank-routing_number">
        Routing number <span class="bank-req">*</span>
    </label>

    <div class="bank-input-wrap bank-input-with-eye">
        <input id="bank-routing_number"
               name="routing_number"
               type="password"
               inputmode="numeric"
               pattern="[0-9]{9}"
               maxlength="9"
               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
               class="bank-control-input"
               placeholder="•••••••••"
               title="Please enter a valid 9-digit US bank routing number"
               required>

        <button type="button"
                class="bank-toggle-eye-btn"
                data-target="bank-routing_number"
                aria-label="Toggle routing number visibility">
            <svg width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </button>
    </div>

    <p class="bank-help">9-digit US bank routing number.</p>
</div>

                        <!-- Bank Account Number -->
                        <div class="bank-field">
                            <label for="bank-account_number">
                                Bank account number <span id="bank-account-required" class="bank-req">*</span>
                            </label>
                            <div class="bank-input-wrap bank-input-with-eye">
                                <input id="bank-account_number"
                                       name="account_number"
                                       type="password"
                                       inputmode="numeric"
                                       pattern="[0-9]{4,17}"
                                       maxlength="17"
                                       autocomplete="off"
                                       class="bank-control-input"
                                       placeholder="••••••••••••"
                                       required>
                                <button type="button" class="bank-toggle-eye-btn" data-target="bank-account_number" aria-label="Toggle account number visibility">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="bank-help" id="bank-account-help">Enter an account number containing 4 to 17 digits.</p>
                        </div>

                        <!-- Re-enter Account Number -->
                        <div class="bank-field">
                            <label for="bank-account_number_confirmation">
                                Re-enter account number <span id="bank-confirm-required" class="bank-req">*</span>
                            </label>
                            <div class="bank-input-wrap bank-input-with-eye">
                                <input id="bank-account_number_confirmation"
                                       name="account_number_confirmation"
                                       type="password"
                                       inputmode="numeric"
                                       maxlength="17"
                                       autocomplete="off"
                                       class="bank-control-input"
                                       placeholder="••••••••••••"
                                       required>
                                <button type="button" class="bank-toggle-eye-btn" data-target="bank-account_number_confirmation" aria-label="Toggle confirmation account number visibility">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <p class="bank-help" id="bank-confirm-match-note" hidden>Account numbers match.</p>
                        </div>
                    </div>

                    <!-- Column 2: Company Information -->
                    <div class="bank-card">
                        <h2 class="bank-card-title">Company information</h2>
                        <p class="bank-card-sub">Use the legal business address associated with this account.</p>

                        <!-- Legal Company Name -->
                        <div class="bank-field">
                            <label for="bank-business_legal_name">
                                Legal company name <span class="bank-req">*</span>
                            </label>
                            <input id="bank-business_legal_name"
                                   name="business_legal_name"
                                   class="bank-control-input"
                                   maxlength="150"
                                   autocomplete="organization"
                                   placeholder="e.g. Saffron Table LLC"
                                   required>
                        </div>

                        <!-- DBA / Doing Business As -->
                        <div class="bank-field">
                            <label for="bank-doing_business_as">
                                DBA / Doing Business As (optional)
                            </label>
                            <input id="bank-doing_business_as"
                                   name="doing_business_as"
                                   class="bank-control-input"
                                   maxlength="150"
                                   placeholder="e.g. Saffron Table">
                        </div>

                        <!-- Street Address -->
                        <div class="bank-field">
                            <label for="bank-address">
                                Street address <span class="bank-req">*</span>
                            </label>
                            <input id="bank-address"
                                   name="address"
                                   class="bank-control-input"
                                   maxlength="255"
                                   autocomplete="street-address"
                                   placeholder="128 Atlantic Avenue"
                                   required>
                        </div>

                        <!-- Country -->
                        <div class="bank-field">
                            <label for="bank-country_id">
                                Country <span class="bank-req">*</span>
                            </label>
                            <select id="bank-country_id"
                                    name="country_id"
                                    class="bank-control-input bank-select"
                                    autocomplete="country"
                                    required>
                                <option value="">Select country</option>
                            </select>
                        </div>

                        <!-- State -->
                        <div class="bank-field">
                            <label for="bank-state_id">
                                State <span class="bank-req">*</span>
                            </label>
                            <select id="bank-state_id"
                                    name="state_id"
                                    class="bank-control-input bank-select"
                                    autocomplete="address-level1"
                                    required
                                    disabled>
                                <option value="">Select state</option>
                            </select>
                        </div>

                        <!-- City & ZIP code in 2-col -->
                        <div class="bank-row-2col">
                            <div class="bank-field">
                                <label for="bank-city_id">
                                    City
                                </label>
                                <select id="bank-city_id"
                                        name="city_id"
                                        class="bank-control-input bank-select"
                                        autocomplete="address-level2"
                                        disabled>
                                    <option value="">Select city</option>
                                </select>
                            </div>

  <div class="bank-field">
    <label for="bank-postal_code">
        ZIP code <span class="bank-req">*</span>
    </label>
    <input id="bank-postal_code"
           name="postal_code" minlength="5" maxlength="9"
           type="text"
           class="bank-control-input"
           pattern="[0-9]{5,9}"
           inputmode="numeric"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           autocomplete="postal-code"
           placeholder="11201"
           title="Please enter a valid ZIP code with 5 to 9 digits"
           required>
</div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation & Platform Commission Agreement (Figma reference) -->
                <div class="bank-agreement-card">
                    <!-- Ownership confirmation -->
                    <label class="bank-checkbox-row">
                        <input type="checkbox" id="bank-confirm-ownership" required aria-describedby="bank-ownership-error">
                        <span class="bank-checkbox-label">
                            I confirm this account belongs to <span id="bank-confirm-org-name">{{ $restaurantName }}</span> and is authorized to receive Dawat payouts.
                            <span class="bank-req">*</span>
                        </span>
                    </label>
                    <p class="bank-error" id="bank-ownership-error" hidden></p>

                    <!-- Platform commission (managed dynamically by backend settings) -->
                    <div id="bank-commission" hidden>
                        <label class="bank-checkbox-row">
                            <input type="checkbox"
                                   id="bank-accept_platform_commission"
                                   name="accept_platform_commission"
                                   value="1"
                                   required
                                   aria-describedby="bank-commission-help bank-commission-error">
                            <span class="bank-checkbox-label">
                                <span id="bank-commission-label">I agree to the platform commission applied to every order amount.</span>
                                <span class="bank-req">*</span>
                                <span class="bank-checkbox-sub" id="bank-commission-help"></span>
                            </span>
                        </label>
                        <p class="bank-error" id="bank-commission-error" hidden></p>
                    </div>
                </div>

                <!-- Need a help? Support Card -->
                <a href="mailto:support@dawat.com?subject={{ rawurlencode('Banking Information Assistance') }}" class="bank-support-card-link">
                    <div class="bank-support-left">
                        <div aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="bank-support-title">Need a help?</h4>
                            <p class="bank-support-sub">Chat with our partner support team.</p>
                        </div>
                    </div>
                    <div aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"></line>
                            <polyline points="7 7 17 7 17 17"></polyline>
                        </svg>
                    </div>
                </a>

                <!-- CTA Actions -->
                <div class="bank-actions-row">
                    <button class="bank-save-btn" type="submit" id="bank-save">
                        <span>Submit banking information</span>
                    </button>
                    <p class="bank-subnote">
                        Finish setup, then submit for review.
                    </p>
                </div>
            </fieldset>
        </form>
<!-- 
        @if(!$stepEditable)
            <div class="bank-nav-between">
                <a href="{{ route('restaurant.onboarding.kyc') }}" class="bank-back-link">
                    ← Back to onboarding steps
                </a>
                <a href="{{ route('restaurant.onboarding.kyc', ['step' => 'tax']) }}" class="bank-back-link" style="color: #FF6210; font-weight: 600;">
                    Next: Tax information →
                </a>
            </div>
        @endif -->

        <noscript>
            <p class="bank-error" style="margin-top: 20px;">Enable JavaScript to load and submit your banking information.</p>
        </noscript>
    </div>
</div>

<script>
(() => {
    const urls = {
        show: @json(route('api.restaurant.me.banking.show')),
        store: @json(route('api.restaurant.me.banking.store')),
        update: @json(route('api.restaurant.me.banking.update')),
        countries: @json(route('api.countries.country')),
        states: @json(route('api.countries.states-by-country', ['countryId' => '__ID__'])),
        cities: @json(route('api.countries.cities-by-state', ['stateId' => '__ID__'])),
    };
    const form = document.getElementById('onboarding-bank-form');
    const fields = document.getElementById('bank-fields');
    const message = document.getElementById('bank-message');
    const retry = document.getElementById('bank-retry');
    const save = document.getElementById('bank-save');
    const input = name => form.elements.namedItem(name);
    const country = input('country_id'), state = input('state_id'), city = input('city_id');
    const confirmOrgSpan = document.getElementById('bank-confirm-org-name');
    const confirmMatchNote = document.getElementById('bank-confirm-match-note');
    const ownershipInput = document.getElementById('bank-confirm-ownership');
    const ownershipError = document.getElementById('bank-ownership-error');
    const setOwnershipError = text => {
        ownershipError.textContent = text || '';
        ownershipError.hidden = !text;
        if (text) ownershipInput.setAttribute('aria-invalid', 'true');
        else ownershipInput.removeAttribute('aria-invalid');
    };
    ownershipInput.addEventListener('change', () => { if (ownershipInput.checked) setOwnershipError(''); });
    let existing = false, readOnly = false, locationVersion = 0;
    let savedValues = '';

    // Platform commission agreement
    const commissionBox = document.getElementById('bank-commission');
    const commissionInput = document.getElementById('bank-accept_platform_commission');
    const commissionError = document.getElementById('bank-commission-error');
    let commission = null;
    const commissionNeeded = () => !!commission?.is_required;

    const setCommissionError = text => {
        commissionError.textContent = text || '';
        commissionError.hidden = !text;
        if (text) commissionInput.setAttribute('aria-invalid', 'true');
        else commissionInput.removeAttribute('aria-invalid');
    };
    commissionInput.addEventListener('change', () => { if (commissionInput.checked) setCommissionError(''); });

    // Eye toggle buttons
    document.querySelectorAll('.bank-toggle-eye-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (!targetInput) return;
            const isPassword = targetInput.type === 'password';
            targetInput.type = isPassword ? 'text' : 'password';
            btn.innerHTML = isPassword
                ? `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                   </svg>`
                : `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                   </svg>`;
        });
    });

    // Real-time update company name in confirmation checkbox
    input('business_legal_name')?.addEventListener('input', e => {
        if (confirmOrgSpan) confirmOrgSpan.textContent = e.target.value.trim() || 'your company';
    });

    const snapshot = () => JSON.stringify([...controls.map(control => [control.name, control.value.trim()]), ['ownership', ownershipInput.checked], ['commission', commissionInput.checked]]);

    function notify(text, error = false, success = false) {
        message.textContent = text;
        message.hidden = !text || error || success;
        message.dataset.error = String(error);
        message.dataset.success = String(success);
        if (error && text && window.onboardingToast) window.onboardingToast(text, 'error');
        if (success && text && window.onboardingToast) window.onboardingToast(text, 'success');
    }

    function notifyErrors(errors) {
        message.textContent = errors.join(' ');
        message.hidden = true;
        message.dataset.error = 'true';
        message.dataset.success = 'false';
        if (window.onboardingToasts) window.onboardingToasts(errors, 'error');
        else if (window.onboardingToast) window.onboardingToast(errors.join('\n'), 'error');
    }

    async function api(url, options = {}) {
        let response;
        try { response = await fetch(url, {
            credentials: 'same-origin', ...options,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': input('_token').value, ...options.headers },
        }); } catch {
            throw new Error('Unable to connect. Please check your internet connection and try again.');
        }
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            const error = new Error(response.status === 419 || response.status === 401
                ? 'Your session expired. Refresh the page and sign in again.'
                : (response.status >= 500
                    ? 'Banking information is temporarily unavailable. Please try again.'
                    : (data.message || 'Unable to load or save banking information. Please try again.')));
            error.errors = data.errors || {};
            throw error;
        }
        return data;
    }

    function options(select, rows, label, selected = '') {
        select.replaceChildren(new Option(label, ''));
        for (const row of rows) select.add(new Option(row.name, String(row.id)));
        select.value = String(selected ?? '');
        select.disabled = rows.length === 0;
    }

    function accountRequirements() {
        const accNum = input('account_number').value;
        const confirmNum = input('account_number_confirmation').value;
        const required = !existing || accNum.length > 0 || confirmNum.length > 0;
        input('account_number').required = required;
        input('account_number_confirmation').required = required;
        document.getElementById('bank-account-required').textContent = required ? '*' : '';
        document.getElementById('bank-confirm-required').textContent = required ? '*' : '';

        if (accNum && confirmNum && accNum === confirmNum) {
            confirmMatchNote.hidden = false;
            confirmMatchNote.className = 'bank-help is-match';
            confirmMatchNote.textContent = '✓ Account numbers match.';
        } else if (confirmNum && accNum !== confirmNum) {
            confirmMatchNote.hidden = false;
            confirmMatchNote.className = 'bank-error';
            confirmMatchNote.textContent = 'Account numbers do not match.';
        } else {
            confirmMatchNote.hidden = true;
        }

        input('account_number_confirmation').setCustomValidity(
            confirmNum && confirmNum !== accNum ? 'The account numbers do not match.' : ''
        );
    }
    for (const name of ['account_number', 'account_number_confirmation']) {
        input(name).addEventListener('input', event => {
            event.target.value = event.target.value.replace(/[^0-9]/g, '');
            accountRequirements();
        });
    }

    function clearFieldError(control) {
        const id = `bank-error-${control.name}`;
        document.getElementById(id)?.remove();
        control.removeAttribute('aria-invalid');
        const description = (control.getAttribute('aria-describedby') || '').split(' ').filter(value => value && value !== id).join(' ');
        if (description) control.setAttribute('aria-describedby', description);
        else control.removeAttribute('aria-describedby');
    }

    function fieldError(control, text) {
        clearFieldError(control);
        const node = document.createElement('p');
        node.className = 'bank-error'; node.id = `bank-error-${control.name}`;
        node.textContent = text;
        control.setAttribute('aria-invalid', 'true');
        control.setAttribute('aria-describedby', [control.getAttribute('aria-describedby'), node.id].filter(Boolean).join(' '));
        control.closest('.bank-field')?.append(node);
    }

    function validationError(control) {
        const value = control.value.trim();
        const requiredMessages = {
            account_holder_name: 'Please enter the account holder’s name.',
            routing_number: 'Please enter your 9-digit routing number.',
            account_number: 'Please enter your bank account number.',
            account_number_confirmation: 'Please re-enter your bank account number.',
            business_legal_name: 'Please enter the business legal entity name.',
            address: 'Please enter your business address.',
            country_id: 'Please select a country.', state_id: 'Please select a state.', city_id: 'Please select a city.',
            postal_code: 'Please enter your zip or postal code.',
        };
        if (control.required && !value) return requiredMessages[control.name] || 'Please complete this field.';
        if (control.name === 'postal_code' && value && !/^[0-9]{5,9}$/.test(value)) return 'Please enter a ZIP code with 5 to 9 digits.';
        if (control.name === 'routing_number' && value && !/^[0-9]{9}$/.test(value)) return 'The routing number must contain exactly 9 digits.';
        if (control.name === 'account_number' && value && !/^[0-9]{4,17}$/.test(value)) return 'The account number must contain 4 to 17 digits.';
        if (control.name === 'account_number_confirmation' && value && value !== input('account_number').value.trim()) return 'The account numbers do not match. Please re-enter the same account number.';
        if (control.maxLength > 0 && value.length > control.maxLength) return `Please enter no more than ${control.maxLength} characters.`;
        return '';
    }

    const controls = Array.from(form.querySelectorAll('.bank-field input, .bank-field select'));
    for (const control of controls) {
        const updateError = () => {
            if (control.hasAttribute('aria-invalid')) {
                const error = validationError(control);
                if (error) fieldError(control, error);
                else clearFieldError(control);
            }
            if (control.name === 'account_number' && input('account_number_confirmation').hasAttribute('aria-invalid')) {
                const confirmation = input('account_number_confirmation');
                const error = validationError(confirmation);
                if (error) fieldError(confirmation, error);
                else clearFieldError(confirmation);
            }
        };
        control.addEventListener('input', updateError);
        control.addEventListener('change', updateError);
    }

    function focusFirstError() {
        const control = form.querySelector('[aria-invalid="true"]');
        control?.focus();
        control?.scrollIntoView({ block: 'center' });
    }

    async function initialize() {
        fields.disabled = true;
        retry.hidden = true;
        notify('Loading banking information…');
        try {
            const [bank, countries] = await Promise.all([api(urls.show), api(urls.countries)]);
            const data = bank.data;
            existing = !!data;
            readOnly = ['approved', 'under_review'].includes(bank.onboarding_step?.status);
            for (const name of ['account_holder_name', 'routing_number', 'business_legal_name', 'doing_business_as', 'address', 'postal_code']) {
                input(name).value = data?.[name] ?? '';
            }
            if (confirmOrgSpan && data?.business_legal_name) {
                confirmOrgSpan.textContent = data.business_legal_name;
            }
            input('account_number').value = '';
            input('account_number_confirmation').value = '';
            document.getElementById('bank-account-help').textContent = existing
                ? `Saved account: ${data.account_number_masked}. Leave blank to keep saved.`
                : 'Enter an account number containing 4 to 17 digits.';
            options(country, countries.data, 'Select country', data?.country_id);
            options(state, [], 'Select state');
            options(city, [], 'Select city');
            if (country.value) {
                const result = await api(urls.states.replace('__ID__', encodeURIComponent(country.value)));
                options(state, result.states, 'Select state', data?.state_id);
                if (state.value) {
                    const result = await api(urls.cities.replace('__ID__', encodeURIComponent(state.value)));
                    options(city, result.cities, 'Select city', data?.city_id);
                }
            }
            commission = bank.platform_commission || null;
            commissionBox.hidden = !commission?.is_required;
            commissionInput.required = !!commission?.is_required;
            if (commission) {
                document.getElementById('bank-commission-label').textContent = commission.label;
                document.getElementById('bank-commission-help').textContent = commission.accepted && !commission.needs_reacceptance
                    ? `You agreed to ${commission.accepted_percentage}% on ${new Date(commission.accepted_at).toLocaleDateString()}.`
                    : (commission.needs_reacceptance
                        ? `The commission changed from ${commission.accepted_percentage}% to ${commission.percentage}%. Please agree to the new rate.`
                        : 'Required to submit or update your banking information.');
                commissionInput.checked = !!commission.accepted && !commission.needs_reacceptance;
            }
            setCommissionError('');
            accountRequirements();
            savedValues = snapshot();
            save.querySelector('span').textContent = existing ? 'Update banking information' : 'Submit banking information';
            fields.disabled = readOnly;
            save.hidden = readOnly;
            notify('');
        } catch (error) {
            notify(error.message, true);
            retry.hidden = false;
        }
    }
    retry.addEventListener('click', initialize);

    async function changeLocation(parent, target, url, key, label) {
        const version = ++locationVersion;
        options(target, [], label);
        if (parent === country) options(city, [], 'Select city');
        save.disabled = true;
        notify('');
        try {
            if (parent.value) {
                const result = await api(url.replace('__ID__', encodeURIComponent(parent.value)));
                if (version === locationVersion) options(target, result[key], label);
            }
        } catch (error) {
            if (version === locationVersion) notify(error.message + ' Select the location again to retry.', true);
        } finally {
            if (version === locationVersion) save.disabled = false;
        }
    }
    country.addEventListener('change', () => changeLocation(country, state, urls.states, 'states', 'Select state'));
    state.addEventListener('change', () => changeLocation(state, city, urls.cities, 'cities', 'Select city'));

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (readOnly || fields.disabled || save.disabled) return;
        if (existing && snapshot() === savedValues) {
            notify('No changes were made. Please update at least one field before submitting again.', true);
            return;
        }
        accountRequirements();
        controls.forEach(clearFieldError);
        // Highlight every invalid field and show one toast per error.
        const errors = [];
        for (const control of controls) {
            const error = validationError(control);
            if (error) {
                fieldError(control, error);
                errors.push(error);
            }
        }
        if (!ownershipInput.checked) {
            const ownershipText = 'Please confirm account ownership and authorization to receive Dawat payouts.';
            setOwnershipError(ownershipText);
            errors.push(ownershipText);
        } else {
            setOwnershipError('');
        }
        if (commissionNeeded() && !commissionInput.checked) {
            const commissionText = 'Please agree to the platform commission to continue.';
            setCommissionError(commissionText);
            errors.push(commissionText);
        } else {
            setCommissionError('');
        }
        if (errors.length) {
            notifyErrors(errors);
            if (form.querySelector('[aria-invalid="true"]')) focusFirstError();
            else commissionInput.focus();
            return;
        }
        setCommissionError('');
        if (window.confirmOnboardingSubmission) {
            const confirmed = await window.confirmOnboardingSubmission(existing
                ? 'Update your banking information and submit it for review?'
                : 'Submit your banking information for review?');
            if (!confirmed) return;
        }
        const payload = Object.fromEntries(new FormData(form));
        delete payload._token;
        payload.city_id = city.value || null;
        if (commission?.is_required) payload.accept_platform_commission = commissionInput.checked;
        else delete payload.accept_platform_commission;
        if (existing && !payload.account_number && !payload.account_number_confirmation) {
            delete payload.account_number;
            delete payload.account_number_confirmation;
        }
        fields.disabled = true;
        notify('Saving banking information…');
        try {
            const result = await api(existing ? urls.update : urls.store, {
                method: existing ? 'PATCH' : 'POST',
                headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
            });
            const nextStepUrl = @json(route('restaurant.onboarding.kyc', ['step' => 'tax']));
            notify(result.message || 'Banking information submitted successfully for review.', false, true);
            setTimeout(() => {
                window.location.assign(nextStepUrl);
            }, 600);
        } catch (error) {
            fields.disabled = false;
            // Server validation: highlight every field it reports and toast each error.
            const serverErrors = [];
            for (const [name, errors] of Object.entries(error.errors || {})) {
                const control = input(name);
                const text = Array.isArray(errors) ? errors.join(' ') : String(errors);
                if (control?.closest('.bank-field')) fieldError(control, text);
                else if (name === 'accept_platform_commission') setCommissionError(text);
                serverErrors.push(text);
            }
            if (serverErrors.length) notifyErrors(serverErrors);
            else notify(error.message, true);
            if (form.querySelector('[aria-invalid="true"]')) focusFirstError();
            else if (!commissionError.hidden) commissionInput.focus();
        }
    });
    initialize();
})();
</script>
