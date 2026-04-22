@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="fas fa-brain me-2 text-primary"></i>{{ __('Business Intelligence') }}</h2>
            <p class="text-muted">{{ __('Data-driven insights for your retail operation') }}</p>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('bi.index') }}" method="GET" class="d-inline-flex gap-2">
                <select name="period" class="form-select border-0 shadow-sm bg-white" onchange="this.form.submit()">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                    <option value="this_week" {{ $period == 'this_week' ? 'selected' : '' }}>{{ __('This Week') }}</option>
                    <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                    <option value="this_year" {{ $period == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                </select>
                <button type="button" class="btn btn-white border shadow-sm px-3" onclick="window.print()">
                    <i class="fas fa-download"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- MAIN KPIs -->
    <div class="row mb-4">
        @foreach([
            ['label' => 'Total Revenue', 'value' => $kpis->revenue, 'prev' => $previousKpis->revenue, 'icon' => 'fa-cash-register', 'color' => 'primary'],
            ['label' => 'Gross Profit', 'value' => $kpis->revenue - $kpis->expenses, 'prev' => $previousKpis->revenue - $previousKpis->expenses, 'icon' => 'fa-coins', 'color' => 'success'],
            ['label' => 'Total Expenses', 'value' => $kpis->expenses, 'prev' => $previousKpis->expenses, 'icon' => 'fa-receipt', 'color' => 'danger'],
            ['label' => 'Avg Order Value', 'value' => $kpis->aov, 'prev' => $previousKpis->aov, 'icon' => 'fa-shopping-basket', 'color' => 'warning'],
        ] as $kpi)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-4 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="bg-light p-2 rounded-3 text-{{ $kpi['color'] }}">
                        <i class="fas {{ $kpi['icon'] }} fa-lg"></i>
                    </div>
                    @php 
                        $diff = $kpi['prev'] > 0 ? (($kpi['value'] - $kpi['prev']) / $kpi['prev']) * 100 : ($kpi['value'] > 0 ? 100 : 0);
                    @endphp
                    <span class="x-small px-2 py-1 rounded-pill {{ $diff >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                        <i class="fas fa-arrow-{{ $diff >= 0 ? 'up' : 'down' }} me-1"></i>{{ abs(round($diff, 1)) }}%
                    </span>
                </div>
                <span class="text-muted small fw-bold mb-1">{{ __($kpi['label']) }}</span>
                <h3 class="fw-bold mb-0 lh-1">{{ number_format($kpi['value'], 2) }}</h3>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-white p-4 h-100">
                <h5 class="fw-bold mb-4">{{ __('Revenue Timeline') }}</h5>
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Sales Intelligence -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-white p-4 h-100">
                <h5 class="fw-bold mb-4">{{ __('Quick Insights') }}</h5>
                
                <div class="mb-4">
                    <span class="text-muted small d-block mb-1">{{ __('Peak Sales Day') }}</span>
                    @if($salesIntelligence->peak_day)
                        <h4 class="fw-bold mb-0">{{ Carbon\Carbon::parse($salesIntelligence->peak_day->date)->format('l, d M') }}</h4>
                        <span class="text-primary fw-bold">{{ number_format($salesIntelligence->peak_day->revenue, 2) }}</span>
                    @else
                        <span class="text-muted italic">{{ __('No data yet') }}</span>
                    @endif
                </div>

                <div class="mb-4">
                    <span class="text-muted small d-block mb-2">{{ __('Revenue Distribution') }}</span>
                    <div class="d-flex justify-content-between x-small fw-bold mb-1">
                        <span>Net Profit</span>
                        <span>{{ number_format($kpis->revenue - $kpis->expenses, 2) }}</span>
                    </div>
                    <div class="progress" style="height: 20px; border-radius: 6px;">
                        @php 
                            $profitPct = $kpis->revenue > 0 ? (($kpis->revenue - $kpis->expenses) / $kpis->revenue) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ max(0, $profitPct) }}%">Profit</div>
                        <div class="progress-bar bg-danger" style="width: {{ 100 - max(0, $profitPct) }}%">Exp</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3">
                            <span class="x-small text-muted d-block">{{ __('Transactions') }}</span>
                            <span class="fw-bold h5 mb-0">{{ $kpis->tx_count }}</span>
                        </div>
                    </div>
                     <div class="col-6">
                        <div class="p-3 bg-light rounded-3">
                            <span class="x-small text-muted d-block">{{ __('Net Margin') }}</span>
                            <span class="fw-bold h5 mb-0">{{ $kpis->revenue > 0 ? round((($kpis->revenue - $kpis->expenses)/$kpis->revenue)*100, 1) : 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Tables -->
    <div class="row">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-white overflow-hidden h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-boxes me-2"></i>{{ __('Top Performing Items') }}</h5>
                    <a href="{{ route('bi.products') }}" class="btn btn-sm btn-soft-primary rounded-circle" style="width:32px;height:32px;" title="{{ __('View All Analytics') }}">
                        <i class="fas fa-chart-line"></i>
                    </a>
                </div>
                <div class="card-body p-0 mt-2">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="x-small text-muted">
                                <th class="ps-4 border-0">PRODUCT</th>
                                <th class="border-0">UNITS</th>
                                <th class="border-0 text-end pe-4">REVENUE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $p)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $p->name }}<br><span class="x-small text-muted">{{ $p->category }}</span></td>
                                <td>{{ number_format($p->units) }}</td>
                                <td class="text-end pe-4 fw-bold text-success">{{ number_format($p->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-white overflow-hidden h-100">
                 <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-success"><i class="fas fa-users me-2"></i>{{ __('Valuable Customers') }}</h5>
                    <a href="{{ route('customers') }}" class="btn btn-sm btn-soft-success rounded-circle" style="width:32px;height:32px;" title="{{ __('Customer CRM') }}">
                        <i class="fas fa-user-friends"></i>
                    </a>
                </div>
                <div class="card-body p-0 mt-2">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="x-small text-muted">
                                <th class="ps-4 border-0">CUSTOMER</th>
                                <th class="border-0">ORDERS</th>
                                <th class="border-0 text-end pe-4">LTV</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $c)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $c->name }}</td>
                                <td>{{ number_format($c->orders) }}</td>
                                <td class="text-end pe-4 fw-bold text-primary">{{ number_format($c->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($revenueChart['data']) !!},
                backgroundColor: '#4361ee',
                borderRadius: 4,
                maxBarThickness: 40
            }]
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
</script>

<style>
    .bg-soft-success { background: rgba(25, 135, 84, 0.1); }
    .bg-soft-danger { background: rgba(220, 53, 69, 0.1); }
    .btn-soft-primary { background: rgba(67, 97, 238, 0.1); color: #4361ee; border: none; }
    .btn-soft-success { background: rgba(25, 135, 84, 0.1); color: #198754; border: none; }
    .btn-soft-primary:hover, .btn-soft-success:hover { transform: scale(1.1); filter: brightness(0.9); }
    .x-small { font-size: 11px; }
    .btn-white { background: #fff; }
</style>
@endpush
@endsection
