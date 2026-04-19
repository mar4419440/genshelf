@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="page-hdr">
        <h2>{{ __('Finance') }}</h2>
    </div>

    <div class="card-grid card-grid-4">
        <div class="card metric-card">
            <div class="metric-val">{{ number_format($totalRev, 2) }}</div>
            <div class="metric-lbl">{{ __('Revenue') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val" style="color:var(--rd)">{{ number_format($totalExp, 2) }}</div>
            <div class="metric-lbl">{{ __('Expenses') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val" style="color:var(--am)">{{ number_format($cogs, 2) }}</div>
            <div class="metric-lbl">{{ __('Cost of Goods Sold') }}</div>
        </div>
        <div class="card metric-card">
            <div class="metric-val" style="color:{{ $net >= 0 ? 'var(--gn)' : 'var(--rd)' }}">{{ number_format($net, 2) }}
            </div>
            <div class="metric-lbl">{{ __('Net Profit') }}</div>
        </div>
    </div>

    <div class="split">
        <!-- Expenses -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Expenses') }}</h3>
                <button class="btn btn-sm btn-pr" onclick="openExpenseModal()">➕ {{ __('Add Expense') }}</button>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Amount') }}</th>
                    </tr>
                    @forelse($expenses as $e)
                        <tr>
                            <td>{{ $e->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ __($e->category) }}</td>
                            <td>{{ $e->description }}</td>
                            <td>{{ number_format($e->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

        <!-- Cash Drawer -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Cash Drawer') }}</h3>
                <div style="display:flex;gap:6px">
                    @if(!$isDrawerOpen)
                        <button class="btn btn-sm btn-gn" onclick="openDrawerAction()">{{ __('Open Drawer') }}</button>
                    @else
                        <button class="btn btn-sm btn-am" onclick="cashInOut('in')">💵 {{ __('Cash In') }}</button>
                        <button class="btn btn-sm btn-rd" onclick="cashInOut('out')">💸 {{ __('Cash Out') }}</button>
                        <button class="btn btn-sm btn-o" onclick="closeDrawerAction()">🔒 {{ __('Close Drawer') }}</button>
                    @endif
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>Type</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Description') }}</th>
                    </tr>
                    @forelse($drawerEvents as $e)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($e->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge {{ in_array($e->type, ['open', 'in']) ? 'badge-gn' : 'badge-rd' }}">
                                    {{ strtoupper($e->type) }}
                                </span>
                            </td>
                            <td>{{ number_format($e->amount, 2) }}</td>
                            <td>{{ $e->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">{{ __('No data yet') }}</td>
                        </tr>
                    @endforelse
                </table>
            </div>
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
        function openExpenseModal() {
            const actionUrl = `{{ route('finance.expense.store') }}`;
            const html = `
                    <h3>{{ __('Log Expense') }}</h3>
                    <form action="${actionUrl}" method="POST">
                        @csrf
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Category') }}</label>
                            <select name="category" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                                <option value="Rent">{{ __('Rent') }}</option>
                                <option value="Utilities">{{ __('Utilities') }}</option>
                                <option value="Salaries">{{ __('Salaries') }}</option>
                                <option value="Marketing">{{ __('Marketing') }}</option>
                                <option value="Other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Amount') }}</label>
                            <input name="amount" type="number" step="0.01" required>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Arabic Description') }}</label>
                            <textarea name="description" required style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;"></textarea>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('English Description') }} ({{ __('Optional') }})</label>
                            <textarea name="description_en" style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;"></textarea>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="is_recurring"> {{ __('Is Recurring') }}
                            </label>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Log Expense') }}</button>
                        </div>
                    </form>
                `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').classList.add('active');
            document.getElementById('modal-overlay').style.display = 'flex';
        }

        function openDrawerAction(type = 'open') {
            const actionUrl = `{{ route('finance.drawer.store') }}`;
            const titles = { 'open': '{{ __('Open Drawer') }}', 'close': '{{ __('Close Drawer') }}', 'in': '{{ __('Cash In') }}', 'out': '{{ __('Cash Out') }}' };
            const html = `
                    <h3>${titles[type]}</h3>
                    <form action="${actionUrl}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="${type}">
                        <div style="margin-bottom: 12px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Amount') }}</label>
                            <input name="amount" type="number" step="0.01" value="0.00" required>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Description') }}</label>
                            <textarea name="description" rows="2" style="width:100%; border:1px solid var(--border); border-radius:var(--radius); padding:8px;"></textarea>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Submit') }}</button>
                        </div>
                    </form>
                `;
            document.getElementById('modal-box').innerHTML = html;
            document.getElementById('modal-overlay').classList.add('active');
            document.getElementById('modal-overlay').style.display = 'flex';
        }

        function cashInOut(type) { openDrawerAction(type); }
        function closeDrawerAction() { openDrawerAction('close'); }

        function closeModal() {
            document.getElementById('modal-overlay').classList.remove('active');
            document.getElementById('modal-overlay').style.display = 'none';
        }
    </script>
@endpush