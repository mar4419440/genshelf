@extends('layouts.analytics')

@section('analytics_title', __('Inventory Intelligence'))
@section('analytics_subtitle', __('Stock health, valuation, and reorder optimization'))

@section('analytics_content')
<div class="space-y-8">
    <!-- Stock Health KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            $healthCards = [
                ['label' => __('Normal Stock'), 'value' => $stockHealth->normal, 'icon' => 'check_circle', 'color' => 'emerald'],
                ['label' => __('Low Stock'), 'value' => $stockHealth->low, 'icon' => 'warning', 'color' => 'amber'],
                ['label' => __('Out of Stock'), 'value' => $stockHealth->out, 'icon' => 'error', 'color' => 'rose'],
                ['label' => __('Expiring Soon'), 'value' => $stockHealth->expiring, 'icon' => 'timer', 'color' => 'indigo'],
            ];
        @endphp

        @foreach($healthCards as $card)
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 group hover:border-{{ $card['color'] }}-300 transition-all">
                <div class="w-12 h-12 rounded-xl bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">{{ $card['icon'] }}</span>
                </div>
                <div>
                    <h3 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">{{ $card['label'] }}</h3>
                    <div class="text-2xl font-extrabold text-slate-900">{{ number_format($card['value']) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Inventory Valuation & Storage -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Inventory Valuation & Potential') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Product') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Stock') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Total Cost') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Potential Rev') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Potential Profit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($valuation->take(10) as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ $item->category }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ number_format($item->current_stock) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($item->total_cost, 0) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($item->potential_revenue, 0) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-emerald-600 font-extrabold">{{ number_format($item->potential_profit, 0) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-extrabold text-slate-900">
                        <tr>
                            <td class="px-6 py-4">{{ __('TOTALS') }}</td>
                            <td class="px-6 py-4 text-center">{{ number_format($valuation->sum('current_stock')) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($valuation->sum('total_cost'), 2) }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($valuation->sum('potential_revenue'), 2) }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600">{{ number_format($valuation->sum('potential_profit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Storage Distribution Chart -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-sm text-slate-900 mb-6 uppercase tracking-widest">{{ __('Storage Utilization') }}</h3>
            <div class="h-[300px] w-full">
                <canvas id="storageChart"></canvas>
            </div>
            <div class="mt-6 space-y-2">
                @foreach($storageDistribution->take(5) as $sd)
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-slate-500 font-bold">{{ $sd->storage }} / {{ $sd->product }}</span>
                    <span class="text-slate-900 font-extrabold">{{ number_format($sd->qty) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Reorder Prediction & Expiry -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Reorder Predictions -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Reorder Predictions') }}</h3>
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <span class="material-symbols-outlined text-sm">smart_toy</span>
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Product') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Daily Sales') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Days Left') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Suggested Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reorderPrediction->take(8) as $rp)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $rp->name }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-tighter">{{ $rp->supplier ?: 'No Supplier' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ round($rp->daily_rate, 1) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-lg font-extrabold text-[10px] 
                                    {{ $rp->days_remaining < 7 ? 'bg-rose-100 text-rose-700' : ($rp->days_remaining < 14 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $rp->days_remaining == 999 ? '∞' : $rp->days_remaining . ' ' . __('Days') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ $rp->suggested_date }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expiry Tracker -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Batch Expiry Tracker') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Batch / Product') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Qty') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Expiration') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expiryTracker->take(8) as $et)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $et->product }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $et->batch_number ?: 'NO-BATCH' }} / {{ $et->storage }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ number_format($et->qty) }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ $et->expiration_date }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-lg font-extrabold text-[10px] 
                                    {{ $et->days_until < 30 ? 'bg-rose-100 text-rose-700' : ($et->days_until < 60 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $et->days_until < 0 ? __('EXPIRED') : $et->days_until . ' ' . __('Days') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Storage Distribution Chart (Grouped Bar)
    new Chart(document.getElementById('storageChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($storageDistribution->pluck('product')->unique()->values()) !!},
            datasets: [
                @foreach($storageDistribution->pluck('storage')->unique() as $index => $storage)
                {
                    label: '{{ $storage }}',
                    data: {!! json_encode($storageDistribution->pluck('product')->unique()->values()->map(function($p) use ($storage, $storageDistribution) {
                        return (float)($storageDistribution->where('product', $p)->where('storage', $storage)->first()->qty ?? 0);
                    })) !!},
                    backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'][{{ $index }} % 5],
                    borderRadius: 4
                },
                @endforeach
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } } 
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });
</script>
@endpush
@endsection
