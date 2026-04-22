@extends('layouts.app')

@php
    $currency = DB::table('settings')->where('key', 'currency')->value('value') ?: 'EGP';
@endphp

@push('styles')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary': '#3a24d8',
                    'on-primary': '#ffffff',
                    'primary-container': '#5446f0',
                    'on-primary-container': '#e1ddff',
                    'secondary': '#505f76',
                    'surface': '#f9f9ff',
                    'on-surface': '#111c2d',
                    'on-surface-variant': '#464556',
                    'outline': '#777587',
                    'outline-variant': '#c7c4d8',
                    'surface-container-lowest': '#ffffff',
                    'surface-container-low': '#f0f3ff',
                    'surface-container': '#e7eeff',
                    'surface-container-high': '#dee8ff',
                    'surface-container-highest': '#d8e3fb',
                    'error': '#ba1a1a',
                },
                fontFamily: {
                    'manrope': ['Manrope', 'sans-serif'],
                    'inter': ['Inter', 'sans-serif'],
                },
                borderRadius: {
                    'xl': '0.75rem',
                    '2xl': '1rem',
                }
            }
        }
    }
</script>
<style>
    .font-manrope { font-family: 'Manrope', sans-serif; }
    .font-inter { font-family: 'Inter', sans-serif; }
</style>
@endpush

@section('content')
<div class="tw-wrapper font-inter text-on-surface bg-surface min-h-screen pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 px-4">
        <div class="{{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
            <h1 class="font-manrope font-extrabold text-3xl text-primary tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-4xl">account_balance</span>
                {{ __('Financial Command Center') }}
            </h1>
            <p class="text-on-surface-variant mt-1">{{ __('Manage liquidity, taxes, and cash position') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="bg-white border border-outline-variant px-4 py-2 rounded-xl text-sm font-bold text-on-surface hover:bg-slate-50 transition-all flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#drawerModal">
                <span class="material-symbols-outlined text-lg">point_of_sale</span>
                {{ __('Drawer Event') }}
            </button>
            <button class="bg-primary text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <span class="material-symbols-outlined text-lg">add</span>
                {{ __('New Expense') }}
            </button>
        </div>
    </div>

    <!-- Financial Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-4 mb-8">
        <!-- Cash Position Hero (Spans 2) -->
        <div class="md:col-span-2 bg-primary-container p-8 rounded-2xl shadow-xl shadow-primary/10 text-on-primary-container relative overflow-hidden flex flex-col justify-between min-h-[200px]">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <span class="font-manrope font-bold text-xs uppercase tracking-widest opacity-80">{{ __('CASH ON HAND (DRAWER)') }}</span>
                    @if($isDrawerOpen)
                        <span class="bg-emerald-400/20 text-emerald-400 px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1 border border-emerald-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ __('OPEN') }}
                        </span>
                    @else
                        <span class="bg-error/20 text-error px-3 py-1 rounded-full text-[10px] font-bold border border-error/30 uppercase">
                            {{ __('CLOSED') }}
                        </span>
                    @endif
                </div>
                <div class="font-manrope font-extrabold text-5xl text-white mb-2 tracking-tighter">
                    {{ number_format($cashPosition, 2) }} <span class="text-2xl font-medium opacity-80">{{ $currency }}</span>
                </div>
            </div>
            <div class="relative z-10 flex items-center gap-2">
                <span class="text-xs opacity-70">{{ __('Real-time liquidity across all registers') }}</span>
            </div>
        </div>

        <!-- Lifetime Net Profit -->
        <div class="bg-white p-6 rounded-2xl border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <span class="font-manrope font-bold text-[10px] text-secondary uppercase tracking-wider block mb-2">{{ __('NET PROFIT (LIFETIME)') }}</span>
                <div class="font-manrope font-extrabold text-3xl text-on-surface">
                    {{ number_format($netProfit, 2) }}
                </div>
            </div>
            <div class="mt-4">
                <span class="text-[11px] text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-lg">
                    {{ __('After all costs') }}
                </span>
            </div>
        </div>

        <!-- Receivables (Debt) -->
        <div class="bg-white p-6 rounded-2xl border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <span class="font-manrope font-bold text-[10px] text-secondary uppercase tracking-wider block mb-2">{{ __('RECEIVABLES (DEBT)') }}</span>
                <div class="font-manrope font-extrabold text-3xl text-primary">
                    {{ number_format($totalDebt, 2) }}
                </div>
            </div>
            <div class="mt-4">
                <span class="text-[11px] text-error font-bold bg-error/5 px-2 py-1 rounded-lg flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">warning</span>
                    {{ $overdueCount }} {{ __('overdue') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Finance Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-4">
        <!-- Recent Drawer Events -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-0 rounded-2xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="p-6 border-b border-outline-variant flex items-center justify-between">
                    <h3 class="font-manrope font-bold text-lg text-on-surface">{{ __('Recent Drawer Events') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-low">
                                <th class="px-6 py-4 font-manrope font-bold text-[11px] text-secondary uppercase tracking-wider">{{ __('USER') }}</th>
                                <th class="px-6 py-4 font-manrope font-bold text-[11px] text-secondary uppercase tracking-wider">{{ __('TYPE') }}</th>
                                <th class="px-6 py-4 font-manrope font-bold text-[11px] text-secondary uppercase tracking-wider">{{ __('AMOUNT') }}</th>
                                <th class="px-6 py-4 font-manrope font-bold text-[11px] text-secondary uppercase tracking-wider">{{ __('TIME') }}</th>
                                <th class="px-6 py-4 font-manrope font-bold text-[11px] text-secondary uppercase tracking-wider">{{ __('NOTE') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($drawerEvents as $de)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs uppercase">
                                            {{ substr($de->user->name ?? 'N', 0, 1) }}
                                        </div>
                                        <span class="font-inter font-semibold text-sm text-on-surface">{{ $de->user->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeColors = match($de->type){
                                            'open'=>'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'close'=>'bg-slate-50 text-slate-700 border-slate-100',
                                            'in'=>'bg-primary/5 text-primary border-primary/10',
                                            'out'=>'bg-error/5 text-error border-error/10',
                                            default => 'bg-slate-50 text-slate-700'
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $typeColors }}">
                                        {{ ucfirst($de->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-manrope font-extrabold text-sm text-on-surface">
                                    {{ number_format($de->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-secondary font-medium">
                                    {{ $de->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-on-surface-variant max-w-[200px] truncate" title="{{ $de->description }}">
                                        {{ $de->description ?: '-' }}
                                    </p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Financial Hub -->
        <div class="space-y-6">
            <!-- Action Cards -->
            <div class="space-y-3">
                <a href="{{ route('finance.cashflow') }}" class="group block bg-white p-5 rounded-2xl border border-outline-variant shadow-sm hover:border-primary/40 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center group-hover:bg-primary transition-colors">
                            <span class="material-symbols-outlined text-primary group-hover:text-white">payments</span>
                        </div>
                        <div>
                            <h5 class="font-manrope font-bold text-on-surface text-sm">{{ __('Cash Flow Statement') }}</h5>
                            <p class="text-secondary text-[11px]">{{ __('Track inflow vs outflow') }}</p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('finance.tax') }}" class="group block bg-white p-5 rounded-2xl border border-outline-variant shadow-sm hover:border-emerald-400/40 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center group-hover:bg-emerald-500 transition-colors">
                            <span class="material-symbols-outlined text-emerald-600 group-hover:text-white">receipt_long</span>
                        </div>
                        <div>
                            <h5 class="font-manrope font-bold text-on-surface text-sm">{{ __('Tax Report') }}</h5>
                            <p class="text-secondary text-[11px]">{{ __('Collected vs Remitted tracking') }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Profitability Ratios -->
            <div class="bg-white p-6 rounded-2xl border border-outline-variant shadow-sm">
                <h6 class="font-manrope font-bold text-sm text-on-surface mb-6 uppercase tracking-widest opacity-60">{{ __('Profitability Specs') }}</h6>
                
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="font-inter font-bold text-xs text-secondary">{{ __('Gross Margin') }}</span>
                            <span class="font-manrope font-extrabold text-xs text-emerald-600">{{ $ratios->gross_margin }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full transition-all duration-1000" style="width: {{ $ratios->gross_margin }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="font-inter font-bold text-xs text-secondary">{{ __('Net Profit Margin') }}</span>
                            <span class="font-manrope font-extrabold text-xs text-primary">{{ $ratios->net_margin }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
                            <div class="bg-primary h-full transition-all duration-1000" style="width: {{ $ratios->net_margin }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Tax Card -->
            <div class="bg-surface-container p-6 rounded-2xl border border-outline-variant shadow-none">
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                    <span class="font-manrope font-bold text-[10px] text-secondary uppercase tracking-widest">{{ __('TAX POSITION (MONTH)') }}</span>
                </div>
                <div class="font-manrope font-extrabold text-2xl text-on-surface mb-1">
                    {{ number_format($taxCollected, 2) }} <span class="text-xs font-medium">{{ $currency }}</span>
                </div>
                <p class="text-[10px] text-on-surface-variant font-medium">{{ __('Collected sales tax for the current month') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Drawer Event -->
<div class="modal fade" id="drawerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden bg-white">
            <form action="{{ route('finance.drawer.store') }}" method="POST">
                @csrf
                <div class="p-8">
                    <div class="flex justify-between items-center mb-8">
                        <h5 class="font-manrope font-extrabold text-xl text-on-surface">{{ __('New Drawer Event') }}</h5>
                        <button type="button" class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center text-secondary hover:bg-surface-variant transition-colors" data-bs-dismiss="modal">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="font-manrope font-bold text-[11px] text-secondary uppercase tracking-widest block mb-3">{{ __('Event Type') }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <input type="radio" class="hidden peer/open" name="type" id="dr_open" value="open" required>
                                <label for="dr_open" class="peer-checked/open:bg-emerald-500 peer-checked/open:text-white peer-checked/open:border-emerald-500 border border-outline-variant rounded-xl py-2 text-center text-xs font-bold text-on-surface-variant cursor-pointer hover:bg-slate-50 transition-all">{{ __('Open') }}</label>

                                <input type="radio" class="hidden peer/in" name="type" id="dr_in" value="in">
                                <label for="dr_in" class="peer-checked/in:bg-primary peer-checked/in:text-white peer-checked/in:border-primary border border-outline-variant rounded-xl py-2 text-center text-xs font-bold text-on-surface-variant cursor-pointer hover:bg-slate-50 transition-all">{{ __('In') }}</label>

                                <input type="radio" class="hidden peer/out" name="type" id="dr_out" value="out">
                                <label for="dr_out" class="peer-checked/out:bg-error peer-checked/out:text-white peer-checked/out:border-error border border-outline-variant rounded-xl py-2 text-center text-xs font-bold text-on-surface-variant cursor-pointer hover:bg-slate-50 transition-all">{{ __('Out') }}</label>

                                <input type="radio" class="hidden peer/close" name="type" id="dr_close" value="close">
                                <label for="dr_close" class="peer-checked/close:bg-slate-800 peer-checked/close:text-white peer-checked/close:border-slate-800 border border-outline-variant rounded-xl py-2 text-center text-xs font-bold text-on-surface-variant cursor-pointer hover:bg-slate-50 transition-all">{{ __('Close') }}</label>
                            </div>
                        </div>

                        <div>
                            <label class="font-manrope font-bold text-[11px] text-secondary uppercase tracking-widest block mb-2">{{ __('Amount') }}</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface font-bold">{{ $currency }}</span>
                                <input type="number" step="0.01" name="amount" class="w-full pl-12 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-xl font-manrope font-extrabold text-on-surface focus:border-primary outline-none transition-all" placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label class="font-manrope font-bold text-[11px] text-secondary uppercase tracking-widest block mb-2">{{ __('Description / Note') }}</label>
                            <textarea name="description" class="w-full p-4 bg-surface-container-low border border-outline-variant rounded-xl font-inter text-sm text-on-surface focus:border-primary outline-none transition-all" rows="3" placeholder="{{ __('Reason for event...') }}"></textarea>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-3">
                        <button type="button" class="flex-1 py-3 rounded-xl text-sm font-bold text-secondary hover:bg-surface-container transition-colors" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white text-sm font-bold shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all">{{ __('Log Event') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection