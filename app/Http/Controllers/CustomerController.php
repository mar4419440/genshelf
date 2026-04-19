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
}
