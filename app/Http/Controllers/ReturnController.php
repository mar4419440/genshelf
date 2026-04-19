<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Returns;
use App\Models\DefectiveProduct;

class ReturnController extends Controller
{
    public function index()
    {
        $returnsQuery = Returns::latest();
        if (request()->filled('search_log')) {
            $s = request('search_log');
            $returnsQuery->where(function($q) use ($s) {
                $q->where('reason', 'like', "%$s%")
                  ->orWhere('refund_amount', 'like', "%$s%")
                  ->orWhere('created_at', 'like', "%$s%");
            });
        }
        $returns = $returnsQuery->get();

        $defectiveQuery = DefectiveProduct::with(['product', 'supplier'])->latest();
        if (request()->filled('search_defective')) {
            $s = request('search_defective');
            $defectiveQuery->where(function($q) use ($s) {
                $q->where('description', 'like', "%$s%")
                  ->orWhere('status', 'like', "%$s%")
                  ->orWhere('created_at', 'like', "%$s%")
                  ->orWhereHas('product', function($sub) use ($s) {
                      $sub->where('name', 'like', "%$s%");
                  })
                  ->orWhereHas('supplier', function($sub) use ($s) {
                      $sub->where('name', 'like', "%$s%");
                  });
            });
        }
        $defectiveProducts = $defectiveQuery->get();

        $products = \App\Models\Product::all();
        $suppliers = \App\Models\Supplier::all();
        
        $preFilledProduct = null;
        if (request()->filled('product_id')) {
            $preFilledProduct = \App\Models\Product::find(request('product_id'));
        }

        $transactionsQuery = \App\Models\Transaction::with('customer')->latest();
        if (request()->filled('search_invoice')) {
            $search = request('search_invoice');
            $transactionsQuery->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('total', 'like', "%$search%")
                  ->orWhere('created_at', 'like', "%$search%")
                  ->orWhereHas('customer', function($sub) use ($search) {
                      $sub->where('name', 'like', "%$search%");
                  })
                  ->orWhere('items_snapshot', 'like', "%$search%");
            });
        }
        $transactions = $transactionsQuery->take(50)->get();
        
        return view('pages.returns.index', compact('returns', 'defectiveProducts', 'products', 'suppliers', 'transactions', 'preFilledProduct'));
    }

    public function storeReturn(Request $request)
    {
        // Handle checkbox input before validation
        $request->merge(['restocked' => $request->has('restocked')]);

        $validated = $request->validate([
            'transaction_id' => 'nullable|exists:transactions,id',
            'product_id' => 'nullable|exists:products,id',
            'type' => 'required|in:invoice,general',
            'reason' => 'required|string|max:255',
            'refund_amount' => 'required|numeric|min:0',
            'refund_method' => 'required|in:cash,credit',
            'restocked' => 'nullable|boolean'
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
            'transaction_id' => 'nullable|exists:transactions,id',
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
