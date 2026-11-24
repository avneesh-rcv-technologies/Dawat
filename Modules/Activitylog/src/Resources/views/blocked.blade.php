<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Blocked</title>
    <style>
        :root {
            --bg-1: #f8fafc;
            --bg-2: #eef2ff;
            --bg-3: #e2e8f0;
            --card-bg: rgba(255, 255, 255, 0.88);
            --card-border: rgba(148, 163, 184, 0.35);
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --danger-soft: #7f1d1d;
            --danger: #ef4444;
            --danger-dark: #991b1b;
            --accent: #38bdf8;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            color: var(--text-primary);
            background:
                radial-gradient(900px 500px at 90% -10%, rgba(56, 189, 248, 0.2), transparent 60%),
                radial-gradient(700px 420px at -10% 110%, rgba(239, 68, 68, 0.12), transparent 60%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2) 45%, var(--bg-3));
            padding: 20px;
        }

        .card {
            width: min(94vw, 640px);
            padding: 32px;
            border-radius: 20px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            backdrop-filter: blur(14px);
            box-shadow:
                0 20px 45px rgba(15, 23, 42, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            border-radius: 999px;
            color: var(--danger-soft);
            background: rgba(239, 68, 68, 0.14);
            border: 1px solid rgba(239, 68, 68, 0.35);
            margin-bottom: 16px;
        }

        .icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.1));
            border: 1px solid rgba(239, 68, 68, 0.35);
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: clamp(1.45rem, 2vw, 1.8rem);
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .meta {
            margin-top: 20px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(248, 250, 252, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.35);
            font-size: 0.9rem;
            color: var(--text-muted);
            display: grid;
            gap: 8px;
        }

        .meta strong {
            color: var(--text-secondary);
            font-weight: 600;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            color: #0b1120;
            background: linear-gradient(135deg, #7dd3fc, var(--accent));
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.25);
        }

        .btn-muted {
            color: var(--text-secondary);
            border-color: rgba(148, 163, 184, 0.3);
            background: rgba(255, 255, 255, 0.9);
        }

        .btn-muted:hover {
            border-color: rgba(148, 163, 184, 0.5);
            background: rgba(241, 245, 249, 1);
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">You are blocked</span>

        <div class="icon-wrap" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L4 5V11C4 16.55 7.84 21.74 12 23C16.16 21.74 20 16.55 20 11V5L12 2Z" stroke="#fecaca" stroke-width="1.6"/>
                <path d="M9 9L15 15M15 9L9 15" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>

        <h1>Request Blocked</h1>
        <p>Your IP address has been temporarily blocked for security protection. If you believe this is a mistake, contact support with the details below.</p>

        <div class="meta">
            <div><strong>IP Address:</strong> {{ $ipAddress ?? request()->ip() }}</div>
            <div><strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}</div>
        </div>

        <div class="actions">
            <a class="btn btn-primary" href="mailto:{{ fn_get_setting('company.support_email') }}">Contact Support</a>
            <a class="btn btn-muted" href="{{ url('/') }}">Go to Homepage</a>
        </div>
    </main>
</body>
</html>
