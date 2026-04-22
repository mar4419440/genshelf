@extends('layouts.app')

@php
    $currency = DB::table('settings')->where('key', 'currency')->value('value') ?: 'EGP';
@endphp

@push('styles')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary': '#3a24d8',
                    'on-primary': '#ffffff',
                    'primary-container': '#5446f0',
                    'on-primary-container': '#e1ddff',
                    'secondary': '#505f76',
                    'surface': '#f9f9ff',
                    'on-surface': '#111c2d',
                    'on-surface-variant': '#464556',
                    'outline': '#777587',
                    'outline-variant': '#c7c4d8',
                    'surface-container-lowest': '#ffffff',
                    'surface-container-low': '#f0f3ff',
                    'surface-container': '#e7eeff',
                    'surface-container-high': '#dee8ff',
                    'surface-container-highest': '#d8e3fb',
                    'error': '#ba1a1a',
                },
                fontFamily: {
                    'manrope': ['Manrope', 'sans-serif'],
                    'inter': ['Inter', 'sans-serif'],
                },
                borderRadius: {
                    'xl': '0.75rem',
                    '2xl': '1rem',
                }
            }
        }
    }
</script>
<style>
    .font-manrope { font-family: 'Manrope', sans-serif; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .bg-grid-white { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M92 40h8v10h-8V40zm0 0h8V30h-8v10zm0 20h8v10h-8V60zm0 0h8v10h-8V60zM76 50h8v10h-8V50zm0 0h8V40h-8v10zm0 20h8v10h-8V70zm0 0h8v10h-8V70zM60 40h8v10h-8V40zm0 0h8V30h-8v10zm0 20h8v10h-8V60zm0 0h8v10h-8V60zM44 50h8v10h-8V50zm0 0h8V40h-8v10zm0 20h8v10h-8V70zm0 0h8v10h-8V70zM28 40h8v10h-8V40zm0 0h8V30h-8v10zm0 20h8v10h-8V60zm0 0h8v10h-8V60zM12 50h8v10h-8V50zm0 0h8V40h-8v10zm0 20h8v10h-8V70zm0 0h8v10h-8V70z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
</style>
@endpush

@section('content')
<div class="tw-wrapper font-inter text-on-surface bg-surface min-h-screen pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 px-4">
        <div class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
            <h1 class="font-manrope font-extrabold text-3xl text-primary tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-4xl">analytics</span>
                {{ __('Intelligence Overview') }}
            </h1>
            <p class="text-on-surface-variant mt-1">{{ __('Data-driven insights for your retail operation') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('bi.index') }}" method="GET" class="flex gap-2">
                <select name="period" class="bg-white border border-outline-variant px-4 py-2 rounded-xl text-sm font-semibold outline-none focus:border-primary transition-colors" onchange="this.form.submit()">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                    <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>{{ __('This Week') }}</option>
                    <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                    <option value="this_year" {{ $period == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                </select>
                <button type="button" class="bg-white border border-outline-variant p-2 rounded-xl hover:bg-slate-50 transition-colors shadow-sm" onclick="window.print()">
                    <span class="material-symbols-outlined text-on-surface-variant">download</span>
                </button>
            </form>
        </div>
    </div>

    <!-- KPI Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-4 mb-8">
        <!-- Revenue Hero (Spans 2) -->
        <div class="md:col-span-2 bg-primary-container p-8 rounded-2xl shadow-xl shadow-primary/10 text-on-primary-container relative overflow-hidden flex flex-col justify-between min-h-[220px]">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-manrope font-bold text-xs uppercase tracking-widest opacity-80">{{ __('TOTAL REVENUE') }}</span>
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
                <div class="font-manrope font-extrabold text-5xl text-white mb-4 tracking-tighter">
                    {{ number_format($kpis->revenue, 2) }} <span class="text-2xl font-medium opacity-80">{{ $currency }}</span>
                </div>
            </div>
            <div class="relative z-10 flex items-center gap-3">
                @php 
                    $revDiff = $previousKpis->revenue > 0 ? (($kpis->revenue - $previousKpis->revenue) / $previousKpis->revenue) * 100 : 0;
                @endphp
                <span class="bg-white/20 px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">{{ $revDiff >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
                    {{ abs(round($revDiff, 1)) }}%
                </span>
                <span class="text-xs opacity-70">{{ __('vs last month') }}</span>
            </div>
        </div>

        <!-- Expenses Tile -->
        <div class="bg-white p-6 rounded-2xl border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <span class="font-manrope font-bold text-[10px] text-secondary uppercase tracking-wider block mb-2">{{ __('EXPENSES') }}</span>
                <div class="font-manrope font-extrabold text-3xl text-on-surface">
                    {{ number_format($kpis->expenses, 2) }}
                </div>
            </div>
            <div class="mt-4">
                @php 
                    $expDiff = $previousKpis->expenses > 0 ? (($kpis->expenses - $previousKpis->expenses) / $previousKpis->expenses) * 100 : 0;
                @endphp
                <div class="flex items-center gap-2 mb-3">
                    <span class="{{ $expDiff <= 0 ? 'text-emerald-600' : 'text-error' }} text-xs font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">{{ $expDiff <= 0 ? 'arrow_downward' : 'arrow_upward' }}</span>
                        {{ abs(round($expDiff, 1)) }}%
                    </span>
                    <span class="text-[10px] text-secondary">{{ __('from last period') }}</span>
                </div>
                <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
                    <div class="bg-error h-full" style="width: {{ $kpis->revenue > 0 ? min(100, ($kpis->expenses / $kpis->revenue) * 100) : 0 }}%;"></div>
                </div>
            </div>
        </div>

        <!-- Net Profit Tile -->
        <div class="bg-white p-6 rounded-2xl border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <span class="font-manrope font-bold text-[10px] text-secondary uppercase tracking-wider block mb-2">{{ __('NET PROFIT') }}</span>
                <div class="font-manrope font-extrabold text-3xl text-primary">
                    {{ number_format($kpis->revenue - $kpis->expenses, 2) }}
                </div>
            </div>
            <div class="mt-4">
                 @php 
                    $profit = $kpis->revenue - $kpis->expenses;
                    $prevProfit = $previousKpis->revenue - $previousKpis->expenses;
                    $profitDiff = $prevProfit > 0 ? (($profit - $prevProfit) / $prevProfit) * 100 : 0;
                @endphp
                <div class="flex items-center gap-2 mb-3">
                    <span class="{{ $profitDiff >= 0 ? 'text-emerald-600' : 'text-error' }} text-xs font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">{{ $profitDiff >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
                        {{ abs(round($profitDiff, 1)) }}%
                    </span>
                    <span class="text-[10px] text-secondary">{{ __('growth') }}</span>
                </div>
                <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
                    <div class="bg-primary h-full" style="width: {{ $kpis->revenue > 0 ? max(0, min(100, (($kpis->revenue - $kpis->expenses) / $kpis->revenue) * 100)) : 0 }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-4">
        <!-- Revenue Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-2xl border border-outline-variant shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-manrope font-bold text-xl text-on-surface">{{ __('Revenue Performance') }}</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                            <span class="text-xs font-semibold text-secondary">{{ __('Revenue') }}</span>
                        </div>
                    </div>
                </div>
                <div class="h-[350px] w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Asymmetric Insight Banner -->
            <div class="relative bg-on-surface rounded-2xl p-8 overflow-hidden flex items-center justify-between border border-primary/20 shadow-xl">
                <div class="absolute top-0 left-0 w-full h-full bg-grid-white opacity-5"></div>
                <div class="relative z-10 w-full lg:w-2/3">
                    <p class="font-manrope font-bold text-white text-2xl mb-2">{{ __('Predictive Growth Insights') }}</p>
                    <p class="text-secondary-container text-sm mb-6 leading-relaxed">
                        {{ __('Based on your historical performance, our AI models can forecast upcoming sales trends and inventory needs.') }}
                    </p>
                    <a href="{{ route('bi.forecast') }}" class="inline-flex items-center gap-2 bg-primary text-white font-manrope font-bold text-xs px-6 py-3 rounded-full uppercase tracking-widest shadow-lg hover:bg-primary-container transition-all">
                        <span class="material-symbols-outlined text-sm">auto_graph</span>
                        {{ __('Run Analysis') }}
                    </a>
                </div>
                <div class="absolute right-[-20px] bottom-[-20px] w-48 h-48 opacity-10 hidden lg:block">
                    <span class="material-symbols-outlined text-white text-[180px]">monitoring</span>
                </div>
            </div>
        </div>

        <!-- Sidebar Lists -->
        <div class="space-y-8">
            <!-- Top Items -->
            <div>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="font-manrope font-bold text-lg text-on-surface">{{ __('Top Performing Items') }}</h3>
                    <a href="{{ route('bi.products') }}" class="text-primary font-manrope font-bold text-xs tracking-widest hover:opacity-70 transition-opacity">{{ __('VIEW ALL') }}</a>
                </div>
                <div class="space-y-3">
                    @foreach($topProducts as $p)
                    <div class="flex items-center justify-between p-4 bg-white border border-outline-variant rounded-2xl hover:border-primary/30 hover:bg-surface transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-surface-container flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">package_2</span>
                            </div>
                            <div>
                                <p class="font-manrope font-bold text-on-surface text-sm truncate w-32">{{ $p->name }}</p>
                                <p class="font-inter text-secondary text-[11px] uppercase tracking-wider font-semibold">{{ __($p->category) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-manrope font-extrabold text-on-surface text-sm">{{ number_format($p->revenue, 0) }}</p>
                            <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-bold uppercase tracking-wider">
                                {{ number_format($p->units) }} {{ __('Units') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Customers -->
            <div>
                <div class="flex items-center justify-between mb-4 px-1 mt-4">
                    <h3 class="font-manrope font-bold text-lg text-on-surface">{{ __('Valuable Customers') }}</h3>
                    <a href="{{ route('customers') }}" class="text-primary font-manrope font-bold text-xs tracking-widest hover:opacity-70 transition-opacity">{{ __('MANAGE CRM') }}</a>
                </div>
                <div class="space-y-3">
                    @foreach($topCustomers as $c)
                    <div class="flex items-center justify-between p-4 bg-white border border-outline-variant rounded-2xl hover:border-primary/30 hover:bg-surface transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center group-hover:bg-primary-container group-hover:text-white transition-all overflow-hidden border border-outline-variant/30">
                                <span class="font-manrope font-bold text-sm">{{ substr($c->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-manrope font-bold text-on-surface text-sm">{{ $c->name }}</p>
                                <p class="font-inter text-secondary text-[11px] font-semibold">{{ $c->orders }} {{ __('Orders') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-manrope font-extrabold text-primary text-sm">{{ number_format($c->total, 0) }}</p>
                            <span class="text-secondary text-[9px] font-bold uppercase">{{ __('LTV') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient for the chart
    const gradient = revCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, '#3a24d8');
    gradient.addColorStop(1, '#5446f0');

    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: '{{ __("Revenue") }}',
                data: {!! json_encode($revenueChart['data']) !!},
                borderColor: '#3a24d8',
                borderWidth: 4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3a24d8',
                pointBorderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 6,
                fill: true,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    if (!chartArea) return null;
                    const grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    grad.addColorStop(0, 'rgba(58, 36, 216, 0.1)');
                    grad.addColorStop(1, 'rgba(58, 36, 216, 0)');
                    return grad;
                },
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111c2d',
                    padding: 12,
                    titleFont: { family: 'Manrope', size: 14, weight: 'bold' },
                    bodyFont: { family: 'Inter', size: 13 },
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.03)', borderDash: [5, 5] },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#505f76' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#505f76' }
                }
            }
        }
    });
</script>
@endpush
@endsection
