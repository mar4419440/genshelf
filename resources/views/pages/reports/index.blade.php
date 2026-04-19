@extends('layouts.app')

@section('content')
    <div class="page-hdr">
        <h2 style="font-size:24px; font-weight:700;">{{ __('Business Intelligence Hub') }}</h2>
        <div style="display:flex;gap:8px">
            <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-sm btn-o">📥 {{ __('Export CSV') }}</a>
            <button class="btn btn-sm btn-gn" onclick="location.reload()">🔄 {{ __('Refresh Data') }}</button>
        </div>
    </div>

    <!-- BI Tabs -->
    @if($dashboard->overdueCount > 0)
        <div class="card" style="background: rgba(var(--rd-rgb), 0.1); border: 1px solid var(--rd); padding: 16px; margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px;">⚠️</span>
                <div>
                    <strong style="color:var(--rd); display:block;">{{ __('Attention Needed: Overdue Debts') }}</strong>
                    <span style="font-size:13px; color:var(--tx2);">{{ $dashboard->overdueCount }} {{ __('transactions have passed their due date.') }}</span>
                </div>
            </div>
            <button class="btn btn-sm btn-rd" onclick="showTab('tab-analysis', document.querySelectorAll('.tab-btn')[1])">{{ __('View Details') }}</button>
        </div>
    @endif

    @if($dashboard->lowAlerts > 0)
        <div class="card" style="background: rgba(var(--o-rgb), 0.1); border: 1px solid var(--o); padding: 16px; margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px;">📦</span>
                <div>
                    <strong style="color:var(--o); display:block;">{{ __('Low Stock Warning') }}</strong>
                    <span style="font-size:13px; color:var(--tx2);">{{ $dashboard->lowAlerts }} {{ __('products are below their minimum threshold.') }}</span>
                </div>
            </div>
            <a href="{{ route('inventory') }}" class="btn btn-sm btn-o">{{ __('Manage Inventory') }}</a>
        </div>
    @endif

    <div style="display:flex; gap:12px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
        <button class="tab-btn active" onclick="showTab('tab-dashboard', this)">📊 {{ __('Dashboard') }}</button>
        <button class="tab-btn" onclick="showTab('tab-analysis', this)">🔬 {{ __('Analysis') }}</button>
        <button class="tab-btn" onclick="showTab('tab-history', this)">🕒 {{ __('Historical Log') }}</button>
    </div>

    <!-- TAB: DASHBOARD -->
    <div id="tab-dashboard" class="bi-tab active">
        <div class="card-grid card-grid-4" style="margin-bottom: 24px;">
            <div class="card metric-card">
                <div class="metric-val">{{ number_format($dashboard->todayRevenue, 2) }}</div>
                <div class="metric-lbl">{{ __('Today\'s Revenue') }}</div>
            </div>
            <div class="card metric-card">
                <div class="metric-val">{{ $dashboard->totalStock }}</div>
                <div class="metric-lbl">{{ __('Total Stock Units') }}</div>
            </div>
            <div class="card metric-card {{ $dashboard->lowAlerts > 0 ? 'card-rd' : '' }}">
                <div class="metric-val">{{ $dashboard->lowAlerts }}</div>
                <div class="metric-lbl">{{ __('Low Stock Alerts') }}</div>
            </div>
            <div class="card metric-card">
                <div class="metric-val">{{ $dashboard->pendingPO }}</div>
                <div class="metric-lbl">{{ __('Pending POs') }}</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom: 20px;">
            <div class="card">
                <h3 style="margin-bottom:16px;">📈 {{ __('Weekly Sales Trend') }}</h3>
                <div style="height: 200px; display: flex; align-items: flex-end; gap: 12px; padding: 10px 0;">
                    @foreach($dashboard->weekData as $index => $val)
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center;">
                            <div style="width:100%; max-width:40px; background:{{ $index == $dashboard->todayDay ? 'var(--pr)' : 'var(--pr-l)' }}; height: {{ ($val/$dashboard->maxSale) * 100 }}%; border-radius: 4px 4px 0 0; position:relative;" title="{{ number_format($val, 2) }}">
                               @if($val > 0) <span style="position:absolute; top:-18px; font-size:10px; font-weight:700; color:var(--tx2);">{{ number_format($val, 0) }}</span> @endif
                            </div>
                            <div style="font-size:11px; margin-top:8px; color:var(--tx2);">{{ $dashboard->days[$index] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <h3 style="margin-bottom:16px;">🏗️ {{ __('Location Mix') }}</h3>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @foreach($salesByPOS as $sp)
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                <span>{{ $sp->storage->name ?? 'Unknown' }}</span>
                                <span style="font-weight:700;">{{ number_format($sp->revenue, 0) }}</span>
                            </div>
                            <div style="height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; background:var(--gn); width:{{ $dashboard->todayRevenue > 0 ? ($sp->revenue / $summary->revenue) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- TAB: ANALYSIS -->
    <div id="tab-analysis" class="bi-tab" style="display:none;">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
            <div class="card">
                <h3 style="margin-bottom:12px">🔥 {{ __('Top Selling Products') }}</h3>
                <div class="table-wrap">
                    <table>
                        @foreach($topSelling as $index => $p)
                            <tr>
                                <td style="width:30px; font-weight:700; color:var(--tx3)">{{ $index + 1 }}</td>
                                <td>{{ $p['name'] }}</td>
                                <td style="text-align:right"><strong>{{ number_format($p['revenue'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="card">
                <h3 style="margin-bottom:12px">📉 {{ __('Least Selling Products') }}</h3>
                <div class="table-wrap">
                    <table>
                        @foreach($leastSelling as $index => $p)
                            <tr>
                                <td style="width:30px; font-weight:700; color:var(--tx3)">{{ $index + 1 }}</td>
                                <td>{{ $p['name'] }}</td>
                                <td style="text-align:right"><strong>{{ number_format($p['revenue'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <div class="card" style="border-left: 4px solid var(--rd);">
             <h3 style="color:var(--rd); margin-bottom:16px;">⚠️ {{ __('Outstanding Debts') }}</h3>
             <div class="table-wrap">
                 <table>
                     <tr>
                         <th>{{ __('Customer') }}</th>
                         <th>{{ __('Total') }}</th>
                         <th>{{ __('Due') }}</th>
                         <th>{{ __('Date') }}</th>
                     </tr>
                     @forelse($duePayments as $dp)
                        <tr>
                            <td>{{ $dp->customer->name ?? __('Walk-in') }}</td>
                            <td>{{ number_format($dp->total, 2) }}</td>
                            <td style="color:var(--rd); font-weight:700;">{{ number_format($dp->due_amount, 2) }}</td>
                            <td>{{ $dp->due_date ? $dp->due_date->format('Y-m-d') : '—' }}</td>
                        </tr>
                     @empty
                        <tr><td colspan="4" class="empty-state">{{ __('Perfect! No debts found.') }}</td></tr>
                     @endforelse
                 </table>
             </div>
        </div>
    </div>

    <!-- TAB: HISTORY (Hierarchical) -->
    <div id="tab-history" class="bi-tab" style="display:none;">
        <div class="card">
            @foreach($groupedTransactions as $month => $monthTransactions)
                <div style="margin-bottom: 30px;">
                    <div style="background: var(--bg); padding: 8px 16px; border-radius: var(--radius); border: 1px solid var(--border); font-weight: 700; color: var(--pr); margin-bottom: 12px; display:flex; justify-content:space-between; align-items:center;">
                        <span>📅 {{ \Carbon\Carbon::parse($month)->format('F Y') }}</span>
                        <span class="badge badge-pr">{{ $monthTransactions->count() }} {{ __('tx') }}</span>
                    </div>
                    
                    <div class="table-wrap">
                        <table>
                            <tr style="background:#f8fafc; font-size:11px; color:var(--tx2);">
                                <th>{{ __('Day') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Store') }}</th>
                                <th>{{ __('Items') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Paid') }}</th>
                            </tr>
                            @foreach($monthTransactions as $tx)
                                <tr>
                                    <td><strong>{{ $tx->created_at->format('d') }}</strong></td>
                                    <td>{{ $tx->customer->name ?? __('Walk-in') }}</td>
                                    <td><small>{{ $tx->storage->name ?? '—' }}</small></td>
                                    <td>
                                        @php
                                            $items = is_string($tx->items) ? json_decode($tx->items, true) : $tx->items;
                                            $count = is_array($items) ? count($items) : 0;
                                        @endphp
                                        <span class="badge badge-o" style="font-size:10px;">{{ $count }} {{ __('items') }}</span>
                                    </td>
                                    <td style="font-weight:700;">{{ number_format($tx->total, 2) }}</td>
                                    <td style="color:var(--gn)">{{ number_format($tx->paid_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .tab-btn { background:none; border:none; padding: 10px 16px; font-weight: 600; color: var(--tx2); cursor:pointer; }
        .tab-btn.active { color: var(--pr); border-bottom: 2px solid var(--pr); }
        .tab-btn:hover { color: var(--pr); }
        .metric-card { text-align:center; padding: 24px 12px; }
        .metric-val { font-size: 28px; font-weight: 800; color: var(--tx); margin-bottom: 4px; }
        .metric-lbl { font-size: 11px; font-weight: 600; color: var(--tx3); text-transform: uppercase; letter-spacing: 0.5px; }
        .card-rd { background: rgba(var(--rd-rgb), 0.05); border: 1px solid var(--rd); }
    </style>
@endsection

@push('scripts')
    <script>
        function showTab(tabId, btn) {
            document.querySelectorAll('.bi-tab').forEach(t => t.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
@endpush