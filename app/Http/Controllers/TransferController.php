<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransfer;
use App\Models\Product;

class TransferController extends Controller
{
    public function index()
    {
        if (DB::table('settings')->where('key', 'toggle_transfers')->value('value') != '1') {
            return view('pages.transfers.disabled');
        }

        $transfers = StockTransfer::with('product')->get();
        // Useful for the actual stock transfer modal form later
        $products = Product::where('is_service', false)->get();

        return view('pages.transfers.index', compact('transfers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_location' => 'required|string',
            'to_location' => 'required|string',
            'qty' => 'required|integer|min:1',
            'reason' => 'required|string',
            'reason_en' => 'nullable|string'
        ]);

        $validated['user_id'] = auth()->id();

        DB::transaction(function () use ($validated) {
            StockTransfer::create($validated);
        $productId = $request->product_id;
        $fromId = $request->from_storage_id;
        $toId = $request->to_storage_id;
        $qty = $request->qty;

        // Verify enough stock in FromStorage
        $batches = \App\Models\ProductBatch::where('product_id', $productId)
            ->where('storage_id', $fromId)
            ->where('qty', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $available = $batches->sum('qty');
        if ($available < $qty) {
            return redirect()->back()->with('error', __('Not enough stock in source storage.'));
        }

        \DB::transaction(function () use ($productId, $fromId, $toId, $qty, $batches, $request) {
            $remainingToTransfer = $qty;
            foreach ($batches as $batch) {

            if ($qtyToDeduct > 0) {
                throw new \Exception(__('Not enough stock for transfer.'));
            }
        });

        return redirect()->back()->with('success', __('Stock transfer processed successfully.'));
    }

    public function destroy(StockTransfer $transfer)
    {
        $transfer->delete();
        return redirect()->back()->with('success', __('Transfer record deleted.'));
    }
}
