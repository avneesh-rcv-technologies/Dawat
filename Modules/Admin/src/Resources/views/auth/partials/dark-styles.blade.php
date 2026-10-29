    <style>
        :root { color-scheme: light; }
        body { margin: 0; }
        .login-bg {
            position: relative;
            isolation: isolate;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(ellipse 42% 62% at 50% 48%, rgba(133, 143, 148, .16) 0%, rgba(67, 73, 76, .09) 42%, transparent 100%),
                radial-gradient(ellipse at 12% 18%, rgba(255, 255, 255, .035), transparent 34%),
                radial-gradient(ellipse at 88% 82%, rgba(170, 179, 183, .045), transparent 38%),
                linear-gradient(145deg, #141617 0%, #202325 48%, #0b0c0d 100%);
            background-size: 120% 120%;
            animation: login-background-drift 32s ease-in-out infinite alternate;
        }
        .login-bg::before, .login-bg::after { position: absolute; z-index: -1; content: ''; border: 1px solid rgba(220,228,231,.045); border-radius: 50%; pointer-events: none; }
        .login-bg::before { width: min(68vw, 760px); aspect-ratio: 1; top: -38%; left: -17%; box-shadow: 0 0 0 55px rgba(220,228,231,.012), 0 0 0 110px rgba(220,228,231,.008); animation: login-orbit 38s ease-in-out infinite alternate; }
        .login-bg::after { width: min(58vw, 620px); aspect-ratio: 1; right: -16%; bottom: -42%; box-shadow: 0 0 0 48px rgba(220,228,231,.012), 0 0 0 96px rgba(220,228,231,.007); animation: login-orbit 44s ease-in-out -8s infinite alternate-reverse; }
        .login-bg > div { position: relative; z-index: 1; display: flex; width: 100%; max-width: 500px; flex-direction: column; gap: 14px; }
        .login-home-link { display: inline-flex; width: fit-content; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(220,228,231,.10); background: rgba(25,27,29,.72); color: #c5cccf; font-size: 13px; font-weight: 600; text-decoration: none; transition: color .16s ease,background .16s ease,border-color .16s ease; }
        .login-home-link:hover { border-color: rgba(220,228,231,.20); background: #191b1d; color: #fff; }
        .login-home-link svg { width: 17px; height: 17px; }
        .login-form { overflow: hidden; border: 0; border-radius: 0; background: #191b1d; box-shadow: 18px 18px 42px rgba(0,0,0,.52), -10px -10px 30px rgba(255,255,255,.035), inset 1px 1px 0 rgba(255,255,255,.035); animation: login-card-enter .65s cubic-bezier(.2,.75,.25,1) both; }
        .login-form .logo { min-height: 116px; padding: 25px 32px; border: 0; background: #111213; box-shadow: inset 0 -1px 0 rgba(255,255,255,.045); }
        .login-form .logo img { max-width: min(100%, 230px); max-height: 66px; object-fit: contain; mix-blend-mode: screen; }
        .login-form .logo span { color: #fff; font-family: Georgia, serif; font-size: 28px; }
        .login-form form { gap: 22px; padding: 34px 44px 40px; background: linear-gradient(145deg, #1d1f21 0%, #17191b 100%); }
        .login-form .welcome { margin-bottom: 4px; }
        .login-form .welcome h3 { margin: 0; color: #f2f5f6; font-size: 29px; line-height: 1.2; letter-spacing: -.03em; }
        .login-form .welcome p { margin: 8px 0 0; color: #a5afb4; font-size: 13px; line-height: 1.5; text-align: center; }
        .login-form form label { color: #d6dcdf; font-size: 13px; font-weight: 650; }
        .glass-input { height: 50px; padding: 0 16px 0 48px; border: 1px solid rgba(255,255,255,.035); border-radius: 10px; background: #1b1d1f; box-shadow: inset 5px 5px 10px rgba(0,0,0,.38), inset -4px -4px 9px rgba(255,255,255,.035); transition: border-color .18s ease, box-shadow .18s ease, background .18s ease; }
        .glass-input:focus-within { border: 1px solid #00a86b; background: #1b1d1f; box-shadow: inset 4px 4px 8px rgba(0,0,0,.35), inset -3px -3px 7px rgba(255,255,255,.035), 0 0 0 3px rgba(0,168,107,.13); }
        .icon-wrapper { position: absolute; left: 15px; display: flex; width: 20px; align-items: center; justify-content: center; }
        .icon-wrapper i { color: #a2adb2; font-size: 14px; }
        .login-form .glass-input .login-input,
        .login-form .glass-input .login-input:focus { width: 100%; min-width: 0; border: 0 !important; outline: 0 !important; box-shadow: none !important; background: transparent !important; color: #f2f5f6; font-size: 14px; }
        .login-input::placeholder { color: #899499; }
        .login-form .glass-input .login-input { color-scheme: dark; caret-color: #f2f5f6; }
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill),
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill):hover,
        .login-form .glass-input .login-input:is(:autofill, :-webkit-autofill):focus {
            background: #1b1d1f !important;
            -webkit-box-shadow: 0 0 0 1000px #1b1d1f inset !important;
            box-shadow: 0 0 0 1000px #1b1d1f inset !important;
            -webkit-text-fill-color: #f2f5f6 !important;
            color: #f2f5f6 !important;
        }
        .login-form .toggle-password { color: #a2adb2; }
        .login-form #remember-me { width: 16px; height: 16px; accent-color: #00a86b; }
        .login-form .remember-row { color: #b6c0c4; font-size: 12px; }
        .login-form .forgot-link { color: #45c797; text-decoration: none; }
        .login-form .forgot-link:hover { color: #78e2b8; text-decoration: underline; }
        .login-form .login-submit { min-height: 48px; border: 1px solid rgba(255,255,255,.045); border-radius: 10px; background: #1c1e20; color: #f4f6f7; font-size: 14px; font-weight: 650; box-shadow: 6px 6px 12px rgba(0,0,0,.42), -4px -4px 10px rgba(255,255,255,.045); transition: transform .16s ease, color .16s ease, box-shadow .16s ease; }
        .login-form .login-submit:hover { transform: translateY(-1px); color: #78e2b8; box-shadow: 8px 8px 16px rgba(0,0,0,.45), -5px -5px 12px rgba(255,255,255,.05); }
        .login-form .login-submit:active { transform: translateY(0); box-shadow: inset 4px 4px 8px rgba(0,0,0,.42), inset -3px -3px 7px rgba(255,255,255,.04); }
        .login-form :is(button, a, input):focus-visible { outline: 3px solid rgba(0,168,107,.35); outline-offset: 3px; }
        @keyframes login-background-drift { from { background-position: 0% 0%; } to { background-position: 100% 100%; } }
        @keyframes login-orbit { from { transform: translate3d(-10px, -8px, 0) scale(.98); opacity: .7; } to { transform: translate3d(18px, 14px, 0) scale(1.04); opacity: 1; } }
        @keyframes login-card-enter { from { opacity: 0; transform: translateY(14px) scale(.99); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @media (max-width: 520px) { .login-bg { padding: 20px; } .login-form { border-radius: 0; } .login-form .logo { min-height: 96px; padding: 20px 24px; } .login-form form { gap: 19px; padding: 28px 24px 30px; } .login-form .welcome h3 { font-size: 25px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
    </style>
