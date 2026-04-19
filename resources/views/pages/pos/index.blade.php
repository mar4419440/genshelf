@extends('layouts.app')

@push('styles')
    <style>
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            height: calc(100vh - 160px);
            margin-top: -10px;
        }

        .pos-main {
            display: flex;
            flex-direction: column;
            gap: 16px;
            overflow: hidden;
        }

        .pos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
        }

        .cat-chip {
            padding: 8px 16px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 13px;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
        }

        .cat-chip.active {
            background: var(--pr);
            color: #fff;
            border-color: var(--pr);
        }

        .pos-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .pos-item-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            transition: transform 0.1s, border-color 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 120px;
        }

        .pos-item-card:hover {
            border-color: var(--pr);
            transform: translateY(-2px);
        }

        .pos-item-card:active {
            transform: scale(0.98);
        }

        .pic-name {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .pic-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .pic-price {
            color: var(--pr);
            font-weight: 800;
            font-size: 16px;
        }

        .pic-stock {
            font-size: 11px;
            color: var(--tx3);
            font-weight: 600;
        }

        .pos-sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            overflow: hidden;
        }

        .pos-cart {
            flex: 1;
            overflow-y: auto;
        }

        .cart-item-row {
            display: flex;
            flex-direction: column;
            padding: 12px 0;
            border-bottom: 1px solid var(--bg3);
            gap: 8px;
        }

        .cir-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cir-name {
            font-weight: 600;
            font-size: 13px;
            flex: 1;
        }

        .cir-del {
            color: var(--rd);
            background: none;
            padding: 0 4px;
            font-size: 14px;
        }

        .cir-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cir-qty {
            display: flex;
            align-items: center;
        }

        .qty-input {
            width: 80px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            font-weight: 700;
            padding: 8px;
            font-size: 15px;
        }

        .pos-totals {
            border-top: 2px solid var(--border);
            padding-top: 16px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .grand-total {
            font-size: 24px;
            font-weight: 800;
            color: var(--pr);
            margin-top: 8px;
            border-top: 1px solid var(--bg3);
            padding-top: 8px;
        }

        .checkout-btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 16px;
        }
    </style>
@endpush

@section('content')
    <div class="pos-layout">
        <!-- Main Panel -->
        <div class="pos-main">
            <div class="pos-header">
                <div style="display:flex; gap:12px; align-items:center;">
                    <h2 style="font-weight:800;">🛒 {{ __('POS') }}</h2>
                    <input type="text" id="pos-search" class="search-bar" placeholder="{{ __('Search products...') }}"
                        style="width: 250px;">
                </div>
                <select id="pos-storage-selector" class="search-bar" onchange="updateActiveStorage(this.value)">
                    @foreach(\App\Models\Storage::where('type', 'pos')->get() as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Categories -->
            <div class="category-scroll">
                <div class="cat-chip active" onclick="filterCategory('all', this)">{{ __('All Items') }}</div>
                @php $categories = \App\Models\Category::all(); @endphp
                @foreach($categories as $c)
                    <div class="cat-chip" onclick="filterCategory('{{ strtolower($c->name) }}', this)">{{ $c->name }}</div>
                @endforeach
            </div>

            <!-- Products -->
            <div class="pos-products-grid" id="pos-product-grid">
                @foreach($products as $p)
                    <div class="pos-item-card" data-cat="{{ strtolower($p->category) }}"
                        onclick="addToCart('{{ $p->id }}', '{{ addslashes($p->name) }}', {{ $p->default_price }}, {{ $p->current_stock }})">
                        <div class="pic-name">{{ $p->name }}</div>
                        <div class="pic-bottom">
                            <div class="pic-price">{{ number_format($p->default_price, 2) }}</div>
                            <div class="pic-stock">{{ __('Stock') }}: {{ $p->current_stock }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar / Cart -->
        <div class="pos-sidebar">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-weight:700;">{{ __('Active Cart') }}</h3>
                <button class="btn btn-sm btn-o" onclick="openServiceModal()">➕ Service</button>
            </div>

            <form action="{{ route('pos.checkout') }}" method="POST" id="checkout-form">
                @csrf
                <input type="hidden" name="storage_id" id="cart-storage-id">
                <input type="hidden" name="cart_data" id="cart-data-input">

                <div style="position:relative; margin-bottom:12px;">
                    <input type="hidden" name="customer_id" id="selected-customer-id">
                    <input type="text" id="customer-search" class="search-bar" style="width:100%"
                        placeholder="{{ __('Find Customer...') }}" onfocus="showCustomerDropdown()"
                        oninput="filterCustomerDropdown(this.value)">
                    <div id="customer-dropdown" class="card"
                        style="position:absolute; width:100%; display:none; z-index:100; max-height:200px; overflow-y:auto; padding:0;">
                        <div style="padding:10px; cursor:pointer;" onclick="selectCustomer('', 'Walk-in')">
                            {{ __('Walk-in Customer') }}
                        </div>
                        @foreach($customers as $c)
                            <div class="customer-item" data-name="{{ strtolower($c->name) }}"
                                onclick="selectCustomer('{{ $c->id }}', '{{ addslashes($c->name) }}')"
                                style="padding:10px; cursor:pointer; border-top:1px solid var(--border);">{{ $c->name }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="pos-cart" id="cart-items-container">
                    <div class="empty-state" style="margin-top:40px;">{{ __('Cart is empty') }}</div>
                </div>

                <div class="pos-totals">
                    <div class="total-row"><span>{{ __('Subtotal') }}</span> <span id="summary-subtotal">0.00</span></div>
                    <div class="total-row"><span>{{ __('Tax') }} ({{ $taxRate }}%)</span> <span id="summary-tax">0.00</span>
                    </div>

                    <div style="margin-top:12px; background:var(--bg3); padding:10px; border-radius:8px;">
                        <label style="font-size:11px; font-weight:700; color:var(--tx2);">{{ __('Amount Paid') }}</label>
                        <input type="number" name="paid_amount" id="cart-paid-amount" step="0.01" class="search-bar"
                            style="width:100%; border:none; background:none; padding:4px 0; font-size:16px; font-weight:700;"
                            placeholder="Full Payment">
                    </div>

                    <div class="grand-total">
                        <span>{{ __('Total') }}</span>
                        <span id="summary-total">0.00</span>
                    </div>

                    <button type="button" class="btn btn-pr checkout-btn"
                        onclick="submitCheckout()">{{ __('Place Order') }}</button>
                    <button type="button" class="btn btn-o btn-sm" style="width:100%; border:none;"
                        onclick="clearCart()">{{ __('Clear Cart') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let cart = [];
        const taxRate = {{ $taxRate }};

        function updateActiveStorage(id) { document.getElementById('cart-storage-id').value = id; }

        function filterCategory(cat, chip) {
            document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            document.querySelectorAll('.pos-item-card').forEach(card => {
                if (cat === 'all' || card.getAttribute('data-cat') === cat) card.style.display = 'flex';
                else card.style.display = 'none';
            });
        }

        document.getElementById('pos-search').addEventListener('input', e => {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.pos-item-card').forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(q) ? 'flex' : 'none';
            });
        });

        function addToCart(id, name, price, stock) {
            const existing = cart.find(i => i.id === id && !i.isService);
            if (existing) {
                if (existing.qty >= stock) { alert("Insufficient Stock"); return; }
                existing.qty++;
            } else {
                cart.push({ id, name, price: parseFloat(price), qty: 1, isService: false, maxStock: stock });
            }
            renderCart();
        }

        function setQty(index, val) {
            const item = cart[index];
            if (!item) return;
            let n = parseInt(val) || 1; // Minimum 1 logic
            if (n < 1) n = 1;
            if (!item.isService && n > item.maxStock) { alert("Max stock reached"); n = item.maxStock; }
            item.qty = n;
            renderCart();
        }

        function updateQty(index, delta) {
            const item = cart[index];
            let n = item.qty + delta;
            if (n < 1) return; // Prevent 0
            setQty(index, n);
        }

        function renderCart() {
            const container = document.getElementById('cart-items-container');
            if (cart.length === 0) {
                container.innerHTML = '<div class="empty-state" style="margin-top:40px;">Cart is empty</div>';
            } else {
                container.innerHTML = cart.map((item, i) => `
                            <div class="cart-item-row">
                                <div class="cir-top">
                                    <span class="cir-name">${item.name}</span>
                                    <button type="button" class="cir-del" onclick="removeFromCart(${i})">✕</button>
                                </div>
                                <div class="cir-actions">
                                    <div class="cir-qty">
                                        <input type="number" class="qty-input" value="${item.qty}" min="1" onchange="setQty(${i}, this.value)">
                                    </div>
                                    <div style="font-weight:700; width: 80px; text-align: right;">${(item.price * item.qty).toFixed(2)}</div>
                                </div>
                            </div>
                        `).join('');
            }
            updateSummary();
        }

        function updateSummary() {
            let sub = cart.reduce((s, i) => s + (i.price * i.qty), 0);
            let tax = sub * (taxRate / 100);
            let tot = sub + tax;
            document.getElementById('summary-subtotal').innerText = sub.toFixed(2);
            document.getElementById('summary-tax').innerText = tax.toFixed(2);
            document.getElementById('summary-total').innerText = tot.toFixed(2);
            document.getElementById('cart-data-input').value = JSON.stringify(cart);
        }

        function removeFromCart(i) { cart.splice(i, 1); renderCart(); }
        function clearCart() { cart = []; renderCart(); }

        function showCustomerDropdown() { document.getElementById('customer-dropdown').style.display = 'block'; }
        function filterCustomerDropdown(q) {
            q = q.toLowerCase();
            document.querySelectorAll('.customer-item').forEach(it => {
                it.style.display = it.getAttribute('data-name').includes(q) ? 'block' : 'none';
            });
        }
        function selectCustomer(id, name) {
            document.getElementById('selected-customer-id').value = id;
            document.getElementById('customer-search').value = name;
            document.getElementById('customer-dropdown').style.display = 'none';
        }

        function submitCheckout() {
            if (cart.length === 0) { alert("Cart is empty"); return; }
            document.getElementById('checkout-form').submit();
        }

        window.addEventListener('DOMContentLoaded', () => {
            updateActiveStorage(document.getElementById('pos-storage-selector').value);
        });

        // Close dropdown
        window.onclick = e => { if (!e.target.closest('#customer-search')) document.getElementById('customer-dropdown').style.display = 'none'; }
    </script>
@endpush