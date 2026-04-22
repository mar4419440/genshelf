@extends('layouts.app')

@section('content')
<div class="container-fluid text-center">
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1"><i class="fas fa-stream me-2 text-primary"></i>{{ __('Cash Flow Statement') }}</h2>
            <p class="text-muted">{{ __('Liquidity tracking (Inflow vs Outflow) for') }} {{ $month ? Carbon\Carbon::create(null, $month)->format('F') : '' }} {{ $year }}</p>
        </div>
    </div>

    @php 
        $operating = $cashFlowSummary['operating'];
    @endphp

    <div class="row justify-content-center g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white p-4 h-100 text-start">
                <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-plus-circle text-success me-2"></i>{{ __('CASH INFLOWS') }}</h5>
                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                    <span class="text-muted">{{ __('Cash Sales (POS)') }}</span>
                    <span class="fw-bold text-success">+{{ number_format($operating['inflow'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between h4 fw-bold mt-4 pt-3 border-top">
                    <span>{{ __('TOTAL INFLOW') }}</span>
                    <span class="text-success">{{ number_format($operating['inflow'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm bg-white p-4 h-100 text-start">
                <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="fas fa-minus-circle text-danger me-2"></i>{{ __('CASH OUTFLOWS') }}</h5>
                 <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ __('Cash Expenses') }}</span>
                    <span class="fw-bold text-danger">-{{ number_format($operating['outflow'], 2) }}</span>
                </div>
                <!-- You can add more outflows here if tracked separately -->
                <div class="d-flex justify-content-between h4 fw-bold mt-4 pt-3 border-top">
                    <span>{{ __('TOTAL OUTFLOW') }}</span>
                    <span class="text-danger">{{ number_format($operating['outflow'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-10">
            <div class="card border-0 shadow-sm bg-primary text-white p-5 mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="fw-bold mb-0 lh-1">{{ number_format($operating['net'], 2) }}</h1>
                        <span class="text-white-50 fw-bold">{{ __('NET CASH POSITION FOR PERIOD') }}</span>
                    </div>
                    <i class="fas fa-wallet fa-4x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
