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

<div class="card-grid card-grid-3">
    <div class="card metric-card">
        <div class="metric-val">{{ number_format($totalRev, 2) }}</div>
        <div class="metric-lbl">{{ __('All-Time Revenue') }}</div>
    </div>
    <div class="card metric-card">
        <div class="metric-val">{{ $totalTxCount }}</div>
        <div class="metric-lbl">{{ __('Total Transactions') }}</div>
    </div>
    <div class="card metric-card">
        <div class="metric-val">{{ number_format($avgOrder, 2) }}</div>
        <div class="metric-lbl">{{ __('Avg Order Value') }}</div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom:12px">{{ __('Top Selling Products') }}</h3>
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
                <tr><td colspan="4" class="empty-state">{{ __('No data yet') }}</td></tr>
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
                <th>{{ __('Subtotal') }}</th>
                <th>{{ __('Tax') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
            @forelse($transactions->take(50) as $tx)
                @php
                    $itemsStr = '';
                    $itemsArr = is_string($tx->items) ? json_decode($tx->items, true) : $tx->items;
                    if(is_array($itemsArr)) {
                        $itemsStr = implode(', ', array_map(function($i) { return $i['name'] ?? 'Item'; }, $itemsArr));
                    }
                @endphp
                <tr>
                    <td>{{ $itemsStr }}</td>
                    <td>{{ number_format($tx->subtotal, 2) }}</td>
                    <td>{{ number_format($tx->tax, 2) }}</td>
                    <td>{{ number_format($tx->total, 2) }}</td>
                    <td>{{ $tx->customer->name ?? __('Walk-in Customer') }}</td>
                    <td>{{ $tx->user->displayName ?? ($tx->user->name ?? '') }}</td>
                    <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">{{ __('No data yet') }}</td></tr>
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
