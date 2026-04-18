<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('batches');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) like ?', ['%' . $search . '%']);
        }

        $products = $query->get()->map(function($p) {
            $p->current_stock = $p->batches->sum('qty');
            return $p;
        });

        // Use the global low stock default if the product's threshold isn't set
        $lowStockDefault = \DB::table('settings')->where('key', 'low_stock_default')->value('value') ?? 5;

        return view('pages.inventory.index', compact('products', 'lowStockDefault'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Category', 'Default Price', 'Low Stock Threshold', 'Is Service (0 or 1)']);
            fputcsv($file, ['Example Product', 'Electronics', '99.99', '10', '0']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCSV(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('csv_file')->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $header = array_shift($data);
        $count = 0;

        foreach ($data as $row) {
            if (count($row) < 3) continue;

            Product::create([
                'name' => $row[0],
                'category' => $row[1] ?? 'General',
                'default_price' => $row[2] ?? 0,
                'low_stock_threshold' => $row[3] ?? 5,
                'is_service' => ($row[4] ?? 0) == 1 ? 1 : 0,
            ]);
            $count++;
        }

        return redirect()->back()->with('success', __(':count products imported successfully.', ['count' => $count]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'is_service' => 'boolean'
        ]);
        
        $validated['is_service'] = $request->has('is_service') ? 1 : 0;

        Product::create($validated);
        return redirect()->back()->with('success', __('Product created successfully.'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'is_service' => 'boolean'
        ]);

        $validated['is_service'] = $request->has('is_service') ? 1 : 0;

        $product->update($validated);
        return redirect()->back()->with('success', __('Product updated successfully.'));
    }

    public function destroy(Product $product)
    {
        // Don't delete if there are active batches (foreign key constrains might exist depending on migration)
        if ($product->batches()->count() > 0) {
            return redirect()->back()->with('error', __('Cannot delete product with active stock batches.'));
        }
        
        $product->delete();
        return redirect()->back()->with('success', __('Product deleted successfully.'));
    }
}
