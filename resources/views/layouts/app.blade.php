<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Apotek Medico') }}</title>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700,800" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Scripts --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --am-bg: #ffffff;
            --am-panel: #ffffff;
            --am-panel-soft: #f1f5f9;
            --am-border: #e2e8f0;
            --am-text: #1e293b;
            --am-muted: #64748b;
            --am-red: #ef233c;
            --am-red-dark: #d90429;
        }

        html,
        body {
            min-height: 100%;
            background: var(--am-bg) !important;
            color: var(--am-text);
            font-family: 'Nunito', sans-serif;
        }

        #app {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(239, 68, 68, 0.06), transparent 28%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.05), transparent 30%),
                var(--am-bg);
        }

        .auth-navbar {
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid var(--am-border);
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(12px);
            min-height: 72px;
        }

        .auth-navbar .navbar-brand {
            color: var(--am-text) !important;
            font-weight: 800;
            letter-spacing: 0.4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auth-logo {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--am-red), var(--am-red-dark));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 12px 28px rgba(239, 35, 60, 0.28);
        }

        .auth-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .auth-brand-text small {
            color: var(--am-muted);
            font-size: 11px;
            letter-spacing: 3px;
            font-weight: 700;
        }

        .navbar-toggler {
            border-color: var(--am-border);
            background: var(--am-panel-soft);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.15rem rgba(239, 68, 68, 0.25);
        }

        .navbar-toggler-icon {
            filter: none;
        }

        .auth-navbar .nav-link {
            color: #334155 !important;
            font-weight: 700;
            border-radius: 12px;
            padding: 10px 14px !important;
            transition: 0.2s ease;
        }

        .auth-navbar .nav-link:hover {
            color: #1e293b !important;
            background: rgba(15, 23, 42, 0.05);
        }

        .auth-navbar .nav-link.auth-login-link {
            background: linear-gradient(135deg, var(--am-red), var(--am-red-dark));
            color: #fff !important;
            box-shadow: 0 12px 26px rgba(239, 35, 60, 0.22);
        }

        .auth-navbar .dropdown-menu {
            background: var(--am-panel);
            border: 1px solid var(--am-border);
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.1);
            padding: 8px;
        }

        .auth-navbar .dropdown-item {
            color: #334155;
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: 600;
        }

        .auth-navbar .dropdown-item:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #1e293b;
        }

        .auth-main {
            min-height: 100vh;
            padding: 0;
        }

        .container {
            max-width: 1180px;
        }
    </style>
</head>

<body>
    <div id="app">
        <main class="auth-main">
            @yield('content')
        </main>
    </div>
</body>
</html>