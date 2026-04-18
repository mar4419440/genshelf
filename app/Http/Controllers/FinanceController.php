<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $totalRev = DB::table('transactions')->sum('total');
        $totalExp = DB::table('expenses')->sum('amount');
        $cogs = DB::table('purchase_orders')->where('status', 'received')->sum('total_cost');
        
        $net = $totalRev - $cogs - $totalExp;

        $expenses = Expense::latest()->take(20)->get();
        $drawerEvents = \App\Models\CashDrawerEvent::with('user')->latest()->take(15)->get();

        $lastOpen = \App\Models\CashDrawerEvent::where('type', 'open')->latest()->first();
        $isDrawerOpen = false;
        
        if ($lastOpen) {
            $closedAfter = \App\Models\CashDrawerEvent::where('type', 'close')
                ->where('created_at', '>', $lastOpen->created_at)
                ->exists();
            $isDrawerOpen = !$closedAfter;
        }

        return view('pages.finance.index', compact('totalRev', 'totalExp', 'cogs', 'net', 'expenses', 'drawerEvents', 'isDrawerOpen'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'is_recurring' => 'boolean'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_recurring'] = $request->has('is_recurring') ? 1 : 0;

        Expense::create($validated);
        return redirect()->back()->with('success', __('Expense logged successfully.'));
    }

    public function storeDrawerEvent(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:open,close,in,out',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['amount'] = $validated['amount'] ?? 0;

        \App\Models\CashDrawerEvent::create($validated);
        return redirect()->back()->with('success', __('Drawer event logged.'));
    }
}
