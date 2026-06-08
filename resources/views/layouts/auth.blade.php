<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#4f46e5" />
    <title>@yield('title', 'Login') — Lakkad Loha</title>
    <link rel="manifest" href="/manifest.json" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/app.css" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            padding: 24px;
        }
        .auth-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }
        .auth-logo-icon {
            width: 48px; height: 48px;
            background: var(--primary);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 22px;
        }
        .auth-logo-name { font-size: 20px; font-weight: 800; color: var(--text); }
        .auth-logo-sub { font-size: 12px; color: var(--text-3); }
        .auth-title { font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .auth-subtitle { font-size: 13px; color: var(--text-3); margin-bottom: 28px; }
        .divider { border-color: var(--border) !important; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="bi bi-building"></i></div>
            <div>
                <div class="auth-logo-name">Lakkad Loha</div>
                <div class="auth-logo-sub">Staff Management Portal</div>
            </div>
        </div>
        @yield('content')
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
