@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>{{ __('Warranty Tracking') }}</h2>
</div>

<div class="card">
    <h3 style="margin-bottom:12px">{{ __('Products Configured with Warranty') }}</h3>
    <p style="color:var(--tx2); font-size:13px; margin-bottom:16px;">
        {{ __('Below is a list of all items in your inventory currently offering a warranty.') }}
    </p>
    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Product Barcode') }}</th>
                <th>{{ __('Product Name') }}</th>
                <th>{{ __('Warranty Duration') }}</th>
            </tr>
            @forelse($warrantyProducts as $wp)
                <tr>
                    <td style="font-family:monospace; color:var(--tx2);">{{ $wp->barcode ?: '—' }}</td>
                    <td><strong>{{ $wp->name }}</strong></td>
                    <td style="font-weight:600; color:var(--pr);">
                        {{ $wp->warranty_duration }} {{ trans_choice(__('Month|Months'), $wp->warranty_duration ?? 0) }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty-state">{{ __('No products with warranty configured.') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h3>{{ __('Active / Registered Warranties') }}</h3>
        <button class="btn btn-sm btn-pr" onclick="openWarrantyModal()">+ {{ __('Manual Registration') }}</button>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Purchase Date') }}</th>
                <th>{{ __('Expiry Date') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            @forelse($warranties as $w)
                @php
                    $now = \Carbon\Carbon::now();
                    $exp = \Carbon\Carbon::parse($w->end_date);
                    $in30 = \Carbon\Carbon::now()->addDays(30);
                    
                    if ($exp < $now) {
                        $st = 'expired';
                        $badge = 'badge-rd';
                    } elseif ($exp <= $in30) {
                        $st = 'expiring';
                        $badge = 'badge-am';
                    } else {
                        $st = 'active';
                        $badge = 'badge-gn';
                    }
                @endphp
                <tr>
                    <td>{{ $w->product->name ?? '?' }}</td>
                    <td>{{ $w->customer->name ?? '—' }}</td>
                    <td>{{ $w->purchase_date }}</td>
                    <td>{{ $w->end_date }}</td>
                    <td>
                        <span class="badge {{ $badge }}">
                            {{ $st === 'active' ? __('Active') : ($st === 'expiring' ? __('Expiring') : __('Expired')) }}
                        </span>
                    </td>
                    <td class="no-sort">
                        <form action="{{ route('warranty.destroy', $w->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-rd">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">{{ __('No data yet') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<!-- Modal Container -->
<div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
  <div class="modal" id="modal-box" style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;"></div>
</div>
@endsection

@push('scripts')
<script>
    function openWarrantyModal() {
        const actionUrl = `{{ route('warranty.store') }}`;
        
        const html = `
            <h3>{{ __('Register Warranty') }}</h3>
            <form action="${actionUrl}" method="POST">
                @csrf
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Customer') }}</label>
                    <select name="customer_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                        <option value="">-- {{ __('Select Customer') }} --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Product') }}</label>
                    <select name="product_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                        <option value="">-- {{ __('Select Product') }} --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:10px; margin-bottom: 16px;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Purchase Date') }}</label>
                        <input name="purchase_date" type="date" value="{{ date('Y-m-d') }}" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('End Date') }}</label>
                        <input name="end_date" type="date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Save Warranty') }}</button>
                </div>
            </form>
        `;
        document.getElementById('modal-box').innerHTML = html;
        document.getElementById('modal-overlay').classList.add('active');
        document.getElementById('modal-overlay').style.display = 'flex';
        document.getElementById('modal-overlay').style.alignItems = 'center';
        document.getElementById('modal-overlay').style.justifyContent = 'center';
    }

    function closeModal() { 
        document.getElementById('modal-overlay').classList.remove('active'); 
        document.getElementById('modal-overlay').style.display = 'none';
    }
</script>
@endpush
