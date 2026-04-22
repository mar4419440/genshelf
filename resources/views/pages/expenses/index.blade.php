@extends('layouts.app')

@push('styles')
<!-- Tailwind CDN (Latest Stable) -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        corePlugins: {
            preflight: false, // Prevent destruction of global Bootstrap layout
        },
        theme: {
            extend: {
            "colors": {
                    "tertiary-fixed": "#89f5e7", "outline-variant": "#c6c6cd", "on-tertiary-fixed-variant": "#005049",
                    "on-primary": "#ffffff", "surface-container-lowest": "#ffffff", "surface": "#f7f9fb",
                    "inverse-surface": "#2d3133", "surface-container-highest": "#e0e3e5", "surface-dim": "#d8dadc",
                    "primary-fixed-dim": "#bec6e0", "secondary-fixed-dim": "#b9c7e0", "tertiary-fixed-dim": "#6bd8cb",
                    "on-background": "#191c1e", "outline": "#76777d", "on-secondary-fixed-variant": "#3a485c",
                    "primary-fixed": "#dae2fd", "on-error": "#ffffff", "tertiary": "#000000",
                    "surface-bright": "#f7f9fb", "surface-tint": "#565e74", "on-surface-variant": "#45464d",
                    "on-secondary-fixed": "#0d1c2f", "on-primary-container": "#7c839b", "on-secondary": "#ffffff",
                    "secondary-container": "#d5e3fd", "inverse-primary": "#bec6e0", "primary-container": "#131b2e",
                    "on-tertiary-fixed": "#00201d", "error-container": "#ffdad6", "secondary": "#515f74",
                    "on-primary-fixed-variant": "#3f465c", "surface-variant": "#e0e3e5", "surface-container-high": "#e6e8ea",
                    "surface-container": "#eceef0", "tertiary-container": "#00201d", "background": "#f7f9fb",
                    "on-tertiary": "#ffffff", "on-primary-fixed": "#131b2e", "on-secondary-container": "#57657b",
                    "surface-container-low": "#f2f4f6", "inverse-on-surface": "#eff1f3", "on-error-container": "#93000a",
                    "primary": "#000000", "on-surface": "#191c1e", "on-tertiary-container": "#0c9488",
                    "secondary-fixed": "#d5e3fd", "error": "#ba1a1a"
            },
            "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
            "spacing": { "comfortable-padding": "16px", "container-margin": "24px", "section-gap": "32px", "gutter": "16px", "compact-padding": "8px", "unit": "4px" },
            "fontFamily": {
                    "body-lg": ["Public Sans"], "data-mono": ["Public Sans"], "h3": ["Public Sans"],
                    "body-md": ["Public Sans"], "h2": ["Public Sans"], "h1": ["Public Sans"],
                    "label-md": ["Public Sans"], "body-sm": ["Public Sans"]
            },
            "fontSize": {
                    "body-lg": ["16px", {"lineHeight": "24px", "letterSpacing": "0", "fontWeight": "400"}],
                    "data-mono": ["14px", {"lineHeight": "20px", "letterSpacing": "-0.01em", "fontWeight": "500"}],
                    "h3": ["20px", {"lineHeight": "28px", "letterSpacing": "0", "fontWeight": "600"}],
                    "body-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0", "fontWeight": "400"}],
                    "h2": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                    "h1": ["30px", {"lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.04em", "fontWeight": "600"}],
                    "body-sm": ["13px", {"lineHeight": "18px", "letterSpacing": "0", "fontWeight": "400"}]
            }
            }
        }
    }
</script>
<style>
    .tw-wrapper *, .tw-wrapper ::before, .tw-wrapper ::after {
        box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e5e7eb;
    }
    .tw-wrapper input:focus, .tw-wrapper button:focus { outline: none; }
    .tw-wrapper table { text-indent: 0; border-color: inherit; border-collapse: collapse; }
    .tw-wrapper button { background-color: transparent; background-image: none; cursor: pointer; }
    .tw-wrapper input { border-style: solid; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    
    /* Global Overrides for this view */
    body { background-color: #f7f9fb !important; }
</style>
@endpush

@section('content')
<div class="tw-wrapper font-body-md text-on-surface">
    <!-- Main Content Canvas -->
    <main class="flex-1 p-6 pb-20">
        <div class="max-w-7xl mx-auto space-y-section-gap">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="font-h1 text-h1 text-primary">{{ __('Expenses Overview') }}</h2>
                    <p class="text-body-md text-on-surface-variant">{{ __('Track and manage your retail operational costs.') }}</p>
                </div>
                <!-- Controls -->
                <div class="flex items-center gap-comfortable-padding">
                    <form action="{{ route('expenses.index') }}" method="GET" class="flex gap-2" id="filterForm">
                        <input type="hidden" name="period" value="{{ request('period') }}" id="periodFilter">
                        <select name="status" class="bg-white border border-outline px-3 py-2 rounded-lg text-secondary font-label-md outline-none" onchange="document.getElementById('filterForm').submit()">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        </select>
                        <a href="{{ route('expenses.index') }}" class="flex items-center gap-2 px-comfortable-padding py-2 border border-outline rounded-lg bg-white text-secondary font-label-md hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-[20px]">refresh</span> {{ __('Reset') }}
                        </a>
                    </form>
                </div>
            </div>

            <!-- Summary Bento Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter mb-8">
                <!-- Total Monthly Summary Card -->
                <div class="md:col-span-4 bg-white border border-outline-variant p-comfortable-padding rounded-xl shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Total Monthly Expense') }}</span>
                    </div>
                    <div class="mb-6">
                        <p class="text-h1 font-h1 text-primary">${{ number_format($totalInPeriod, 2) }}</p>
                        <p class="text-body-sm text-on-surface-variant">{{ $expenses->total() }} {{ __('Transactions') }}</p>
                    </div>
                    
                    @php 
                        $budgetTotal = $budgets->sum('budgeted_amount');
                        $spentTotal = $categoryBreakdown->sum('total');
                        $pct = $budgetTotal > 0 ? ($spentTotal / $budgetTotal) * 100 : 0;
                    @endphp
                    <div class="w-full bg-surface-container rounded-full h-1.5 overflow-hidden">
                        <div class="bg-primary h-full" style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                    <p class="mt-2 text-label-md text-on-surface-variant">{{ round($pct, 1) }}% {{ __('of monthly budget utilized') }}</p>
                </div>

                <!-- Category Breakdown Card -->
                <div class="md:col-span-8 bg-white border border-outline-variant p-comfortable-padding rounded-xl shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Category Distribution') }}</span>
                        <span class="material-symbols-outlined text-outline-variant">pie_chart</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $colors = ['bg-primary', 'bg-secondary', 'bg-on-tertiary-container', 'bg-outline-variant'];
                        @endphp
                        @foreach($categoryBreakdown->take(4) as $idx => $cat)
                        <div class="space-y-2">
                            <p class="text-label-md text-on-surface-variant">{{ ucfirst(__($cat->category)) }}</p>
                            <p class="text-h3 font-h3">${{ number_format($cat->total, 0) }}</p>
                            <div class="h-1 w-12 {{ $colors[$idx % 4] }}"></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-8 flex gap-2 h-2 overflow-hidden rounded-full">
                        @foreach($categoryBreakdown->take(4) as $idx => $cat)
                            <div class="{{ $colors[$idx % 4] }}" style="width: {{ $spentTotal > 0 ? ($cat->total / $spentTotal) * 100 : 0 }}%"></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Transaction Control Row -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <form action="{{ route('expenses.index') }}" method="GET" class="relative w-full md:w-96">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                    <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 bg-white border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-body-md outline-none" placeholder="{{ __('Search by description...') }}" type="text"/>
                </form>
                
                <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0">
                    <span class="text-label-md text-on-surface-variant mr-2">{{ __('Filter') }}:</span>
                    <a href="{{ route('expenses.index') }}" class="{{ !request('category') ? 'bg-primary text-on-primary' : 'bg-white border border-outline-variant text-on-surface-variant hover:bg-surface-container' }} px-4 py-1.5 rounded-full text-label-md transition-colors whitespace-nowrap">
                        {{ __('All') }}
                    </a>
                    @foreach($categories as $cat => $subs)
                    <a href="{{ route('expenses.index', ['category' => $cat]) }}" class="{{ request('category') == $cat ? 'bg-primary text-on-primary' : 'bg-white border border-outline-variant text-on-surface-variant hover:bg-surface-container' }} px-4 py-1.5 rounded-full text-label-md transition-colors whitespace-nowrap">
                        {{ ucfirst(__($cat)) }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="bg-white border border-outline-variant rounded-xl shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Description') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Amount') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-center">{{ __('Category') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Method') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse($expenses as $e)
                            <tr class="hover:bg-surface transition-colors {{ $loop->even ? 'bg-surface-container-low' : '' }}">
                                <td class="px-6 py-4 font-data-mono text-on-surface">
                                    {{ $e->expense_date?->format('M d, Y') }}
                                    <div class="text-[10px] text-outline mt-1">#{{ $e->id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-primary">{{ $e->local_description }}</p>
                                    @if($e->status === 'draft')
                                        <p class="text-body-sm text-on-error-container font-bold">{{ __('DRAFT') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-h3 text-right text-primary">${{ number_format($e->amount, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $catColor = match($e->category) {
                                            'rent' => 'bg-secondary-container text-on-secondary-container',
                                            'utilities' => 'bg-tertiary-fixed text-on-tertiary-fixed-variant',
                                            'inventory' => 'bg-primary-fixed text-on-primary-fixed-variant',
                                            default => 'bg-surface-container-highest text-on-surface-variant'
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-[12px] font-bold uppercase {{ $catColor }}">
                                        {{ __($e->category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-body-md uppercase text-[11px] font-bold">
                                        @php
                                            $methodIcon = match($e->payment_method) {
                                                'cash' => 'payments',
                                                'card' => 'credit_card',
                                                default => 'account_balance'
                                            };
                                        @endphp
                                        <span class="material-symbols-outlined text-[18px]">{{ $methodIcon }}</span>
                                        {{ str_replace('_', ' ', $e->payment_method) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        @if($e->status === 'draft')
                                        <form action="{{ route('expenses.approve', $e) }}" method="POST">
                                            @csrf
                                            <button class="material-symbols-outlined text-outline hover:text-on-tertiary-container" title="{{ __('Approve') }}">check_circle</button>
                                        </form>
                                        @endif
                                        <button class="material-symbols-outlined text-outline hover:text-primary" onclick="editExpense({{ $e->id }})" title="{{ __('Edit') }}">edit</button>
                                        <form action="{{ route('expenses.destroy', $e) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf @method('DELETE')
                                            <button class="material-symbols-outlined text-outline hover:text-error" title="{{ __('Delete') }}">delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] opacity-50 mb-2">inbox</span>
                                    <p>{{ __('No records found') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($expenses->hasPages())
                <div class="px-6 py-4 border-t border-outline-variant flex items-center justify-between">
                    {{ $expenses->links() }}
                </div>
                @endif
            </div>

            <!-- Floating Action Button -->
            <button onclick="openAddExpenseModal()" class="fixed bottom-12 right-12 h-14 w-14 rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-50 group border-0" style="background:#000;color:#fff;">
                <span class="material-symbols-outlined text-[32px]" style="color:#fff;">add</span>
                <span class="absolute right-full mr-4 px-3 py-1.5 rounded-lg text-label-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none" style="background:#000;color:#fff;">{{ __('Add Expense') }}</span>
            </button>
            
        </div>
    </main>
</div>

<!-- Modal Overlay -->
<div class="modal-overlay" id="expense-modal-overlay" onclick="if(event.target===this)closeExpenseModal()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center;">
    <div id="expense-modal-box" style="background:var(--bg2,#fff);padding:24px;border-radius:16px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);"></div>
</div>
@endsection

@push('scripts')
<script>
    const expensesData = @json($expenses->items());
    const categoryOptions = `@foreach($categories as $cat => $subs)<option value="{{ $cat }}">{{ ucfirst(__($cat)) }}</option>@endforeach`;

    function openAddExpenseModal() {
        const html = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h3 style="font-size:18px;font-weight:700;color:var(--tx,#191c1e);margin:0;">{{ __('Log New Expense') }}</h3>
                <button type="button" onclick="closeExpenseModal()" style="background:none;border:none;font-size:22px;color:var(--tx2,#555);cursor:pointer;padding:4px;">✕</button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Category') }}</label>
                        <select name="category" required>${categoryOptions}</select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Date') }}</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Description (AR)') }}</label>
                        <input type="text" name="description" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Amount') }}</label>
                        <input type="number" name="amount" step="0.01" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Payment Method') }}</label>
                        <select name="payment_method" required>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:20px;">
                    <button type="button" class="btn btn-o" onclick="closeExpenseModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Save') }}</button>
                </div>
            </form>
        `;
        renderExpenseModal(html);
    }

    function editExpense(id) {
        const e = expensesData.find(x => x.id === id);
        if (!e) return;

        const html = `
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h3 style="font-size:18px;font-weight:700;color:var(--tx,#191c1e);margin:0;">{{ __('Modify Expense') }}</h3>
                <button type="button" onclick="closeExpenseModal()" style="background:none;border:none;font-size:22px;color:var(--tx2,#555);cursor:pointer;padding:4px;">✕</button>
            </div>
            <form action="/expenses/${e.id}" method="POST">
                @csrf
                @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Category') }}</label>
                        <select name="category">${categoryOptions.replace('value="'+e.category+'"', 'value="'+e.category+'" selected')}</select>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Date') }}</label>
                        <input type="date" name="expense_date" value="${e.expense_date ? e.expense_date.substring(0,10) : ''}" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Description (AR)') }}</label>
                        <input type="text" name="description" value="${e.description || ''}" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Amount') }}</label>
                        <input type="number" name="amount" step="0.01" value="${e.amount || ''}" required>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Payment Method') }}</label>
                        <select name="payment_method">
                            <option value="cash" ${e.payment_method==='cash'?'selected':''}>{{ __('Cash') }}</option>
                            <option value="bank_transfer" ${e.payment_method==='bank_transfer'?'selected':''}>{{ __('Bank Transfer') }}</option>
                            <option value="card" ${e.payment_method==='card'?'selected':''}>{{ __('Card') }}</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:20px;">
                    <button type="button" class="btn btn-o" onclick="closeExpenseModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">{{ __('Update') }}</button>
                </div>
            </form>
        `;
        renderExpenseModal(html);
    }

    function renderExpenseModal(html) {
        document.getElementById('expense-modal-box').innerHTML = html;
        const overlay = document.getElementById('expense-modal-overlay');
        overlay.style.display = 'flex';
    }

    function closeExpenseModal() {
        document.getElementById('expense-modal-overlay').style.display = 'none';
    }
</script>
@endpush
