<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();

        $toggleCredit = \DB::table('settings')->where('key', 'toggle_credit')->value('value') == '1';
        $toggleLoyalty = \DB::table('settings')->where('key', 'toggle_loyalty')->value('value') == '1';

        return view('pages.customers.index', compact('customers', 'toggleCredit', 'toggleLoyalty'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'loyalty_points' => 'integer|min:0',
            'credit_balance' => 'numeric'
        ]);

        Customer::create($validated);
        return redirect()->back()->with('success', __('Customer added successfully.'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'loyalty_points' => 'integer|min:0',
            'credit_balance' => 'numeric'
        ]);

        $customer->update($validated);
        return redirect()->back()->with('success', __('Customer updated successfully.'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->back()->with('success', __('Customer deleted successfully.'));
    }

    public function pay(Request $request, Customer $customer)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $amount = $request->amount;

        if ($amount > $customer->credit_balance) {
            return redirect()->back()->with('error', __('Amount exceeds balance.'));
        }

        \DB::transaction(function () use ($customer, $amount) {
            $customer->decrement('credit_balance', $amount);

            \App\Models\Transaction::create([
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
                'total' => 0,
                'paid_amount' => $amount,
                'due_amount' => -$amount,
                'payment_method' => 'cash',
                'items' => json_encode([['name' => __('Debt Repayment'), 'qty' => 1, 'price' => $amount]])
            ]);
        });

        return redirect()->back()->with('success', __('Payment recorded successfully.'));
    }

    public function history(Customer $customer)
    {
        $history = \App\Models\Transaction::where('customer_id', $customer->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json($history);
    }
}
