@extends('layouts.analytics')

@section('analytics_title', __('Operations & Quality Control'))
@section('analytics_subtitle', __('Returns, staff performance, and system transparency'))

@section('analytics_content')
<div class="space-y-8">
    <!-- Returns & Quality KPI Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">assignment_return</span>
            </div>
            <div>
                <h3 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">{{ __('Return Rate') }}</h3>
                <div class="text-3xl font-extrabold text-slate-900">{{ round($returns['rate'], 2) }}%</div>
                <div class="text-[10px] font-bold text-rose-500">{{ $returns['kpis']->count }} {{ __('Total Returns') }}</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">verified_user</span>
            </div>
            <div>
                <h3 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">{{ __('Warranty Active') }}</h3>
                <div class="text-3xl font-extrabold text-slate-900">{{ $warranty->claims }}</div>
                <div class="text-[10px] font-bold text-indigo-500">{{ $warranty->expiring }} {{ __('Expiring Soon') }}</div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-6">
            <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">report_problem</span>
            </div>
            <div>
                <h3 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">{{ __('Defective Items') }}</h3>
                <div class="text-3xl font-extrabold text-slate-900">{{ $defective->count() }}</div>
                <div class="text-[10px] font-bold text-amber-500">{{ __('Pending Action') }}</div>
            </div>
        </div>
    </div>

    <!-- Staff Performance & Audit -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Staff Leaderboard -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Staff Performance Leaderboard') }}</h3>
                <span class="material-symbols-outlined text-primary">military_tech</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-4 text-left">{{ __('Employee') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('Invoices') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Revenue') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('AOV') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staff as $s)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $s->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-600">{{ number_format($s->invoices) }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ number_format($s->revenue, 2) }}</td>
                            <td class="px-6 py-4 text-right font-bold text-indigo-600">{{ number_format($s->aov, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Log Summary -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('System Audit Log') }}</h3>
                <span class="material-symbols-outlined text-slate-400">policy</span>
            </div>
            <div class="overflow-y-auto max-h-[400px] no-scrollbar">
                @foreach($audit as $a)
                <div class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors flex items-start gap-4">
                    <div class="w-2 h-2 rounded-full mt-1.5 {{ str_contains($a->action, 'delete') ? 'bg-rose-500' : 'bg-emerald-500' }}"></div>
                    <div class="flex-1">
                        <div class="text-xs font-bold text-slate-900">{{ $a->action }}</div>
                        <div class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $a->name }} • {{ Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-4 bg-slate-50 text-center">
                <button class="text-xs font-bold text-primary uppercase tracking-widest">{{ __('View Full Log') }}</button>
            </div>
        </div>
    </div>

    <!-- Top Returned Products -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-manrope font-bold text-lg text-slate-900">{{ __('Top Returned Products') }}</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($returns['topReturned'] as $tr)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 relative group overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-slate-200/50 group-hover:scale-125 transition-transform">
                        <span class="material-symbols-outlined text-6xl">inventory_2</span>
                    </div>
                    <div class="relative z-10">
                        <div class="text-xs font-bold text-slate-700 truncate mb-1">{{ $tr->name }}</div>
                        <div class="text-2xl font-extrabold text-rose-600">{{ $tr->count }}</div>
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Returns') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
