@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6 {{ app()->getLocale() === 'ar' ? 'text-right' : '' }}">
            <h2 class="fw-bold"><i class="fas fa-chart-line {{ app()->getLocale() === 'ar' ? 'ms-2' : 'me-2' }} text-primary"></i>{{ __('Product Performance') }}</h2>
            <p class="text-muted">{{ __('Deep dive into inventory profitability and sales velocity') }}</p>
        </div>
        <div class="col-md-6 {{ app()->getLocale() === 'ar' ? 'text-start' : 'text-end' }}">
            <form action="{{ route('bi.products') }}" method="GET" class="d-inline-flex gap-2">
                <select name="period" class="form-select border-0 shadow-sm bg-white" onchange="this.form.submit()">
                    <option value="this_month" {{ $period == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                    <option value="this_year" {{ $period == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                </select>
                <button type="button" class="btn btn-white border shadow-sm px-3" onclick="window.print()">
                    <i class="fas fa-file-excel text-success"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Product Table -->
    <div class="card border-0 shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="x-small text-muted fw-bold">
                        <th class="{{ app()->getLocale() === 'ar' ? 'pe-4' : 'ps-4' }} py-3 border-0">{{ __('PRODUCT') }}</th>
                        <th class="border-0">{{ __('CATEGORY') }}</th>
                        <th class="border-0">{{ __('UNITS SOLD') }}</th>
                        <th class="border-0">{{ __('REVENUE') }}</th>
                        <th class="border-0">{{ __('AVG PRICE') }}</th>
                        <th class="border-0 {{ app()->getLocale() === 'ar' ? 'ps-4 text-start' : 'pe-4 text-end' }}">{{ __('SALES %') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalRevenue = $products->sum('revenue'); @endphp
                    @foreach($products as $p)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-dark">{{ $p->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-normal">{{ __($p->category) }}</span>
                        </td>
                        <td class="fw-bold">{{ number_format($p->units_sold) }}</td>
                        <td class="text-primary fw-bold">{{ number_format($p->revenue, 2) }}</td>
                        <td>{{ number_format($p->revenue / max(1, $p->units_sold), 2) }}</td>
                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @php $pct = $totalRevenue > 0 ? ($p->revenue / $totalRevenue) * 100 : 0; @endphp
                                <div class="progress" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="x-small fw-bold">{{ round($pct, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 11px; }
    [dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .text-end { text-align: left !important; }
    [dir="rtl"] .pe-4 { padding-left: 1.5rem !important; padding-right: 0 !important; }
    [dir="rtl"] .ps-4 { padding-right: 1.5rem !important; padding-left: 0 !important; }
    [dir="rtl"] th, [dir="rtl"] td { text-align: right !important; }
    [dir="rtl"] .text-end { text-align: left !important; }
</style>
@endsection
