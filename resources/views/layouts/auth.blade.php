<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#0f0f13">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="All Habit">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>@yield('title') — {{ config('app.name', 'All Habit') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f0f13;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .auth-card {
            width: 100%;
            max-width: 380px;
        }
        .card {
            background: #1a1a23;
            border: 1px solid #2a2a38;
            border-radius: 12px;
        }
        .card-body { padding: 24px; }
        h4 { color: #e8e8ed; }
        .form-label { color: #8b8b9e; font-size: 0.85rem; }
        .form-control {
            background: #14141c;
            border-color: #2a2a38;
            color: #e8e8ed;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .form-control:focus {
            background: #14141c;
            border-color: #6366f1;
            color: #e8e8ed;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-check-input {
            background-color: #14141c;
            border-color: #2a2a38;
        }
        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        .form-check-label { color: #8b8b9e; font-size: 0.85rem; }
        .btn-dark {
            background: #6366f1;
            border-color: #6366f1;
            border-radius: 8px;
            padding: 10px;
            font-weight: 700;
        }
        .btn-dark:hover {
            background: #5558e6;
            border-color: #5558e6;
        }
        a { color: #6366f1; }
        small { color: #5a5a6e; }
        .invalid-feedback { font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="card">
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
