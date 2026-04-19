@extends('layouts.app')

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Dashboard') }}</h2>
    </div>

    <div class="card-grid card-grid-4">
        <div class="card metric-card">
            <div class="metric-val">{{ number_format($todayRevenue, 2) }}</div>
            <div class="metric-lbl">{{ __("Today's Revenue") }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val">{{ $totalStock }}</div>
            <div class="metric-lbl">{{ __('Total Stock') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val" style="color:var(--am)">{{ $lowAlerts }}</div>
            <div class="metric-lbl">{{ __('Low Stock Alerts') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val" style="color:var(--bl)">{{ $pendingPO }}</div>
            <div class="metric-lbl">{{ __('Pending Orders') }}</div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px">{{ __('Weekly Sales') }}</h3>
        <div class="chart-bar-wrap">
            @foreach($weekData as $index => $value)
                <div class="chart-bar">
                    <div class="bar-val">{{ number_format($value) }}</div>
                    <div class="bar {{ $index === $todayDay ? 'today' : '' }}"
                        style="height:{{ $maxSale > 0 ? ($value / $maxSale) * 140 : 0 }}px"></div>
                    <div class="bar-lbl">{{ $days[$index] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px">{{ __('Recent Transactions') }}</h3>
        @if(count($recentTx) === 0)
            <div class="empty-state">{{ __('No data yet') }}</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Items') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                    @foreach($recentTx as $tx)
                        <tr>
                            <td>
                                @php
                                    $items = is_array($tx->items) ? $tx->items : json_decode($tx->items, true);
                                @endphp
                                @if(is_array($items))
                                    @foreach($items as $item)
                                        <span class="badge badge-gn" style="font-size: 10px;">{{ $item['name'] ?? 'Item' }}
                                            ({{ $item['qty'] ?? 1 }})</span>
                                    @endforeach
                                @endif
                            </td>
                            <td>{{ number_format($tx->total, 2) }}</td>
                            <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
@endsection