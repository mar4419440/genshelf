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
                            <button class="btn btn-xs btn-o" onclick='openCustomerModal(@json($c))'>{{ __('Edit') }}</button>
                            @if($toggleCredit && $c->credit_balance > 0)
                                <button class="btn btn-xs btn-gn"
                                    onclick="recordPayment('{{ $c->id }}')">{{ __('Record Payment') }}</button>
                            @endif
                            <button class="btn btn-xs btn-bl"
                                onclick="viewCustomerHistory('{{ $c->id }}')">{{ __('History') }}</button>

                            <form action="{{ route('customers.destroy', $c->id) }}" method="POST" style="display:inline;"
                                onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-rd">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + ($toggleCredit ? 1 : 0) + ($toggleLoyalty ? 1 : 0) }}" class="empty-state">
                            {{ __('No data yet') }}</td>
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
                            <input name="phone" type="text" value="${c.phone || ''}">
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

        function viewCustomerHistory(id) { alert('History logic pending'); }
        function recordPayment(id) { alert('Payment logic pending'); }
    </script>
@endpush