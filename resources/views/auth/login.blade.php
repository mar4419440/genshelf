<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GenShelf') }} - Login</title>
    <style>
        :root {
            --bg: #f4f5f7; --bg2: #ffffff;
            --tx: #1a1a2e; --tx2: #555770;
            --pr: #4f46e5; --pr-h: #4338ca;
            --rd: #dc2626; --border: #d1d5db; --radius: 8px;
            --font: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font); background: var(--bg); color: var(--tx); font-size: 14px; line-height: 1.5; }
        
        #login-screen { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { background: var(--bg2); padding: 40px; border-radius: 12px; width: 100%; max-width: 380px; border: 1px solid var(--border); }
        .login-box h1 { font-size: 24px; margin-bottom: 4px; color: var(--pr); text-align: center; }
        .login-box p { color: var(--tx2); margin-bottom: 24px; font-size: 13px; text-align: center; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--tx2); margin-bottom: 4px; }
        input { font-family: var(--font); font-size: 13px; border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 12px; width: 100%; outline: none; }
        input:focus { border-color: var(--pr); }
        .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; font-weight: 500; padding: 10px; font-size: 14px; border: none; border-radius: var(--radius); cursor: pointer; transition: background .15s; background: var(--pr); color: #fff; }
        .btn:hover { background: var(--pr-h); }
        .login-err { color: var(--rd); font-size: 12px; margin-bottom: 16px; padding: 10px; background: #fee2e2; border-radius: var(--radius); border: 1px solid #fecaca; }
        
        html[dir="rtl"] { direction: rtl; }
    </style>
</head>
<body>
    <div id="login-screen">
        <div class="login-box">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-width: 100%; height: auto; max-height: 80px;">
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
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="username">
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
