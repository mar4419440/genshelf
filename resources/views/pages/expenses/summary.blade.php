@extends('layouts.app')

@push('styles')
<style>
    .premium-card {
        background: #fff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
    }
    
    .glowing-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
        box-shadow: 0 8px 15px -3px currentColor;
    }

    .variance-table thead th {
        background: var(--bg2);
        color: var(--tx3);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        padding: 15px 20px;
        border: none;
    }

    .progress-fat {
        height: 10px;
        border-radius: 20px;
        background: #f0f0f0;
    }
    
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-900 mb-1" style="letter-spacing:-0.5px;">{{ __('Expense') }} <span class="text-primary">{{ __('Intelligence') }}</span></h2>
            <p class="text-muted mb-0">{{ __('Advanced spending analysis & budget reconciliation') }}</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                <button class="btn btn-white border-0 px-4 py-3 fw-bold" onclick="window.print()">
                    <i class="fas fa-file-export me-2 text-primary"></i>{{ __('Export Data') }}
                </button>
                <a href="{{ route('expenses.index') }}" class="btn btn-primary border-0 px-4 py-3 fw-bold">
                    <i class="fas fa-history me-2"></i>{{ __('Log History') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Stats summary for the period -->
    <div class="row g-4 mb-5">
        @php 
            $currentPeriodSpent = $budgetComparison->sum('actual');
            $currentPeriodBudget = $budgetComparison->sum('budgeted');
            $savings = max(0, $currentPeriodBudget - $currentPeriodSpent);
        @endphp
        
        <div class="col-md-4">
            <div class="card premium-card p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="glowing-icon bg-primary text-white">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div>
                        <span class="text-muted x-small fw-bold text-uppercase ls-1">{{ __('Total Outflow') }}</span>
                        <h3 class="fw-900 mb-0">{{ number_format($currentPeriodSpent, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card premium-card p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="glowing-icon bg-success text-white">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div>
                        <span class="text-muted x-small fw-bold text-uppercase ls-1">{{ __('Budget Savings') }}</span>
                        <h3 class="fw-900 mb-0">{{ number_format($savings, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card premium-card p-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="glowing-icon bg-info text-white">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <span class="text-muted x-small fw-bold text-uppercase ls-1">{{ __('Budget Efficiency') }}</span>
                        <h3 class="fw-900 mb-0">{{ $currentPeriodBudget > 0 ? round(($currentPeriodSpent / $currentPeriodBudget) * 100, 1) : 0 }}%</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Trend Chart -->
        <div class="col-md-8">
            <div class="card premium-card h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-area me-2 text-primary"></i>{{ __('Spending Velocity') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Category Breakdown -->
        <div class="col-md-4">
            <div class="card premium-card h-100 border-0">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-4">{{ __('Asset Allocation') }}</h5>
                    <div style="height: 220px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                        <div style="position: absolute; top:50%; left:50%; transform:translate(-50%, -50%);">
                            <span class="text-muted x-small fw-bold">{{ __('DIVERSIFIED') }}</span>
                        </div>
                    </div>
                    <div id="categoryLegends" class="mt-4 row g-2 text-start"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Variance Table -->
    <div class="card premium-card border-0 mb-4 overflow-hidden">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">{{ __('Variance Matrix') }} <span class="text-muted fw-normal" style="font-size:13px;">— {{ Carbon\Carbon::create(null, $month)->format('F') }} {{ $year }}</span></h5>
            
            <div class="dropdown">
                <button class="btn btn-sm btn-white border px-3 rounded-pill fw-bold" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar-alt me-2"></i>{{ __('Switch Period') }}
                </button>
                <div class="dropdown-menu dropdown-menu-end p-4 shadow-lg border-0 rounded-4" style="width: 250px;">
                    <form action="{{ route('expenses.summary') }}" method="GET">
                        <div class="mb-3">
                            <label class="x-small fw-bold ls-1 text-uppercase text-muted mb-2">{{ __('Month') }}</label>
                            <select name="month" class="form-select border-0 bg-light rounded-3">
                                @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ Carbon\Carbon::create(null, $m)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="x-small fw-bold ls-1 text-uppercase text-muted mb-2">{{ __('Year') }}</label>
                            <input type="number" name="year" class="form-control border-0 bg-light rounded-3" value="{{ $year }}">
                        </div>
                        <button class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">{{ __('Execute Analysis') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table variance-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">{{ __('Asset Category') }}</th>
                        <th>{{ __('Projected') }}</th>
                        <th>{{ __('Utilized') }}</th>
                        <th>{{ __('Net Variance') }}</th>
                        <th>{{ __('Resource Load') }}</th>
                        <th class="pe-4 text-end">{{ __('Outcome') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgetComparison as $item)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ ucfirst(__($item['category'])) }}</td>
                        <td>{{ number_format($item['budgeted'], 2) }}</td>
                        <td class="fw-900">{{ number_format($item['actual'], 2) }}</td>
                        <td class="{{ $item['variance'] < 0 ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ $item['variance'] < 0 ? '-' : '+' }}{{ number_format(abs($item['variance']), 2) }}
                        </td>
                        <td style="width: 200px;">
                            @if($item['pct_used'] !== null)
                            <div class="d-flex align-items-center gap-3">
                                <div class="progress flex-grow-1 progress-fat shadow-none">
                                    <div class="progress-bar bg-{{ $item['over_budget'] ? 'danger shadow-danger' : 'primary shadow-primary' }}" style="width: {{ min(100, $item['pct_used']) }}%"></div>
                                </div>
                                <span class="x-small fw-900">{{ $item['pct_used'] }}%</span>
                            </div>
                            @else
                            <span class="text-muted x-small ls-1 fw-bold">— N/A</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            @if($item['over_budget'])
                                <span class="badge bg-soft-danger text-danger border-0 rounded-pill fw-bold" style="font-size:10px;">{{ __('OVER-BUDGET') }}</span>
                            @else
                                <span class="badge bg-soft-success text-success border-0 rounded-pill fw-bold" style="font-size:10px;">{{ __('ON-TRACK') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trend->pluck('label')) !!},
            datasets: [{
                label: '{{ __("Monthly Spending") }}',
                data: {!! json_encode($trend->pluck('total')) !!},
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Chart
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catData = {!! json_encode($monthlyByCategory->pluck('total')) !!};
    const catLabels = {!! json_encode($monthlyByCategory->pluck('category')->map(fn($c) => ucfirst(__($c)))) !!};
    
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: ['#4361ee', '#4cc9f0', '#4895ef', '#3f37c9', '#560bad', '#b5179e', '#f72585'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });

    // Populate legends
    const legendContainer = document.getElementById('categoryLegends');
    const colors = ['#4361ee', '#4cc9f0', '#4895ef', '#3f37c9', '#560bad', '#b5179e', '#f72585'];
    catLabels.forEach((label, i) => {
        legendContainer.innerHTML += `
            <div class="col-6">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:10px; height:10px; border-radius:3px; background:${colors[i % colors.length]}"></div>
                    <span class="x-small fw-bold text-muted">${label}</span>
                </div>
            </div>
        `;
    });
</script>

<style>
    .bg-soft-danger { background: rgba(220, 53, 69, 0.1); }
    .bg-soft-success { background: rgba(25, 135, 84, 0.1); }
    .x-small { font-size: 11px; }
</style>
@endpush
@endsection
