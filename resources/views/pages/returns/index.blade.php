@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>{{ __('Invoices & Returns') }}</h2>
    <div style="display:flex;gap:8px">
        <button class="btn btn-pr" onclick="openReturnModal('invoice')">📄 {{ __('Invoice Return') }}</button>
        <button class="btn btn-am" onclick="openReturnModal('defective')">⚠ {{ __('Defective Return') }}</button>
        <button class="btn btn-o" onclick="openReturnModal('general')">📦 {{ __('General Return') }}</button>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h3>{{ __('Returns Log') }}</h3>
        <form method="GET" action="{{ route('returns') }}" style="display:flex; gap:8px;">
            <input type="text" name="search_log" value="{{ request('search_log') }}" class="search-bar" style="margin-bottom:0; width:200px;" placeholder="{{ __('Value, Reason, Date...') }}">
            <button type="submit" class="btn btn-sm btn-pr">{{ __('Search') }}</button>
            @if(request()->filled('search_log'))
                <a href="{{ route('returns') }}" class="btn btn-sm btn-o">✕</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>Type</th>
                <th>{{ __('Reason') }}</th>
                <th>{{ __('Refund Amount') }}</th>
                <th>{{ __('Refund Method') }}</th>
            </tr>
            @forelse($returns as $r)
                <tr>
                    <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <span class="badge {{ $r->type === 'defective' ? 'badge-rd' : ($r->type === 'invoice' ? 'badge-bl' : 'badge-am') }}">
                            {{ $r->type }}
                        </span>
                    </td>
                    <td>{{ $r->reason }}</td>
                    <td>{{ number_format($r->refund_amount, 2) }}</td>
                    <td>{{ $r->refund_method }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">{{ __('No data yet') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom:12px">🔍 {{ __('Find Invoice to Return') }}</h3>
    <form method="GET" action="{{ route('returns') }}" style="display:flex; gap:8px; margin-bottom:12px;">
        <input type="text" name="search_invoice" value="{{ request('search_invoice') }}" class="search-bar" style="margin-bottom:0; flex:1;" placeholder="{{ __('Search Invoice ID, Customer, Date, Total, or Item Name...') }}">
        <button type="submit" class="btn btn-pr">{{ __('Search') }}</button>
        @if(request()->filled('search_invoice'))
            <a href="{{ route('returns') }}" class="btn btn-o">{{ __('Clear') }}</a>
        @endif
    </form>
    <div class="table-wrap">
        <table>
            <tr style="background:var(--bg3); font-size:11px;">
                <th>#</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
            @forelse($transactions as $t)
                <tr>
                    <td><strong>{{ $t->id }}</strong></td>
                    <td>{{ $t->customer->name ?? __('Walk-in') }}</td>
                    <td>{{ number_format($t->total, 2) }}</td>
                    <td>{{ $t->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div style="display:flex; gap:4px;">
                            <a href="{{ route('pos.invoice', $t->id) }}" target="_blank" class="btn btn-xs btn-o" title="{{ __('View Invoice') }}">📄</a>
                            <button class="btn btn-xs btn-rd" onclick="openReturnModal('invoice', {id: '{{ $t->id }}', total: '{{ $t->total }}'})">↩ {{ __('Process Return') }}</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">{{ __('No invoices found.') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h3>{{ __('Defective Products Log') }}</h3>
        <form method="GET" action="{{ route('returns') }}" style="display:flex; gap:8px;">
            <input type="text" name="search_defective" value="{{ request('search_defective') }}" class="search-bar" style="margin-bottom:0; width:200px;" placeholder="{{ __('Item, Supplier, Issue...') }}">
            <button type="submit" class="btn btn-sm btn-pr">{{ __('Search') }}</button>
            @if(request()->filled('search_defective'))
                <a href="{{ route('returns') }}" class="btn btn-sm btn-o">✕</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Supplier') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
            @forelse($defectiveProducts as $d)
                <tr>
                    <td>{{ $d->product->name ?? '?' }}</td>
                    <td>{{ $d->supplier->name ?? '—' }}</td>
                    <td>{{ $d->description }}</td>
                    <td>{{ $d->created_at->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge {{ $d->status === 'resolved' ? 'badge-gn' : ($d->status === 'claimed' ? 'badge-am' : 'badge-rd') }}">
                            {{ __($d->status) }}
                        </span>
                    </td>
                    <td>
                        <select onchange="updateDefectStatus('{{ $d->id }}', this.value)" style="padding:4px;font-size:11px">
                            <option value="open" {{ $d->status === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                            <option value="claimed" {{ $d->status === 'claimed' ? 'selected' : '' }}>{{ __('Claimed') }}</option>
                            <option value="resolved" {{ $d->status === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                        </select>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">{{ __('No data yet') }}</td></tr>
            @endforelse
        </table>
    </div>
</div>

    <!-- Modal Container -->
    <div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
      <div class="modal" id="modal-box" style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;"></div>
    </div>
    @endsection
    
    @push('scripts')
    <script>
        @if(isset($preFilledProduct) && $preFilledProduct)
            window.addEventListener('DOMContentLoaded', () => {
                openReturnModal('defective', { product_id: '{{ $preFilledProduct->id }}' });
            });
        @endif

        function openReturnModal(type, preFill = null) {
            let html = '';
            
            // Unified header
            let title = '';
            if(type === 'defective') title = '{{ __("Log Defective Product") }}';
            else if(type === 'invoice') title = '{{ __("Return from Invoice") }}';
            else title = '{{ __("General Return") }}';

            html = `<h3>${title}</h3>`;

            if (type === 'defective') {
                html += `
                    <form action="{{ route('defective.store') }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 12px; background:var(--bg3); padding:10px; border-radius:8px;">
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--tx3);margin-bottom:4px;">{{ __('Step 1: Link to Invoice (Optional)') }}</label>
                            <select name="transaction_id" style="width:100%; padding:6px; font-size:12px;">
                                <option value="">-- {{ __('Not linked to an invoice') }} --</option>
                                @foreach($transactions as $t)
                                    <option value="{{ $t->id }}">ID: {{ $t->id }} - {{ $t->customer->name ?? 'Walk-in' }} - {{ number_format($t->total, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Product') }}</label>
                            <select name="product_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" ${preFill && preFill.product_id == '{{ $p->id }}' ? 'selected' : ''}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Supplier') }}</label>
                            <select name="supplier_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Issues Description') }}</label>
                            <textarea name="description" required style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;"></textarea>
                        </div>
                        <input type="hidden" name="status" value="open">
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Log Issue') }}</button>
                        </div>
                    </form>
                `;
            } else {
                html += `
                    <form action="{{ route('returns.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="${type}">
                        
                        <div style="margin-bottom: 12px; background:var(--bg3); padding:10px; border-radius:8px;">
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--tx3);margin-bottom:4px;">{{ __('Step 1: Link to Invoice (Required for Invoice Return)') }}</label>
                            <select name="transaction_id" ${type === 'invoice' ? 'required' : ''} style="width:100%; padding:8px;">
                                <option value="">-- ${type === 'invoice' ? '{{ __("Select Transaction") }}' : '{{ __("No Invoice (Unknown Origin)") }}'} --</option>
                                @foreach($transactions as $t)
                                    <option value="{{ $t->id }}" ${preFill && preFill.id == '{{ $t->id }}' ? 'selected' : ''}>ID: {{ $t->id }} - {{ $t->customer->name ?? 'Walk-in' }} - {{ number_format($t->total, 2) }}</option>
                                @endforeach
                            </select>
                        </div>

                        ${type !== 'invoice' ? `
                            <div style="margin-bottom: 12px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Select Product') }}</label>
                                <select name="product_id" style="width:100%; padding:8px;">
                                    <option value="">-- {{ __('Optional') }} --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" ${preFill && preFill.product_id == '{{ $p->id }}' ? 'selected' : ''}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        ` : ''}

                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Reason') }}</label>
                            <input name="reason" required value="${type === 'invoice' ? 'Invoice Return' : ''}">
                        </div>
                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Refund Amount') }}</label>
                                <input name="refund_amount" type="number" step="0.01" required value="${preFill ? preFill.total : ''}">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Refund Method') }}</label>
                                <select name="refund_method" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="credit">{{ __('Store Credit') }}</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="restocked" checked>
                                {{ __('Restock Items') }}
                            </label>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Process Return') }}</button>
                        </div>
                    </form>
                `;
            }
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').classList.add('active');
            document.getElementById('modal-overlay').style.display = 'flex';
        }

        function updateDefectStatus(id, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('defective') }}/${id}`;
            form.innerHTML = `@csrf @method('PUT') <input type="hidden" name="status" value="${status}">`;
            document.body.appendChild(form);
            form.submit();
        }
    
        function closeModal() { 
            document.getElementById('modal-overlay').classList.remove('active'); 
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
    @endpush
