@extends('layouts.app')

@push('styles')
    <style>
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr 450px;
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

        .pos-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .pos-item-card {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 110px;
        }

        .pos-item-card:hover {
            border-color: var(--pr);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .pic-name { font-weight: 700; font-size: 13px; margin-bottom: 4px; }
        .pic-price { color: var(--pr); font-weight: 800; font-size: 15px; }
        .pic-stock { font-size: 10px; color: var(--tx3); font-weight: 600; }

        .pos-sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .pos-cart { flex: 1; overflow-y: auto; }
        .cart-item-row { padding: 12px 0; border-bottom: 1px solid var(--bg3); }
        .cir-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
        .cir-name { font-weight: 600; font-size: 13px; }
        .cir-del { color: var(--rd); background: none; font-size: 14px; }
        .cir-actions { display: flex; justify-content: space-between; align-items: center; }
        
        .removed-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed var(--border);
        }
        
        .removed-item {
            background: var(--rd-l);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .disposition-select {
            font-size: 11px;
            padding: 4px;
            border-radius: 4px;
            border: 1px solid var(--rd);
            background: #fff;
        }

        .qty-input {
            width: 70px;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
<div class="page-hdr">
    <h2>✏️ {{ __('Edit Invoice') }} #{{ $transaction->id }}</h2>
    <a href="{{ route('reports') }}" class="btn btn-o">🔙 {{ __('Back to Management') }}</a>
</div>

<div class="pos-layout">
    <!-- Main Panel -->
    <div class="pos-main">
        <div style="display:flex; gap:12px; align-items:center; margin-bottom:10px;">
            <input type="text" id="pos-search" class="search-bar" placeholder="{{ __('Search products...') }}" style="flex:1;">
            <button class="btn btn-o" onclick="openServiceModal()">➕ {{ __('Service') }}</button>
        </div>

        <!-- Products -->
        <div class="pos-products-grid" id="pos-product-grid">
            @foreach($products as $p)
                <div class="pos-item-card" onclick="addToCart('{{ $p->id }}', '{{ addslashes($p->name) }}', {{ $p->default_price }}, {{ $p->current_stock }})">
                    <div class="pic-name">{{ $p->name }}</div>
                    <div class="pic-bottom" style="display:flex; justify-content:space-between; align-items:flex-end;">
                        <div class="pic-price">{{ number_format($p->default_price, 2) }}</div>
                        <div class="pic-stock">{{ __('Stock') }}: {{ $p->current_stock }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Sidebar / Cart -->
    <div class="pos-sidebar">
        <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" id="edit-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="cart_data" id="cart-data-input">
            
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:var(--tx2);">{{ __('Customer') }}</label>
                <select name="customer_id" class="search-bar">
                    <option value="">{{ __('Walk-in Customer') }}</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $transaction->customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pos-cart">
                <h4 style="font-weight:700; border-bottom:1px solid var(--border); padding-bottom:8px; margin-bottom:10px;">{{ __('Items in Cart') }}</h4>
                <div id="cart-items-container"></div>

                <div id="removed-items-container" class="removed-section" style="display:none;">
                    <h4 style="font-weight:700; color:var(--rd); margin-bottom:10px;">📉 {{ __('Removed Items (Disposition)') }}</h4>
                    <div id="removed-list"></div>
                </div>
            </div>

            <div class="pos-totals" style="border-top:2px solid var(--border); padding-top:16px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:var(--tx2);">{{ __('Amount Paid') }}</label>
                        <input type="number" name="paid_amount" value="{{ $transaction->paid_amount }}" step="0.01" class="search-bar" oninput="updateSummary()">
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:var(--tx2);">{{ __('Due Date') }}</label>
                        <input type="date" name="due_date" value="{{ $transaction->due_date }}" class="search-bar">
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                    <span>{{ __('Subtotal') }}</span> <span id="summary-subtotal">0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                    <span>{{ __('Tax') }} ({{ $taxRate }}%)</span> <span id="summary-tax">0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:22px; font-weight:800; color:var(--pr); border-top:1px solid var(--bg3); padding-top:8px; margin-top:8px;">
                    <span>{{ __('TOTAL') }}</span> <span id="summary-total">0.00</span>
                </div>

                <button type="button" class="btn btn-pr" onclick="submitEdit()" style="width:100%; padding:14px; font-size:16px; font-weight:700; margin-top:15px;">💾 {{ __('Save Changes') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Service Modal -->
<div id="service-modal" class="modal-backdrop" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);">
    <div class="card" style="width:400px; margin: 100px auto; padding:24px; border-radius:16px;">
        <h3 style="font-weight:800; margin-bottom:20px;">🛠️ {{ __('Add Custom Service') }}</h3>
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div><label>{{ __('Service Name') }}</label><input type="text" id="svc-name" class="search-bar" style="width:100%"></div>
            <div><label>{{ __('Price') }}</label><input type="number" id="svc-price" class="search-bar" style="width:100%" step="0.01"></div>
            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="button" class="btn btn-pr" style="flex:2" onclick="addServiceToCart()">{{ __('Add to Cart') }}</button>
                <button type="button" class="btn btn-o" style="flex:1" onclick="closeServiceModal()">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = @json($cartItems);
    
    let originalItems = JSON.parse(JSON.stringify(cart));
    let removedItems = [];
    const taxRate = {{ $taxRate }};

    function addToCart(id, name, price, stock) {
        const existing = cart.find(i => i.id == id && !i.isService);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ id, name, price: parseFloat(price), qty: 1, isService: false, maxStock: stock, isOriginal: false });
        }
        renderCart();
    }

    function removeFromCart(index) {
        const item = cart[index];
        if (item.isOriginal && !item.isService) {
            removedItems.push(item);
        }
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        container.innerHTML = cart.map((item, i) => `
            <div class="cart-item-row">
                <div class="cir-top">
                    <span class="cir-name">${item.name} ${item.isOriginal ? '<small class="badge badge-pr">ORIGINAL</small>' : ''}</span>
                    <button type="button" class="cir-del" onclick="removeFromCart(${i})">✕</button>
                </div>
                <div class="cir-actions">
                    <div style="display:flex; gap:8px;">
                        <input type="number" class="qty-input" value="${item.qty}" min="1" onchange="updateQty(${i}, this.value)">
                        <input type="number" step="0.01" class="qty-input" value="${item.price}" onchange="updatePrice(${i}, this.value)" style="width:90px;">
                    </div>
                    <div style="font-weight:700;">${(item.price * item.qty).toFixed(2)}</div>
                </div>
            </div>
        `).join('');

        const removedContainer = document.getElementById('removed-items-container');
        const removedList = document.getElementById('removed-list');
        if (removedItems.length > 0) {
            removedContainer.style.display = 'block';
            removedList.innerHTML = removedItems.map((item, i) => `
                <div class="removed-item">
                    <span style="font-size:12px; font-weight:600;">${item.name}</span>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <select name="dispositions[${item.id}]" class="disposition-select">
                            <option value="restock">🔄 {{ __('Restock') }}</option>
                            <option value="damage">⚠️ {{ __('Mark Damaged') }}</option>
                        </select>
                        <button type="button" class="btn btn-xs btn-o" onclick="restoreItem(${i})">⤴️ {{ __('Restore') }}</button>
                    </div>
                </div>
            `).join('');
        } else {
            removedContainer.style.display = 'none';
        }
        updateSummary();
    }

    function updateQty(i, val) { 
        cart[i].qty = parseInt(val) || 1; 
        updateSummary(); 
    }
    
    function updatePrice(i, val) { 
        cart[i].price = parseFloat(val) || 0; 
        updateSummary(); 
    }

    function restoreItem(i) {
        cart.push(removedItems[i]);
        removedItems.splice(i, 1);
        renderCart();
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

    function submitEdit() {
        if (cart.length === 0) { alert("Cart cannot be empty. Use Returns for full cancellations."); return; }
        document.getElementById('edit-form').submit();
    }

    function openServiceModal() { document.getElementById('service-modal').style.display = 'block'; }
    function closeServiceModal() { document.getElementById('service-modal').style.display = 'none'; }
    function addServiceToCart() {
        const name = document.getElementById('svc-name').value;
        const price = parseFloat(document.getElementById('svc-price').value) || 0;
        if(!name) return;
        cart.push({ id: 'svc_'+Date.now(), name, price, qty: 1, isService: true, isOriginal: false });
        renderCart();
        closeServiceModal();
    }

    document.getElementById('pos-search').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.pos-item-card').forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(q) ? 'flex' : 'none';
        });
    });

    window.onload = renderCart;
</script>
@endsection
