<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        $purchaseOrders = PurchaseOrder::with(['supplier', 'product'])->get();
        $products = Product::where('is_service', false)->get(); // For new PO dropdown

        $costMode = DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
        $categories = \App\Models\Category::all();

        return view('pages.suppliers.index', compact('suppliers', 'purchaseOrders', 'products', 'costMode', 'categories'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="po_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Supplier Name', 'Product Name', 'Quantity', 'Unit Cost']);
            fputcsv($file, ['Main Supplier', 'Classic Widget', '50', '15.50']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importPOs(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $header = array_shift($data);
        $count = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                if (count($row) < 4)
                    continue;

                $supplierName = trim($row[0]);
                $productName = trim($row[1]);
                $qty = (int) $row[2];
                $unitCost = (float) $row[3];

                $supplier = Supplier::where('name', $supplierName)->first();
                $product = Product::where('name', $productName)->first();

                if (!$supplier || !$product) {
                    $errors[] = "Row " . ($index + 2) . ": Supplier or Product not found.";
                    continue;
                }

                PurchaseOrder::create([
                    'supplier_id' => $supplier->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $qty * $unitCost,
                    'status' => 'pending'
                ]);
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Import failed: ') . $e->getMessage());
        }

        $msg = __(':count POs imported successfully.', ['count' => $count]);
        if (!empty($errors)) {
            $msg .= " " . __('Errors in rows: ') . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $category = \App\Models\Category::find($validated['category_id']);
        $validated['category'] = $category->name;
        $validated['category_en'] = $category->name_en;
        unset($validated['category_id']);

        Supplier::create($validated);
        return redirect()->back()->with('success', __('Supplier added successfully.'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $category = \App\Models\Category::find($validated['category_id']);
        $validated['category'] = $category->name;
        $validated['category_en'] = $category->name_en;
        unset($validated['category_id']);

        $supplier->update($validated);
        return redirect()->back()->with('success', __('Supplier updated successfully.'));
    }

    public function destroy(Supplier $supplier)
    {
        // Check for attached POs
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->back()->with('error', __('Cannot delete supplier with active purchase orders.'));
        }

        $supplier->delete();
        return redirect()->back()->with('success', __('Supplier deleted successfully.'));
    }

    // Purchase Order Methods
    public function storePO(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'cost' => 'required|numeric|min:0',
        ]);

        $costMode = DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';

        if ($costMode === 'unit') {
            $unitCost = $validated['cost'];
            $totalCost = $validated['qty'] * $validated['cost'];
        } else {
            $totalCost = $validated['cost'];
            $unitCost = $validated['qty'] > 0 ? $validated['cost'] / $validated['qty'] : 0;
        }

        PurchaseOrder::create([
            'supplier_id' => $validated['supplier_id'],
            'product_id' => $validated['product_id'],
            'qty' => $validated['qty'],
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', __('Purchase order created successfully.'));
    }

    public function receivePO(PurchaseOrder $po)
    {
        if ($po->status === 'received') {
            return redirect()->back()->with('error', __('This PO has already been received.'));
        }

        \DB::transaction(function () use ($po) {
            $po->update(['status' => 'received']);

            // Add stock via batch
            \App\Models\ProductBatch::create([
                'product_id' => $po->product_id,
                'supplier_id' => $po->supplier_id,
                'qty' => $po->qty,
                'cost' => $po->unit_cost,
                'batch_number' => 'PO-' . $po->id . '-' . time()
            ]);
        });

        return redirect()->back()->with('success', __('Purchase order received and stock updated.'));
    }
}
