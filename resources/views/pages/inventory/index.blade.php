@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Product Management') }}</h2>
        <div style="display:flex;gap:8px">
            <button class="btn btn-sm btn-o" onclick="document.getElementById('cat-guide').style.display='block'">📋
                {{ __('Category Guide') }}</button>
            <a href="{{ route('inventory.template') }}" class="btn btn-o">📥 {{ __('Template') }}</a>
            <button class="btn btn-o" onclick="document.getElementById('import-file').click()">📤
                {{ __('Import CSV') }}</button>

            <form id="import-form" action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data"
                style="display:none;">
                @csrf
                <input type="file" id="import-file" name="csv_file"
                    onchange="document.getElementById('import-form').submit()">
            </form>
        </div>
    </div>

    <!-- Category Guide Modal Helper -->
    <div id="cat-guide" class="card" style="display:none; margin-bottom: 20px; border: 1px solid var(--pr);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3>📋 {{ __('Import Category Paths') }}</h3>
            <button class="btn btn-sm btn-o" onclick="document.getElementById('cat-guide').style.display='none'">✕</button>
        </div>
        <p style="font-size:12px; color:var(--tx2); margin-bottom:12px;">
            {{ __('Copy these paths exactly into the Category_Path column of your CSV:') }}
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            @php $cats = \App\Models\Category::all(); @endphp
            @foreach($cats as $c)
                <code style="background:var(--bg3); padding:4px 8px; border-radius:4px; font-size:11px; cursor:copy;"
                    onclick="navigator.clipboard.writeText(this.innerText); alert('Copied!')">{{ $c->full_path }}</code>
            @endforeach
        </div>
    </div>

    <form method="GET" action="{{ route('inventory') }}">
        <div style="display: flex; gap: 8px;">
            <input class="search-bar" type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ __('Search...') }}" style="flex: 1;">
            <button type="submit" class="btn btn-pr" style="height: 38px;">{{ __('Search') }}</button>
        </div>
    </form>

    <div class="card" style="margin-top: 10px;">
        <div class="table-wrap">
            <table id="inv-table">
                <tr>
                    <th>{{ __('Barcode') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Storage') }}</th>
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
                        <td style="font-family:monospace; font-weight:700; color:var(--pr); font-size:12px;">
                            {{ $p->barcode ?: '—' }}
                        </td>
                        <td>
                            <strong>{{ $p->name }}</strong>
                            @if($p->is_service)
                                <span class="badge badge-pr">{{ __('Service') }}</span>
                            @endif
                        </td>
                        <td>{{ $p->category }}</td>
                        <td style="font-size:12px; color:var(--tx2);">{{ $p->storage_names ?: __('Unknown') }}</td>
                        <td>{{ number_format($p->default_price, 2) }}</td>
                        <td>{{ $p->is_service ? '—' : $stock }}</td>
                        <td><span class="badge {{ $badge }}">{{ $stLabel }}</span></td>
                        <td>
                            <div style="display:flex;gap:4px">
                                @if(!$p->is_service)
                                    <button class="btn btn-xs btn-gn" onclick='openRestockModal(@json($p))'>📦
                                        {{ __('Restock') }}</button>
                                    <a href="{{ route('returns', ['product_id' => $p->id]) }}" class="btn btn-xs btn-rd"
                                        title="{{ __('Log Damage/Return') }}">🔄</a>
                                @endif
                                <button class="btn btn-sm btn-o" onclick='openProductModal(@json($p))'>{{ __('Edit') }}</button>
                                <a href="{{ url('/pos/barcode/' . $p->id) }}" target="_blank" class="btn btn-sm btn-o">🏷️</a>
                                <form action="{{ route('inventory.destroy', $p->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-rd">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">{{ __('No data yet') }}</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal" id="modal-box"
            style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;">
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let costMode = '{{ $costMode ?? 'unit' }}';
        let suppliers = @json($suppliers);
        let categories = @json($categories);

        function openProductModal(product = null) {
            const isEdit = product !== null;
            const actionUrl = isEdit ? `{{ url('inventory') }}/${product.id}` : `{{ route('inventory.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let p = isEdit ? product : { name: '', barcode: '', name_en: '', category: '', default_price: '', low_stock_threshold: '{{ $lowStockDefault }}', is_service: 0 };

            let supplierOptions = `<option value="">{{ __('Select Supplier') }}</option>` +
                suppliers.map(s => `<option value="${s.id}">${s.name}</option>`).join('');

            let categoryOptions = `<option value="">{{ __('Select Category') }}</option>` +
                categories.map(c => `<option value="${c.id}" ${p && p.category === c.full_path ? 'selected' : ''}>${c.name} ${c.parent ? '(' + c.parent.name + ')' : ''}</option>`).join('');

            const html = `
                                                <h3>${isEdit ? '{{ __('Edit Product') }}' : '{{ __('Add Product') }}'}</h3>
                                                <form action="${actionUrl}" method="POST">
                                                    @csrf
                                                    ${methodField}
                                                    <div style="margin-bottom: 12px;">
                                                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Barcode / SKU') }}</label>
                                                        <input name="barcode" value="${p.barcode || ''}" placeholder="Scan or enter barcode" style="width:100%; font-family:monospace; font-weight:700; font-size:15px; background:var(--bg3);">
                                                    </div>
                                                    <div style="display:flex; gap:10px; margin-bottom: 12px;">
                                                        <div style="flex:1;">
                                                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Name') }}</label>
                                                            <input name="name" value="${p.name}" required>
                                                        </div>
                                                        <div style="flex:1;">
                                                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Name') }} ({{ __('Optional') }})</label>
                                                            <input name="name_en" value="${p.name_en || ''}">
                                                        </div>
                                                    </div>
                                                    <div style="margin-bottom: 12px;">
                                                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Category') }}</label>
                                                        <select name="category_id" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius);">
                                                            ${categoryOptions}
                                                        </select>
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

                                                    ${!isEdit ? `
                                                    <div id="cost-fields" style="display: block;">
                                                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                                                            <div style="flex:1;">
                                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Initial Cost') }} (${costMode === 'unit' ? '{{ __("Unit") }}' : '{{ __("Total") }}'})</label>
                                                                <input name="cost" type="number" step="0.01" required>
                                                            </div>
                                                            <div style="flex:1;">
                                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Supplier') }}</label>
                                                                <select name="supplier_id" required>${supplierOptions}</select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    ` : ''}

                                                    <div style="margin-bottom: 16px;">
                                                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                                            <input type="checkbox" name="is_service" ${p.is_service ? 'checked' : ''} onchange="document.getElementById('cost-fields')?.style.setProperty('display', this.checked ? 'none' : 'block')">
                                                            {{ __('This is a Service (No Stock Tracking)') }}
                                                        </label>
                                                    </div>
                                                    <div style="display:flex; gap:8px;">
                                                        <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-pr" style="flex:1;">${isEdit ? '{{ __('Update') }}' : '{{ __('Save') }}'}</button>
                                                    </div>
                                                </form>
                                            `;
            renderModal(html);
        }

        function openRestockModal(product) {
            let supplierOptions = `<option value="">{{ __('Select Supplier') }}</option>` +
                suppliers.map(s => `<option value="${s.id}">${s.name}</option>`).join('');

            const html = `
                                                <h3>{{ __('Restock') }}: ${product.name}</h3>
                                                <form action="{{ url('inventory') }}/${product.id}/restock" method="POST">
                                                    @csrf
                                                    <div style="margin-bottom: 12px;">
                                                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Supplier') }}</label>
                                                        <select name="supplier_id" required>${supplierOptions}</select>
                                                    </div>
                                                    <div style="display:flex; gap:10px; margin-bottom: 16px;">
                                                        <div style="flex:1;">
                                                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Quantity') }}</label>
                                                            <input name="qty" type="number" required min="1">
                                                        </div>
                                                        <div style="flex:1;">
                                                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('New Cost') }} ({{ __('Optional') }})</label>
                                                            <input name="cost" type="number" step="0.01" placeholder="${costMode === 'unit' ? '{{ __("Unit Cost") }}' : '{{ __("Total Cost") }}'}">
                                                        </div>
                                                    </div>
                                                    <div style="display:flex; gap:8px;">
                                                        <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-gn" style="flex:1;">📦 {{ __('Restock') }}</button>
                                                    </div>
                                                </form>
                                            `;
            renderModal(html);
        }

        function renderModal(html) {
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').classList.add('active');
            document.getElementById('modal-overlay').style.display = 'flex';
            document.getElementById('modal-overlay').style.alignItems = 'center';
            document.getElementById('modal-overlay').style.justifyContent = 'center';

            // Hijack form submission for offline support
            const form = document.querySelector('#modal-box form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (!navigator.onLine) {
                        e.preventDefault();

                        const formData = new FormData(form);
                        const data = {};
                        formData.forEach((value, key) => { data[key] = value });

                        const action = form.getAttribute('action');
                        const method = (data._method || form.getAttribute('method')).toUpperCase();

                        const requestQueue = JSON.parse(localStorage.getItem('generic_offline_queue') || '[]');
                        requestQueue.push({
                            url: action,
                            method: method,
                            payload: data,
                            timestamp: Date.now()
                        });

                        localStorage.setItem('generic_offline_queue', JSON.stringify(requestQueue));
                        alert('You are offline. Your action has been saved locally and will sync when internet returns.');

                        closeModal();
                        updateGenericConnectionStatus && updateGenericConnectionStatus();
                    }
                });
            }
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
@endpush