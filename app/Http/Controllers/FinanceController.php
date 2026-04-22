<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\CashDrawerEvent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceController extends Controller
{
    // ========== INDEX — الصفحة الرئيسية ==========

    public function index()
    {
        // إجماليات عامة
        $totalRev  = DB::table('transactions')->sum('total');
        $totalExp  = Expense::approved()->sum('amount');
        $cogs      = DB::table('purchase_orders')->where('status', 'received')->sum('total_cost');
        $netProfit = $totalRev - $cogs - $totalExp;

        // هذا الشهر
        $monthRevenue = DB::table('transactions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $monthExpenses = Expense::approved()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $monthNet = $monthRevenue - $monthExpenses;

        // Cash Position من الـ Drawer
        $cashPosition = $this->calculateCashPosition();

        // Accounts Receivable (مديونيات العملاء)
        $totalDebt = DB::table('transactions')->where('due_amount', '>', 0)->sum('due_amount');
        $overdueCount = Transaction::where('due_amount', '>', 0)
            ->where('due_date', '<', now())
            ->count();

        // Tax Collected this month
        $taxCollected = DB::table('transactions')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('tax');

        $expenses     = Expense::with('user')->latest('expense_date')->take(20)->get();
        $drawerEvents = CashDrawerEvent::with('user')->latest()->take(15)->get();
        $isDrawerOpen = $this->isDrawerOpen();

        // Ratios
        $ratios = (object) [
            'gross_margin' => $totalRev > 0 ? round((($totalRev - $cogs) / $totalRev) * 100, 1) : 0,
            'net_margin'   => $totalRev > 0 ? round(($netProfit / $totalRev) * 100, 1) : 0,
        ];

        return view('pages.finance.index', compact(
            'totalRev', 'totalExp', 'cogs', 'netProfit',
            'monthRevenue', 'monthExpenses', 'monthNet',
            'cashPosition', 'totalDebt', 'overdueCount',
            'taxCollected', 'expenses', 'drawerEvents',
            'isDrawerOpen', 'ratios'
        ));
    }

    // ========== CASH FLOW STATEMENT ==========

    public function cashFlow(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month');

        $baseWhere = function ($q) use ($year, $month) {
            $q->whereYear('created_at', $year);
            if ($month) $q->whereMonth('created_at', $month);
        };

        $cashSales = DB::table('transactions')->where($baseWhere)->where('payment_method', 'cash')->sum('total');
        $cashExpenses = Expense::approved()->where(function ($q) use ($year, $month) {
                $q->whereYear('expense_date', $year);
                if ($month) $q->whereMonth('expense_date', $month);
            })->where('payment_method', 'cash')->sum('amount');
        
        $inventoryPurchases = DB::table('purchase_orders')->where($baseWhere)->where('status', 'received')->sum('total_cost');

        $cashFlowSummary = [
            'operating' => [
                'inflow'  => (float) $cashSales,
                'outflow' => (float) ($cashExpenses + $inventoryPurchases),
                'net'     => (float) ($cashSales - ($cashExpenses + $inventoryPurchases)),
            ]
        ];

        return view('pages.finance.cash_flow', compact('cashFlowSummary', 'year', 'month'));
    }

    // ========== TAX REPORT ==========

    public function taxReport(Request $request)
    {
        $year = $request->get('year', now()->year);
        $monthlyTax = DB::table('transactions')
            ->whereYear('created_at', $year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(tax) as tax_collected'))
            ->groupBy('month')->orderBy('month')->get();

        return view('pages.finance.tax', compact('monthlyTax', 'year'));
    }

    // ========== STORE EXPENSE — مُصلَح ==========

    public function storeExpense(Request $request)
    {
        return app(ExpenseController::class)->store($request);
    }

    // ========== DRAWER EVENTS ==========

    public function storeDrawerEvent(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:open,close,in,out',
            'amount'      => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['amount']  = $validated['amount'] ?? 0;

        CashDrawerEvent::create($validated);
        return redirect()->back()->with('success', __('Drawer event logged.'));
    }

    // ========== PRIVATE HELPERS ==========

    private function calculateCashPosition(): float
    {
        $drawerIn  = CashDrawerEvent::whereIn('type', ['open', 'in'])->sum('amount');
        $drawerOut = CashDrawerEvent::whereIn('type', ['close', 'out'])->sum('amount');
        $cashSales = DB::table('transactions')->where('payment_method', 'cash')->sum('total');
        $cashExp   = Expense::approved()->where('payment_method', 'cash')->sum('amount');

        return (float) ($drawerIn + $cashSales - $drawerOut - $cashExp);
    }

    private function isDrawerOpen(): bool
    {
        $lastOpen = CashDrawerEvent::where('type', 'open')->latest()->first();
        if (!$lastOpen) return false;
        return !CashDrawerEvent::where('type', 'close')->where('created_at', '>', $lastOpen->created_at)->exists();
    }
}
