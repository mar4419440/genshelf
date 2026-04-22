@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Special Offers') }}</h2>
        <button class="btn btn-pr" onclick="openOfferModal()">➕ {{ __('Add Offer') }}</button>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <tr>
                    <th>{{ __('Offer Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Value') }}</th>
                    <th>{{ __('Start Date') }}</th>
                    <th>{{ __('End Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
@forelse($offers as $o)
                        <tr>
                            <td><strong>{{ $o->name }}</strong></td>
                            <td>
                                @switch($o->type)
                                    @case('pct') <span class="badge badge-bl">{{ __('Percentage Off') }}</span> @break
                                    @case('fixed') <span class="badge badge-pr">{{ __('Fixed Discount') }}</span> @break
                                    @case('bogo') <span class="badge badge-gn">{{ __('BOGO (Mix & Match)') }}</span> @break
                                    @case('cash_back') <span class="badge badge-am">{{ __('Cash Back Reward') }}</span> @break
                                @endswitch
                            </td>
                            <td>
                                <span class="fw-bold text-primary">
                                    {{ $o->type === 'pct' ? $o->value . '%' : number_format($o->value, 2) }}
                                </span>
                            </td>
                            <td>{{ $o->start_date->format('Y-m-d') }}</td>
                            <td>{{ $o->end_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge {{ $o->active ? 'badge-gn' : 'badge-rd' }}">
                                    {{ $o->active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                             <td class="no-sort">
                                <div class="action-btns justify-content-center">
                                    <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;" onclick='openOfferModal(@json($o))' title="{{ __('Edit Special Offer') }}">✏️</button>
                                    <form action="{{ route('offers.destroy', $o->id) }}" method="POST" style="display:inline;"
                                        onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle" style="width:30px;height:30px;" title="{{ __('Delete Special Offer') }}">🗑️</button>
                                    </form>
                                </div>
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

    <!-- Modal Container -->
    <div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal" id="modal-box"
            style="background:var(--bg2); padding: 25px; border-radius: var(--radius); width: 100%; max-width: 650px; display: inline-block; max-height:90vh; overflow-y:auto;">
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const allProducts = @json($products);

        function openOfferModal(offer = null) {
            const isEdit = offer !== null;
            const actionUrl = isEdit ? `{{ url('offers') }}/${offer.id}` : `{{ route('offers.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let o = isEdit ? offer : { name: '', name_en: '', type: 'pct', value: '0', start_date: '', end_date: '', active: true, applicable_products: [] };
            if (!o.applicable_products) o.applicable_products = [];

            const html = `
                    <h3 style="margin-bottom:20px; font-weight:800;">${isEdit ? '{{ __('Edit Special Offer') }}' : '{{ __('Create New Offer') }}'}</h3>
                    <form action="${actionUrl}" method="POST" id="offer-form">
                        @csrf
                        ${methodField}
                        <div style="display:flex; gap:15px; margin-bottom: 15px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('Arabic Name') }}</label>
                                <input name="name" value="${o.name || ''}" required placeholder="مثال: عرض الصيف">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('English Name') }}</label>
                                <input name="name_en" value="${o.name_en || ''}" placeholder="e.g. Summer Sale">
                            </div>
                        </div>

                        <div style="display:flex; gap:15px; margin-bottom: 20px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('Offer Type') }}</label>
                                <select name="type" required onchange="updateOfferLabels(this.value)" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius);">
                                    <option value="pct" ${o.type == 'pct' ? 'selected' : ''}>{{ __('Percentage Discount (%)') }}</option>
                                    <option value="fixed" ${o.type == 'fixed' ? 'selected' : ''}>{{ __('Fixed Amount Off') }}</option>
                                    <option value="bogo" ${o.type == 'bogo' ? 'selected' : ''}>{{ __('BOGO (Mix & Match)') }}</option>
                                    <option value="cash_back" ${o.type == 'cash_back' ? 'selected' : ''}>{{ __('Cash Back Reward') }}</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label id="value-label" style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('Value') }}</label>
                                <input name="value" type="number" step="0.01" value="${o.value || 0}" required>
                                <small id="type-hint" style="color:var(--tx3); font-size:10px;"></small>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('Applicable Products') }} <span style="font-weight:normal; opacity:0.7;">({{ __('Leave empty for all products') }})</span></label>
                            <input type="text" placeholder="{{ __('Search products...') }}" oninput="filterProductList(this.value)" style="margin-bottom:8px; background:var(--bg);">
                            <div id="product-picker" style="max-height: 200px; overflow-y:auto; border:1px solid var(--border); border-radius:var(--radius); padding:10px; background:var(--bg);">
                                ${allProducts.map(p => `
                                    <label style="display:flex; align-items:center; gap:10px; padding:6px; cursor:pointer; font-size:13px; border-bottom:1px solid rgba(0,0,0,0.05);" class="product-opt" data-name="${p.name.toLowerCase()}">
                                        <input type="checkbox" name="applicable_products[]" value="${p.id}" ${o.applicable_products.includes(p.id) || o.applicable_products.includes(String(p.id)) ? 'checked' : ''}>
                                        <span>${p.name} <small style="color:var(--tx3)">(${p.category})</small></span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>

                        <div style="display:flex; gap:15px; margin-bottom: 20px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('Start Date') }}</label>
                                <input name="start_date" type="date" value="${o.start_date ? formatDate(o.start_date) : ''}" required>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px;">{{ __('End Date') }}</label>
                                <input name="end_date" type="date" value="${o.end_date ? formatDate(o.end_date) : ''}" required>
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer; font-weight:600;">
                                <input type="checkbox" name="active" ${o.active ? 'checked' : ''} style="width:18px; height:18px;">
                                {{ __('Enable this offer immediately') }}
                            </label>
                        </div>

                        <div style="display:flex; gap:10px; border-top:1px solid var(--border); padding-top:20px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()" style="padding:12px 25px;">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="flex:1; padding:12px;">${isEdit ? '{{ __('Update Offer') }}' : '{{ __('Save Offer') }}'}</button>
                        </div>
                    </form>
                `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').classList.add('active');
            document.getElementById('modal-overlay').style.display = 'flex';
            updateOfferLabels(o.type);
        }

        function filterProductList(q) {
            q = q.toLowerCase();
            document.querySelectorAll('.product-opt').forEach(el => {
                el.style.display = el.dataset.name.includes(q) ? 'flex' : 'none';
            });
        }

        function updateOfferLabels(type) {
            const label = document.getElementById('value-label');
            const hint = document.getElementById('type-hint');
            const valInput = document.getElementsByName('value')[0];

            switch(type) {
                case 'pct': 
                    label.innerText = '{{ __("Discount Percentage") }}'; 
                    hint.innerText = '{{ __("e.g. 10 for 10% off items") }}';
                    valInput.readOnly = false;
                    break;
                case 'fixed': 
                    label.innerText = '{{ __("Fixed Amount Off") }}'; 
                    hint.innerText = '{{ __("e.g. 5.00 off each item") }}';
                    valInput.readOnly = false;
                    break;
                case 'bogo': 
                    label.innerText = '{{ __("BOGO Logic") }}'; 
                    hint.innerText = '{{ __("Cheapest item in each pair is free") }}';
                    valInput.value = 1;
                    valInput.readOnly = true;
                    break;
                case 'cash_back':
                    label.innerText = '{{ __("Cash Back Value") }}';
                    hint.innerText = '{{ __("Discount applied as store reward") }}';
                    valInput.readOnly = false;
                    break;
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toISOString().split('T')[0];
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
@endpush