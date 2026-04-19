<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'GenShelf')) - {{ __('Modern POS & Inventory System') }}</title>
    <meta name="description"
        content="@yield('meta_description', 'GenShelf is a high-performance POS and Inventory management system.')">
    <meta name="keywords" content="@yield('meta_keywords', 'GenShelf, POS System, Inventory Management')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "GenShelf",
      "operatingSystem": "Web",
      "applicationCategory": "BusinessApplication",
      "description": "GenShelf is a high-performance POS and Inventory management system for modern retail businesses."
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f5f7;
            --bg2: #ffffff;
            --bg3: #eaecef;
            --tx: #1a1a2e;
            --tx2: #555770;
            --tx3: #8b8da3;
            --pr: #4f46e5;
            --pr-h: #4338ca;
            --pr-l: #eef2ff;
            --gn: #16a34a;
            --gn-l: #dcfce7;
            --am: #d97706;
            --am-l: #fef3c7;
            --rd: #dc2626;
            --rd-l: #fee2e2;
            --bl: #2563eb;
            --bl-l: #dbeafe;
            --border: #d1d5db;
            --radius: 8px;
            --font: 'Outfit', sans-serif;
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

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--bg2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar .logo {
            padding: 25px 20px;
            text-decoration: none;
            border-bottom: 1px solid var(--bg3);
            display: block;
        }

        .logo-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }

        .logo-icon {
            background: var(--pr);
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .logo-text {
            font-size: 20px;
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

        .sidebar nav {
            display: flex;
            flex-direction: column;
            padding: 10px 0;
            flex: 1;
        }

        .sidebar nav a {
            text-decoration: none;
            color: var(--tx2);
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .15s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar nav a:hover {
            background: var(--bg);
            color: var(--pr);
        }

        .sidebar nav a.active {
            background: var(--pr-l);
            color: var(--pr);
            border-left-color: var(--pr);
            font-weight: 600;
        }

        .sidebar-actions {
            padding: 15px 20px;
            border-top: 1px solid var(--bg3);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .lang-btn {
            background: var(--bg3);
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--tx2);
            border-radius: var(--radius);
            text-decoration: none;
            text-align: center;
        }

        .user-btn {
            background: var(--pr-l);
            color: var(--pr);
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }

        .main-content {
            flex: 1;
            padding: 20px 30px;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            min-width: 0;
        }

        .page-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 15px;
        }

        .page-hdr h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--tx);
        }

        .card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .card-grid {
            display: grid;
            gap: 16px;
        }

        .card-grid-4 {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }

        .metric-card {
            text-align: center;
            padding: 24px 16px;
        }

        .metric-card .metric-val {
            font-size: 32px;
            font-weight: 700;
            color: var(--pr);
        }

        .metric-card .metric-lbl {
            font-size: 13px;
            font-weight: 500;
            color: var(--tx2);
            margin-top: 8px;
            text-transform: uppercase;
        }

        .table-wrap {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--bg3);
            font-size: 13px;
        }

        th {
            background: var(--bg);
            font-weight: 600;
            color: var(--tx2);
            text-transform: uppercase;
            font-size: 11px;
            cursor: pointer;
        }

        .mobile-header {
            display: none;
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 101;
        }

        .mobile-menu-btn {
            font-size: 24px;
            background: none;
            color: var(--tx);
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }

            .mobile-header {
                display: flex;
            }

            .sidebar {
                position: fixed;
                left: -250px;
                transition: left 0.3s ease;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
                height: 100vh;
            }

            .sidebar.active {
                left: 0;
            }

            .overlay.active {
                display: block;
            }

            html[dir="rtl"] .sidebar {
                right: -250px;
                left: auto;
            }

            html[dir="rtl"] .sidebar.active {
                right: 0;
            }

            .main-content {
                padding: 16px 14px;
            }
        }

        html[dir="rtl"] {
            direction: rtl;
        }

        html[dir="rtl"] th,
        html[dir="rtl"] td {
            text-align: right;
        }

        html[dir="rtl"] .sidebar {
            border-right: none;
            border-left: 1px solid var(--border);
        }

        html[dir="rtl"] .sidebar nav a {
            border-left: none;
            border-right: 3px solid transparent;
        }

        html[dir="rtl"] .sidebar nav a.active {
            border-right-color: var(--pr);
        }

        @stack('styles')
    </style>
</head>

<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="mobile-header">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
        <a href="{{ route('dashboard') }}" style="text-decoration:none;">
            <div class="logo-brand">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                        <path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" />
                    </svg>
                </div>
                <div class="logo-text"><span class="gen">Gen</span><span class="shelf">Shelf</span></div>
            </div>
        </a>
        <div style="width: 24px;"></div>
    </div>
    <div class="overlay" id="mobile-overlay" onclick="toggleSidebar()"></div>
    <div class="app-container">
        <div class="sidebar" id="app-sidebar">
            <a href="{{ route('dashboard') }}" class="logo">
                <div class="logo-brand">
                    <div class="logo-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                            <path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4" />
                        </svg>
                    </div>
                    <div class="logo-text"><span class="gen">Gen</span><span class="shelf">Shelf</span></div>
                </div>
            </a>
            <nav>
                @php
                    $roles = auth()->user()->role ? (is_string(auth()->user()->role->permissions) ? json_decode(auth()->user()->role->permissions, true) : auth()->user()->role->permissions) : [];
                    if (empty($roles)) {
                        $roles = ['dashboard', 'pos', 'inventory', 'suppliers', 'customers', 'offers', 'returns', 'finance', 'reports', 'warranty', 'transfers', 'settings', 'users'];
                    }
                @endphp
                @if(in_array('dashboard', $roles)) <a href="{{ route('dashboard') }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 {{ __('Dashboard') }}</a> @endif
                @if(in_array('pos', $roles)) <a href="{{ route('pos') }}"
                class="{{ request()->routeIs('pos') ? 'active' : '' }}">🛒 {{ __('Point of Sale') }}</a> @endif
                @if(in_array('inventory', $roles)) <a href="{{ route('inventory') }}"
                class="{{ request()->routeIs('inventory') ? 'active' : '' }}">📦 {{ __('Inventory') }}</a> @endif
                @if(in_array('suppliers', $roles)) <a href="{{ route('suppliers') }}"
                    class="{{ request()->routeIs('suppliers') ? 'active' : '' }}">🤝 {{ __('Suppliers & PO') }}</a>
                @endif
                @if(in_array('customers', $roles)) <a href="{{ route('customers') }}"
                class="{{ request()->routeIs('customers') ? 'active' : '' }}">👥 {{ __('Customers') }}</a> @endif
                @if(in_array('offers', $roles)) <a href="{{ route('offers') }}"
                class="{{ request()->routeIs('offers') ? 'active' : '' }}">⭐ {{ __('Special Offers') }}</a> @endif
                @if(in_array('returns', $roles)) <a href="{{ route('returns') }}"
                class="{{ request()->routeIs('returns') ? 'active' : '' }}">📦 {{ __('Returns') }}</a> @endif
                @if(in_array('finance', $roles)) <a href="{{ route('finance') }}"
                class="{{ request()->routeIs('finance') ? 'active' : '' }}">💰 {{ __('Finance') }}</a> @endif
                @if(in_array('reports', $roles)) <a href="{{ route('reports') }}"
                class="{{ request()->routeIs('reports') ? 'active' : '' }}">📈 {{ __('Reports') }}</a> @endif
                @if(in_array('warranty', $roles)) <a href="{{ route('warranty') }}"
                class="{{ request()->routeIs('warranty') ? 'active' : '' }}">🛡️ {{ __('Warranty') }}</a> @endif
                @if(in_array('transfers', $roles)) <a href="{{ route('transfers') }}"
                    class="{{ request()->routeIs('transfers') ? 'active' : '' }}">🚚 {{ __('Stock Transfers') }}</a>
                @endif
                @if(in_array('settings', $roles)) <a href="{{ route('settings') }}"
                class="{{ request()->routeIs('settings') ? 'active' : '' }}">⚙️ {{ __('Settings') }}</a> @endif
                @if(in_array('users', $roles)) <a href="{{ route('users') }}"
                class="{{ request()->routeIs('users') ? 'active' : '' }}">🔑 {{ __('Users') }}</a> @endif
            </nav>
            <div class="sidebar-actions">
                <a href="{{ route('set-language', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                    class="lang-btn">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
                <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: none;">@csrf</form>
                <button class="user-btn"
                    onclick="document.getElementById('logout-form').submit();">{{ auth()->user()->name }} ↗</button>
            </div>
        </div>
        <div class="main-content">
            @if(session('success'))
                <div
                    style="background:var(--gn-l);color:var(--gn);padding:12px 16px;border-radius:var(--radius);margin-bottom:16px;font-weight:600;">
            {{ session('success') }}</div> @endif
            @if(session('error'))
                <div
                    style="background:var(--rd-l);color:var(--rd);padding:12px 16px;border-radius:var(--radius);margin-bottom:16px;font-weight:600;">
            {{ session('error') }}</div> @endif
            @yield('content')
            <footer
                style="margin-top: auto; padding: 25px 0; border-top: 1px solid var(--border); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; opacity: 0.8;">
                <span
                    style="font-size: 11px; color: var(--tx2); text-transform: uppercase; letter-spacing: 0.5px;">{{ __('Copyrights Reserved © 2026') }}</span>
                <a href="https://gen-code-delta.vercel.app/" target="_blank" style="text-decoration: none;"><span
                        style="font-weight: 700; color: var(--pr); font-size: 13px;">Gen Code</span></a>
            </footer>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            document.getElementById('app-sidebar').classList.toggle('active');
            document.getElementById('mobile-overlay').classList.toggle('active');
        }
    </script>
    @stack('scripts')
</body>

</html>