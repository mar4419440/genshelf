@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="fas fa-university me-2 text-primary"></i>{{ __('Financial Command Center') }}</h2>
            <p class="text-muted">{{ __('Manage liquidity, taxes, and cash position') }}</p>
        </div>
        <div class="col-md-6 text-end d-flex justify-content-end gap-2 pt-2">
            <button class="btn btn-white border shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#drawerModal">
                <i class="fas fa-cash-register me-2"></i>{{ __('Drawer Event') }}
            </button>
            <button class="btn btn-primary shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus me-2"></i>{{ __('New Expense') }}
            </button>
        </div>
    </div>

    <!-- Top Row: General Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
             <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <span class="text-muted small fw-bold mb-1">{{ __('CASH ON HAND (DRAWER)') }}</span>
                <h2 class="fw-bold mb-0 {{ $cashPosition >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($cashPosition, 2) }}
                </h2>
                <div class="mt-2 d-flex align-items-center gap-2">
                    @if($isDrawerOpen)
                        <span class="badge bg-soft-success text-success border border-success rounded-pill x-small"><i class="fas fa-circle me-1 animate-pulse"></i>{{ __('OPEN') }}</span>
                    @else
                        <span class="badge bg-soft-danger text-danger border border-danger rounded-pill x-small">{{ __('CLOSED') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <span class="text-muted small fw-bold mb-1">{{ __('NET PROFIT (LIFETIME)') }}</span>
                <h2 class="fw-bold mb-0 text-primary">{{ number_format($netProfit, 2) }}</h2>
                <span class="text-muted x-small mt-1">{{ __('After all costs & expenses') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <span class="text-muted small fw-bold mb-1">{{ __('RECEIVABLES (DEBT)') }}</span>
                <h2 class="fw-bold mb-0 text-warning">{{ number_format($totalDebt, 2) }}</h2>
                <span class="text-danger x-small mt-1 fw-bold">{{ $overdueCount }} {{ __('overdue invoices') }}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-4 bg-white h-100">
                <span class="text-muted small fw-bold mb-1">{{ __('TAX POSITION (MONTH)') }}</span>
                <h2 class="fw-bold mb-0 text-info">{{ number_format($taxCollected, 2) }}</h2>
                <span class="text-muted x-small mt-1">{{ __('Collected sales tax') }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Links & Ratios -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm bg-white overflow-hidden">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">{{ __('Recent Drawer Events') }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light x-small fw-bold">
                            <tr>
                                <th class="ps-4 border-0">{{ __('USER') }}</th>
                                <th class="border-0">{{ __('TYPE') }}</th>
                                <th class="border-0">{{ __('AMOUNT') }}</th>
                                <th class="border-0">{{ __('TIME') }}</th>
                                <th class="pe-4 border-0">{{ __('NOTE') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drawerEvents as $de)
                            <tr>
                                <td class="ps-4 fw-bold small">{{ $de->user->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge rounded-pill px-3 bg-{{ match($de->type){'open'=>'success','close'=>'dark','in'=>'primary','out'=>'danger'} }}">
                                        {{ ucfirst($de->type) }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ number_format($de->amount, 2) }}</td>
                                <td class="text-muted x-small">{{ $de->created_at->format('d M, H:i') }}</td>
                                <td class="pe-4 text-muted small truncate-text" title="{{ $de->description }}">{{ $de->description ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="row g-3">
                <div class="col-12">
                    <a href="{{ route('finance.cashflow') }}" class="card border-0 shadow-sm p-4 bg-white text-decoration-none hover-up">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-stream text-primary fa-2x"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ __('Cash Flow Statement') }}</h5>
                                <span class="text-muted small">{{ __('Track inflow vs outflow') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12">
                    <a href="{{ route('finance.tax') }}" class="card border-0 shadow-sm p-4 bg-white text-decoration-none hover-up">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-file-invoice text-success fa-2x"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ __('Tax Report') }}</h5>
                                <span class="text-muted small">{{ __('Collected vs Remitted tracking') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                 <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 bg-white h-100">
                        <h6 class="fw-bold mb-3">{{ __('Profitability Specs') }}</h6>
                        <div class="d-flex justify-content-between mb-2 x-small fw-bold">
                            <span>Gross Margin</span>
                            <span class="text-success">{{ $ratios->gross_margin }}%</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $ratios->gross_margin }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-2 x-small fw-bold">
                            <span>Net Profit Margin</span>
                            <span class="text-primary">{{ $ratios->net_margin }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ $ratios->net_margin }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Drawer Event -->
<div class="modal fade" id="drawerModal" tabindex="-1">
    <div class="modal-dialog border-0">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('finance.drawer.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ __('New Drawer Event') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <div>
                            <label class="form-label x-small fw-bold">{{ __('Event Type') }}</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="dr_open" value="open" required>
                                <label class="btn btn-outline-success" for="dr_open">{{ __('Open') }}</label>
                                <input type="radio" class="btn-check" name="type" id="dr_in" value="in">
                                <label class="btn btn-outline-primary" for="dr_in">{{ __('In') }}</label>
                                <input type="radio" class="btn-check" name="type" id="dr_out" value="out">
                                <label class="btn btn-outline-danger" for="dr_out">{{ __('Out') }}</label>
                                <input type="radio" class="btn-check" name="type" id="dr_close" value="close">
                                <label class="btn btn-outline-dark" for="dr_close">{{ __('Close') }}</label>
                            </div>
                        </div>
                        <div>
                            <label class="form-label x-small fw-bold">{{ __('Amount') }}</label>
                            <input type="number" step="0.01" name="amount" class="form-control border-0 bg-light py-2 px-3 fw-bold" placeholder="0.00">
                        </div>
                        <div>
                             <label class="form-label x-small fw-bold">{{ __('Description / Note') }}</label>
                             <textarea name="description" class="form-control border-0 bg-light py-2 px-3" rows="3" placeholder="{{ __('Reason for event...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-4">{{ __('Log Event') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background: rgba(25, 135, 84, 0.1); }
    .bg-soft-danger { background: rgba(220, 53, 69, 0.1); }
    .x-small { font-size: 11px; }
    .hover-up { transition: transform 0.2s; }
    .hover-up:hover { transform: translateY(-5px); }
    .animate-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .truncate-text { max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection