@php
    $status = $status ?? 404;
    $pages = [
        403 => ['Access denied', 'you are not permitted to do this work', 'Contact your administrator if you need access to this page or action.'],
        404 => ['Page not found', "We couldn’t find that page.", 'The link may have changed, or the page may no longer be available.'],
    ];
    [$title, $heading, $description] = $pages[$status] ?? $pages[404];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="color-scheme" content="light dark">
    <title>{{ $status }} | {{ $title }} — Dawat</title>
    <style>
        :root {
            --bg: #f6f7f9;
            --surface: #ffffff;
            --panel: #f8f9fb;
            --text: #182230;
            --muted: #667085;
            --border: #e7eaf0;
            --accent: #b54732;
            --tint: #fff1ec;
            --button: #182230;
            --button-text: #ffffff;
            --grid: #e9ecf1;
            --shadow: 0 24px 64px -32px #1d29392b;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; min-height: 100svh; color: var(--text); background: var(--bg); font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        .shell { display: flex; flex-direction: column; min-height: 100vh; min-height: 100svh; max-width: 1320px; margin: auto; padding: 0 48px; }
        .header { display: flex; align-items: center; justify-content: space-between; min-height: 100px; gap: 20px; }
        .brand { display: inline-flex; align-items: center; gap: 10px; font-size: 23px; font-weight: 750; letter-spacing: -1px; }
        .brand-mark { display: grid; place-items: center; width: 35px; height: 35px; border-radius: 10px; background: var(--button); color: var(--button-text); font-size: 25px; font-weight: 650; }
        .header-link { display: inline-flex; align-items: center; gap: 8px; color: var(--muted); font-size: 13px; font-weight: 500; }
        .header-link:hover { color: var(--text); }
        .content { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 0 72px; }
        .card { width: 100%; max-width: 1040px; display: grid; grid-template-columns: .9fr 1.1fr; border: 1px solid var(--border); border-radius: 24px; overflow: hidden; background: var(--surface); box-shadow: var(--shadow); }
        .visual { position: relative; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 470px; padding: 48px 24px; background: var(--panel); border-right: 1px solid var(--border); isolation: isolate; }
        .visual::before { content: ""; position: absolute; inset: 24px; z-index: -1; background-image: linear-gradient(var(--grid) 1px, transparent 1px), linear-gradient(90deg, var(--grid) 1px, transparent 1px); background-size: 32px 32px; mask-image: radial-gradient(ellipse at center, #000 15%, transparent 70%); }
        .orbit { position: absolute; width: 280px; height: 280px; border: 1px solid var(--border); border-radius: 50%; z-index: -1; }
        .orbit::after { content: ""; position: absolute; top: 36px; right: 34px; width: 8px; height: 8px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 5px var(--tint); }
        .code { margin: 0; font-size: clamp(100px, 12vw, 150px); font-weight: 750; line-height: 1; letter-spacing: -9px; font-variant-numeric: tabular-nums; }
        .visual-label { margin-top: 24px; display: inline-flex; gap: 8px; align-items: center; border: 1px solid var(--border); border-radius: 100px; background: var(--surface); padding: 8px 13px; color: var(--muted); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .visual-label::before { content: ""; width: 5px; height: 5px; border-radius: 50%; background: var(--accent); }
        .details { padding: 56px 48px; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .icon { display: grid; place-items: center; width: 48px; height: 48px; background: var(--tint); color: var(--accent); border-radius: 14px; margin-bottom: 24px; }
        .eyebrow { margin: 0 0 12px; color: var(--muted); font-size: 12px; line-height: 1.5; font-weight: 600; letter-spacing: .09em; text-transform: uppercase; }
        h1 { margin: 0 0 16px; font-size: clamp(26px, 3vw, 34px); line-height: 1.2; font-weight: 650; letter-spacing: -1.1px; overflow-wrap: break-word; }
        .description { margin: 0; color: var(--muted); font-size: 14px; line-height: 1.8; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 28px; }
        .button { display: inline-flex; justify-content: center; align-items: center; gap: 8px; padding: 12px 17px; min-height: 44px; background: var(--button); color: var(--button-text); border: 1px solid var(--button); border-radius: 8px; font-size: 13px; font-weight: 600; transition: opacity .15s, background .15s; }
        .button:hover { opacity: .85; }
        .button.secondary { background: var(--surface); color: var(--text); border-color: var(--border); }
        .button.secondary:hover { background: var(--panel); }
        a:focus-visible { outline: 3px solid var(--accent); outline-offset: 4px; border-radius: 8px; }
        .help { display: flex; gap: 9px; align-items: flex-start; margin: 28px 0 0; padding-top: 22px; border-top: 1px solid var(--border); color: var(--muted); font-size: 12px; line-height: 1.7; }
        .help svg { flex-shrink: 0; margin-top: 2px; }
        .footer { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 24px 0; border-top: 1px solid var(--border); color: var(--muted); font-size: 12px; }
        .footer-code { font-variant-numeric: tabular-nums; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #10151d; --surface: #171f2a; --panel: #141c26; --text: #edf1f7; --muted: #a4afbf; --border: #2b3543; --accent: #f1a18c; --tint: #382720; --button: #edf1f7; --button-text: #17202c; --grid: #27313e; --shadow: 0 24px 64px -32px #0008; }
        }
        @media (max-width: 800px) {
            .shell { padding: 0 24px; }
            .header { min-height: 80px; }
            .content { padding: 24px 0 40px; }
            .details { padding: 40px 28px; }
            .visual { min-height: 400px; }
            .orbit { width: 220px; height: 220px; }
        }
        @media (max-width: 600px) {
            .shell { padding: 0 20px; }
            .card { grid-template-columns: 1fr; border-radius: 18px; }
            .visual { min-height: 210px; padding: 30px 20px; border-right: 0; border-bottom: 1px solid var(--border); }
            .orbit { width: 170px; height: 170px; }
            .code { font-size: 88px; letter-spacing: -5px; }
            .visual-label { margin-top: 14px; font-size: 10px; }
            .details { padding: 28px 24px; }
            .icon { width: 40px; height: 40px; margin-bottom: 18px; border-radius: 11px; }
            .actions .button { flex: 1; white-space: nowrap; padding: 12px; }
            .footer { align-items: flex-start; font-size: 11px; }
        }
        @media (prefers-reduced-motion: reduce) { .button { transition: none; } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="header">
            <a class="brand" href="{{ url('/') }}" aria-label="Dawat home"><span class="brand-mark" aria-hidden="true">d</span>Dawat</a>
            <a class="header-link" href="{{ url('/') }}">Return home <span aria-hidden="true">&nearr;</span></a>
        </header>
        <main class="content" aria-labelledby="error-title">
            <div class="card">
                <div class="visual" aria-hidden="true">
                    <div class="orbit"></div>
                    <div class="code">{{ $status }}</div>
                    <div class="visual-label">{{ $title }}</div>
                </div>
                <section class="details">
                    <div class="icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            @if($status === 403)
                                <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"></path>
                            @else
                                <circle cx="10.5" cy="10.5" r="6.5"></circle>
                                <path d="m16 16 5 5M8 10.5h5"></path>
                            @endif
                        </svg>
                    </div>
                    <p class="eyebrow">Error {{ $status }} &middot; {{ $title }}</p>
                    <h1 id="error-title">{{ $heading }}</h1>
                    <p class="description">{{ $description }}</p>
                    <nav class="actions" aria-label="Error page navigation">
                        <a class="button" href="{{ url('/') }}"><svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/></svg>Back to home</a>
                        <a class="button secondary" id="go-back" href="{{ url('/') }}"><span aria-hidden="true">&larr;</span>Go back</a>
                    </nav>
                    <p class="help">
                        <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/></svg>
                        <span>Need help? Your administrator can help you find the right next step.</span>
                    </p>
                </section>
            </div>
        </main>
        <footer class="footer"><span>&copy; {{ date('Y') }} Dawat. All rights reserved.</span><span class="footer-code">Error {{ $status }}</span></footer>
    </div>
    <script>
        document.getElementById('go-back').addEventListener('click', function (event) {
            if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    </script>
</body>
</html>
