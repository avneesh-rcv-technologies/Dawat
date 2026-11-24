{{--
    verified.blade.php
    "Your next chapter is served." — Approved/Verified store launch page
    Included by status.blade.php when store is approved or ?step=approved / ?step=verified
--}}
@php
    $restName     = $timeline['restaurant']['name'] ?? $correctionRestaurant->name ?? 'Your Restaurant';
    $ownerName    = $correctionRestaurant->admin?->name ?? 'Partner';
    $ownerFirst   = explode(' ', trim($ownerName))[0];
    $cuisine      = !empty($correctionRestaurant->cuisine_type) ? $correctionRestaurant->cuisine_type : 'Indian';
    $city         = $correctionRestaurant->city ?? 'Brooklyn';
    $state        = $correctionRestaurant->state ?? 'NY';
    $locationSub  = trim($city . ', ' . $state, ', ');
    
    // Approved date
    $approvedAtRaw = $timeline['restaurant']['approved_at'] ?? $correctionRestaurant->approved_at;
    $approvedDate  = $approvedAtRaw ? \Carbon\Carbon::parse($approvedAtRaw)->format('F j, Y') : now()->format('F j, Y');

    // Operating hours
    $openTime  = $correctionRestaurant->opening_time ? \Carbon\Carbon::parse($correctionRestaurant->opening_time)->format('g:i A') : '11:30 AM';
    $closeTime = $correctionRestaurant->closing_time ? \Carbon\Carbon::parse($correctionRestaurant->closing_time)->format('g:i A') : '10:00 PM';
    $hoursText = "Daily · {$openTime}–{$closeTime} ET";

    // Fulfillment
    $fulfillment = [];
    if ($correctionRestaurant->is_delivery ?? true) $fulfillment[] = 'Delivery';
    if ($correctionRestaurant->is_takeaway ?? true) $fulfillment[] = 'customer pickup';
    $fulfillmentText = count($fulfillment) ? implode(' & ', $fulfillment) : 'Delivery & customer pickup';
@endphp

<style>
    /* ── verified.blade.php — prefix: ver- ── */
    .ver-wrap {
        min-height: 100vh;
        width: 100%;
        background: #0A0A0C;
        color: #fff;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 0 20px 80px;
        box-sizing: border-box;
    }
    .ver-container {
        max-width: 1040px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    .ver-content-body {
        max-width: 680px;
        margin: 0 auto;
    }

    /* ── Header ── */
    .ver-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        border-bottom: 1px solid #1E1E24;
        margin-bottom: 40px;
    }
    .ver-back-link {
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
    .ver-back-link:hover { color: #fff; transform: translateX(-3px); }
    .ver-header-center {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ver-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .ver-store-badge {
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
    .ver-store-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #10B981;
    }
    .ver-logout-btn {
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
    .ver-logout-btn:hover { color: #F43F5E; background: rgba(244, 63, 94, 0.08); }

    /* ── Hero ── */
    .ver-hero {
        margin-bottom: 32px;
    }
    .ver-hero-title {
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(34px, 5vw, 52px);
        font-weight: 700;
        line-height: 1.12;
        color: #fff;
        margin: 0 0 14px;
        letter-spacing: -0.5px;
    }
    .ver-hero-sub {
        color: #A1A1AA;
        font-size: 15.5px;
        line-height: 1.6;
        margin: 0;
    }

    /* ── Restaurant Banner Card ── */
    .ver-card {
        background: #141418;
        border: 1px solid #232328;
        border-radius: 22px;
        overflow: hidden;
        margin-bottom: 36px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }
    .ver-card-image-wrap {
        width: 100%;
        height: 240px;
        overflow: hidden;
        position: relative;
        background: #1c1c22;
    }
    .ver-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;
    }
    .ver-card:hover .ver-card-image {
        transform: scale(1.02);
    }
    .ver-card-image-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(10, 10, 12, 0.1) 0%, rgba(10, 10, 12, 0.65) 100%);
    }
    .ver-card-body {
        padding: 22px 26px 24px;
    }
    .ver-card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }
    .ver-card-name {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.3px;
    }
    .ver-badge-approved {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 9999px;
        padding: 5px 14px;
        font-size: 12.5px;
        font-weight: 600;
        color: #34D399;
    }
    .ver-card-meta {
        font-size: 14px;
        color: #A1A1AA;
        margin: 0 0 4px;
    }
    .ver-card-date {
        font-size: 13px;
        color: #71717A;
        margin: 0;
    }

    /* ── Checklist Section ── */
    .ver-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .ver-section-title {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        margin: 0;
    }
    .ver-section-count {
        font-size: 13.5px;
        font-weight: 500;
        color: #8E8E93;
    }
    .ver-checklist {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 32px;
    }
    .ver-check-item {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .ver-check-icon {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        color: #34D399;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ver-check-text {
        font-size: 15px;
        color: #E4E4E7;
        font-weight: 500;
    }

    /* ── Launch Settings Card ── */
    .ver-settings-card {
        background: #141418;
        border: 1px solid #232328;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .ver-settings-title {
        font-size: 17px;
        font-weight: 700;
        color: #fff;
        margin: 0 0 14px;
    }
    .ver-settings-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 18px;
    }
    .ver-settings-line {
        font-size: 14px;
        color: #D4D4D8;
        margin: 0;
        line-height: 1.55;
    }
    .ver-settings-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #FF6210;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.15s, transform 0.15s;
    }
    .ver-settings-link:hover {
        color: #FF7730;
        transform: translateX(2px);
    }

    /* ── Readiness Checkbox ── */
    .ver-confirm-label {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        cursor: pointer;
        user-select: none;
        margin-bottom: 24px;
    }
    .ver-checkbox-input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }
    .ver-custom-checkbox {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: #FF5500;
        border: 1.5px solid #FF5500;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
        transition: background 0.15s, border-color 0.15s;
    }
    .ver-checkbox-input:not(:checked) + .ver-custom-checkbox {
        background: #141418;
        border-color: #383842;
    }
    .ver-custom-checkbox svg {
        display: block;
    }
    .ver-checkbox-input:not(:checked) + .ver-custom-checkbox svg {
        display: none;
    }
    .ver-confirm-text {
        font-size: 14.5px;
        color: #D4D4D8;
        line-height: 1.55;
    }

    /* ── Decide when to open notice ── */
    .ver-notice-card {
        background: rgba(255, 98, 16, 0.04);
        border: 1px solid rgba(255, 98, 16, 0.22);
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 36px;
    }
    .ver-notice-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 98, 16, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #FF6210;
    }
    .ver-notice-body {
        flex: 1;
        min-width: 0;
    }
    .ver-notice-title {
        font-size: 15px;
        font-weight: 600;
        color: #FF6210;
        margin: 0 0 4px;
    }
    .ver-notice-desc {
        font-size: 13.5px;
        color: #A1A1AA;
        line-height: 1.55;
        margin: 0;
    }

    /* ── Primary Action ── */
    .ver-cta-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        border-top: 1px solid #1E1E24;
        padding-top: 32px;
    }
    .ver-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 480px;
        height: 54px;
        background: #FF5500;
        color: #fff;
        border: none;
        border-radius: 9999px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 22px rgba(255, 85, 0, 0.32);
        transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        font-family: inherit;
    }
    .ver-cta-btn:hover {
        background: #FF6611;
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(255, 85, 0, 0.42);
    }
    .ver-cta-subnote {
        font-size: 13px;
        color: #71717A;
        margin: 0;
        text-align: center;
    }

    /* ── Responsive ── */
    @media (max-width: 600px) {
        .ver-header-center img { height: 22px !important; }
        .ver-card-image-wrap { height: 180px; }
        .ver-card-body { padding: 18px 20px; }
        .ver-card-name { font-size: 20px; }
    }
</style>

<div class="ver-wrap">
    <div class="ver-container">

        {{-- ── Top Header ── --}}
        <header class="ver-header">
            <div class="ver-header-center">
                <a href="{{ route('restaurant.onboarding.kyc') }}" style="display: inline-flex; align-items: center; text-decoration: none;" aria-label="Dawat Home">
                    <img src="{{ asset('dawat-logo-white.png') }}" alt="Dawat" style="height: 28px; width: auto; max-width: 120px; object-fit: contain; display: block;" onerror="this.onerror=null; this.src='{{ asset('website/images/dawat-wordmark.png') }}';">
                </a>
            </div>
            <div class="ver-header-right">
             
                <form action="{{ route('admin.logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="ver-logout-btn">
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
        <div class="ver-content-body">
            <div class="ver-hero">
                <!-- <a href="{{ route('restaurant.onboarding.kyc') }}" class="ver-back-link" aria-label="Back to verified onboarding status">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Back to onboarding steps</span>
                </a> -->

                <h1 class="ver-hero-title">Your next chapter<br>is served.</h1>
            <p class="ver-hero-sub">
                <strong style="color: #fff;">{{ $restName }}</strong> is approved. Your kitchen is ready for its Dawat debut.
            </p>
        </div>

        {{-- ── Approved Card with Banner Food Image ── --}}
        <div class="ver-card">
            <div class="ver-card-image-wrap">
                <img src="https://images.unsplash.com/photo-1585937421612-70a008356fbe?auto=format&fit=crop&w=1200&q=80" 
                     onerror="this.onerror=null; this.src='{{ asset('banner.png') }}';" 
                     alt="{{ $restName }}" 
                     class="ver-card-image">
                <div class="ver-card-image-overlay"></div>
            </div>
            <div class="ver-card-body">
                <div class="ver-card-header-row">
                    <h2 class="ver-card-name">{{ $restName }}</h2>
                    <span class="ver-badge-approved">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Approved
                    </span>
                </div>
                <p class="ver-card-meta">{{ $locationSub }}</p>
                <p class="ver-card-date">Approved {{ $approvedDate }}</p>
            </div>
        </div>

        {{-- ── All Set. All Verified Checklist ── --}}
        <div class="ver-section-header">
            <h3 class="ver-section-title">All set. All verified.</h3>
            <span class="ver-section-count">6 of 6</span>
        </div>

        <div class="ver-checklist">
            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Restaurant profile</span>
            </div>

            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Order preferences</span>
            </div>

            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Menu details</span>
            </div>

            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Banking information</span>
            </div>

            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Business tax information</span>
            </div>

            <div class="ver-check-item">
                <div class="ver-check-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16 9 10 15 7 12"></polyline>
                    </svg>
                </div>
                <span class="ver-check-text">Business verification documents</span>
            </div>
        </div>

        <!-- {{-- ── Your Launch Settings ── --}}
        <div class="ver-settings-card">
            <h4 class="ver-settings-title">Your launch settings</h4>
            <div class="ver-settings-list">
                <p class="ver-settings-line">{{ $fulfillmentText }}</p>
                <p class="ver-settings-line">{{ $hoursText }}</p>
                <p class="ver-settings-line">Orders sent to your Dawat tablet</p>
                <p class="ver-settings-line">Preparation time · 25–35 minutes</p>
            </div>
            <a href="{{ route('dashboard.index') }}" class="ver-settings-link">
                <span>Edit settings</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>  
            </a>
        </div> -->

        {{-- ── Readiness Checkbox ── --}}
        <label class="ver-confirm-label">
            <input type="checkbox" checked class="ver-checkbox-input" id="ver-ready-check">
            <span class="ver-custom-checkbox">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </span>
            <span class="ver-confirm-text">
                My team and order device are ready to accept orders during service hours.
            </span>
        </label>

        {{-- ── You Decide When to Open Notice ── --}}
        <div class="ver-notice-card">
            <div class="ver-notice-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF6210" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                    <line x1="12" y1="2" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="ver-notice-body">
                <h5 class="ver-notice-title">You decide when to open</h5>
                <p class="ver-notice-desc">
                    Going live makes your menu visible to guests. Orders begin during your next available service hours.
                </p>
            </div>
        </div>

        {{-- ── CTA Action ── --}}
        <div class="ver-cta-wrap">
            <a href="{{ route('dashboard.index') }}" class="ver-cta-btn" id="ver-go-live-btn">
                🎉  Go live on Dawat
            </a>
            <p class="ver-cta-subnote">Your restaurant stays offline until you choose to go live.</p>
        </div>

    </div>{{-- /ver-container --}}
</div>{{-- /ver-wrap --}}
