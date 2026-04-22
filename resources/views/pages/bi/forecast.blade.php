@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center text-center">
        <div class="col-md-12">
            <h2 class="fw-bold"><i class="fas fa-crystal-ball {{ app()->getLocale() === 'ar' ? 'ms-2' : 'me-2' }} text-primary"></i>{{ __('Sales Forecasting') }}</h2>
            <p class="text-muted">{{ __('AI-powered predictive analysis based on historical sales patterns (3-Month Moving Average)') }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card border-0 shadow-sm bg-white p-5 mb-4 text-center">
                <h5 class="fw-bold mb-4">{{ __('Projected 3-Month Growth') }}</h5>
                <canvas id="forecastChart" style="max-height: 400px;"></canvas>
            </div>

            <div class="row g-4">
                @foreach($forecast as $i => $amount)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 bg-white text-center">
                        <span class="badge bg-soft-primary text-primary rounded-pill mb-2 w-50 mx-auto border border-primary">
                            {{ __(Carbon\Carbon::now()->addMonths($i + 1)->format('F')) }} {{ Carbon\Carbon::now()->addMonths($i + 1)->format('Y') }}
                        </span>
                        <h3 class="fw-bold text-dark mb-1">{{ number_format($amount, 2) }}</h3>
                        <span class="text-muted small">{{ __('Estimated Revenue') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('forecastChart').getContext('2d');
    
    // Process historical data
    const historical = {!! json_encode($historical->pluck('revenue')) !!};
    const labels = {!! json_encode($historical->map(fn($h) => __(Carbon\Carbon::create($h->year, $h->month, 1)->format('F')) . ' ' . $h->year)) !!};
    
    // Process forecast data
    const forecast = {!! json_encode($forecast) !!};
    
    // Pad historical with nulls for the forecast section
    const histData = historical.map(v => parseFloat(v));
    const combinedLabels = [...labels];
    
    // Pad forecast with nulls for the historical section
    const forecastData = Array(historical.length - 1).fill(null);
    forecastData.push(histData[histData.length - 1]); // Connect point
    
    for(let i=0; i < forecast.length; i++) {
        forecastData.push(forecast[i]);
        combinedLabels.push('{{ __("Projected") }} ' + (i+1));
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: combinedLabels,
            datasets: [
                {
                    label: '{{ __("Historical Revenue") }}',
                    data: histData,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: '{{ __("Projected Forecast") }}',
                    data: forecastData,
                    borderColor: '#f72585',
                    borderDash: [5, 5],
                    backgroundColor: 'rgba(247, 37, 133, 0.05)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } }
            }
        }
    });
</script>

<style>
    .bg-soft-primary { background: rgba(67, 97, 238, 0.05); }
    [dir="rtl"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [dir="rtl"] .text-center { text-align: center !important; }
    [dir="rtl"] .mx-auto { margin-left: auto !important; margin-right: auto !important; }
    [dir="rtl"] .flex-row-reverse { flex-direction: row-reverse !important; }
</style>
@endpush
@endsection
