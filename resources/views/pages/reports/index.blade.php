@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Reports') }}</h2>
        <div style="display:flex;gap:8px">
            <button class="btn btn-sm btn-o" onclick="exportTransactionsCSV()">📥 {{ __('Export CSV') }}</button>
            <button class="btn btn-sm btn-gn" onclick="generateDailyClose()">📊 {{ __('Daily Close Report') }}</button>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('reports') }}" id="filter-form">
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                <!-- Period Select -->
                <div style="flex: 1; min-width: 150px;">
                    <label
                        style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Quick Period') }}</label>
                    <select name="period" class="search-bar" style="width:100%; height:38px;" onchange="this.form.submit()">
                        <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>{{ __('All Time') }}</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                        <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>
                            {{ __('Yesterday') }}
                        </option>
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>
                            {{ __('This Week') }}
                        </option>
                        <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>
                            {{ __('This Month') }}
                        </option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>
                            {{ __('Last Month') }}
                        </option>
                        <option value="this_quarter" {{ request('period') == 'this_quarter' ? 'selected' : '' }}>
                            {{ __('This Quarter') }}
                        </option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>
                            {{ __('This Year') }}
                        </option>
                    </select>
                </div>

                <!-- Specific Month -->
                <div style="width: 120px;">
                    <label
                        style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Month') }}</label>
                    <select name="specific_month" class="search-bar" style="width:100%; height:38px;"
                        onchange="this.form.submit()">
                        <option value="">--</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('specific_month') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Specific Quarter -->
                <div style="width: 100px;">
                    <label
                        style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Quarter') }}</label>
                    <select name="specific_quarter" class="search-bar" style="width:100%; height:38px;"
                        onchange="this.form.submit()">
                        <option value="">--</option>
                        <option value="1" {{ request('specific_quarter') == '1' ? 'selected' : '' }}>Q1</option>
                        <option value="2" {{ request('specific_quarter') == '2' ? 'selected' : '' }}>Q2</option>
                        <option value="3" {{ request('specific_quarter') == '3' ? 'selected' : '' }}>Q3</option>
                        <option value="4" {{ request('specific_quarter') == '4' ? 'selected' : '' }}>Q4</option>
                    </select>
                </div>

                <!-- Specific Year -->
                <div style="width: 100px;">
                    <label
                        style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Year') }}</label>
                    <select name="specific_year" class="search-bar" style="width:100%; height:38px;"
                        onchange="this.form.submit()">
                        <option value="">--</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ request('specific_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Search -->
                <div style="flex: 2; min-width: 200px;">
                    <label
                        style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Search name, item, or value') }}</label>
                    <div style="display:flex; gap:4px">
                        <input type="text" name="search" value="{{ request('search') }}" class="search-bar"
                            style="flex:1; height:38px;" placeholder="{{ __('Search...') }}">
                        <button type="submit" class="btn btn-pr" style="height:38px;">🔍</button>
                        <a href="{{ route('reports') }}" class="btn btn-o"
                            style="height:38px; display:flex; align-items:center;">🔄</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card-grid card-grid-3">
        <div class="card metric-card">
            <div class="metric-val">{{ number_format($summary->revenue, 2) }}</div>
            <div class="metric-lbl">{{ __('Period Revenue') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val">{{ $summary->count }}</div>
            <div class="metric-lbl">{{ __('Transactions') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val">{{ number_format($summary->avg, 2) }}</div>
            <div class="metric-lbl">{{ __('Avg Order Value') }}</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
        <div class="card">
            <h3 style="margin-bottom:12px">🔥 {{ __('Top Selling Products') }}</h3>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Units Sold') }}</th>
                        <th>{{ __('Revenue') }}</th>
                    </tr>
                    @forelse($topSelling as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p['name'] }}</td>
                            <td>{{ $p['units'] }}</td>
                            <td>{{ number_format($p['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:12px">📉 {{ __('Least Selling Products') }}</h3>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Units Sold') }}</th>
                        <th>{{ __('Revenue') }}</th>
                    </tr>
                    @forelse($leastSelling as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p['name'] }}</td>
                            <td>{{ $p['units'] }}</td>
                            <td>{{ number_format($p['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>

    <!-- Multi-POS Sales Summary -->
    <div class="card" style="margin-bottom: 20px;">
        <h3 style="margin-bottom:16px;">🏢 {{ __('Sales by Location / POS') }}</h3>
        <div class="table-wrap">
            <table>
                <tr style="background:#f8fafc;">
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Transactions') }}</th>
                    <th>{{ __('Total Sales') }}</th>
                </tr>
                @foreach($salesByPOS as $sp)
                    <tr>
                        <td><strong>{{ $sp->storage->name ?? __('Unknown') }}</strong></td>
                        <td>
                            <span class="badge {{ ($sp->storage->type ?? '') === 'pos' ? 'badge-gn' : 'badge-o' }}">
                                {{ ($sp->storage->type ?? '') === 'pos' ? __('Store (POS)') : __('Warehouse') }}
                            </span>
                        </td>
                        <td>{{ $sp->count }}</td>
                        <td>{{ number_format($sp->revenue, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background:#f1f5f9; font-weight:700;">
                    <td colspan="3">{{ __('Global Total') }}</td>
                    <td>{{ number_format($salesByPOS->sum('revenue'), 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Due Payments Section -->
    <div class="card" style="margin-bottom: 20px; border-left: 4px solid var(--rd);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 16px;">
            <h3 style="color:var(--rd)">⚠️ {{ __('Due Payments (Debts)') }}</h3>
            <span class="badge badge-rd">{{ $duePayments->count() }}</span>
        </div>
        <div class="table-wrap">
            <table>
                <tr style="background: rgba(var(--rd-rgb), 0.05);">
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Invoice') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Due Amount') }}</th>
                    <th>{{ __('Due Date') }}</th>
                </tr>
                @forelse($duePayments as $dp)
                    <tr>
                        <td><strong>{{ $dp->customer->name ?? __('Walk-in') }}</strong></td>
                        <td>#{{ $dp->id }}</td>
                        <td>{{ number_format($dp->total, 2) }}</td>
                        <td style="color:var(--rd); font-weight:700;">{{ number_format($dp->due_amount, 2) }}</td>
                        <td>
                            @if($dp->due_date)
                                <span style="{{ $dp->due_date < now() ? 'color:var(--rd); font-weight:bold;' : '' }}">
                                    {{ $dp->due_date }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">{{ __('No outstanding debts found.') }}</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px">{{ __('Transaction Log') }}</h3>
        <div class="table-wrap" style="max-height: 400px; overflow-y: auto;">
            <table>
                <tr>
                    <th>{{ __('Items') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Paid') }}</th>
                    <th>{{ __('Due') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
                @forelse($transactions->take(50) as $tx)
                    @php
                        $itemsStr = '';
                        $itemsArr = is_string($tx->items) ? json_decode($tx->items, true) : $tx->items;
                        if (is_array($itemsArr)) {
                            $itemsStr = implode(', ', array_map(function ($i) {
                                return $i['name'] ?? 'Item';
                            }, $itemsArr));
                        }
                    @endphp
                    <tr>
                        <td><small>{{ $itemsStr }}</small></td>
                        <td>{{ number_format($tx->total, 2) }}</td>
                        <td style="color:var(--gn)">{{ number_format($tx->paid_amount, 2) }}</td>
                        <td style="color:var(--rd)">{{ number_format($tx->due_amount, 2) }}</td>
                        <td>{{ $tx->customer->name ?? __('Walk-in Customer') }}</td>
                        <td>{{ $tx->user->displayName ?? ($tx->user->name ?? '') }}</td>
                        <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">{{ __('No data yet') }}</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal" id="modal-box"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function closeModal() { document.getElementById('modal-overlay').classList.remove('show'); }
        function exportTransactionsCSV() { alert('Export logic'); }
        function generateDailyClose() { alert('Daily close logic'); }
    </script>
@endpush