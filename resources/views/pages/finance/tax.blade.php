@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>{{ __('Tax Performance Report') }}</h2>
            <p class="text-muted">{{ __('Collected Sales Tax (VAT) summary for') }} {{ $year }}</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-white border shadow-sm px-4 py-2" onclick="window.print()">
                <i class="fas fa-print me-2"></i>{{ __('Print Report') }}
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
             <div class="card border-0 shadow-sm p-4 bg-primary text-white mb-4">
                <span class="text-white-50 small fw-bold mb-1">{{ __('ANNUAL TAX COLLECTED') }}</span>
                <h1 class="fw-bold mb-0 lh-1">{{ number_format($monthlyTax->sum('tax_collected'), 2) }}</h1>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="x-small text-muted fw-bold">
                        <th class="ps-4 py-3 border-0">{{ __('MONTH') }}</th>
                        <th class="border-0">{{ __('COLLECTED TAX') }}</th>
                        <th class="border-0">{{ __('REMITTANCE STATUS') }}</th>
                        <th class="pe-4 border-0 text-end">{{ __('ACTIONS') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyTax as $m)
                    <tr>
                        <td class="ps-4 fw-bold">
                            {{ Carbon\Carbon::create($year, $m->month, 1)->format('F Y') }}
                        </td>
                        <td class="fw-bold text-primary">
                            {{ number_format($m->tax_collected, 2) }}
                        </td>
                        <td>
                            <span class="badge bg-soft-warning text-warning border border-warning rounded-pill px-3">
                                {{ __('Collected (Pending Remittal)') }}
                            </span>
                        </td>
                        <td class="pe-4 text-end text-muted small italic">
                            {{ __('Auto-calculated from POS') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-warning { background: rgba(255, 193, 7, 0.1); }
    .x-small { font-size: 11px; }
</style>
@endsection
