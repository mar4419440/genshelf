@extends('layouts.analytics')

@section('analytics_title', __('Financial Control'))
@section('analytics_subtitle', __('P&L tracking, cash flow, and financial liability'))

@section('analytics_content')
<div class="space-y-8">
    <!-- P&L Summary Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Profit & Loss Statement') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="px-6 py-4 text-left">{{ __('Line Item') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('This Period') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Previous Period') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('This Year (YTD)') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Last Year') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @php
                        $rows = [
                            ['label' => __('Gross Revenue'), 'key' => 'revenue', 'type' => 'income'],
                            ['label' => __('Cost of Goods (COGS)'), 'key' => 'cogs', 'type' => 'expense'],
                            ['label' => __('Gross Profit'), 'key' => 'gross', 'type' => 'total'],
                            ['label' => __('Operating Expenses'), 'key' => 'expenses', 'type' => 'expense'],
                            ['label' => __('Net Profit'), 'key' => 'net_profit', 'type' => 'net'],
                        ];
                    @endphp
                    @foreach($rows as $row)
                    @php
                        $val = $row['key'] == 'gross' ? ($pnl['current']->revenue - $pnl['current']->cogs) : $pnl['current']->{$row['key']};
                        $prevVal = $row['key'] == 'gross' ? ($pnl['prev']->revenue - $pnl['prev']->cogs) : $pnl['prev']->{$row['key']};
                        $tyVal = $row['key'] == 'gross' ? ($pnl['thisYear']->revenue - $pnl['thisYear']->cogs) : $pnl['thisYear']->{$row['key']};
                        $lyVal = $row['key'] == 'gross' ? ($pnl['lastYear']->revenue - $pnl['lastYear']->cogs) : $pnl['lastYear']->{$row['key']};
                    @endphp
                    <tr class="{{ $row['type'] == 'total' ? 'bg-slate-50 font-bold' : ($row['type'] == 'net' ? 'bg-primary/5 font-extrabold' : '') }}">
                        <td class="px-6 py-4">{{ $row['label'] }}</td>
                        <td class="px-6 py-4 text-right {{ $row['type'] == 'income' ? 'text-emerald-600' : ($row['type'] == 'expense' ? 'text-rose-600' : '') }}">
                            {{ number_format($val, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-400">{{ number_format($prevVal, 2) }}</td>
                        <td class="px-6 py-4 text-right text-slate-900">{{ number_format($tyVal, 2) }}</td>
                        <td class="px-6 py-4 text-right text-slate-900">{{ number_format($lyVal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cash Flow & Expenses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Cash Flow Statement -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="font-manrope font-bold text-lg text-slate-900 mb-8">{{ __('Cash Flow Statement') }}</h3>
            <div class="space-y-6">
                @foreach(['operating' => __('Operating Activities'), 'investing' => __('Investing Activities'), 'financing' => __('Financing Activities')] as $key => $label)
                <div class="p-6 rounded-2xl border border-slate-100 bg-slate-50/50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-bold text-sm text-slate-900">{{ $label }}</h4>
                        <span class="text-xs font-extrabold {{ $cashFlow[$key]['in'] - $cashFlow[$key]['out'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ __('NET:') }} {{ number_format($cashFlow[$key]['in'] - $cashFlow[$key]['out'], 0) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Inflows') }}</div>
                            <div class="text-lg font-extrabold text-emerald-600">{{ number_format($cashFlow[$key]['in'], 0) }}</div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Total Outflows') }}</div>
                            <div class="text-lg font-extrabold text-rose-600">{{ number_format($cashFlow[$key]['out'], 0) }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Expense Treemap -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm flex flex-col">
            <h3 class="font-manrope font-bold text-lg text-slate-900 mb-8">{{ __('Expense Analysis (by Category)') }}</h3>
            <div class="flex-1 min-h-[300px] w-full">
                <canvas id="expenseChart"></canvas>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-4">
                @foreach($expenseStats['treemap'] as $exp)
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                    <span class="w-3 h-3 rounded-full bg-primary/40"></span>
                    <div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase">{{ __($exp->category) }}</div>
                        <div class="text-sm font-extrabold text-slate-900">{{ number_format($exp->total, 0) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Debt Tracker & Tax -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Debt Tracker -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Outstanding Debtors') }}</h3>
                <span class="text-rose-600 font-extrabold text-sm">{{ number_format($debtors->sum('outstanding'), 2) }} {{ $currency }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Customer') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Phone') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Balance') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($debtors->take(6) as $debtor)
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $debtor->name }}</td>
                            <td class="px-6 py-4 text-center text-slate-500">{{ $debtor->phone }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-rose-600">{{ number_format($debtor->outstanding, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <button class="p-2 text-primary hover:bg-primary/5 rounded-lg">
                                    <span class="material-symbols-outlined text-sm">notifications</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tax Collected vs Remitted -->
        <div class="bg-slate-900 p-8 rounded-3xl shadow-2xl text-white">
            <h3 class="font-manrope font-bold text-lg mb-8">{{ __('Taxation Overview') }}</h3>
            <div class="space-y-8">
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Collected') }}</span>
                        <span class="text-xl font-extrabold text-emerald-400">{{ number_format($taxStats['collected'] ?? 0, 0) }}</span>
                    </div>
                    <div class="h-2 w-full bg-white/10 rounded-full overflow-hidden">
                        <div class="bg-emerald-400 h-full" style="width: 100%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Remitted') }}</span>
                        <span class="text-xl font-extrabold text-amber-400">{{ number_format($taxStats['remitted'] ?? 0, 0) }}</span>
                    </div>
                    @php 
                        $taxPct = ($taxStats['collected'] ?? 0) > 0 ? (($taxStats['remitted'] ?? 0) / $taxStats['collected']) * 100 : 0;
                    @endphp
                    <div class="h-2 w-full bg-white/10 rounded-full overflow-hidden">
                        <div class="bg-amber-400 h-full" style="width: {{ min(100, $taxPct) }}%"></div>
                    </div>
                </div>
                <div class="pt-8 border-t border-white/10">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Current Liability') }}</div>
                    <div class="text-4xl font-extrabold text-indigo-400">{{ number_format(($taxStats['collected'] ?? 0) - ($taxStats['remitted'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Expense Pie Chart
    new Chart(document.getElementById('expenseChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($expenseStats['treemap']->pluck('category')) !!},
            datasets: [{
                data: {!! json_encode($expenseStats['treemap']->pluck('total')) !!},
                backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } }
            }
        }
    });
</script>
@endpush
@endsection
