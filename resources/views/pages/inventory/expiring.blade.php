@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>⏳ {{ __('Expirations Tracking') }}</h2>
</div>

<div class="card">
    <h3 style="margin-bottom:12px">{{ __('Expiring Product Batches') }}</h3>
    <p style="color:var(--tx2); font-size:13px; margin-bottom:16px;">
        {{ __('Monitor product batches with expiration dates. Batches already expired or expiring soon are highlighted.') }}
    </p>

    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Batch Number') }}</th>
                <th>{{ __('Expiry Date') }}</th>
                <th>{{ __('Time Left') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            @forelse($batches as $batch)
                @php
                    $now = \Carbon\Carbon::now();
                    $exp = \Carbon\Carbon::parse($batch->expiration_date);
                    $in30 = \Carbon\Carbon::now()->addDays(30);
                    
                    if ($exp < $now) {
                        $st = 'expired';
                        $badge = 'badge-rd';
                    } elseif ($exp <= $in30) {
                        $st = 'expiring';
                        $badge = 'badge-am';
                    } else {
                        $st = 'good';
                        $badge = 'badge-gn';
                    }

                    $timeLeft = $exp->diffForHumans(['parts' => 2, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
                @endphp
                <tr style="{{ $st === 'expired' ? 'background: var(--rd-l);' : '' }}">
                    <td>
                        <strong>{{ $batch->product->name ?? __('Unknown') }}</strong>
                        @if($batch->product && $batch->product->barcode)
                            <div style="font-size:11px; color:var(--tx3); font-family:monospace;">{{ $batch->product->barcode }}</div>
                        @endif
                    </td>
                    <td>{{ $batch->batch_number ?: '—' }}</td>
                    <td>{{ $exp->format('Y-m-d') }}</td>
                    <td style="font-weight:600; color:var(--{{ $st === 'expired' ? 'rd' : ($st === 'expiring' ? 'am' : 'gn') }});">
                        {{ $st === 'expired' ? __('Expired ') . $timeLeft . __(' ago') : $timeLeft }}
                    </td>
                    <td>{{ $batch->qty }}</td>
                    <td>
                        <span class="badge {{ $badge }}">
                            {{ $st === 'good' ? __('Good') : ($st === 'expiring' ? __('Expiring Soon') : __('Expired')) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">{{ __('No expiring batches found.') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>
@endsection
