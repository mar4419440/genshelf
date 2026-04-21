<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['batches.storage']);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) like ?', ['%' . $search . '%']);
        }

        $products = $query->get()->map(function ($p) {
            $p->current_stock = $p->batches->sum('qty');
            // Extract unique storage names
            $p->storage_names = $p->batches->map(function ($b) {
                return $b->storage ? $b->storage->name : null;
            })->filter()->unique()->implode(', ');
            return $p;
        });

        // Use the global low stock default if the product's threshold isn't set
        $lowStockDefault = \DB::table('settings')->where('key', 'low_stock_default')->value('value') ?? 5;
        $costMode = \DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
        $suppliers = Supplier::all();
        $categories = \App\Models\Category::with('parent')->get();

        return view('pages.inventory.index', compact('products', 'lowStockDefault', 'costMode', 'suppliers', 'categories'));
    }

    public function expiring(Request $request)
    {
        $batches = \App\Models\ProductBatch::with('product')
            ->whereNotNull('expiration_date')
            ->where('qty', '>', 0)
            ->orderBy('expiration_date', 'asc')
            ->get();
            
        return view('pages.inventory.expiring', compact('batches'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $categories = \App\Models\Category::all();
        $storages = \App\Models\Storage::where('is_active', true)->get();
        $lowStockDefault = \DB::table('settings')->where('key', 'low_stock_default')->value('value') ?? 5;
        $costMode = \DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';

        return view('pages.inventory.create', compact('suppliers', 'categories', 'storages', 'lowStockDefault', 'costMode'));
    }

    public function downloadTemplate()
    {
        $costMode = \DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
        $costHeader = $costMode === 'total' ? __('Total_Cost') : __('Unit_Cost');

        $reqStyle = '<style bgcolor="#FFE6E6">';
        $reqEnd = '</style>';

        $headers = [
            __('Barcode'),
            $reqStyle . __('Arabic_Name') . $reqEnd,
            __('English_Name'),
            __('Category_Path'), // Defaults to General if empty, but good to highlight
            $reqStyle . __('Default_Price') . $reqEnd,
            __('Low_Stock_Threshold'),
            __('Is_Service'),
            $reqStyle . __('Storage_Name') . $reqEnd,
            $reqStyle . __('Supplier_Name') . $reqEnd,
            $reqStyle . __('Initial_Qty') . $reqEnd,
            $reqStyle . $costHeader . $reqEnd,
        ];

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray([$headers]);
        $xlsx->downloadAs('product_template.xlsx');
        exit;
    }

    public function importCSV(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt,xlsx,xls']);

        $path = $request->file('csv_file')->getRealPath();
        $extension = $request->file('csv_file')->getClientOriginalExtension();

        if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($path)) {
                $data = $xlsx->rows();
            } else {
                return redirect()->back()->with('error', \Shuchkin\SimpleXLSX::parseError());
            }
        } else {
            $data = array_map('str_getcsv', file($path));
        }

        $header = array_shift($data);
        $count = 0;

        foreach ($data as $row) {
            if (count($row) < 5)
                continue;

            $barcode = trim($row[0] ?? '');
            $nameAr = trim($row[1] ?? '');
            $nameEn = trim($row[2] ?? '');
            $categoryPath = trim($row[3] ?? 'General');
            $price = floatval($row[4] ?? 0);
            $threshold = intval($row[5] ?? 5);
            $isService = ($row[6] ?? 0) == 1 ? 1 : 0;
            $storageName = trim($row[7] ?? '');
            $supplierName = trim($row[8] ?? '');
            $qty = intval($row[9] ?? 0);
            $cost = floatval($row[10] ?? 0);

            if (empty($nameAr))
                continue;

            $category = \App\Models\Category::where('full_path', $categoryPath)->orWhere('name', $categoryPath)->first();
            $categoryEn = $category ? $category->name_en : null;

            if (empty($barcode)) {
                $barcode = '2026' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
            }

            $storage = \App\Models\Storage::where('name', $storageName)->first();
            $storageId = $storage ? $storage->id : (\App\Models\Storage::first()->id ?? null);

            $supplier = \App\Models\Supplier::where('name', $supplierName)->first();
            $supplierId = $supplier ? $supplier->id : null;

            $product = new Product();
            $product->barcode = $barcode;
            $product->name = $nameAr;
            $product->name_en = $nameEn;
            $product->category = $categoryPath;
            $product->category_en = $categoryEn;
            $product->default_price = $price;
            $product->low_stock_threshold = $threshold;
            $product->is_service = $isService;
            $product->save();

            if (!$isService && $qty > 0) {
                $costMode = \DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
                $unitCost = $cost;
                if ($costMode === 'total' && $qty > 0) {
                    $unitCost = $cost / $qty;
                }

                $product->batches()->create([
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'storage_id' => $storageId,
                    'supplier_id' => $supplierId,
                    'batch_number' => 'IMPORT-' . time()
                ]);
            }
            $count++;
        }

        return redirect()->back()->with('success', __(':count products imported successfully.', ['count' => $count]));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'default_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'is_service' => 'nullable|boolean',
            'has_warranty' => 'nullable|boolean',
            'warranty_duration' => 'nullable|integer|min:0',
            'has_expiration' => 'nullable|boolean',
            'expiration_date' => 'nullable|date',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ];

        if (!$request->has('is_service')) {
            $rules['supplier_id'] = 'required|exists:suppliers,id';
            $rules['storage_id'] = 'required|exists:storages,id';
            $rules['cost'] = 'required|numeric|min:0';
            $rules['initial_qty'] = 'required|integer|min:0';
        }

        $validated = $request->validate($rules);

        $category = \App\Models\Category::find($validated['category_id']);
        $validated['category'] = $category->full_path;
        $validated['category_en'] = $category->name_en;
        $supplier_id = $validated['supplier_id'] ?? null;
        $storage_id = $validated['storage_id'] ?? null;
        $cost = $validated['cost'] ?? 0;
        $qty = $validated['initial_qty'] ?? 0;

        $validated['has_warranty'] = $request->has('has_warranty');
        $validated['warranty_duration'] = $validated['warranty_duration'] ?? 0;
        $validated['has_expiration'] = $request->has('has_expiration');
        $validated['is_service'] = $request->has('is_service') ? 1 : 0;

        // Auto-generate barcode if empty
        if (empty($request->barcode)) {
            $validated['barcode'] = '2026' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } else {
            $validated['barcode'] = $request->barcode;
        }

        unset($validated['category_id'], $validated['supplier_id'], $validated['cost'], $validated['initial_qty'], $validated['storage_id']);

        $product = Product::create($validated);

        if (!$product->is_service && $supplier_id) {
            // Convert total cost to unit cost if setting is 'total'
            $costMode = \DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
            $unitCost = $cost;
            if ($costMode === 'total' && $qty > 0) {
                $unitCost = $cost / $qty;
            }

            \App\Models\ProductBatch::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier_id,
                'storage_id' => $storage_id,
                'qty' => $qty,
                'unit_cost' => $unitCost,
                'expiration_date' => $validated['expiration_date'] ?? null,
                'batch_number' => 'INITIAL-' . time()
            ]);
        }

        // Finalize structured barcode if it was auto-generated
        if (substr($product->barcode, 0, 4) === '2026') {
            $sCode = str_pad($storage_id ?? 0, 2, '0', STR_PAD_LEFT);
            $cCode = str_pad($category->id, 4, '0', STR_PAD_LEFT);
            $pCode = str_pad($product->id, 6, '0', STR_PAD_LEFT);
            $product->update(['barcode' => $sCode . $cCode . $pCode]);
        }

        return redirect()->back()->with('success', __('Product created successfully.'));
    }

    public function restock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'qty' => 'required|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $costMode = DB::table('settings')->where('key', 'cost_display_mode')->value('value') ?? 'unit';
        $cost = $validated['cost'] ?? 0;

        if ($costMode === 'total' && $cost > 0) {
            $cost = $cost / $validated['qty'];
        }

        ProductBatch::create([
            'product_id' => $product->id,
            'supplier_id' => $validated['supplier_id'],
            'qty' => $validated['qty'],
            'unit_cost' => $cost,
            'expiration_date' => $request->input('expiration_date'),
            'batch_number' => 'RESTOCK-' . time()
        ]);

        return redirect()->back()->with('success', __('Stock updated successfully.'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'default_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'is_service' => 'nullable|boolean',
            'has_warranty' => 'nullable|boolean',
            'warranty_duration' => 'nullable|integer|min:0',
            'has_expiration' => 'nullable|boolean',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
        ]);

        $category = \App\Models\Category::find($validated['category_id']);
        $validated['category'] = $category->full_path;
        $validated['category_en'] = $category->name_en;
        unset($validated['category_id']);

        $validated['is_service'] = $request->has('is_service') ? 1 : 0;
        $validated['has_warranty'] = $request->has('has_warranty') ? 1 : 0;
        $validated['warranty_duration'] = $validated['warranty_duration'] ?? 0;
        $validated['has_expiration'] = $request->has('has_expiration') ? 1 : 0;

        // Handle barcode
        if (!empty($request->barcode)) {
            $validated['barcode'] = $request->barcode;
        } elseif (empty($product->barcode)) {
            // Auto-generate structured barcode: StorageID(2) + CategoryID(4) + ProductID(6)
            $sCode = str_pad($product->batches()->first()?->storage_id ?? 0, 2, '0', STR_PAD_LEFT);
            $cCode = str_pad($category->id, 4, '0', STR_PAD_LEFT);
            $pCode = str_pad($product->id, 6, '0', STR_PAD_LEFT);
            $validated['barcode'] = $sCode . $cCode . $pCode;
        }

        $product->update($validated);

        // Optional: Update existing stock expiration dates if provided from the Edit modal
        if ($request->has('has_expiration') && $request->filled('expiration_date')) {
            $product->batches()->where('qty', '>', 0)->update(['expiration_date' => $request->expiration_date]);
        } elseif (!$request->has('has_expiration')) {
            $product->batches()->update(['expiration_date' => null]);
        }

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
