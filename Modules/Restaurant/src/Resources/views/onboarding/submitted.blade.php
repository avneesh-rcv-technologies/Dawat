{{--
    submitted.blade.php
    "You're on the list. We're on it." — post-KYC submission confirmation page
    Included by status.blade.php when ?step=submitted
--}}
@php
    $ownerName   = $correctionRestaurant->admin?->name ?? 'Partner';
    $ownerFirst  = explode(' ', trim($ownerName))[0];
    $ownerEmail  = $correctionRestaurant->admin?->email ?? '';
    $restName    = $timeline['restaurant']['name'] ?? 'your restaurant';
    $restId      = $correctionRestaurant->id ?? 0;
    $yearPart    = now()->format('y');   // e.g. 26
    $monthDay    = now()->format('md');  // e.g. 1117
    $appId       = 'DAW-' . str_pad($restId, 5, '0', STR_PAD_LEFT) . '-' . $yearPart . $monthDay;
    $submittedAt = now();               // best available; use real timestamp if stored
@endphp

<style>
    /* ── submitted.blade.php — prefix: sub- ── */
    .sub-wrap {
        min-height: 100vh;
        width: 100%;
        background: #0A0A0C;
        color: #fff;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 0 20px 80px;
        box-sizing: border-box;
    }
    .sub-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .sub-content-body {
        max-width: 680px;
        margin: 0 auto;
    }

    /* ── Header ── */
    .sub-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }
    .sub-back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #A1A1AA;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        margin-bottom: 24px;
        transition: color 0.15s, transform 0.15s;
    }
    .sub-back-link:hover { color: #fff; transform: translateX(-3px); }
    .sub-header-center {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sub-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .sub-store-badge {
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
    .sub-store-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #FF6210;
    }
    .sub-logout-btn {
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
        transition: color 0.15s, background 0.15s;
        font-family: inherit;
    }
    .sub-logout-btn:hover { color: #F43F5E; background: rgba(244, 63, 94, 0.08); }

    /* ── Hero ── */
    .sub-hero {
        margin-bottom: 36px;
    }
    .sub-hero-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(34px, 5vw, 52px);
        font-weight: 700;
        line-height: 1.1;
        color: #fff;
        margin: 0 0 14px;
        letter-spacing: -0.5px;
    }
    .sub-hero-sub {
        color: #A1A1AA;
        font-size: 15.5px;
        line-height: 1.65;
        margin: 0;
    }
    .sub-hero-name { color: #fff; font-weight: 600; }

    /* ── Review Card (amber dark) ── */
    .sub-review-card {
        background: #120E0A;
        border: 1px solid #3A2510;
        border-radius: 20px;
        padding: 28px 28px 24px;
        margin-bottom: 36px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .sub-review-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at 50% 0%, rgba(255, 98, 16, 0.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .sub-review-icon-wrap {
        width: 64px; height: 64px;
        border-radius: 20px;
        background: rgba(255, 98, 16, 0.12);
        border: 1px solid rgba(255, 98, 16, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        position: relative;
    }
    .sub-review-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255, 98, 16, 0.15);
        border: 1px solid rgba(255, 98, 16, 0.3);
        border-radius: 9999px;
        padding: 5px 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: #FF6210;
        margin-bottom: 16px;
    }
    .sub-review-badge-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #FF6210;
        animation: sub-pulse 2s infinite;
    }
    @keyframes sub-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.85); }
    }
    .sub-review-timeline-label {
        font-size: 22px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 10px;
        font-family: 'Playfair Display', Georgia, serif;
    }
    .sub-review-meta {
        font-size: 13px;
        color: #6B6B75;
        line-height: 1.8;
    }
    .sub-review-meta span { display: block; }

    /* ── Section heading ── */
    .sub-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 20px;
    }

    /* ── Timeline ── */
    .sub-timeline {
        margin-bottom: 32px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .sub-tl-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        position: relative;
    }
    .sub-tl-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 19px;
        top: 40px;
        width: 2px;
        height: calc(100% - 8px);
        background: #232328;
    }
    .sub-tl-left {
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 2px;
    }
    .sub-tl-icon {
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }
    .sub-tl-icon.done   { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); }
    .sub-tl-icon.active { background: rgba(255, 98, 16, 0.12);  border: 1px solid rgba(255, 98, 16, 0.3); }
    .sub-tl-icon.idle   { background: #141418; border: 1px solid #232328; }
    .sub-tl-body {
        padding-bottom: 32px;
        flex: 1;
        min-width: 0;
    }
    .sub-tl-item:last-child .sub-tl-body { padding-bottom: 0; }
    .sub-tl-label {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
        margin: 0 0 4px;
        padding-top: 8px;
    }
    .sub-tl-label.done  { color: #34D399; }
    .sub-tl-label.muted { color: #A1A1AA; }
    .sub-tl-desc {
        font-size: 13.5px;
        color: #71717A;
        margin: 0;
        line-height: 1.55;
    }
    .sub-tl-time {
        font-size: 12px;
        color: #52525B;
        margin-top: 3px;
    }

    /* ── Info Cards ── */
    .sub-info-card {
        background: #141418;
        border: 1px solid #232328;
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 16px;
        text-decoration: none;
        transition: border-color 0.2s, background 0.2s;
    }
    .sub-info-card:hover { border-color: #383842; background: #17171B; }
    .sub-info-card-icon {
        width: 44px; height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sub-info-card-icon.orange { background: rgba(255, 98, 16, 0.1); }
    .sub-info-card-icon.blue   { background: rgba(99, 102, 241, 0.1); }
    .sub-info-card-body { flex: 1; min-width: 0; }
    .sub-info-card-title {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 600;
        color: #fff;
    }
    .sub-info-card-desc {
        margin: 0;
        font-size: 13.5px;
        color: #8E8E93;
        line-height: 1.55;
    }
    .sub-info-card-arrow {
        color: #FF6210;
        display: flex;
        align-items: center;
        margin-left: 8px;
        flex-shrink: 0;
        align-self: center;
    }

    /* ── CTA ── */
    .sub-cta-wrap {
        padding-top: 32px;
        border-top: 1px solid #1E1E24;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        margin-top: 36px;
    }
    .sub-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 54px;
        padding: 0 42px;
        background: #FF5500;
        color: #fff;
        border: none;
        border-radius: 9999px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        box-shadow: 0 4px 20px rgba(255, 85, 0, 0.28);
        transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        min-width: 260px;
    }
    .sub-cta-btn:hover {
        background: #FF6611;
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(255, 85, 0, 0.38);
    }
    .sub-cta-footnote {
        font-size: 13px;
        color: #52525B;
        text-align: center;
        margin: 0;
    }
    .sub-cta-footnote a { color: #A1A1AA; text-decoration: none; }

    /* ── Responsive ── */
    @media (max-width: 600px) {
        .sub-header-center img { height: 22px !important; }
        .sub-review-card { padding: 22px 18px 20px; }
        .sub-cta-btn { min-width: unset; width: 100%; }
        .sub-info-card { padding: 16px 18px; }
    }
</style>

<div class="sub-wrap">
    <div class="sub-container">

        {{-- ── Header ── --}}
        <header class="sub-header">
            <div class="sub-header-center">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
            </div>
            <div class="sub-header-right">
              
                <form action="{{ route('admin.logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="sub-logout-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- ── Hero & Content ── --}}
        <div class="sub-content-body">
            <div class="sub-hero">
                <a href="{{ route('restaurant.onboarding.kyc') }}" class="sub-back-link" aria-label="Back to onboarding steps">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Back to onboarding steps</span>
                </a>

                <h1 class="sub-hero-title">You're on the list.<br>We're on it.</h1>
            <p class="sub-hero-sub">
                Thanks, <span class="sub-hero-name">{{ $ownerFirst }}</span>. Your application is submitted
                and <span class="sub-hero-name">{{ $restName }}</span> is now under review.
            </p>
        </div>

        {{-- ── Review Status Card ── --}}
        <div class="sub-review-card">
            {{-- Hourglass icon --}}
            <div class="sub-review-icon-wrap">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 22h14"></path>
                    <path d="M5 2h14"></path>
                    <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"></path>
                    <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>
                </svg>
            </div>

            {{-- Badge --}}
            <div>
                <span class="sub-review-badge">
                    <span class="sub-review-badge-dot"></span>
                    Under Review · Pending Approval
                </span>
            </div>

            <p class="sub-review-timeline-label">Usually 1–3 business days</p>

            <div class="sub-review-meta">
                <!-- <span>Application {{ $appId }}</span> -->
                <span>Submitted {{ $submittedAt->format('F j, Y') }}</span>
            </div>
        </div>

        {{-- ── What Happens Next ── --}}
        <h2 class="sub-section-title">What happens next</h2>

        <div class="sub-timeline">

            {{-- Step 1: Received --}}
            <div class="sub-tl-item">
                <div class="sub-tl-left">
                    <div class="sub-tl-icon done">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="sub-tl-body">
                    <p class="sub-tl-label done">Application received</p>
                    <p class="sub-tl-time">{{ $submittedAt->format('M j, Y') }} · {{ $submittedAt->format('g:i A') }} {{ $submittedAt->format('T') }}</p>
                </div>
            </div>

            {{-- Step 2: Business & payout review --}}
            <div class="sub-tl-item">
                <div class="sub-tl-left">
                    <div class="sub-tl-icon active">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="sub-tl-body">
                    <p class="sub-tl-label muted">Business &amp; payout review</p>
                    <p class="sub-tl-desc">Documents, W-9 and bank account checks are in progress.</p>
                </div>
            </div>

            {{-- Step 3: Approval & launch --}}
            <div class="sub-tl-item">
                <div class="sub-tl-left">
                    <div class="sub-tl-icon idle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                            <line x1="4" y1="22" x2="4" y2="15"></line>
                        </svg>
                    </div>
                </div>
                <div class="sub-tl-body">
                    <p class="sub-tl-label muted">Approval &amp; launch</p>
                    <p class="sub-tl-desc">Once approved, you choose when to start receiving orders.</p>
                </div>
            </div>

        </div>

        {{-- ── Store Offline Warning ── --}}
        <div class="sub-info-card" style="border-color: rgba(255, 98, 16, 0.2); background: rgba(255, 98, 16, 0.04);">
            <div class="sub-info-card-icon orange">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3l2-4h14l2 4"></path>
                    <path d="M5 21V10.85M19 21V10.85M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path>
                </svg>
            </div>
            <div class="sub-info-card-body">
                <p class="sub-info-card-title" style="color: #FF6210;">Your store is still offline</p>
                <p class="sub-info-card-desc">You won't receive orders during review. If we need more information, we'll contact you by email.</p>
            </div>
        </div>

        {{-- ── Need a hand ── --}}
        @if($ownerEmail)
        <a href="mailto:partner-support@dawat.com?subject=Onboarding%20Help%20%E2%80%94%20{{ urlencode($appId) }}&body=Hi%20Dawat%20support%2C%0A%0AMy%20application%20ID%20is%20{{ urlencode($appId) }}." class="sub-info-card">
        @else
        <a href="mailto:partner-support@dawat.com" class="sub-info-card">
        @endif
            <div class="sub-info-card-icon orange">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3v5z"></path>
                    <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3v5z"></path>
                </svg>
            </div>
            <div class="sub-info-card-body">
                <p class="sub-info-card-title">Need a help?</p>
                <p class="sub-info-card-desc">Chat with our partner support team.</p>
            </div>
            <div class="sub-info-card-arrow">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="7" y1="17" x2="17" y2="7"></line>
                    <polyline points="7 7 17 7 17 17"></polyline>
                </svg>
            </div>
        </a>

        {{-- ── CTA ── --}}
        <div class="sub-cta-wrap">
            <a href="{{ route('restaurant.onboarding.kyc') }}" class="sub-cta-btn">
                Back to onboarding steps
            </a>
            @if($ownerEmail)
            <p class="sub-cta-footnote">We'll email <a href="mailto:{{ $ownerEmail }}">{{ $ownerEmail }}</a> with updates.</p>
            @endif
        </div>

        </div>{{-- /sub-content-body --}}
    </div>{{-- /sub-container --}}
</div>{{-- /sub-wrap --}}
