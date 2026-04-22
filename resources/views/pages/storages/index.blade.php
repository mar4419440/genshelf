@extends('layouts.app')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <h2 style="font-size:24px; font-weight:700; color:var(--tx);">{{ __('Storage Locations') }}</h2>
        <button class="btn btn-pr" onclick="openStorageModal()">
            + {{ __('Add Storage') }}
        </button>
    </div>

    <div class="card" style="padding: 20px;">
        <div style="overflow-x:auto;">
            <table style="width:100%; text-align:left; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600;">{{ __('Name') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600;">{{ __('Type') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600;">{{ __('Conditions') }}
                        </th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600;">{{ __('Status') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600; text-align:right;">
                            {{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($storages as $storage)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px;font-weight:500;">
                                {{ $storage->name }}
                                @if($storage->name_en) <span
                                style="color:var(--tx2); font-size:12px;">({{ $storage->name_en }})</span> @endif
                            </td>
                            <td style="padding:12px;">
                                <span class="badge {{ $storage->type === 'pos' ? 'badge-gn' : 'badge-o' }}" style="text-transform:uppercase; font-size:10px;">
                                    {{ $storage->type === 'pos' ? __('Store (POS)') : __('Warehouse') }}
                                </span>
                            </td>
                            <td style="padding:12px;color:var(--tx2); font-size:13px;">{{ $storage->conditions ?: '-' }}</td>
                            <td style="padding:12px;">
                                <span
                                    style="padding:4px 8px; border-radius:4px; font-size:11px; {{ $storage->is_active ? 'background:#ecfdf5; color:#059669;' : 'background:#fef2f2; color:#dc2626;' }}">
                                    {{ $storage->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                             <td style="padding:12px; text-align:right;">
                                <div class="action-btns justify-content-end">
                                    <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;"
                                        onclick='openStorageModal(@json($storage))' title="{{ __('Edit Storage Unit') }}">✏️</button>
                                    <form action="{{ route('storages.destroy', $storage->id) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this storage location?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle"
                                            style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" title="{{ __('Delete Storage Unit') }}">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:20px; text-align:center; color:var(--tx2);">
                                {{ __('No storage locations found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal" id="modal-box"
            style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 450px; display: inline-block;">
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openStorageModal(storage = null) {
            const isEdit = storage !== null;
            const actionUrl = isEdit ? `{{ url('storages') }}/${storage.id}` : `{{ route('storages.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let s = isEdit ? storage : { name: '', name_en: '', conditions: '', is_active: true };

            const html = `
                    <h3 style="margin-bottom:16px;">${isEdit ? '{{ __('Edit Storage') }}' : '{{ __('Add Storage') }}'}</h3>
                    <form action="${actionUrl}" method="POST">
                        @csrf
                        ${methodField}
                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Name') }}</label>
                                <input name="name" value="${s.name || ''}" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius);" required>
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Name') }}</label>
                                <input name="name_en" value="${s.name_en || ''}" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius);">
                            </div>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Location Type') }}</label>
                            <select name="type" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius);" required>
                                <option value="storage" ${s.type === 'storage' ? 'selected' : ''}>{{ __('Warehouse / Storage') }}</option>
                                <option value="pos" ${s.type === 'pos' ? 'selected' : ''}>{{ __('Point of Sale (Store)') }}</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Conditions') }} ({{ __('temperature, humidity, etc.') }})</label>
                            <textarea name="conditions" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:var(--radius); min-height:80px;">${s.conditions || ''}</textarea>
                        </div>
                        ${isEdit ? `
                        <div style="margin-bottom: 20px; display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="is_active" id="is_active" ${s.is_active ? 'checked' : ''}>
                            <label for="is_active" style="font-size:13px;">{{ __('Available for stock') }}</label>
                        </div>
                        ` : ''}
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" style="width:50%;" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="width:50%;">${isEdit ? '{{ __('Update') }}' : '{{ __('Save') }}'}</button>
                        </div>
                    </form>
                `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').style.display = 'flex';
            document.getElementById('modal-overlay').style.alignItems = 'center';
            document.getElementById('modal-overlay').style.justifyContent = 'center';
        }

        function closeModal() {
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
@endpush