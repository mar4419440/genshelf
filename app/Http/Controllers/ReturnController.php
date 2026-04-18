<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Returns;
use App\Models\DefectiveProduct;

class ReturnController extends Controller
{
    public function index()
    {
        // Eloquent Models 'Returns' (plural used to avoid PHP keyword `return`)
        $returns = Returns::all();
        $defectiveProducts = DefectiveProduct::with(['product', 'supplier'])->get();
        $products = \App\Models\Product::all();
        $suppliers = \App\Models\Supplier::all();
        $transactions = \App\Models\Transaction::latest()->take(100)->get();
        
        return view('pages.returns.index', compact('returns', 'defectiveProducts', 'products', 'suppliers', 'transactions'));
    }

    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'nullable|exists:transactions,id',
            'type' => 'required|in:invoice,general',
            'reason' => 'required|string|max:255',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|in:cash,credit',
            'restocked' => 'boolean'
        ]);

        $validated['restocked'] = $request->has('restocked') ? 1 : 0;
        $validated['user_id'] = auth()->id();

        DB::transaction(function() use ($validated, $request) {
            $return = Returns::create($validated);

            // If credit refund, deduct from customer balance
            if ($validated['refund_method'] === 'credit' && $validated['transaction_id']) {
                $transaction = \App\Models\Transaction::find($validated['transaction_id']);
                if ($transaction->customer_id) {
                    \App\Models\Customer::find($transaction->customer_id)->decrement('credit_balance', $validated['refund_amount']);
                }
            }

            // Logic for restocking would go here (adding back to a batch)
        });

        return redirect()->back()->with('success', __('Return processed successfully.'));
    }

    public function storeDefective(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'description' => 'required|string',
            'status' => 'required|in:open,claimed,resolved'
        ]);

        DefectiveProduct::create($validated);
        return redirect()->back()->with('success', __('Defective item logged successfully.'));
    }

    public function updateDefective(Request $request, DefectiveProduct $defective)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,claimed,resolved'
        ]);

        $defective->update($validated);
        return redirect()->back()->with('success', __('Status updated successfully.'));
    }
}
