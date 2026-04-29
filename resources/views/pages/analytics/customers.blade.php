@extends('layouts.analytics')

@section('analytics_title', __('Customer & Loyalty Intelligence'))
@section('analytics_subtitle', __('RFM segmentation, CLV, and churn risk analysis'))

@section('analytics_content')
<div class="space-y-8">
    <!-- RFM Segmentation -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Customer Segmentation (RFM)') }}</h3>
                    <p class="text-slate-500 text-xs">{{ __('Recency vs Frequency (Bubble size = Monetary)') }}</p>
                </div>
                <div class="flex gap-2">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <span class="material-symbols-outlined text-sm">bubble_chart</span>
                    </span>
                </div>
            </div>
            <div class="h-[400px] w-full">
                <canvas id="rfmChart"></canvas>
            </div>
        </div>

        <!-- Segment Breakdown -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Segment Distribution') }}</h3>
            <div class="space-y-4">
                @php
                    $segments = [
                        'Champions' => ['color' => 'emerald', 'desc' => 'Your best customers'],
                        'Loyal' => ['color' => 'blue', 'desc' => 'Consistent purchasers'],
                        'Potential Loyalists' => ['color' => 'indigo', 'desc' => 'Recent but low frequency'],
                        'About To Sleep' => ['color' => 'amber', 'desc' => 'Decreasing activity'],
                        'At Risk' => ['color' => 'orange', 'desc' => 'High risk of churn'],
                        'Hibernating' => ['color' => 'rose', 'desc' => 'Long time since last purchase'],
                    ];
                @endphp
                @foreach($segments as $name => $info)
                @php $count = $rfm->where('segment', $name)->count(); @endphp
                <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-50 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full bg-{{ $info['color'] }}-500"></span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ __($name) }}</div>
                            <div class="text-[9px] text-slate-400 font-bold uppercase">{{ __($info['desc']) }}</div>
                        </div>
                    </div>
                    <div class="text-sm font-extrabold text-slate-900">{{ $count }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- CLV & Loyalty -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Customer Lifetime Value Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Top 20 Customers by CLV') }}</h3>
                <span class="text-emerald-600 font-extrabold text-xs uppercase">{{ __('Lifetime Value') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Customer') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Orders') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Total Spent') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('CLV Estimate') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($clv->take(10) as $c)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $c->name }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $c->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ $c->total_orders }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ number_format($c->total_spent, 0) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-indigo-600 font-extrabold">{{ number_format($c->clv, 0) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loyalty & Churn -->
        <div class="space-y-8">
            <!-- Loyalty Points Leaderboard -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Loyalty Points Leaderboard') }}</h3>
                <div class="space-y-4">
                    @foreach($loyalty as $l)
                    <div class="flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                                {{ substr($l->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-bold text-slate-700">{{ $l->name }}</span>
                        </div>
                        <div class="text-amber-600 font-extrabold text-sm">{{ number_format($l->loyalty_points) }} {{ __('pts') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Churn Risk Indicator (Buying Frequency) -->
            <div class="bg-slate-900 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-16 -mt-16"></div>
                <div class="flex items-center gap-3 mb-6">
                    <span class="p-2 bg-white/10 text-rose-300 rounded-lg">
                        <span class="material-symbols-outlined">running_with_errors</span>
                    </span>
                    <div>
                        <h3 class="text-white font-manrope font-bold text-lg">{{ __('Churn Risk Indicators') }}</h3>
                        <p class="text-slate-400 text-[10px] uppercase font-bold">{{ __('Based on purchase frequency, not debt') }}</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-[200px] overflow-y-auto no-scrollbar">
                    @foreach($churn as $c)
                    <div class="p-3 bg-white/5 rounded-2xl border border-white/10 flex items-center justify-between">
                        <div>
                            <div class="text-white text-xs font-bold">{{ $c->name }}</div>
                            <div class="text-rose-300 text-[9px] font-bold uppercase">{{ __('Last:') }} {{ $c->last_purchase }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-white text-xs font-extrabold">{{ $c->delay_days }} {{ __('Purchase Delay') }}</div>
                            <div class="text-slate-400 text-[9px] font-bold uppercase">{{ __('Risk: High') }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-6 border-t border-white/10 flex justify-between items-center">
                    <span class="text-white/60 text-[10px] font-bold uppercase tracking-widest">{{ __('Total High Risk Customers') }}</span>
                    <span class="text-white font-extrabold text-2xl">{{ $churn->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // RFM Scatter Chart
    const rfmData = {!! json_encode($rfm->map(fn($c) => [
        'x' => $c->recency,
        'y' => $c->frequency,
        'r' => min(20, max(4, $c->monetary / 1000)), // Scale monetary to bubble size
        'name' => $c->name,
        'segment' => $c->segment
    ])) !!};

    const segmentColors = {
        'Champions': '#10b981',
        'Loyal': '#3b82f6',
        'Potential Loyalists': '#6366f1',
        'About To Sleep': '#f59e0b',
        'At Risk': '#f97316',
        'Hibernating': '#f43f5e'
    };

    new Chart(document.getElementById('rfmChart'), {
        type: 'bubble',
        data: {
            datasets: Object.keys(segmentColors).map(segment => ({
                label: segment,
                data: rfmData.filter(d => d.segment === segment),
                backgroundColor: segmentColors[segment] + '80', // 50% opacity
                borderColor: segmentColors[segment],
                borderWidth: 1
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.raw.name}: R=${ctx.raw.x}d, F=${ctx.raw.y}tx`
                    }
                }
            },
            scales: {
                y: { title: { display: true, text: '{{ __("Frequency (Total Orders)") }}', font: { size: 10, weight: 'bold' } }, grid: { borderDash: [5, 5] } },
                x: { title: { display: true, text: '{{ __("Recency (Days since last order)") }}', font: { size: 10, weight: 'bold' } }, grid: { display: false } }
            }
        }
    });
</script>
@endpush
@endsection
