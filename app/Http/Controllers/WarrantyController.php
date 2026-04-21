<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warranty;
use Illuminate\Support\Facades\DB;

class WarrantyController extends Controller
{
    public function index()
    {
        if (DB::table('settings')->where('key', 'toggle_warranty')->value('value') != '1') {
            return view('pages.warranty.disabled');
        }

        $warranties = Warranty::with(['product', 'customer'])->get();
        $products = \App\Models\Product::all();
        $customers = \App\Models\Customer::all();
        
        // Fetch products that actually offer a warranty
        $warrantyProducts = \App\Models\Product::where('has_warranty', true)->get();

        return view('pages.warranty.index', compact('warranties', 'products', 'customers', 'warrantyProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'purchase_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:purchase_date'
        ]);

        Warranty::create($validated);
        return redirect()->back()->with('success', __('Warranty registered successfully.'));
    }

    public function destroy(Warranty $warranty)
    {
        $warranty->delete();
        return redirect()->back()->with('success', __('Warranty deleted successfully.'));
    }
}
