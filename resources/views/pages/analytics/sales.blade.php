@extends('layouts.analytics')

@section('analytics_title', __('Sales & Revenue Analytics'))
@section('analytics_subtitle', __('Detailed product performance and sales patterns'))

@section('analytics_content')
<div class="space-y-8">
    <!-- ABC Analysis & Sales Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Product Performance Breakdown') }}</h3>
                <div class="flex gap-2">
                    <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">{{ __('ABC CLASSIFIED') }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Product') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Units') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Revenue') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Margin %') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Class') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($abcAnalysis as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-semibold tracking-tighter">{{ $item->category }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ number_format($item->units_sold) }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ number_format($item->revenue, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="{{ $item->gross_margin > 20 ? 'text-emerald-600' : 'text-amber-600' }} font-bold">
                                    {{ round($item->gross_margin, 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-md font-extrabold text-[10px] 
                                    {{ $item->class == 'A' ? 'bg-emerald-100 text-emerald-700' : ($item->class == 'B' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $item->class }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pareto Chart -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Pareto ABC Analysis') }}</h3>
            <div class="h-[300px] w-full">
                <canvas id="paretoChart"></canvas>
            </div>
            <div class="mt-6 space-y-3">
                <div class="p-3 bg-emerald-50 rounded-2xl flex items-center justify-between border border-emerald-100">
                    <span class="text-xs font-bold text-emerald-700">{{ __('Class A (80% Revenue)') }}</span>
                    <span class="text-sm font-extrabold text-emerald-900">{{ $abcAnalysis->where('class', 'A')->count() }} {{ __('Items') }}</span>
                </div>
                <div class="p-3 bg-amber-50 rounded-2xl flex items-center justify-between border border-amber-100">
                    <span class="text-xs font-bold text-amber-700">{{ __('Class B (15% Revenue)') }}</span>
                    <span class="text-sm font-extrabold text-amber-900">{{ $abcAnalysis->where('class', 'B')->count() }} {{ __('Items') }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-2xl flex items-center justify-between border border-slate-200">
                    <span class="text-xs font-bold text-slate-500">{{ __('Class C (5% Revenue)') }}</span>
                    <span class="text-sm font-extrabold text-slate-700">{{ $abcAnalysis->where('class', 'C')->count() }} {{ __('Items') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Heatmap & Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Sales Heatmap -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-lg text-slate-900 mb-6">{{ __('Hourly Sales Heatmap') }}</h3>
            <div class="overflow-x-auto">
                <div class="min-w-[600px]">
                    <div class="grid grid-cols-[80px_repeat(24,1fr)] gap-1">
                        <div></div>
                        @for($h=0; $h<24; $h++)
                            <div class="text-[9px] text-slate-400 font-bold text-center">{{ $h }}h</div>
                        @endfor

                        @php
                            $days = [
                                7 => __('Sat'), 1 => __('Sun'), 2 => __('Mon'), 
                                3 => __('Tue'), 4 => __('Wed'), 5 => __('Thu'), 6 => __('Fri')
                            ];
                            $maxCount = 0;
                            foreach($heatmap as $day) { $maxCount = max($maxCount, max($day)); }
                            if($maxCount == 0) $maxCount = 1;
                        @endphp

                        @foreach($days as $dayId => $dayName)
                            <div class="text-[11px] font-bold text-slate-500 py-1">{{ $dayName }}</div>
                            @foreach($heatmap[$dayId] as $count)
                                @php $opacity = $count / $maxCount; @endphp
                                <div class="aspect-square rounded-sm transition-transform hover:scale-110 cursor-pointer" 
                                     style="background: rgba(79, 70, 229, {{ 0.05 + ($opacity * 0.95) }})"
                                     title="{{ $count }} {{ __('orders') }}">
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 justify-end">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Less') }}</span>
                <div class="w-20 h-2 bg-gradient-to-r from-indigo-50 to-indigo-600 rounded-full"></div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('More') }}</span>
            </div>
        </div>

        <!-- Payment Trend -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-lg text-slate-900 mb-6">{{ __('Payment Method Distribution Trend') }}</h3>
            <div class="h-[300px] w-full">
                <canvas id="paymentTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Special Offers Impact -->
    <div class="bg-slate-900 rounded-3xl p-8 border border-slate-700 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
            <div class="max-w-md">
                <span class="text-indigo-400 text-[10px] font-bold uppercase tracking-widest">{{ __('Marketing Intelligence') }}</span>
                <h3 class="text-white font-manrope font-extrabold text-2xl mt-2">{{ __('Special Offers Impact') }}</h3>
                <p class="text-slate-400 text-sm mt-2 leading-relaxed">
                    {{ __('Comparison of revenue generated through discounted promotions versus standard pricing transactions.') }}
                </p>
                <div class="mt-6 flex gap-4">
                    <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                        <div class="text-white/60 text-[9px] font-bold uppercase tracking-widest">{{ __('Promotion Rev') }}</div>
                        <div class="text-white font-bold text-xl mt-1">{{ number_format($offerImpact['withOffers']->sum('revenue'), 0) }}</div>
                    </div>
                    <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                        <div class="text-white/60 text-[9px] font-bold uppercase tracking-widest">{{ __('Total Discounts') }}</div>
                        <div class="text-indigo-400 font-bold text-xl mt-1">{{ number_format($offerImpact['withOffers']->sum('discount'), 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="flex-1 h-[200px]">
                <canvas id="offerImpactChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pareto Chart
    new Chart(document.getElementById('paretoChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($abcAnalysis->take(15)->pluck('name')) !!},
            datasets: [
                {
                    label: '{{ __("Revenue") }}',
                    data: {!! json_encode($abcAnalysis->take(15)->pluck('revenue')) !!},
                    backgroundColor: '#4f46e5',
                    borderRadius: 4,
                    order: 2
                },
                {
                    label: '{{ __("Cumulative %") }}',
                    data: {!! json_encode($abcAnalysis->take(15)->pluck('cumulative_pct')) !!},
                    type: 'line',
                    borderColor: '#f59e0b',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    yAxisID: 'y1',
                    order: 1,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                y1: { 
                    position: 'right', 
                    beginAtZero: true, 
                    max: 100, 
                    grid: { borderDash: [5, 5] },
                    ticks: { callback: v => v + '%' }
                },
                x: { display: false }
            }
        }
    });

    // Payment Trend (Stacked Area)
    new Chart(document.getElementById('paymentTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($paymentTrend['labels']) !!},
            datasets: [
                {
                    label: '{{ __("Cash") }}',
                    data: {!! json_encode($paymentTrend['datasets']['cash']) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: '{{ __("Card") }}',
                    data: {!! json_encode($paymentTrend['datasets']['card']) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: '{{ __("Debt") }}',
                    data: {!! json_encode($paymentTrend['datasets']['debt']) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } } },
            scales: {
                y: { stacked: true, beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Offer Impact Chart
    new Chart(document.getElementById('offerImpactChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($paymentTrend['labels']) !!},
            datasets: [
                {
                    label: '{{ __("With Offers") }}',
                    data: {!! json_encode($paymentTrend['raw_dates']->map(fn($d) => (float)($offerImpact['withOffers']->get($d)->revenue ?? 0))) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                },
                {
                    label: '{{ __("Standard") }}',
                    data: {!! json_encode($paymentTrend['raw_dates']->map(fn($d) => (float)($offerImpact['withoutOffers']->get($d)->revenue ?? 0))) !!},
                    backgroundColor: 'rgba(255, 255, 255, 0.1)',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.4)', font: { size: 9 } } }
            }
        }
    });
</script>
@endpush
@endsection
