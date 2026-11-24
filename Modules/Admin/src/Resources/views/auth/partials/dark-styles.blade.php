    {{-- Shared auth screen styles: black surfaces, Dawat orange, NewYork + Urbanist, as on the public website. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'NewYork';
            src: url("{{ asset('website/fonts/NewYork-Regular.otf') }}") format('opentype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        :root {
            color-scheme: dark;
            --auth-brand: #FF6210;
            --auth-brand-dark: #E0530B;
            --auth-ink: #FFFFFF;
            --auth-muted: rgba(255, 255, 255, .62);
            --auth-faint: rgba(255, 255, 255, .42);
            --auth-line: rgba(255, 255, 255, .12);
            --auth-field: #101010;
        }

        html, body { min-height: 100%; }
        body { margin: 0; background: #000; color: var(--auth-ink); font-family: 'Urbanist', ui-sans-serif, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        ::selection { background: var(--auth-brand); color: #fff; }

        /* Layout: photo panel on the left, form on the right */
        .auth-shell { display: grid; min-height: 100vh; min-height: 100dvh; grid-template-columns: minmax(0, 1fr); background: #000; }
        @media (min-width: 1024px) { .auth-shell { grid-template-columns: minmax(0, 1.15fr) minmax(480px, .85fr); } }

        .auth-visual { position: relative; display: none; overflow: hidden; isolation: isolate; }
        @media (min-width: 1024px) { .auth-visual { display: flex; flex-direction: column; justify-content: space-between; padding: 44px 56px 52px; } }
        .auth-visual-photo { position: absolute; inset: 0; z-index: -2; width: 100%; height: 100%; object-fit: cover; transform: scale(1.06); animation: auth-photo-drift 24s ease-in-out infinite alternate; }
        .auth-visual::after { position: absolute; inset: 0; z-index: -1; content: ''; background:
            radial-gradient(70% 55% at 0% 100%, rgba(255, 98, 16, .32) 0%, rgba(255, 98, 16, 0) 70%),
            linear-gradient(180deg, rgba(0, 0, 0, .62) 0%, rgba(0, 0, 0, .18) 32%, rgba(0, 0, 0, .55) 62%, rgba(0, 0, 0, .94) 100%),
            linear-gradient(90deg, rgba(0, 0, 0, 0) 70%, #000 100%); }
        .auth-wordmark { display: block; width: auto; height: 38px; }
        .auth-visual-copy { max-width: 560px; }
        .auth-eyebrow { display: inline-flex; align-items: center; gap: 10px; padding: 8px 16px; border: 1px solid rgba(255, 255, 255, .14); border-radius: 999px; background: rgba(255, 255, 255, .06); color: #fff; font-size: 13px; font-weight: 600; letter-spacing: .02em; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        .auth-eyebrow-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--auth-brand); box-shadow: 0 0 0 4px rgba(255, 98, 16, .25); }
        .auth-visual h1 { margin: 22px 0 16px; font-family: 'NewYork', Georgia, serif; font-size: clamp(40px, 4.2vw, 60px); font-weight: 400; line-height: 1.04; letter-spacing: -.01em; }
        .auth-visual h1 span { color: var(--auth-brand); }
        .auth-visual-copy > p { max-width: 470px; margin: 0; color: rgba(255, 255, 255, .78); font-size: 17px; line-height: 1.6; }
        .auth-features { display: grid; gap: 14px; margin: 34px 0 0; padding: 22px 0 0; border-top: 1px solid rgba(255, 255, 255, .14); list-style: none; }
        .auth-features li { display: flex; align-items: center; gap: 14px; }
        .auth-feature-icon { display: grid; flex: 0 0 40px; width: 40px; height: 40px; place-items: center; border: 1px solid rgba(255, 98, 16, .35); border-radius: 12px; background: rgba(255, 98, 16, .14); color: #FF8B4D; }
        .auth-feature-icon .material-icons-outlined { font-size: 20px; }
        .auth-features strong { display: block; font-size: 15px; font-weight: 700; }
        .auth-features small { display: block; margin-top: 2px; color: var(--auth-muted); font-size: 13px; }

        /* Form column */
        .auth-main { position: relative; display: flex; align-items: center; justify-content: center; padding: 28px 20px 40px; overflow: hidden; background:
            radial-gradient(60% 45% at 100% 0%, rgba(255, 98, 16, .16) 0%, rgba(255, 98, 16, 0) 70%),
            radial-gradient(50% 40% at 0% 100%, rgba(255, 98, 16, .08) 0%, rgba(255, 98, 16, 0) 70%),
            #050505; }
        @media (min-width: 640px) { .auth-main { padding: 40px 48px; } }
        .auth-main-inner { display: flex; width: 100%; max-width: 420px; flex-direction: column; gap: 32px; animation: auth-enter .6s cubic-bezier(.2, .75, .25, 1) both; }

        .login-home-link { display: inline-flex; width: fit-content; align-items: center; gap: 8px; padding: 8px 14px; border: 1px solid var(--auth-line); border-radius: 999px; background: rgba(255, 255, 255, .04); color: var(--auth-muted); font-size: 13px; font-weight: 600; text-decoration: none; transition: color .16s ease, border-color .16s ease, background-color .16s ease; }
        .login-home-link:hover { border-color: rgba(255, 98, 16, .5); background: rgba(255, 98, 16, .08); color: #fff; }
        .login-home-link svg { width: 16px; height: 16px; }

        .login-form { background: transparent; }
        .login-form .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
        .login-form .logo img { width: auto; height: 34px; }
        @media (min-width: 1024px) { .login-form .logo { display: none; } }
        .login-form form { display: flex; flex-direction: column; gap: 20px; padding: 0; }
        .login-form form > * { margin: 0 !important; }

        .login-form .welcome h3 { margin: 0; color: #fff; font-family: 'NewYork', Georgia, serif; font-size: 40px; font-weight: 400; line-height: 1.1; text-align: left; }
        .login-form .welcome p { margin: 10px 0 0; color: var(--auth-muted); font-size: 15px; line-height: 1.55; text-align: left; }
        .login-form .welcome { margin-bottom: 8px !important; }

        .login-form form label { color: rgba(255, 255, 255, .86); font-size: 13px; font-weight: 600; }
        .login-form .field, .login-form form > .space-y-2 { display: flex; flex-direction: column; gap: 8px; }
        .login-form form > .space-y-2 > * { margin: 0 !important; }

        .glass-input { position: relative; display: flex; align-items: center; height: 54px; padding: 0 16px 0 48px; border: 1px solid var(--auth-line); border-radius: 14px; background: var(--auth-field); transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease; }
        .glass-input:has(.toggle-password) { padding-right: 54px; }
        .glass-input:hover { border-color: rgba(255, 255, 255, .22); }
        .glass-input:focus-within { border-color: var(--auth-brand); background: #141414; box-shadow: 0 0 0 4px rgba(255, 98, 16, .16); }
        .icon-wrapper { position: absolute; left: 17px; display: flex; width: 18px; align-items: center; justify-content: center; }
        .icon-wrapper i { color: var(--auth-faint); font-size: 14px; transition: color .18s ease; }
        .glass-input:focus-within .icon-wrapper i { color: var(--auth-brand); }
        .login-form .glass-input .login-input,
        .login-form .glass-input .login-input:focus { width: 100%; min-width: 0; height: 100%; padding: 0; border: 0 !important; outline: 0 !important; box-shadow: none !important; background: transparent !important; color: #fff; font-family: inherit; font-size: 15px; }
        .login-form .glass-input .login-input { color-scheme: dark; caret-color: var(--auth-brand); }
        .login-input::placeholder { color: rgba(255, 255, 255, .35); }
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill),
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill):hover,
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill):focus {
            -webkit-box-shadow: 0 0 0 1000px #141414 inset !important;
            box-shadow: 0 0 0 1000px #141414 inset !important;
            -webkit-text-fill-color: #fff !important;
            color: #fff !important;
        }
        .login-form .toggle-password { position: absolute; top: 50%; right: 12px; display: grid; width: 34px; height: 34px; place-items: center; padding: 0; border: 0; border-radius: 10px; background: transparent; color: var(--auth-faint); cursor: pointer; transform: translateY(-50%); opacity: 1; transition: color .16s ease, background-color .16s ease; }
        .login-form .toggle-password:hover { background: rgba(255, 255, 255, .06); color: #fff; }

        .login-form .remember-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--auth-muted); font-size: 13px; }
        .login-form .remember-row label { color: var(--auth-muted); font-size: 13px; font-weight: 500; cursor: pointer; }
        .login-form #remember-me { width: 18px; height: 18px; border: 1.5px solid rgba(255, 255, 255, .3); border-radius: 5px; background-color: transparent; cursor: pointer; }
        .login-form #remember-me:checked { border-color: var(--auth-brand); background-color: var(--auth-brand); }
        .login-form .forgot-link, .login-form .back-login { color: var(--auth-brand); font-size: 13px; font-weight: 600; text-decoration: none; }
        .login-form .forgot-link:hover, .login-form .back-login:hover { color: #FF8B4D; text-decoration: underline; text-underline-offset: 3px; }

        /* Primary action: the website's orange pill button */
        .login-form .login-submit, .login-form .reset-submit { display: inline-flex; width: 100%; min-height: 54px; align-items: center; justify-content: center; gap: 10px; padding: 0 24px; border: 0; border-radius: 999px; background: var(--auth-brand); color: #fff; font-family: inherit; font-size: 15px; font-weight: 700; letter-spacing: .01em; cursor: pointer; box-shadow: 0 14px 34px -14px rgba(255, 98, 16, .85); transition: transform .16s ease, background-color .16s ease, box-shadow .16s ease; }
        .login-form .login-submit:hover, .login-form .reset-submit:hover { transform: translateY(-1px); background: var(--auth-brand-dark); box-shadow: 0 18px 40px -14px rgba(255, 98, 16, .95); }
        .login-form .login-submit:active, .login-form .reset-submit:active { transform: translateY(0); }
        .login-form .login-submit svg, .login-form .reset-submit svg { width: 18px; height: 18px; transition: transform .16s ease; }
        .login-form .login-submit:hover svg, .login-form .reset-submit:hover svg { transform: translateX(3px); }
        .login-form .login-submit:disabled, .login-form .reset-submit:disabled { opacity: .6; cursor: wait; transform: none; }

        .auth-secure-note { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0; color: var(--auth-faint); font-size: 12px; }
        .auth-secure-note .material-icons-outlined { font-size: 15px; }

        .login-form .reset-status { padding: 12px 14px; border: 1px solid rgba(34, 197, 94, .28); border-radius: 12px; background: rgba(34, 197, 94, .1); color: #86efac; font-size: 13px; line-height: 1.5; }
        .login-form .auth-status { color: #d1d5db; font-size: 13px; }
        .login-form .partner-auth-error, .login-form .auth-status[data-error="true"] { display: block; color: #fca5a5; font-size: 13px; }
        .login-form .partner-auth-error:empty, .login-form .auth-status:empty { display: none; }
        .login-form .auth-captcha { align-self: flex-start; max-width: 100%; overflow-x: auto; color-scheme: light; }
        .login-form .auth-captcha .g-recaptcha { line-height: 0; }
        .login-form .auth-captcha iframe { display: block; border: 0; background: transparent; }

        .login-form :is(button, a, input):focus-visible, .login-home-link:focus-visible { outline: 2px solid var(--auth-brand); outline-offset: 3px; }

        @keyframes auth-enter { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes auth-photo-drift { from { transform: scale(1.06) translate3d(0, 0, 0); } to { transform: scale(1.12) translate3d(-1.5%, -1%, 0); } }
        @media (max-width: 480px) { .login-form .welcome h3 { font-size: 32px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
    </style>
