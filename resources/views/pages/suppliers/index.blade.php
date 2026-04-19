@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Suppliers & PO') }}</h2>
    </div>

    <div class="split">
        <!-- Supplier Directory -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Supplier Directory') }}</h3>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-sm btn-o" onclick="importSupplierCSV()">📥 {{ __('Import CSV') }}</button>
                    <button class="btn btn-sm btn-pr" onclick="openSupplierModal()">➕ {{ __('Add Supplier') }}</button>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    @forelse($suppliers as $s)
                        <tr>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->category }}</td>
                            <td>{{ $s->email }}</td>
                            <td>{{ $s->phone }}</td>
                            <td class="no-sort">
                                <button class="btn btn-xs btn-o"
                                    onclick='openSupplierModal(@json($s))'>{{ __('Edit') }}</button>
                                <form action="{{ route('suppliers.destroy', $s->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-rd">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        <!-- Purchase Orders -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Purchase Orders') }}</h3>
                <div style="display:flex;gap:8px">
                    <a href="{{ route('suppliers.po.template') }}" class="btn btn-sm btn-o">📥 {{ __('PO Template') }}</a>
                    <button class="btn btn-sm btn-o" onclick="document.getElementById('import-po-file').click()">📤
                        {{ __('Import PO CSV') }}</button>
                    <button class="btn btn-sm btn-pr" onclick="openPOModal()">➕ {{ __('New PO') }}</button>

                    <form id="import-po-form" action="{{ route('suppliers.po.import') }}" method="POST"
                        enctype="multipart/form-data" style="display:none;">
                        @csrf
                        <input type="file" id="import-po-file" name="csv_file"
                            onchange="document.getElementById('import-po-form').submit()">
                    </form>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Qty') }}</th>
                        @if(($costMode ?? 'unit') === 'unit')
                            <th>{{ __('Unit Cost') }}</th>
                        @else
                            <th>{{ __('Total Cost') }}</th>
                        @endif
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td>{{ $po->supplier->name ?? '?' }}</td>
                            <td>{{ $po->product->name ?? '?' }}</td>
                            <td>{{ $po->qty }}</td>
                            <td>
                                @if(($costMode ?? 'unit') === 'unit')
                                    {{ number_format($po->unit_cost, 2) }}
                                @else
                                    {{ number_format($po->total_cost, 2) }}
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $po->status === 'pending' ? 'badge-am' : 'badge-gn' }}">
                                    {{ $po->status === 'pending' ? __('Pending') : __('Received') }}
                                </span>
                            </td>
                            <td class="no-sort">
                                @if($po->status === 'pending')
                                    <form action="{{ route('purchase-orders.receive', $po->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-gn">{{ __('Receive') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
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
        let categories = @json($categories);

        function openSupplierModal(supplier = null) {
            const isEdit = supplier !== null;
            const actionUrl = isEdit ? `{{ url('suppliers') }}/${supplier.id}` : `{{ route('suppliers.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let s = isEdit ? supplier : { name: '', name_en: '', category: '', email: '', phone: '', contact_person: '', address: '' };

            let categoryOptions = `<option value="">{{ __('Select Category') }}</option>` +
                categories.map(c => `<option value="${c.id}" ${s && s.category === c.name ? 'selected' : ''}>${c.name}${c.name_en ? ' - ' + c.name_en : ''}</option>`).join('');

            const html = `
                        <h3>${isEdit ? '{{ __('Edit Supplier') }}' : '{{ __('Add Supplier') }}'}</h3>
                        <form action="${actionUrl}" method="POST">
                            @csrf
                            ${methodField}
                            <div style="display:flex; gap:10px; margin-bottom: 12px;">
                                <div style="flex:1;">
                                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Name') }}</label>
                                    <input name="name" value="${s.name || ''}" required>
                                </div>
                                <div style="flex:1;">
                                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Name') }} ({{ __('Optional') }})</label>
                                    <input name="name_en" value="${s.name_en || ''}">
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
                                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Email') }}</label>
                                    <input name="email" type="email" value="${s.email || ''}">
                                </div>
                                <div style="flex:1;">
                                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Phone') }}</label>
                                    <input name="phone" type="text" value="${s.phone || ''}" required>
                                </div>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Contact Person') }}</label>
                                <input name="contact_person" value="${s.contact_person || ''}">
                            </div>
                            <div style="margin-bottom: 16px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Address') }}</label>
                                <textarea name="address" rows="2" style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;">${s.address || ''}</textarea>
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

        function openPOModal() {
            const actionUrl = `{{ route('purchase-orders.store') }}`;

            const html = `
                        <h3>{{ __('New Purchase Order') }}</h3>
                        <form action="${actionUrl}" method="POST">
                            @csrf
                            <div style="margin-bottom: 12px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Supplier') }}</label>
                                <select name="supplier_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                    <option value="">-- {{ __('Select Supplier') }} --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
                                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Quantity') }}</label>
                                    <input name="qty" type="number" value="1" min="1" required>
                                </div>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label id="cost-label" style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">
                                    {{ ($costMode ?? 'unit') === 'unit' ? __('Unit Cost') : __('Total Cost') }}
                                </label>
                                <input name="cost" type="number" step="0.01" value="0.00" required>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Submit PO') }}</button>
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

        function importSupplierCSV() { alert('Import logic pending'); }
    </script>
@endpush