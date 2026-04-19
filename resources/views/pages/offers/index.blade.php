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
                                {{ $o->type === 'pct' ? __('% Off') :
                    ($o->type === 'fixed' ? __('Fixed Off') :
                        ($o->type === 'bogo' ? __('Buy X Get Y') : __('Bundle Deal'))) }}
                            </td>
                            <td>
                                {{ $o->type === 'pct' ? $o->value . '%' : number_format($o->value, 2) }}
                            </td>
                            <td>{{ $o->start_date }}</td>
                            <td>{{ $o->end_date }}</td>
                            <td>
                                <span class="badge {{ $o->active ? 'badge-gn' : 'badge-rd' }}">
                                    {{ $o->active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="no-sort">
                                <button class="btn btn-xs btn-o" onclick='openOfferModal(@json($o))'>{{ __('Edit') }}</button>
                                <form action="{{ route('offers.destroy', $o->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-rd">{{ __('Delete') }}</button>
                                </form>
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
            style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;">
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openOfferModal(offer = null) {
            const isEdit = offer !== null;
            const actionUrl = isEdit ? `{{ url('offers') }}/${offer.id}` : `{{ route('offers.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let o = isEdit ? offer : { name: '', type: 'fixed', value: '0', start_date: '', end_date: '', active: true };

            const html = `
                    <h3>${isEdit ? '{{ __('Edit Offer') }}' : '{{ __('Add Offer') }}'}</h3>
                    <form action="${actionUrl}" method="POST">
                        @csrf
                        ${methodField}
                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Offer Name') }}</label>
                                <input name="name" value="${o.name || ''}" required>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Offer Name') }} ({{ __('Optional') }})</label>
                                <input name="name_en" value="${o.name_en || ''}">
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Type') }}</label>
                                <select name="type" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                    <option value="fixed" ${o.type == 'fixed' ? 'selected' : ''}>{{ __('Fixed Off') }}</option>
                                    <option value="pct" ${o.type == 'pct' ? 'selected' : ''}>{{ __('Percentage Off') }}</option>
                                    <option value="bogo" ${o.type == 'bogo' ? 'selected' : ''}>{{ __('BOGO') }}</option>
                                </select>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Value') }}</label>
                                <input name="value" type="number" step="0.01" value="${o.value || 0}" required>
                            </div>
                        </div>
                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Start Date') }}</label>
                                <input name="start_date" type="date" value="${o.start_date || ''}" required>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('End Date') }}</label>
                                <input name="end_date" type="date" value="${o.end_date || ''}" required>
                            </div>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="active" ${o.active ? 'checked' : ''}>
                                {{ __('Active') }}
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
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
@endpush