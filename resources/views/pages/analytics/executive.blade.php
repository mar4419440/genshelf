@extends('layouts.analytics')

@section('analytics_title', __('Executive Overview'))
@section('analytics_subtitle', __('High-level business performance and key metrics'))

@section('analytics_content')
<div class="space-y-8">
    <!-- KPI Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @php
            $cards = [
                ['label' => __('Total Revenue'), 'value' => $kpis->revenue, 'prev' => $prevKpis->revenue, 'format' => 'currency', 'icon' => 'payments', 'color' => 'indigo'],
                ['label' => __('Net Profit'), 'value' => $kpis->net_profit, 'prev' => $prevKpis->net_profit, 'format' => 'currency', 'icon' => 'account_balance_wallet', 'color' => 'emerald'],
                ['label' => __('Total Invoices'), 'value' => $kpis->invoices, 'prev' => $prevKpis->invoices, 'format' => 'number', 'icon' => 'description', 'color' => 'blue'],
                ['label' => __('Avg Order Value'), 'value' => $kpis->aov, 'prev' => $prevKpis->aov, 'format' => 'currency', 'icon' => 'shopping_cart', 'color' => 'amber'],
                ['label' => __('Active Customers'), 'value' => $kpis->active_customers, 'prev' => $prevKpis->active_customers, 'format' => 'number', 'icon' => 'group', 'color' => 'rose'],
            ];
        @endphp

        @foreach($cards as $card)
            @php
                $diff = $card['prev'] > 0 ? (($card['value'] - $card['prev']) / $card['prev']) * 100 : 0;
                $isUp = $diff >= 0;
            @endphp
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-xl">{{ $card['icon'] }}</span>
                    </div>
                    <div class="flex items-center gap-1 {{ $isUp ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-bold bg-{{ $isUp ? 'emerald' : 'rose' }}-50 px-2 py-1 rounded-lg">
                        <span class="material-symbols-outlined text-[14px]">{{ $isUp ? 'trending_up' : 'trending_down' }}</span>
                        {{ abs(round($diff, 1)) }}%
                    </div>
                </div>
                <div class="space-y-1">
                    <h3 class="text-slate-500 text-xs font-bold uppercase tracking-wider">{{ $card['label'] }}</h3>
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        @if($card['format'] == 'currency')
                            {{ number_format($card['value'], 2) }} <span class="text-sm font-medium opacity-60">{{ $currency }}</span>
                        @else
                            {{ number_format($card['value']) }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Main Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="font-manrope font-bold text-xl text-slate-900">{{ __('Revenue Performance') }}</h3>
                    <p class="text-slate-500 text-xs">{{ __('Comparison with same period last year') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-primary"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">{{ __('Current Period') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-slate-300"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Last Year') }}</span>
                    </div>
                </div>
            </div>
            <div class="h-[400px] w-full">
                <canvas id="trendChart"></canvas>
            </div>

            @if(!empty($forecast['forecast']))
            <div class="mt-8 pt-8 border-t border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <span class="material-symbols-outlined">auto_graph</span>
                    </div>
                    <div>
                        <h4 class="font-manrope font-bold text-sm text-slate-900">{{ __('30-Day Revenue Forecast') }}</h4>
                        <p class="text-slate-500 text-[10px]">{{ __('Based on Linear Regression analysis of the last 90 days') }}</p>
                    </div>
                </div>
                <div class="h-[150px] w-full">
                    <canvas id="forecastChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Mini Charts -->
        <div class="space-y-8">
            <!-- Top Products -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Top 5 Products') }}</h3>
                <div class="space-y-4">
                    @foreach($miniCharts['top_products'] as $p)
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="font-bold text-slate-700 truncate w-32">{{ $p->name }}</span>
                            <span class="font-extrabold text-slate-900">{{ number_format($p->revenue, 0) }} {{ $currency }}</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                            @php $pct = $kpis->revenue > 0 ? ($p->revenue / $kpis->revenue) * 100 : 0; @endphp
                            <div class="bg-primary h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Payment Methods') }}</h3>
                <div class="h-[200px] w-full">
                    <canvas id="paymentChart"></canvas>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    @foreach($miniCharts['payment_distribution'] as $pm)
                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-600 bg-slate-50 p-2 rounded-xl">
                            <span class="w-2 h-2 rounded-full {{ in_array($pm->payment_method, ['debt', 'partial']) ? 'bg-amber-500' : 'bg-primary' }}"></span>
                            <span class="uppercase">{{ __($pm->payment_method) }}</span>
                            <span class="ml-auto">{{ round(($pm->total / ($kpis->revenue ?: 1)) * 100) }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Return Rate -->
            <div class="bg-slate-900 p-6 rounded-3xl shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                <h3 class="text-white/60 text-[10px] font-bold uppercase tracking-widest mb-2">{{ __('Return Rate') }}</h3>
                <div class="flex items-end gap-3">
                    <div class="text-4xl font-extrabold text-white">{{ round($miniCharts['return_rate'], 1) }}%</div>
                    <div class="mb-1 text-[10px] font-bold {{ $miniCharts['return_rate'] < 5 ? 'text-emerald-400' : ($miniCharts['return_rate'] < 10 ? 'text-amber-400' : 'text-rose-400') }}">
                        {{ $miniCharts['return_rate'] < 5 ? 'EXCELLENT' : ($miniCharts['return_rate'] < 10 ? 'MODERATE' : 'CRITICAL') }}
                    </div>
                </div>
                <div class="mt-4 h-1 w-full bg-white/10 rounded-full">
                    <div class="{{ $miniCharts['return_rate'] < 5 ? 'bg-emerald-400' : ($miniCharts['return_rate'] < 10 ? 'bg-amber-400' : 'bg-rose-400') }} h-full" style="width: {{ $miniCharts['return_rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Shared Config
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($trend['labels']) !!},
            datasets: [
                {
                    label: '{{ __("Current Period") }}',
                    data: {!! json_encode($trend['currentData']) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: '{{ __("Last Year") }}',
                    data: {!! json_encode($trend['lastYearData']) !!},
                    borderColor: '#cbd5e1',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    @if(!empty($forecast['forecast']))
    // Forecast Chart
    new Chart(document.getElementById('forecastChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($forecast['labels']) !!},
            datasets: [{
                label: '{{ __("Forecast") }}',
                data: {!! json_encode($forecast['forecast']) !!},
                borderColor: '#f59e0b',
                borderDash: [8, 4],
                borderWidth: 3,
                pointRadius: 0,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { ticks: { font: { size: 9 } } }
            }
        }
    });
    @endif

    // Payment Distribution
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($miniCharts['payment_distribution']->pluck('payment_method')) !!},
            datasets: [{
                data: {!! json_encode($miniCharts['payment_distribution']->pluck('total')) !!},
                backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#f59e0b', '#f43f5e'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
@endsection
