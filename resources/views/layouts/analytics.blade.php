@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary': '#4f46e5',
                    'primary-h': '#4338ca',
                    'surface': '#f8fafc',
                    'card': '#ffffff',
                    'border': '#e2e8f0',
                },
                fontFamily: {
                    'manrope': ['Manrope', 'sans-serif'],
                    'inter': ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
    .analytics-tab.active {
        color: var(--pr);
        border-bottom: 2px solid var(--pr);
        background: var(--pr-l);
    }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .active .material-symbols-outlined {
        font-variation-settings: 'FILL' 1;
    }
</style>
@endpush

@section('content')
<div class="analytics-container font-inter">
    <!-- Header with Date Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="font-manrope font-extrabold text-3xl text-slate-900 tracking-tight">
                @yield('analytics_title', 'Analytics Report')
            </h1>
            <p class="text-slate-500 mt-1">@yield('analytics_subtitle', 'Comprehensive business intelligence for GenShelf')</p>
        </div>

        <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-2">
            <form id="dateFilterForm" method="GET" class="flex flex-wrap items-center gap-2">
                @php
                    $periods = [
                        'today' => __('Today'),
                        'this_week' => __('This Week'),
                        'this_month' => __('This Month'),
                        'last_month' => __('Last Month'),
                        'this_quarter' => __('This Quarter'),
                        'this_year' => __('This Year'),
                        'custom' => __('Custom Range')
                    ];
                @endphp
                <select name="period" id="periodSelect" class="bg-slate-50 border-none rounded-xl text-sm font-semibold px-4 py-2 focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer" onchange="handlePeriodChange(this.value)">
                    @foreach($periods as $val => $label)
                        <option value="{{ $val }}" {{ ($period ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <div id="customDateRange" class="{{ ($period ?? '') == 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date', substr($start, 0, 10)) }}" class="bg-slate-50 border-none rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-primary/20">
                    <span class="text-slate-400 text-xs font-bold">{{ __('TO') }}</span>
                    <input type="date" name="end_date" value="{{ request('end_date', substr($end, 0, 10)) }}" class="bg-slate-50 border-none rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-primary/20">
                </div>

                <button type="submit" class="bg-primary text-white p-2 rounded-xl hover:bg-primary-h transition-all shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Dashboard Tabs -->
    <div class="flex overflow-x-auto pb-1 mb-8 border-b border-slate-200 no-scrollbar gap-2">
        @php
            $tabs = [
                ['route' => 'analytics.executive', 'label' => __('Executive Overview'), 'icon' => 'monitoring'],
                ['route' => 'analytics.sales', 'label' => __('Sales & Revenue'), 'icon' => 'payments'],
                ['route' => 'analytics.inventory', 'label' => __('Inventory Intelligence'), 'icon' => 'inventory_2'],
                ['route' => 'analytics.finance', 'label' => __('Financial Control'), 'icon' => 'account_balance_wallet'],
                ['route' => 'analytics.customers', 'label' => __('Customer & Loyalty'), 'icon' => 'group'],
                ['route' => 'analytics.operations', 'label' => __('Operations & Quality'), 'icon' => 'settings_suggest'],
            ];
        @endphp

        @foreach($tabs as $tab)
            <a href="{{ route($tab['route'], ['period' => $period ?? 'this_month']) }}" 
               class="analytics-tab flex items-center gap-2 px-5 py-3 rounded-t-xl text-sm font-bold whitespace-nowrap transition-all hover:bg-slate-50 {{ request()->routeIs($tab['route']) ? 'active bg-white text-primary border-b-2 border-primary shadow-[0_4px_10px_-5px_rgba(79,70,229,0.3)]' : 'text-slate-500' }}">
                <span class="material-symbols-outlined text-lg">{{ $tab['icon'] }}</span>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <!-- Main Dashboard Content -->
    <div class="analytics-content">
        @yield('analytics_content')
    </div>
</div>

<script>
    function handlePeriodChange(val) {
        const customRange = document.getElementById('customDateRange');
        if (val === 'custom') {
            customRange.classList.remove('hidden');
            customRange.classList.add('flex');
        } else {
            customRange.classList.add('hidden');
            customRange.classList.remove('flex');
            document.getElementById('dateFilterForm').submit();
        }
    }
</script>
@endsection
