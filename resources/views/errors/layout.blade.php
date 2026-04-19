<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GenShelf') }} - @yield('title')</title>
    <style>
        :root {
            --bg: #f4f5f7;
            --bg2: #ffffff;
            --tx: #1a1a2e;
            --tx2: #555770;
            --pr: #4f46e5;
            --pr-h: #4338ca;
            --radius: 8px;
            --font: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--tx);
            font-size: 14px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        .error-container {
            background: var(--bg2);
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .logo-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .logo-icon {
            background: var(--pr);
            color: #fff;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            direction: ltr;
        }

        .logo-text .gen {
            color: var(--pr);
        }

        .logo-text .shelf {
            color: var(--tx);
        }

        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: var(--tx);
            line-height: 1;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--tx);
            margin-bottom: 12px;
        }

        .error-message {
            color: var(--tx2);
            margin-bottom: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-weight: 500;
            font-size: 14px;
            border-radius: var(--radius);
            text-decoration: none;
            background: var(--pr);
            color: #fff;
            transition: background .15s;
        }

        .btn:hover {
            background: var(--pr-h);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="logo-brand">
            <div class="logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                    <path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" />
                </svg>
            </div>
            <div class="logo-text">
                <span class="gen">Gen</span><span class="shelf">Shelf</span>
            </div>
        </div>

        <div class="error-code">@yield('code')</div>
        <div class="error-title">@yield('title')</div>
        <div class="error-message">@yield('message')</div>

        <a href="{{ url('/') }}" class="btn">{{ __('Back to Dashboard') }}</a>
    </div>
</body>

</html>