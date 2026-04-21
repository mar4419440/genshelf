@extends('layouts.app')

@push('styles')
    <style>
        .category-item:hover {
            background-color: var(--pr-l) !important;
            color: var(--pr) !important;
        }
    </style>
@endpush

@section('content')
    <div style="margin-bottom: 24px;">
        <h2 style="font-size:24px; font-weight:700; color:var(--tx);">{{ __('Add New Product') }}</h2>
        <p style="color:var(--tx2); font-size:14px;">
            {{ __('Fill in the details to add a new item to your local inventory.') }}
        </p>
    </div>

    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- Left Side: Basic Info -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card" style="padding: 24px;">
                    <h3
                        style="font-size:16px; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        📄 {{ __('Basic Information') }}
                    </h3>

                    <div style="margin-bottom:16px;">
                        <label
                            style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Barcode / SKU') }}</label>
                        <input type="text" name="barcode"
                            style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; font-family:monospace; font-weight:700; font-size:15px; background:var(--bg3);"
                            placeholder="Scan or enter barcode">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Arabic Name') }}</label>
                            <input type="text" name="name"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                required placeholder="اسم المنتج">
                        </div>
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('English Name') }}</label>
                            <input type="text" name="name_en"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                placeholder="Product Name">
                        </div>
                    </div>

                    <div style="margin-bottom:16px; position:relative;" id="category-search-container">
                        <label
                            style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Category') }}</label>

                        <!-- Searchable Select Trigger -->
                        <div id="category-select-trigger" onclick="toggleCategorySearch()"
                            style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px; background:var(--bg); cursor:pointer; display:flex; justify-content:space-between; align-items:center;">
                            <span id="selected-category-text" style="color:var(--tx);">{{ __('Select Category') }}</span>
                            <span style="color:var(--tx2); font-size:12px;">▼</span>
                        </div>

                        <!-- Hidden Input for Form Submission -->
                        <input type="hidden" name="category_id" id="category_id_input" required>

                        <!-- Dropdown Menu -->
                        <div id="category-dropdown"
                            style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--bg2); border:1px solid var(--border); border-radius:8px; margin-top:4px; z-index:1000; box-shadow:0 4px 12px rgba(0,0,0,0.1); overflow:hidden;">
                            <div style="padding:8px; border-bottom:1px solid var(--border);">
                                <input type="text" id="category-search-input" onkeyup="filterCategories()"
                                    placeholder="{{ __('Search...') }}"
                                    style="width:100%; padding:8px; border:1px solid var(--border); border-radius:6px; background:var(--bg); color:var(--tx);">
                            </div>
                            <ul id="category-list"
                                style="list-style:none; padding:0; margin:0; max-height:200px; overflow-y:auto;">
                                @foreach ($categories as $c)
                                    <li class="category-item" data-id="{{ $c->id }}"
                                        data-name="{{ strtolower($c->full_path . ' ' . $c->name_en) }}"
                                        onclick="selectCategory('{{ $c->id }}', '{{ $c->full_path }}{{ $c->name_en ? ' - ' . $c->name_en : '' }}')"
                                        style="padding:10px 12px; cursor:pointer; font-size:14px; color:var(--tx); border-bottom:1px solid var(--bg2);">
                                        {{ $c->full_path }} {{ $c->name_en ? ' - ' . $c->name_en : '' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div
                        style="display:grid; grid-template-columns: 100px 1fr; gap:16px; align-items:center; background:var(--bg2); padding:12px; border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="is_service" id="is_service" value="1" onchange="toggleServiceFields()">
                            <label for="is_service" style="font-weight:600; font-size:13px;">{{ __('Service') }}</label>
                        </div>
                        <p style="font-size:12px; color:var(--tx2);">
                            {{ __('Check this if the item is a service (no stock tracking needed).') }}
                        </p>
                    </div>
                </div>

                <div class="card" id="stock-card" style="padding: 24px; display:block;">
                    <h3
                        style="font-size:16px; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                        📦 {{ __('Initial Stock & Restock') }}
                    </h3>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Initial Storage Location') }}</label>
                            <select name="storage_id" id="storage_id"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                required>
                                <option value="">{{ __('Select Storage') }}</option>
                                @foreach ($storages as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Primary Supplier') }}</label>
                            <select name="supplier_id" id="supplier_id"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                required>
                                <option value="">{{ __('Select Supplier') }}</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Initial Quantity') }}</label>
                            <input type="number" name="initial_qty" id="initial_qty" value="0"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                required>
                        </div>
                        <div>
                            <label
                                style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Buying Cost') }}
                                ({{ $costMode == 'unit' ? __('Per Unit') : __('Total') }})</label>
                            <input type="number" step="0.01" name="cost" id="cost"
                                style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;"
                                required placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Pricing & Options -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card" style="padding: 24px;">
                    <h3 style="font-size:16px; font-weight:600; margin-bottom:16px;">💰 {{ __('Pricing & Alerts') }}</h3>

                    <div style="margin-bottom:16px;">
                        <label
                            style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Selling Price') }}</label>
                        <input type="number" step="0.01" name="default_price"
                            style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;" required
                            placeholder="0.00">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label
                            style="display:block; font-size:12px; font-weight:600; color:var(--tx2); margin-bottom:6px;">{{ __('Low Stock Alert at') }}</label>
                        <input type="number" name="low_stock_threshold" value="{{ $lowStockDefault }}"
                            style="width:100%; padding:12px; border:1px solid var(--border); border-radius:8px;">
                    </div>
                </div>

                <div class="card" style="padding: 24px;">
                    <h3 style="font-size:16px; font-weight:600; margin-bottom:16px;">🛡️ {{ __('Warranty & Expiry') }}</h3>

                    <div style="margin-bottom:12px; display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" name="has_warranty" id="has_warranty" value="1" onchange="toggleWarranty()">
                        <label for="has_warranty"
                            style="font-size:13px; font-weight:600;">{{ __('Enable Warranty') }}</label>
                    </div>
                    <div id="warranty-input" style="display:none; margin-bottom:20px;">
                        <label
                            style="display:block; font-size:11px; margin-bottom:4px;">{{ __('Warranty Duration (Months)') }}</label>
                        <input type="number" name="warranty_duration"
                            style="width:100%; padding:8px; border:1px solid var(--border); border-radius:6px;">
                    </div>

                    <div style="margin-bottom:12px; display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" name="has_expiration" id="has_expiration" value="1" onchange="toggleExpiration()">
                        <label for="has_expiration"
                            style="font-size:13px; font-weight:600;">{{ __('Has Expiration Date') }}</label>
                    </div>
                    <div id="expiration-input" style="display:none;">
                        <label style="display:block; font-size:11px; margin-bottom:4px;">{{ __('Expiration Date') }}</label>
                        <input type="date" name="expiration_date"
                            style="width:100%; padding:8px; border:1px solid var(--border); border-radius:6px;">
                    </div>
                </div>

                <button type="submit" class="btn btn-pr" style="padding:16px; font-weight:700; font-size:16px;">
                    🚀 {{ __('Complete and Save Product') }}
                </button>
                <a href="{{ route('inventory') }}"
                    style="text-align:center; color:var(--tx2); font-size:13px; text-decoration:none;">{{ __('Back to Inventory') }}</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function toggleWarranty() {
            document.getElementById('warranty-input').style.display = document.getElementById('has_warranty').checked ? 'block' : 'none';
        }
        function toggleExpiration() {
            document.getElementById('expiration-input').style.display = document.getElementById('has_expiration').checked ? 'block' : 'none';
        }
        function toggleServiceFields() {
            const isService = document.getElementById('is_service').checked;
            document.getElementById('stock-card').style.display = isService ? 'none' : 'block';
            document.getElementById('storage_id').required = !isService;
            document.getElementById('supplier_id').required = !isService;
            document.getElementById('cost').required = !isService;
        }

        // Searchable Category Select Logic
        function toggleCategorySearch() {
            const dropdown = document.getElementById('category-dropdown');
            const isHidden = dropdown.style.display === 'none';
            dropdown.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                document.getElementById('category-search-input').focus();
            }
        }

        function filterCategories() {
            const input = document.getElementById('category-search-input').value.toLowerCase();
            const items = document.querySelectorAll('.category-item');
            items.forEach(item => {
                const text = item.getAttribute('data-name');
                item.style.display = text.includes(input) ? 'block' : 'none';
            });
        }

        function selectCategory(id, name) {
            document.getElementById('category_id_input').value = id;
            document.getElementById('selected-category-text').innerText = name;
            document.getElementById('category-dropdown').style.display = 'none';
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function (e) {
            const container = document.getElementById('category-search-container');
            if (!container.contains(e.target)) {
                document.getElementById('category-dropdown').style.display = 'none';
            }
        });
    </script>
@endpush