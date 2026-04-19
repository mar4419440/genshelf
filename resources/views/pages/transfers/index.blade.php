@extends('layouts.app')

@section('title', __('Stock Transfers'))

@section('content')
    <div class="page-header">
        <div class="ph-text">
            <h1>📦 {{ __('Stock Transfers') }}</h1>
            <p>{{ __('Move inventory between storages and stores') }}</p>
        </div>
        <div class="ph-actions">
            <button class="btn btn-gn" onclick="openTransferModal()">{{ __('New Transfer') }}</button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('To') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('User') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                        <tr>
                            <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                            <td><strong>{{ $t->product->name }}</strong></td>
                            <td><span class="badge badge-o">{{ $t->fromStorage->name }}</span></td>
                            <td><span class="badge badge-gn">{{ $t->toStorage->name }}</span></td>
                            <td>{{ number_format($t->qty, 2) }}</td>
                            <td>{{ $t->user->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">{{ __('No transfers recorded yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="transfer-modal">
        <div class="modal" style="max-width: 500px;">
            <h3>{{ __('New Stock Transfer') }}</h3>
            <p style="font-size: 11px; margin-bottom: 12px;"><a href="{{ route('storages.index') }}" target="_blank">➕
                    {{ __('Add New Storage/Store') }}</a></p>
            <form action="{{ route('transfers.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ __('Product') }}</label>
                    <select name="product_id" required style="width:100%" onchange="loadStock(this.value)">
                        <option value="">{{ __('Select Product') }}</option>
                        @foreach(\App\Models\Product::all() as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>{{ __('From Storage') }}</label>
                        <select id="from_storage" name="from_storage_id" required style="width:100%"
                            onchange="checkStock()">
                            @foreach(\App\Models\Storage::all() as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('To Location') }}</label>
                        <select name="to_storage_id" required style="width:100%">
                            @foreach(\App\Models\Storage::all() as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->type }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('Quantity to Move') }}</label>
                    <input type="number" name="qty" step="0.01" required placeholder="{{ __('Available') }}: 0"
                        id="transfer_qty">
                </div>
                <div class="form-group">
                    <label>{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2"></textarea>
                </div>
                <div style="display:flex; gap:8px; margin-top:16px;">
                    <button type="button" class="btn btn-o" onclick="closeTransferModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-gn" style="flex:1;">{{ __('Confirm Transfer') }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openTransferModal() { document.getElementById('transfer-modal').classList.add('show'); }
        function closeTransferModal() { document.getElementById('transfer-modal').classList.remove('show'); }

        function loadStock(productId) {
            // Simple logic for Demo; in real app, we fetch available per storage via AJAX
        }
    </script>
@endsection