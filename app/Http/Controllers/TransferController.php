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

            // Subtract stock from oldest batches (FIFO)
            $qtyToDeduct = $validated['qty'];
            $batches = \App\Models\ProductBatch::where('product_id', $validated['product_id'])
                ->where('qty', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($qtyToDeduct <= 0)
                    break;

                if ($batch->qty >= $qtyToDeduct) {
                    $batch->decrement('qty', $qtyToDeduct);
                    $qtyToDeduct = 0;
                } else {
                    $qtyToDeduct -= $batch->qty;
                    $batch->update(['qty' => 0]);
                }
            }

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
