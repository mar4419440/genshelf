@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Customers') }}</h2>
        <div style="display:flex;gap:8px">
            <button class="btn btn-pr" onclick="openCustomerModal()">➕ {{ __('Add Customer') }}</button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Email') }}</th>
                    @if($toggleCredit)
                        <th>{{ __('Credit Balance') }}</th>
                    @endif
                    @if($toggleLoyalty)
                        <th>{{ __('Loyalty Points') }}</th>
                    @endif
                    <th>{{ __('Actions') }}</th>
                </tr>
                @forelse($customers as $c)
                    <tr>
                        <td><strong>{{ $c->name }}</strong></td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->email }}</td>
                        @if($toggleCredit)
                            <td>
                                @if($c->credit_balance > 0)
                                    <span style="color:var(--rd)">{{ number_format($c->credit_balance, 2) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                        @if($toggleLoyalty)
                            <td>{{ $c->loyalty_points }} pts</td>
                        @endif
                        <td class="no-sort">
                            <div class="action-btns justify-content-center">
                                <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;" onclick='openCustomerModal(@json($c))' title="{{ __('Edit customer details') }}">✏️</button>
                                
                                @if($toggleCredit)
                                    <button class="btn btn-sm btn-soft-success rounded-circle" style="width:30px;height:30px;"
                                        onclick="recordPayment('{{ $c->id }}')" title="{{ __('Record a payment/clear debt') }}">💰</button>
                                    <button class="btn btn-sm btn-soft-danger rounded-circle" style="width:30px;height:30px;" 
                                        onclick="addDebt('{{ $c->id }}')" title="{{ __('Add a manual debt record') }}">💳</button>
                                @endif

                                <button class="btn btn-sm btn-soft-info rounded-circle" style="width:30px;height:30px;"
                                    onclick="viewCustomerHistory('{{ $c->id }}')" title="{{ __('View transaction history') }}">📜</button>

                                <form action="{{ route('customers.destroy', $c->id) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle" style="width:30px;height:30px;" title="{{ __('Delete customer') }}">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + ($toggleCredit ? 1 : 0) + ($toggleLoyalty ? 1 : 0) }}" class="empty-state">
                            {{ __('No data yet') }}
                        </td>
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
        function openCustomerModal(customer = null) {
            const isEdit = customer !== null;
            const actionUrl = isEdit ? `{{ url('customers') }}/${customer.id}` : `{{ route('customers.store') }}`;
            const methodField = isEdit ? `@method('PUT')` : '';

            let c = isEdit ? customer : { name: '', phone: '', email: '', loyalty_points: '0', credit_balance: '0.00' };

            const html = `
                                    <h3>${isEdit ? '{{ __('Edit Customer') }}' : '{{ __('Add Customer') }}'}</h3>
                                    <form action="${actionUrl}" method="POST">
                                        @csrf
                                        ${methodField}
                                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Name') }}</label>
                                                <input name="name" value="${c.name || ''}" required>
                                            </div>
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Name') }} ({{ __('Optional') }})</label>
                                                <input name="name_en" value="${c.name_en || ''}">
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:10px; margin-bottom: 12px;">
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Phone') }}</label>
                                                <input name="phone" type="text" value="${c.phone || ''}" required>
                                            </div>
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Email') }}</label>
                                                <input name="email" type="email" value="${c.email || ''}">
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:10px; margin-bottom: 16px;">
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Loyalty Points') }}</label>
                                                <input name="loyalty_points" type="number" value="${c.loyalty_points || 0}">
                                            </div>
                                            <div style="flex:1;">
                                                <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Credit Balance') }} (-)</label>
                                                <input name="credit_balance" type="number" step="0.01" value="${c.credit_balance || 0}">
                                            </div>
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
            document.getElementById('modal-overlay').style.alignItems = 'center';
            document.getElementById('modal-overlay').style.justifyContent = 'center';
        }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('modal-overlay').style.display = 'none';
        }

        function viewCustomerHistory(id) {
            document.getElementById('modal-box').innerHTML = `<h3>{{ __('Loading...') }}</h3>`;
            document.getElementById('modal-overlay').style.display = 'flex';

            fetch(`{{ url('customers') }}/${id}/history`)
                .then(res => res.json())
                .then(data => {
                    let html = `<h3 style="margin-bottom:16px">{{ __('Transaction History') }}</h3>`;
                    if (data.length === 0) {
                        html += `<p class="empty-state">{{ __('No transactions found.') }}</p>`;
                    } else {
                        html += `
                                            <div class="table-wrap" style="max-height:400px; overflow-y:auto;">
                                                <table>
                                                    <tr>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Paid') }}</th>
                                                        <th>{{ __('Due') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                    </tr>
                                        `;
                        data.forEach(tx => {
                            const date = new Date(tx.created_at).toLocaleString();
                            html += `
                                                <tr>
                                                    <td style="font-size:11px">${date}</td>
                                                    <td>${parseFloat(tx.total).toFixed(2)}</td>
                                                    <td>${parseFloat(tx.paid_amount || 0).toFixed(2)}</td>
                                                    <td style="color:var(--rd)">${parseFloat(tx.due_amount || 0).toFixed(2)}</td>
                                                    <td style="text-transform:capitalize;">${tx.payment_method}</td>
                                                </tr>
                                            `;
                        });
                        html += `</table></div>`;
                    }
                    html += `<button class="btn btn-o" onclick="closeModal()" style="margin-top:16px; width:100%">{{ __('Close') }}</button>`;
                    document.getElementById('modal-box').innerHTML = html;
                });
        }
        function recordPayment(id) {
            const customer = @json($customers).find(c => c.id == id);
            const html = `
                            <h3>{{ __('Record Payment') }}</h3>
                            <p>{{ __('Customer') }}: <strong>${customer.name}</strong></p>
                            <p>{{ __('Current Debt') }}: <strong style="color:var(--rd)">${parseFloat(customer.credit_balance).toFixed(2)}</strong></p>
                            <form action="{{ url('customers') }}/${id}/pay" method="POST">
                                @csrf
                                <div class="form-group" style="margin-bottom:16px">
                                    <label>{{ __('Amount to Pay') }}</label>
                                    <input name="amount" type="number" step="0.01" max="${customer.credit_balance}" value="${customer.credit_balance}" required autofocus style="width:100%">
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-gn" style="flex:1;">{{ __('Confirm Payment') }}</button>
                                </div>
                            </form>
                        `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').style.display = 'flex';
        }

        function addDebt(id) {
            const customer = @json($customers).find(c => c.id == id);
            const html = `
                    <h3>{{ __('Add Debt') }}</h3>
                    <p>{{ __('Customer') }}: <strong>${customer.name}</strong></p>
                    <form action="{{ url('customers') }}/${id}/add-debt" method="POST">
                        @csrf
                        <div class="form-group" style="margin-bottom:12px">
                            <label>{{ __('Amount to Add') }}</label>
                            <input name="amount" type="number" step="0.01" required autofocus style="width:100%">
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label>{{ __('Notes') }}</label>
                            <input name="notes" type="text" placeholder="{{ __('Reason for debt') }}" style="width:100%">
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-rd" style="flex:1;">{{ __('Confirm Adjustment') }}</button>
                        </div>
                    </form>
                `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').style.display = 'flex';
        }
    </script>
@endpush