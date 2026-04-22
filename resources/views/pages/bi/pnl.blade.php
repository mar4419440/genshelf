@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>{{ __('Profit & Loss Statement') }}</h2>
            <p class="text-muted">{{ __('Monthly financial performance summary for') }} {{ $year }}</p>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('bi.pnl') }}" method="GET" class="d-inline-flex gap-2">
                <input type="number" name="year" class="form-control border-0 shadow-sm bg-white" 
                       value="{{ $year }}" placeholder="{{ __('Year') }}" onchange="this.form.submit()">
                <button type="button" class="btn btn-white border shadow-sm px-3" onclick="window.print()">
                    <i class="fas fa-print"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="x-small text-muted">
                        <th class="ps-4 py-3 border-0">{{ __('MONTH') }}</th>
                        <th class="border-0">{{ __('TOTAL REVENUE') }}</th>
                        <th class="border-0">{{ __('COGS (PURCHASE COST)') }}</th>
                        <th class="border-0">{{ __('GROSS PROFIT') }}</th>
                        <th class="border-0">{{ __('OTHER EXPENSES') }}</th>
                        <th class="border-0 ps-4 pe-4 bg-soft-primary text-primary fw-bold">{{ __('NET PROFIT') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pnlTable as $row)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $row['month'] }}</td>
                        <td>{{ number_format($row['revenue'], 2) }}</td>
                        <td class="text-muted">{{ number_format($row['cogs'], 2) }}</td>
                        <td class="text-success fw-bold">{{ number_format($row['gross_profit'], 2) }}</td>
                        <td class="text-danger">{{ number_format($row['expenses'], 2) }}</td>
                        <td class="ps-4 pe-4 {{ $row['net_profit'] >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fw-bold">
                            {{ number_format($row['net_profit'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light border-top border-2">
                    @php 
                        $totalRev = array_sum(array_column($pnlTable, 'revenue'));
                        $totalCogs = array_sum(array_column($pnlTable, 'cogs'));
                        $totalExp = array_sum(array_column($pnlTable, 'expenses'));
                    @endphp
                    <tr class="fw-bold h5">
                        <td class="ps-4">{{ __('ANNUAL TOTAL') }}</td>
                        <td>{{ number_format($totalRev, 2) }}</td>
                        <td>{{ number_format($totalCogs, 2) }}</td>
                        <td class="text-success">{{ number_format($totalRev - $totalCogs, 2) }}</td>
                        <td class="text-danger">{{ number_format($totalExp, 2) }}</td>
                        <td class="ps-4 pe-4 bg-primary text-white">{{ number_format($totalRev - $totalCogs - $totalExp, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background: rgba(67, 97, 238, 0.05); }
    .bg-soft-success { background: rgba(25, 135, 84, 0.05); }
    .bg-soft-danger { background: rgba(220, 53, 69, 0.05); }
    .x-small { font-size: 11px; letter-spacing: 0.05em; font-weight: 800; }
</style>
@endsection
