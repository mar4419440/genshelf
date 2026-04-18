@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>{{ __('Stock Transfers') }}</h2>
    <button class="btn btn-pr" onclick="openTransferModal()">➕ {{ __('Add Transfer') }}</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('From') }}</th>
                <th>{{ __('To') }}</th>
                <th>{{ __('Qty') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Reason') }}</th>
            </tr>
            @forelse($transfers as $st)
                <tr>
                    <td>{{ $st->product->name ?? '?' }}</td>
                    <td>{{ $st->from_location }}</td>
                    <td>{{ $st->to_location }}</td>
                    <td>{{ $st->qty }}</td>
                    <td>{{ $st->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $st->reason }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">{{ __('No data yet') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<!-- Modal Container -->
<div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
  <div class="modal" id="modal-box" style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;"></div>
</div>
@endsection

@push('scripts')
<script>
    function openTransferModal() {
        const actionUrl = `{{ route('transfers.store') }}`;
        
        const html = `
            <h3>{{ __('New Stock Transfer') }}</h3>
            <form action="${actionUrl}" method="POST">
                @csrf
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Product') }}</label>
                    <select name="product_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                        <option value="">-- {{ __('Select Product') }} --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:10px; margin-bottom: 12px;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('From Location') }}</label>
                        <input name="from_location" value="{{ __('Main Store') }}" required>
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('To Location') }}</label>
                        <input name="to_location" required>
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Quantity') }}</label>
                    <input name="qty" type="number" value="1" min="1" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Reason') }}</label>
                    <textarea name="reason" rows="2" style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;"></textarea>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Process Transfer') }}</button>
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
