@extends('layouts.app')

@push('styles')
    <style>
        /* ===== POS SPECIFIC ===== */
        .pos-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
            max-height: 65vh;
            overflow-y: auto;
            padding: 4px;
        }

        .pos-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
            cursor: pointer;
            transition: border-color .15s;
            text-align: center;
        }

        .pos-card:hover {
            border-color: var(--pr);
        }

        .pos-card .pc-name {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .pos-card .pc-price {
            color: var(--pr);
            font-weight: 700;
            font-size: 15px;
        }

        .pos-card .pc-stock {
            font-size: 11px;
            color: var(--tx3);
            margin-top: 4px;
        }

        .cart-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
        }

        .cart-items {
            max-height: 40vh;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid var(--bg3);
            font-size: 13px;
        }

        .cart-item .ci-name {
            flex: 1;
            font-weight: 500;
        }

        .cart-item .ci-price-input {
            width: 70px;
            text-align: center;
            font-size: 12px;
            padding: 4px;
        }

        .cart-item .ci-qty {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .cart-item .ci-qty button {
            width: 24px;
            height: 24px;
            padding: 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg3);
            border-radius: 4px;
        }

        .cart-item .ci-qty span {
            width: 24px;
            text-align: center;
            font-weight: 600;
        }

        .cart-item .ci-total {
            font-weight: 600;
            width: 70px;
            text-align: right;
        }

        .cart-item .ci-del {
            background: none;
            color: var(--rd);
            font-size: 16px;
            padding: 2px 6px;
        }

        .cart-summary {
            border-top: 2px solid var(--tx);
            padding-top: 12px;
            margin-top: 12px;
        }

        .cart-summary .cs-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 13px;
        }

        .cart-summary .cs-total {
            font-size: 18px;
            font-weight: 700;
            color: var(--pr);
            border-top: 1px solid var(--border);
            padding-top: 8px;
            margin-top: 4px;
        }

        .cart-actions {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }

        .cart-actions .btn {
            flex: 1;
            justify-content: center;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Point of Sale') }}</h2>
    </div>

    <div class="split-pos">
        <!-- Products Section -->
        <div>
            <input class="search-bar" type="text" placeholder="{{ __('Search products...') }}" id="pos-search">
            <div class="pos-products" id="pos-product-grid">
                @forelse($products as $p)
                    <div class="pos-card"
                        onclick="addToCart('{{ $p->id }}', '{{ addslashes($p->name) }}', {{ $p->default_price }}, {{ $p->current_stock }})">
                        <div class="pc-name">{{ $p->name }}</div>
                        <div class="pc-price">{{ number_format($p->default_price, 2) }}</div>
                        <div class="pc-stock">{{ __('Stock') }}: {{ $p->current_stock }}</div>
                    </div>
                @empty
                    <div class="empty-state">{{ __('No data yet') }}</div>
                @endforelse
            </div>
        </div>

        <!-- Cart Section -->
        <div>
            <div class="cart-box">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <h3>{{ __('Cart') }}</h3>
                    <button class="btn btn-sm btn-o" onclick="openServiceModal()">➕ {{ __('Add Service') }}</button>
                </div>

                <form action="{{ route('pos.checkout') }}" method="POST" id="checkout-form">
                    @csrf
                    <div class="form-group" style="margin-bottom: 14px;">
                        <select name="customer_id" id="cart-customer" style="width: 100%; padding: 8px;">
                            <option value="">{{ __('Walk-in Customer') }}</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($toggleCredit)
                        <label style="font-size:12px;display:flex;align-items:center;gap:6px;margin-bottom:10px">
                            <input type="checkbox" name="credit_sale" id="cart-credit-sale"> {{ __('Credit Sale') }}
                        </label>
                    @endif

                    <!-- Hidden inputs to submit cart data -->
                    <input type="hidden" name="cart_data" id="cart-data-input">

                    <div class="cart-items" id="cart-items-container">
                        <div class="empty-state" style="padding:20px" id="empty-cart-msg">{{ __('Cart is empty') }}</div>
                    </div>

                    <div class="cart-summary">
                        <div class="cs-row">
                            <span>{{ __('Subtotal') }}</span>
                            <span id="summary-subtotal">0.00</span>
                        </div>
                        @if($taxRate > 0)
                            <div class="cs-row">
                                <span>{{ __('Tax') }} ({{ $taxRate }}%)</span>
                                <span id="summary-tax">0.00</span>
                            </div>
                        @endif
                        <div class="cs-row cs-total">
                            <span>{{ __('Grand Total') }}</span>
                            <span id="summary-total">0.00</span>
                        </div>

                        <!-- Partial Payment / Debt -->
                        <div class="form-group"
                            style="margin-top: 12px; border-top: 1px dashed var(--border); padding-top: 12px;">
                            <label
                                style="display:block; font-size:11px; color:var(--tx2); margin-bottom:4px;">{{ __('Amount Paid') }}
                                ({{ __('Leave empty for full pay') }})</label>
                            <input type="number" name="paid_amount" id="cart-paid-amount" step="0.01" class="search-bar"
                                style="width:100%; height:38px;" placeholder="0.00">
                        </div>
                    </div>

                    <div class="cart-actions">
                        <button type="button" class="btn btn-rd" onclick="clearCart()">{{ __('Clear') }}</button>
                        <button type="button" class="btn btn-gn" onclick="submitCheckout()">{{ __('Checkout') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let cart = [];
        const taxRate = {{ $taxRate }};

        // Quick search logic
        document.getElementById('pos-search').addEventListener('input', function (e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.pos-card').forEach(card => {
                const name = card.querySelector('.pc-name').innerText.toLowerCase();
                card.style.display = name.includes(q) ? 'block' : 'none';
            });
        });

        function addToCart(id, name, price, stock) {
            const existing = cart.find(i => i.id === id && !i.isService);
            if (existing) {
                if (existing.qty >= stock) { alert("{{ __('Not enough stock') }}"); return; }
                existing.qty++;
            } else {
                cart.push({ id: id, name: name, price: parseFloat(price), qty: 1, isService: false, maxStock: stock });
            }
            renderCart();
        }

        function updateQty(index, delta) {
            const item = cart[index];
            if (!item) return;

            let newQty = parseInt(item.qty) + delta;

            if (newQty <= 0) {
                cart.splice(index, 1);
            } else if (!item.isService && newQty > item.maxStock) {
                alert("{{ __('Not enough stock') }}");
            } else {
                item.qty = newQty;
            }
            renderCart();
        }

        function updatePrice(index, price) {
            price = parseFloat(price) || 0;
            cart[index].price = price;

            // Surgically update totals instead of full renderCart to preserve focus
            const item = cart[index];
            const itemsContainer = document.getElementById('cart-items-container');
            const itemRow = itemsContainer.querySelectorAll('.cart-item')[index];
            if (itemRow) {
                itemRow.querySelector('.ci-total').innerText = (price * item.qty).toFixed(2);
            }

            updateSummary();
        }

        function updateSummary() {
            let subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            let tax = subtotal * (taxRate / 100);
            let total = subtotal + tax;

            document.getElementById('summary-subtotal').innerText = subtotal.toFixed(2);
            if (document.getElementById('summary-tax')) {
                document.getElementById('summary-tax').innerText = tax.toFixed(2);
            }
            document.getElementById('summary-total').innerText = total.toFixed(2);

            // Sync to hidden input
            document.getElementById('cart-data-input').value = JSON.stringify(cart);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items-container');
            const emptyMsg = document.getElementById('empty-cart-msg');

            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(emptyMsg);
                emptyMsg.style.display = 'block';
            } else {
                emptyMsg.style.display = 'none';
                container.innerHTML = cart.map((item, i) => `
                        <div class="cart-item">
                            <div class="ci-name">${item.name}</div>
                            <input class="ci-price-input" type="number" step="0.01" value="${item.price}" oninput="updatePrice(${i}, this.value)">
                            <div class="ci-qty">
                                <button type="button" onclick="updateQty(${i}, -1)">−</button>
                                <span>${item.qty}</span>
                                <button type="button" onclick="updateQty(${i}, 1)">+</button>
                            </div>
                            <div class="ci-total">${(item.price * item.qty).toFixed(2)}</div>
                            <button type="button" class="ci-del" onclick="removeFromCart(${i})">✕</button>
                        </div>
                    `).join('');
            }
            updateSummary();
        }

        function submitCheckout() {
            if (cart.length === 0) {
                alert("{{ __('Cart is empty') }}");
                return;
            }
            document.getElementById('checkout-form').submit();
        }

        // Service Modal Logic
        function openServiceModal() {
            const modal = document.getElementById('modal-box');
            modal.innerHTML = `
                    <div style="padding:10px">
                        <h3 style="margin-bottom:16px">➕ {{ __('Add Custom Service / Item') }}</h3>
                        <div class="form-group" style="margin-bottom:12px">
                            <label>{{ __('Item Name') }}</label>
                            <input type="text" id="svc-name" placeholder="Service / Repair..." style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label>{{ __('Price') }}</label>
                            <input type="number" id="svc-price" step="0.1" placeholder="0.00" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                        </div>
                        <div style="display:flex; gap:10px">
                            <button class="btn btn-pr" onclick="addCustomService()">{{ __('Add to Cart') }}</button>
                            <button class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                        </div>
                    </div>
                `;
            document.getElementById('modal-overlay').classList.add('show');
        }

        function addCustomService() {
            const name = document.getElementById('svc-name').value;
            const price = parseFloat(document.getElementById('svc-price').value) || 0;
            if (!name) { alert("{{ __('Please enter name') }}"); return; }

            cart.push({
                id: 'svc-' + Date.now(),
                name: name,
                price: price,
                qty: 1,
                isService: true
            });

            closeModal();
            renderCart();
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('show');
        }
    </script>
@endpush