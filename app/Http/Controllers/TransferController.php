<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['product', 'fromStorage', 'toStorage', 'user'])->latest()->get();
        return view('pages.transfers.index', compact('transfers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_storage_id' => 'required|exists:storages,id',
            'to_storage_id' => 'required|exists:storages,id|different:from_storage_id',
            'qty' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);

        $productId = $request->product_id;
        $fromId = $request->from_storage_id;
        $toId = $request->to_storage_id;
        $qty = $request->qty;

        $batches = ProductBatch::where('product_id', $productId)
            ->where('storage_id', $fromId)
            ->where('qty', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $available = $batches->sum('qty');
        if ($available < $qty) {
            return redirect()->back()->with('error', __('Not enough stock in source storage.'));
        }

        DB::transaction(function () use ($productId, $fromId, $toId, $qty, $batches, $request) {
            $remainingToTransfer = $qty;
            foreach ($batches as $batch) {
                if ($remainingToTransfer <= 0)
                    break;

                $transferQty = min($batch->qty, $remainingToTransfer);

                $batch->decrement('qty', $transferQty);

                $targetBatch = ProductBatch::firstOrCreate(
                    [
                        'product_id' => $productId,
                        'storage_id' => $toId,
                        'expiry_date' => $batch->expiry_date,
                        'supplier_id' => $batch->supplier_id,
                        'batch_number' => $batch->batch_number ?: 'TRANS-' . time()
                    ],
                    [
                        'qty' => 0,
                        'unit_cost' => $batch->unit_cost ?: 0
                    ]
                );
                $targetBatch->increment('qty', $transferQty);

                $remainingToTransfer -= $transferQty;
            }

            StockTransfer::create([
                'product_id' => $productId,
                'from_storage_id' => $fromId,
                'to_storage_id' => $toId,
                'qty' => $qty,
                'user_id' => auth()->id(),
                'notes' => $request->notes
            ]);
        });

        return redirect()->back()->with('success', __('Stock transferred successfully.'));
    }
}
