@extends('layouts.app')

@push('styles')
<style>
    :root {
        --premium-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .premium-card {
        background: #fff;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .premium-card:hover {
        transform: translateY(-5px);
    }

    .gradient-stat {
        background: var(--premium-gradient);
        color: #fff !important;
    }

    .gradient-stat .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .glass-filter {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .premium-table thead th {
        background: var(--bg2);
        color: var(--tx3);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        padding: 18px 20px;
        border: none;
    }

    .premium-table tbody tr {
        border-bottom: 1px solid var(--bg3);
        transition: all 0.2s;
    }

    .premium-table tbody tr:hover {
        background: #fcfaff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
    }

    .status-pill {
        font-weight: 700;
        font-size: 11px;
        padding: 6px 16px;
        border-radius: 30px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .btn-soft-primary { background: rgba(99, 102, 241, 0.1); color: #6366f1; border: none; }
    .btn-soft-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: none; }
    .btn-soft-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
    .btn-soft-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: none; }
    .btn-soft-primary:hover, .btn-soft-success:hover, .btn-soft-danger:hover, .btn-soft-info:hover { 
        filter: brightness(0.95); 
        transform: scale(1.05);
    }
    
    .floating-btn {
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-5 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-extrabold mb-1" style="font-weight:900; letter-spacing:-0.5px;">
                <span class="text-primary">{{ __('Financial') }}</span> {{ __('Flow') }}
            </h2>
            <p class="text-muted mb-0">{{ __('Enterprise-grade expense tracking & reconciliation') }}</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary px-4 py-3 rounded-pill fw-bold floating-btn" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus-circle me-2"></i>{{ __('Log Expense') }}
            </button>
            <a href="{{ route('expenses.summary') }}" class="btn btn-white border shadow-sm px-4 py-3 rounded-pill ms-2 fw-bold">
                <i class="fas fa-analytics me-2"></i>{{ __('Intelligence') }}
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card premium-card gradient-stat p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-white p-2 rounded-circle text-primary shadow-sm" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <span class="badge bg-white text-primary rounded-pill px-3">{{ __('MTD') }}</span>
                </div>
                <span class="text-muted small fw-bold text-uppercase ls-1">{{ __('Monthly Total') }}</span>
                <h2 class="fw-900 mb-0 mt-1">{{ number_format($totalInPeriod, 2) }}</h2>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card premium-card p-4 border-0 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-soft-primary p-2 rounded-circle text-primary" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <span class="text-muted small fw-bold text-uppercase ls-1">{{ __('Transactions') }}</span>
                <h2 class="fw-900 mb-0 mt-1">{{ $expenses->total() }}</h2>
                <div class="mt-2 x-small text-success fw-bold">
                    <i class="fas fa-check-circle me-1"></i>{{ count($expenses->items()) }} {{ __('recent entries') }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card premium-card p-4 border-0 h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fas fa-chart-line fa-6x"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase ls-1 mb-3 d-block">{{ __('Budget Performance') }}</span>
                
                @php 
                    $budgetTotal = $budgets->sum('budgeted_amount');
                    $spentTotal = $categoryBreakdown->sum('total');
                    $pct = $budgetTotal > 0 ? ($spentTotal / $budgetTotal) * 100 : 0;
                    $status = $pct > 90 ? ['danger', __('Critical')] : ($pct > 70 ? ['warning', __('Heavy')] : ['success', __('Healthy')]);
                @endphp

                <div class="d-flex justify-content-between align-items-end mb-2">
                    <h3 class="fw-900 mb-0">{{ round($pct, 1) }}%</h3>
                    <span class="text-{{ $status[0] }} fw-bold x-small">{{ $status[1] }} {{ __('Utilization') }}</span>
                </div>
                
                <div class="progress" style="height: 12px; border-radius: 10px; background: #f0f0f0;">
                    <div class="progress-bar bg-{{ $status[0] }} shadow-sm" role="progressbar" style="width: {{ min(100, $pct) }}%; border-radius: 10px;"></div>
                </div>
                
                <div class="d-flex justify-content-between mt-3 x-small fw-bold text-muted">
                    <span><i class="fas fa-long-arrow-alt-up me-1 text-primary"></i>{{ number_format($spentTotal, 2) }}</span>
                    <span>{{ number_format($budgetTotal, 2) }} {{ __('Limit') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Filter Bar -->
    <div class="glass-filter px-4 py-3 mb-5">
        <form action="{{ route('expenses.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 pe-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-transparent px-3" placeholder="{{ __('What are you looking for?') }}" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select border-0 bg-transparent fw-bold text-primary">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $cat => $subs)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst(__($cat)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="period" class="form-select border-0 bg-transparent fw-bold">
                    <option value="" {{ !request('period') ? 'selected' : '' }}>{{ __('All Time') }}</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                    <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>{{ __('This Week') }}</option>
                    <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>{{ __('This Month') }}</option>
                    <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>{{ __('This Year') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select border-0 bg-transparent fw-bold">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>{{ __('All Statuses') }}</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                </select>
            </div>
            <div class="col-md-3 text-end d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm flex-grow-1">{{ __('Filter') }}</button>
                @if(request()->hasAny(['search', 'category', 'period', 'status']))
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-danger px-3 rounded-pill shadow-sm" title="{{ __('Clear all filters') }}">
                        <i class="fas fa-times me-1"></i>{{ __('Clear') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main List Card -->
    <div class="card premium-card border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table premium-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">{{ __('Reference') }}</th>
                        <th>{{ __('Allocation') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Value') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="pe-4 text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $e)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-dark">{{ $e->expense_date?->format('d M Y') }}</span><br>
                            <span class="text-muted x-small text-uppercase ls-1">REF#{{ $e->id }}</span>
                        </td>
                        <td>
                            <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold x-small">{{ ucfirst(__($e->category)) }}</span>
                            @if($e->sub_category)
                                <div class="text-muted small mt-1 ms-1"> <i class="fas fa-level-up-alt fa-rotate-90 me-1"></i> {{ ucfirst(__($e->sub_category)) }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold d-flex align-items-center">
                                @if($e->attachment_path) <i class="fas fa-paperclip text-muted me-2 x-small"></i> @endif
                                {{ $e->local_description }}
                            </div>
                            <span class="x-small text-muted text-uppercase">{{ str_replace('_', ' ', $e->payment_method) }}</span>
                        </td>
                        <td>
                            <div class="fw-900 text-dark" style="font-size:16px;">{{ number_format($e->amount, 2) }}</div>
                        </td>
                        <td>
                            <span class="status-pill badge bg-{{ $e->status_badge }}">{{ strtoupper($e->status) }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="action-btns justify-content-end">
                                @if($e->status === 'draft')
                                <form action="{{ route('expenses.approve', $e) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-soft-success rounded-circle" style="width:32px;height:32px;" title="{{ __('Confirm & Approve') }}">
                                        <i class="fas fa-check-double x-small"></i>
                                    </button>
                                </form>
                                @endif
                                
                                @if($e->attachment_path)
                                <a href="{{ Storage::url($e->attachment_path) }}" target="_blank" class="btn btn-sm btn-soft-primary rounded-circle" style="width:32px;height:32px;" title="{{ __('Audit Receipt') }}">
                                    <i class="fas fa-file-invoice x-small"></i>
                                </a>
                                @endif

                                <button class="btn btn-sm btn-soft-info rounded-circle" style="width:32px;height:32px;" title="{{ __('Modify Details') }}" onclick="editExpense({{ $e->id }})">
                                    <i class="fas fa-pen-nib x-small"></i>
                                </button>

                                <form action="{{ route('expenses.destroy', $e) }}" method="POST" onsubmit="return confirm('Archive this entry?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-soft-danger rounded-circle" style="width:32px;height:32px;" title="{{ __('Remove Entry') }}">
                                        <i class="fas fa-trash-alt x-small"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="opacity-25 mb-3"><i class="fas fa-receipt fa-4x"></i></div>
                            <h5 class="fw-bold text-muted">{{ __('Clean Slate!') }}</h5>
                            <p class="text-muted small">{{ __('No expense records found for this criteria.') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="card-footer bg-transparent border-0 py-4 d-flex justify-content-center">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: linear-gradient(135deg, rgba(99,102,241,0.08) 0%, rgba(168,85,247,0.08) 100%);">
                    <div>
                        <h5 class="modal-title fw-900 mb-1" style="letter-spacing: -0.3px;">
                            <i class="fas fa-plus-circle text-primary me-2"></i>{{ __('Log New Expense') }}
                        </h5>
                        <p class="text-muted x-small mb-0">{{ __('Record a new financial outflow with full audit trail') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Category') }}</label>
                            <select name="category" class="form-select border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required id="modal_cat">
                                @foreach($categories as $cat => $subs)
                                    <option value="{{ $cat }}">{{ ucfirst(__($cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Date') }}</label>
                            <input type="date" name="expense_date" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Description (AR)') }}</label>
                            <input type="text" name="description" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required placeholder="{{ __('Enter Arabic description...') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Description (EN)') }}</label>
                            <input type="text" name="description_en" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" placeholder="{{ __('Optional English description...') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Amount') }}</label>
                            <div class="input-group shadow-sm" style="border-radius:10px;overflow:hidden;">
                                <span class="input-group-text border-0 fw-bold text-primary" style="background:#f8f9fb;">$</span>
                                <input type="number" name="amount" class="form-control border-0 fw-bold" style="background:#f8f9fb;padding:12px;" step="0.01" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Payment Method') }}</label>
                            <select name="payment_method" class="form-select border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required>
                                <option value="cash">💵 {{ __('Cash') }}</option>
                                <option value="bank_transfer">🏦 {{ __('Bank Transfer') }}</option>
                                <option value="card">💳 {{ __('Card') }}</option>
                                <option value="cheque">📝 {{ __('Cheque') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Receipt / Attachment') }} <span class="text-muted fw-normal">({{ __('Optional') }})</span></label>
                            <input type="file" name="attachment" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:10px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm floating-btn">
                        <i class="fas fa-save me-2"></i>{{ __('Save Expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form id="editExpenseForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: linear-gradient(135deg, rgba(59,130,246,0.08) 0%, rgba(99,102,241,0.08) 100%);">
                    <div>
                        <h5 class="modal-title fw-900 mb-1" style="letter-spacing: -0.3px;">
                            <i class="fas fa-pen-nib text-info me-2"></i>{{ __('Modify Expense') }}
                        </h5>
                        <p class="text-muted x-small mb-0">{{ __('Update the details of this expense record') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Category') }}</label>
                            <select name="category" id="edit_category" class="form-select border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required>
                                @foreach($categories as $cat => $subs)
                                    <option value="{{ $cat }}">{{ ucfirst(__($cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Date') }}</label>
                            <input type="date" name="expense_date" id="edit_date" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Description (AR)') }}</label>
                            <input type="text" name="description" id="edit_desc" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Description (EN)') }}</label>
                            <input type="text" name="description_en" id="edit_desc_en" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Amount') }}</label>
                            <div class="input-group shadow-sm" style="border-radius:10px;overflow:hidden;">
                                <span class="input-group-text border-0 fw-bold text-primary" style="background:#f8f9fb;">$</span>
                                <input type="number" name="amount" id="edit_amount" class="form-control border-0 fw-bold" style="background:#f8f9fb;padding:12px;" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Payment Method') }}</label>
                            <select name="payment_method" id="edit_payment" class="form-select border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:12px;" required>
                                <option value="cash">💵 {{ __('Cash') }}</option>
                                <option value="bank_transfer">🏦 {{ __('Bank Transfer') }}</option>
                                <option value="card">💳 {{ __('Card') }}</option>
                                <option value="cheque">📝 {{ __('Cheque') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label x-small fw-bold text-uppercase text-muted ls-1">{{ __('Replace Attachment') }} <span class="text-muted fw-normal">({{ __('Optional') }})</span></label>
                            <input type="file" name="attachment" class="form-control border-0 shadow-sm" style="background:#f8f9fb;border-radius:10px;padding:10px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-bold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-info text-white px-5 py-2 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-save me-2"></i>{{ __('Update Expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Store all expenses data for the edit modal
    const expensesData = @json($expenses->items());

    function editExpense(id) {
        const expense = expensesData.find(e => e.id === id);
        if (!expense) return;

        // Set form action
        document.getElementById('editExpenseForm').action = `/expenses/${id}`;

        // Populate fields
        document.getElementById('edit_category').value = expense.category || '';
        document.getElementById('edit_date').value = expense.expense_date ? expense.expense_date.substring(0, 10) : '';
        document.getElementById('edit_desc').value = expense.description || '';
        document.getElementById('edit_desc_en').value = expense.description_en || '';
        document.getElementById('edit_amount').value = expense.amount || '';
        document.getElementById('edit_payment').value = expense.payment_method || 'cash';

        // Show the modal
        new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
    }
</script>
@endpush
