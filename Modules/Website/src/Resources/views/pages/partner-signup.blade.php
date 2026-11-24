@extends('website::layouts.app')

@section('page_class', 'partner-auth-page')

@php
    $authMode = $authMode ?? (($isLogin ?? false) ? 'login' : 'signup');
    $isLogin = $authMode !== 'signup';
    $authTitle = match ($authMode) {
        'login' => 'Partner Login', 'forgot' => 'Forgot password?', 'reset' => 'Reset password', default => 'Sign Up',
    };
@endphp

@section('title', $authTitle.' — Dawat')
@section('meta_description', $isLogin ? 'Log in to your Dawat restaurant partner account.' : 'Create your Dawat partner account and take the next step towards bringing your restaurant to more food lovers.')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ substr(hash_file('sha256', public_path('css/toast.css')), 0, 12) }}">
<style>
    .partner-auth-page > footer { display: none; }
    .partner-auth-scene { height: 100vh; overflow: hidden; }
    .partner-auth-hero::before { content: ''; position: absolute; inset: -8px; background: linear-gradient(180deg, rgba(0,0,0,.52), rgba(0,0,0,.68) 65%, #000), url("{{ \Modules\Website\Support\WebsiteImages::url('website/images/enjoying-street-food-fest 1 (1).png') }}") center 45% / cover; filter: blur(1.5px); }
    .partner-terms-checkbox { appearance: none; border: 1px solid #a8a8a8; border-radius: 3px; background: #fff; }
    .partner-terms-checkbox:checked { border-color: #FF6210; background-color: #FF6210; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m3 8 3 3 7-7'/%3E%3C/svg%3E"); background-position: center; background-repeat: no-repeat; background-size: 12px; }

    body:has(.partner-signup-overlay) { overflow: hidden; }
    body:has(.partner-signup-overlay) > header,
    body:has(.partner-signup-overlay) > footer { pointer-events: none; }
    .partner-signup-overlay { position: fixed; inset: 0; z-index: 100; display: flex; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 48px 20px; background: rgba(0, 8, 12, .72); backdrop-filter: blur(5px); }
    .partner-signup-card { position: relative; width: 100%; max-width: 500px; margin-block: auto; padding: 42px 24px 28px; border-radius: 24px; background: #fff; color: #111; font-family: Urbanist, sans-serif; }
    .partner-signup-card [hidden] { display: none !important; }
    .partner-signup-card button:disabled { opacity: .55; cursor: wait; }
    .partner-signup-card input[aria-invalid="true"] { border-color: #b91c1c; }
    .partner-auth-error { display: block; margin-top: 6px; color: #b91c1c; font-size: 13px; font-weight: 400; line-height: 1.5; text-align: left; }
    .partner-auth-status { margin-top: 12px; font-size: 14px; line-height: 1.5; }
    .partner-auth-status[data-error="true"] { color: #b91c1c; font-size: 13px; font-weight: 400; line-height: 1.5; text-align: left; }
    .partner-email-status { display: block; margin-top: 6px; font-size: 13px; font-weight: 500; color: #666; }
    .partner-email-status[data-state="valid"] { color: #166534; }
    .partner-email-status[data-state="invalid"], .partner-email-status[data-state="error"] { color: #b91c1c; font-weight: 400; }
    .partner-login-options { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 16px; font-size: 14px; }
    .partner-login-remember { display: inline-flex; align-items: center; gap: 8px; font-weight: 400; cursor: pointer; }
    .partner-login-remember input { width: 18px; height: 18px; margin: 0; flex-shrink: 0; cursor: pointer; }
    .partner-auth-secondary { display: block; margin: 16px auto 0; color: #111; text-decoration: underline; background: none; border: 0; cursor: pointer; }
    .partner-information-back { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 22px; font-size: 14px; }
    #partner-otp-panel h2, #partner-signup-success h2 { margin-bottom: 16px; font-size: 24px; font-weight: 700; }
    #partner-otp-panel p, #partner-signup-success p { overflow-wrap: anywhere; }
    .partner-signup-card:focus { outline: none; }
    .partner-signup-close { position: absolute; top: 20px; right: 20px; display: grid; place-items: center; width: 24px; height: 24px; color: #111; }
    .partner-signup-card h1 { margin: 0 0 34px; text-align: center; font-size: 32px; font-weight: 700; line-height: 1.2; }
    .partner-signup-fields { display: grid; gap: 16px; }
    .partner-signup-fields label { display: block; font-size: 14px; font-weight: 600; }
    .partner-signup-fields input { display: block; width: 100%; height: 48px; margin-top: 8px; padding: 0 16px; border: 1px solid #d0d0d0; border-radius: 12px; background: white; color: #111; font: 500 16px Urbanist, sans-serif; }
    .partner-signup-password { position: relative; margin-top: 8px; }
    .partner-signup-password input { margin-top: 0; padding-right: 52px; }
    .partner-signup-eye { position: absolute; top: 4px; right: 4px; display: grid; place-items: center; width: 40px; height: 40px; border: 0; border-radius: 8px; background: transparent; color: #777; cursor: pointer; }
    .partner-signup-eye:hover { color: #ff6210; }
    .partner-signup-eye .eye-slash { display: none; }
    .partner-signup-eye[aria-pressed="true"] .eye-slash { display: block; }
    .partner-signup-fields input::placeholder { color: #a0a0a0; }
    .partner-signup-fields input:focus { outline: 2px solid #ff6210; outline-offset: 2px; }
    .partner-signup-agreement { display: flex; align-items: flex-start; gap: 8px; margin-top: 16px; color: #707070; font-size: 14px; line-height: 1.5; }
    .partner-signup-agreement input { width: 20px; height: 20px; flex-shrink: 0; margin-top: 1px; }
    .partner-signup-card a:not(.partner-signup-close) { color: #111; text-decoration: underline; text-underline-offset: 2px; }
    .partner-signup-submit { width: 100%; min-height: 56px; margin-top: 34px; border: 0; border-radius: 999px; background: #ff6210; color: white; font: 600 16px Urbanist, sans-serif; cursor: pointer; transition: background .2s; }
    .partner-signup-submit:hover { background: #e0530b; }
    .partner-signup-login { margin: 16px 0 0; text-align: center; color: #707070; font-size: 16px; }
    .partner-signup-card :focus-visible { outline: 2px solid #ff6210; outline-offset: 3px; }
    @media (max-width: 480px) {
        .partner-signup-overlay { padding: 20px 16px; }
        .partner-signup-card { padding: 40px 20px 24px; border-radius: 20px; }
        .partner-signup-card h1 { font-size: 30px; margin-bottom: 28px; }
        .partner-signup-agreement { font-size: 13px; }
        .partner-signup-submit { margin-top: 26px; }
        .partner-signup-login { font-size: 14px; }
    }
</style>
@endsection

@section('content')
<div class="partner-auth-scene" inert aria-hidden="true">
    <section class="partner-auth-hero relative isolate flex min-h-[390px] items-center justify-center overflow-hidden px-5 pb-12 pt-28 text-center sm:min-h-[460px] md:min-h-[540px]">
        <div class="relative z-10 mx-auto max-w-[850px]">
            <h2 class="font-newyork text-[42px] leading-tight text-white sm:text-5xl md:text-[65px]">Partner with us</h2>
            <p class="mx-auto mt-4 max-w-[760px] font-urbanist text-sm leading-relaxed text-white/85 sm:text-base md:text-[18px]">Join Dawat to connect your restaurant with more food lovers, grow your reach, and turn every order into a lasting relationship.</p>
        </div>
    </section>
    <section class="relative mx-auto w-[90%] max-w-[1700px] py-16 md:py-24">
        <div class="grid gap-10 md:grid-cols-3 md:gap-14">
            @foreach ([['01', 'Discover More', 'Reach new customers who are looking for their next favourite restaurant.'], ['02', 'Grow Faster', 'Turn more orders into regular customers with a simple, reliable platform.'], ['03', 'Partner Better', 'Get the tools and support you need to build your business with confidence.']] as $reason)
                <div class="text-center">
                    <span class="font-newyork text-[76px] leading-none text-white/35 sm:text-[94px] md:text-[112px]">{{ $reason[0] }}</span>
                    <h2 class="mt-1 font-urbanist text-lg font-semibold text-white md:text-xl">{{ $reason[1] }}</h2>
                    <p class="mx-auto mt-3 max-w-[290px] font-urbanist text-sm leading-relaxed text-white/60">{{ $reason[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
<div class="partner-signup-overlay">
    <section class="partner-signup-card" role="dialog" aria-modal="true" aria-labelledby="partner-signup-title" tabindex="-1">
        <a href="{{ route('website.partner') }}" class="partner-signup-close" aria-label="Close {{ $isLogin ? 'log in' : 'sign up' }} and return to restaurant information">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m5 5 14 14M19 5 5 19"/></svg>
        </a>
        <h1 id="partner-signup-title">{{ $authTitle }}</h1>
        @if($authMode === 'forgot')
            <p class="partner-auth-status" style="margin-bottom: 20px">Enter your restaurant owner email and we'll send you a password reset link.</p>
        @elseif($authMode === 'reset' && !($linkValid ?? false))
            <p class="partner-auth-error">This reset link is invalid or has expired. <a href="{{ route('website.partner.password.request') }}">Request a new link</a>.</p>
        @endif
        <form id="partner-signup-form" method="post"
              data-captcha-enabled="{{ in_array($authMode, ['signup', 'login', 'forgot'], true) && app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled() ? 'true' : 'false' }}"
              @if($isLogin)
              data-auth-mode="{{ $authMode }}"
              data-link-valid="{{ ($linkValid ?? true) ? 'true' : 'false' }}"
              @if(in_array($authMode, ['login', 'reset'], true))
              data-password-challenge-url="{{ route('admin.password.challenge') }}"
              @endif
              action="{{ route(match ($authMode) {
                  'forgot' => 'website.partner.password.email',
                  'reset' => 'website.partner.password.update',
                  default => 'website.partner.login',
              }) }}"
              @else
              action="{{ route('restaurant.onboarding.submit') }}"
              data-captcha-url="{{ route('website.partner-signup.verify-captcha') }}"
              data-email-check-url="{{ route('restaurant.onboarding.check-email') }}"
              data-send-otp-url="{{ route('api.restaurant.auth.send-otp') }}"
              data-resend-otp-url="{{ route('api.restaurant.auth.resend-otp') }}"
              data-verify-otp-url="{{ route('api.restaurant.auth.verify-otp') }}"
              data-otp-type="{{ \Modules\Restaurant\Services\RestaurantOnboardingOtpService::TYPE }}"
              data-partner-url="{{ route('website.partner') }}"
              data-login-url="{{ route('website.partner-login') }}"
              @endif>

            @csrf
            @if($authMode === 'reset')
                <input type="hidden" name="token" value="{{ $token ?? '' }}">
            @endif
            @unless($isLogin)
            <a href="{{ route('website.partner') }}#restaurant-interest-form" class="partner-information-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 5-7 7 7 7M5 12h14"/></svg>
                Back to Restaurant Information
            </a>
            @endunless
            <div class="partner-signup-fields">
                @unless($isLogin)
                <label for="partner-signup-name">YOUR NAME <span aria-hidden="true">*</span>
                    <input id="partner-signup-name" type="text" name="owner_name" placeholder="John Doe" autocomplete="name" minlength="2" maxlength="255" required>
                </label>
                @endunless
                <label for="partner-signup-email">YOUR EMAIL <span aria-hidden="true">*</span>
                    <input id="partner-signup-email" type="email" name="email" placeholder="example@mail.com" autocomplete="email" maxlength="254" @unless($isLogin) aria-describedby="partner-signup-email-status" @endunless value="{{ $authMode === 'reset' ? ($email ?? '') : '' }}" @readonly($authMode === 'reset') required>
                    @unless($isLogin)
                    <span id="partner-signup-email-status" class="partner-email-status" role="status" aria-live="polite" data-state="idle">We'll check the email format and domain before sending an OTP.</span>
                    @endunless
                </label>
                @if($authMode !== 'forgot')
                <div>
                    <label for="partner-signup-password">{{ $authMode === 'reset' ? 'NEW PASSWORD' : 'PASSWORD' }} <span aria-hidden="true">*</span></label>
                    <div class="partner-signup-password">
                        <input id="partner-signup-password" type="password" name="password" placeholder="***********" autocomplete="{{ $authMode === 'login' ? 'current-password' : 'new-password' }}" @if($authMode !== 'login') minlength="8" maxlength="16" @endif required>
                        <button type="button" class="partner-signup-eye" aria-controls="partner-signup-password" aria-label="Show password" aria-pressed="false" data-password-label="password">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="eye-slash" d="m3 3 18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @if(!$isLogin || $authMode === 'reset')
                <div>
                    <label for="partner-signup-confirmation">{{ $authMode === 'reset' ? 'CONFIRM NEW PASSWORD' : 'CONFIRM PASSWORD' }} <span aria-hidden="true">*</span></label>
                    <div class="partner-signup-password">
                        <input id="partner-signup-confirmation" type="password" name="password_confirmation" placeholder="***********" autocomplete="new-password" minlength="8" maxlength="16" required>
                        <button type="button" class="partner-signup-eye" aria-controls="partner-signup-confirmation" aria-label="Show confirm password" aria-pressed="false" data-password-label="confirm password">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path class="eye-slash" d="m3 3 18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif
                @endif
            </div>
            @if($authMode === 'login')
                <div class="partner-login-options">
                    <label class="partner-login-remember"><input type="checkbox" name="remember" value="1" class="partner-terms-checkbox">Remember me</label>
                    <a href="{{ route('website.partner.password.request') }}">Forgot password?</a>
                </div>
            @endif
            @unless($isLogin)
            <label class="partner-signup-agreement">
                <input type="checkbox" name="terms_accepted" value="1" class="partner-terms-checkbox" required>
                <span>I have read and agree to the <a href="{{ route('website.terms-and-conditions') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a></span>
            </label>
            @endunless
            @if (in_array($authMode, ['signup', 'login', 'forgot'], true) && app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
                <div class="mt-5 overflow-x-auto">
                    <div class="g-recaptcha" data-sitekey="{{ app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->siteKey() }}"></div>
                </div>
                <p id="partner-signup-captcha-error" class="partner-auth-error" role="alert"></p>
            @endif
            <button type="submit" class="partner-signup-submit" @disabled($authMode === 'reset' && !($linkValid ?? false))>{{ match ($authMode) { 'login' => 'Log In', 'forgot' => 'Send Reset Link', 'reset' => 'Reset Password', default => 'Create Account' } }}</button>
            <p id="partner-signup-status" class="partner-auth-status" role="status" aria-live="polite" data-error="{{ session('error') ? 'true' : 'false' }}">{{ session('error') ?: session('success') }}</p>
            <a id="partner-signup-information-link" href="{{ route('website.partner', ['restaurant_information' => 'required']) }}#restaurant-interest-form" class="partner-auth-secondary" hidden>Partner with us — Complete restaurant information</a>
        </form>
        @unless($isLogin)
        <section id="partner-otp-panel" hidden aria-labelledby="partner-otp-title">
            <h2 id="partner-otp-title">Verify your email</h2>
            <p>Enter the 6-digit OTP sent to <strong id="partner-otp-email"></strong>.</p>
            <form id="partner-otp-form" novalidate>
                <div class="partner-signup-fields">
                    <label for="partner-otp-code">
                        <input id="partner-otp-code" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" required placeholder="6-digit code" aria-describedby="partner-otp-status">
                    </label>
                </div>
                <p id="partner-otp-status" class="partner-auth-status" role="status" aria-live="polite"></p>
                <p id="partner-otp-expiry" class="partner-auth-status" aria-live="off"></p>
                <button id="partner-otp-verify" type="submit" class="partner-signup-submit" disabled>Verify &amp; Create Account</button>
                <button id="partner-otp-resend" type="button" class="partner-auth-secondary" disabled>Resend OTP</button>
                <button id="partner-otp-back" type="button" class="partner-auth-secondary">Change email / Go back</button>
            </form>
        </section>
        @endunless
        @if(in_array($authMode, ['forgot', 'reset'], true))
            <p class="partner-signup-login"><a href="{{ route('website.partner-login') }}">Back to Log In</a></p>
            @if($authMode === 'forgot')
                <p class="partner-signup-login">Don't have an account? <a href="{{ route('website.partner') }}#restaurant-interest-form">Sign Up</a></p>
            @endif
        @elseif($isLogin)
            <p class="partner-signup-login">Don't have an account? <a href="{{ route('website.partner') }}#restaurant-interest-form">Sign Up</a></p>
        @else
            <p class="partner-signup-login">I already have an account? <a href="{{ route('website.partner-login') }}">Log In</a></p>
        @endif
    </section>
</div>
<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="false"></div>
@endsection

@section('scripts')
@if (in_array($authMode, ['signup', 'login', 'forgot'], true) && app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled())
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<script src="{{ asset('js/toast.js') }}"></script>
<script>
    (() => {
        const card = document.querySelector('.partner-signup-card');
        const overlay = document.querySelector('.partner-signup-overlay');
        const form = document.getElementById('partner-signup-form');
        const closeUrl = @json(route('website.partner'));

        // Keep the background visible while making it unavailable to keyboard navigation.
        [...document.querySelector('main').children].forEach(element => {
            if (element !== overlay && element.id !== 'toast-container') element.inert = true;
        });
        document.querySelector('body > header').inert = true;
        document.querySelector('body > footer').inert = true;
        card.focus({ preventScroll: true });

        form.querySelectorAll('.partner-signup-eye').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.getAttribute('aria-controls'));
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(show));
                button.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} ${button.dataset.passwordLabel}`);
            });
        });

        card.addEventListener('keydown', event => {
            if (event.key === 'Escape') window.location.assign(closeUrl);
            if (event.key !== 'Tab') return;
            const focusable = [...card.querySelectorAll('a[href], input, button')].filter(element => !element.disabled && element.getClientRects().length);
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (event.shiftKey && (document.activeElement === first || document.activeElement === card)) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        });
    })();
</script>
@if($isLogin)
<script src="{{ asset('js/password-transport.js') }}?v={{ substr(hash_file('sha256', public_path('js/password-transport.js')), 0, 12) }}" defer></script>
<script src="{{ asset('js/partner-auth.js') }}?v={{ substr(hash_file('sha256', public_path('js/partner-auth.js')), 0, 12) }}" defer></script>
@else
<script src="{{ asset('js/partner-signup.js') }}" defer></script>
@endif
@endsection
