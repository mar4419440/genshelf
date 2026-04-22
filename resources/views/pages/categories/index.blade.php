@extends('layouts.app')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
        <h2 style="font-size:24px; font-weight:700; color:var(--tx);">{{ __('Categories') }}</h2>
        <div style="display:flex;gap:6px">
            <a href="{{ route('categories.template') }}" class="btn btn-o">📥 {{ __('Template') }}</a>
            <button class="btn btn-o" onclick="document.getElementById('import-category-file').click()">📤 {{ __('Import File') }}</button>
            <button class="btn btn-pr" onclick="openCategoryModal()">
                + {{ __('Add Category') }}
            </button>
            <form id="import-category-form" action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="file" id="import-category-file" name="csv_file" onchange="document.getElementById('import-category-form').submit()">
            </form>
        </div>
    </div>

    <div class="card" style="padding: 20px;">
        <div style="overflow-x:auto;">
            <table style="width:100%; text-align:left; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600; cursor:pointer;">
                            {{ __('Arabic Name') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600; cursor:pointer;">
                            {{ __('English Name') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600;">
                            {{ __('Parent Category') }}</th>
                        <th style="padding:12px; font-size:12px; color:var(--tx2); font-weight:600; text-align:right;"
                            class="no-sort">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px;font-weight:500;">
                                @if($category->parent_id) <span style="color:var(--tx3)">{{ $category->parent->name }} ➔</span> @endif
                                {{ $category->name }}
                            </td>
                            <td style="padding:12px;color:var(--tx2);">{{ $category->name_en ?: '-' }}</td>
                            <td style="padding:12px;">
                                @if($category->parent)
                                    <span class="badge badge-pr">{{ $category->parent->name }}</span>
                                @else
                                    <span style="color:var(--tx3); font-style:italic; font-size:11px;">{{ __('Main Category') }}</span>
                                @endif
                            </td>
                             <td style="padding:12px; text-align:right;">
                                <div class="action-btns justify-content-end">
                                    <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;"
                                        onclick='openCategoryModal(@json($category))' title="{{ __('Edit Category') }}">✏️</button>
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this category?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle"
                                            style="width:30px;height:30px;padding:0;display:flex;align-items:center;justify-content:center;" title="{{ __('Delete Category') }}">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:20px; text-align:center; color:var(--tx2);">
                                {{ __('No categories found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Container -->
    <div class="overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="modal" id="modal-box"
            style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 400px; display: inline-block;">
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openCategoryModal(category = null) {
            const isEdit = category !== null;
            const actionUrl = isEdit ? `{{ url('categories') }}/${category.id}` : `{{ route('categories.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let c = isEdit ? category : { name: '', name_en: '', parent_id: '' };

            const html = `
                    <h3 style="margin-bottom:16px;">${isEdit ? '{{ __('Edit Category') }}' : '{{ __('Add Category') }}'}</h3>
                    <form action="${actionUrl}" method="POST">
                        @csrf
                        ${methodField}
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Name') }}</label>
                            <input name="name" value="${c.name || ''}" class="search-bar" style="width:100%; border:1px solid var(--border);" required>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Name') }} ({{ __('Optional') }})</label>
                            <input name="name_en" value="${c.name_en || ''}" class="search-bar" style="width:100%; border:1px solid var(--border);">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Parent Category') }}</label>
                            <select name="parent_id" class="search-bar" style="width:100%; border:1px solid var(--border);">
                                <option value="">{{ __('None (Main Category)') }}</option>
                                @foreach($categories as $pc)
                                    <option value="{{ $pc->id }}" ${c.parent_id == {{ $pc->id }} ? 'selected' : ''}>{{ $pc->name }}</option>
                                @endforeach
                            </select>
                        </div>
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