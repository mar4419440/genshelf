@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>{{ __('Inventory') }}</h2>
    <div style="display:flex;gap:8px">
        <a href="{{ route('inventory.template') }}" class="btn btn-o">📥 {{ __('Template') }}</a>
        <button class="btn btn-o" onclick="document.getElementById('import-file').click()">📤 {{ __('Import CSV') }}</button>
        <button class="btn btn-pr" onclick="openProductModal()">➕ {{ __('Add Product') }}</button>
        
        <form id="import-form" action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" style="display:none;">
            @csrf
            <input type="file" id="import-file" name="csv_file" onchange="document.getElementById('import-form').submit()">
        </form>
    </div>
</div>

<form method="GET" action="{{ route('inventory') }}">
    <div style="display: flex; gap: 8px;">
        <input class="search-bar" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search...') }}" style="flex: 1;">
        <button type="submit" class="btn btn-pr" style="height: 38px;">{{ __('Search') }}</button>
    </div>
</form>

<div class="card" style="margin-top: 10px;">
    <div class="table-wrap">
        <table id="inv-table">
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Default Price') }}</th>
                <th>{{ __('Stock') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
            @forelse($products as $p)
                @php
                    $stock = $p->current_stock;
                    $threshold = $p->low_stock_threshold > 0 ? $p->low_stock_threshold : $lowStockDefault;
                    
                    if ($stock <= 0 && !$p->is_service) {
                        $st = 'out';
                    } elseif ($stock <= $threshold && !$p->is_service) {
                        $st = 'low';
                    } else {
                        $st = 'in';
                    }

                    $badge = $st === 'in' ? 'badge-gn' : ($st === 'low' ? 'badge-am' : 'badge-rd');
                    $stLabel = $p->is_service ? __('Service') : ($st === 'in' ? __('In Stock') : ($st === 'low' ? __('Low Stock') : __('Out of Stock')));
                @endphp
                <tr>
                    <td>
                        <strong>{{ $p->name }}</strong>
                        @if($p->is_service)
                            <span class="badge badge-pr">{{ __('Service') }}</span>
                        @endif
                    </td>
                    <td>{{ $p->category }}</td>
                    <td>{{ number_format($p->default_price, 2) }}</td>
                    <td>{{ $p->is_service ? '—' : $stock }}</td>
                    <td><span class="badge {{ $badge }}">{{ $stLabel }}</span></td>
                    <td>
                        @if(!$p->is_service)
                            <button class="btn btn-xs btn-o" onclick='openProductModal(@json($p))'>{{ __('Edit') }}</button>
                        @else
                            <button class="btn btn-xs btn-o" onclick='openProductModal(@json($p))'>{{ __('Edit') }}</button>
                        @endif
                        <form action="{{ route('inventory.destroy', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                            @csrf
                            @method('DELETE')
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
<div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
  <div class="modal" id="modal-box" style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;"></div>
</div>

@endsection

@push('scripts')
<script>
    function openProductModal(product = null) {
        const isEdit = product !== null;
        const actionUrl = isEdit ? `{{ url('inventory') }}/${product.id}` : `{{ route('inventory.store') }}`;
        const methodField = isEdit ? `@method('PUT')` : '';
        
        let p = isEdit ? product : { name: '', category: '', default_price: '', low_stock_threshold: '{{ $lowStockDefault }}', is_service: false };

        const html = `
            <h3>${isEdit ? '{{ __('Edit Product') }}' : '{{ __('Add Product') }}'}</h3>
            <form action="${actionUrl}" method="POST">
                @csrf
                ${methodField}
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Name') }}</label>
                    <input name="name" value="${p.name}" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Category') }}</label>
                    <input name="category" value="${p.category}" required>
                </div>
                <div style="display:flex; gap:10px; margin-bottom: 12px;">
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Default Price') }}</label>
                        <input name="default_price" type="number" step="0.01" value="${p.default_price}" required>
                    </div>
                    <div style="flex:1;">
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Low Stock Threshold') }}</label>
                        <input name="low_stock_threshold" type="number" value="${p.low_stock_threshold}">
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="checkbox" name="is_service" ${p.is_service ? 'checked' : ''}>
                        {{ __('This is a Service (No Stock Tracking)') }}
                    </label>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">${isEdit ? '{{ __('Update') }}' : '{{ __('Save') }}'}</button>
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
