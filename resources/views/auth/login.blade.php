<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GenShelf') }} - Login</title>
    <style>
        :root {
            --bg: #f4f5f7;
            --bg2: #ffffff;
            --tx: #1a1a2e;
            --tx2: #555770;
            --pr: #4f46e5;
            --pr-h: #4338ca;
            --rd: #dc2626;
            --border: #d1d5db;
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
        }

        #login-screen {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-box {
            background: var(--bg2);
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .login-box h1 {
            font-size: 24px;
            margin-bottom: 4px;
            color: var(--pr);
            text-align: center;
        }

        .logo-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 12px;
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
            align-items: center;
            direction: ltr;
        }

        .logo-text .gen {
            color: var(--pr);
        }

        .logo-text .shelf {
            color: var(--tx);
        }

        .login-box p {
            color: var(--tx2);
            margin-bottom: 24px;
            font-size: 14px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--tx2);
            margin-bottom: 4px;
        }

        input {
            font-family: var(--font);
            font-size: 13px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 10px 12px;
            width: 100%;
            outline: none;
        }

        input:focus {
            border-color: var(--pr);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            font-weight: 500;
            padding: 10px;
            font-size: 14px;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background .15s;
            background: var(--pr);
            color: #fff;
        }

        .btn:hover {
            background: var(--pr-h);
        }

        .login-err {
            color: var(--rd);
            font-size: 12px;
            margin-bottom: 16px;
            padding: 10px;
            background: #fee2e2;
            border-radius: var(--radius);
            border: 1px solid #fecaca;
        }

        html[dir="rtl"] {
            direction: rtl;
        }
    </style>
</head>

<body>
    <div id="login-screen">
        <div class="login-box">
            <div class="logo-brand">
                <div class="logo-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M12 12h.01"/><path d="M12 17h.01"/></svg>
                </div>
                <div class="logo-text">
                    <span class="gen">Gen</span><span class="shelf">Shelf</span>
                </div>
            </div>
            <p>{{ __('Store Management & POS') }}</p>

            @if ($errors->any())
                <div class="login-err">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">{{ __('Username') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn">{{ __('Sign In') }}</button>
            </form>
        </div>
    </div>
</body>

</html>