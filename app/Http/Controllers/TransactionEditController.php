<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Models\DefectiveProduct;
use Illuminate\Support\Facades\DB;

class TransactionEditController extends Controller
{
    public function edit(Transaction $transaction)
    {
        $transaction->load('items');
        $products = Product::where('is_service', false)
            ->get()
            ->map(function ($p) {
                $p->current_stock = ProductBatch::where('product_id', $p->id)->sum('qty');
                return $p;
            });
            
        $services = Product::where('is_service', true)->get();
        $customers = Customer::all();
        
        $taxRate = (float) DB::table('settings')->where('key', 'tax_rate')->value('value') ?? 0;
        $toggleTax = DB::table('settings')->where('key', 'toggle_tax')->value('value') == '1';
        if (!$toggleTax) $taxRate = 0;

        $cartItems = $transaction->items->map(function($i){
            return [
                'id' => $i->product_id ?? 'svc_'.$i->id,
                'name' => $i->name,
                'price' => (float)$i->unit_price,
                'qty' => $i->qty,
                'isService' => (bool)$i->is_service,
                'maxStock' => 999999,
                'isOriginal' => true
            ];
        });

        return view('pages.pos.edit', compact('transaction', 'products', 'services', 'customers', 'taxRate', 'cartItems'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $cartData = json_decode($request->input('cart_data', '[]'), true);
        if (empty($cartData)) {
            return redirect()->back()->with('error', __('Cart cannot be empty. Use returns for full cancellations.'));
        }

        $dispositions = $request->input('dispositions', []); // mapping of product_id => 'restock' or 'damage'

        try {
            DB::beginTransaction();

            // 1. REVERSE ORIGINAL STOCK MOVEMENTS
            // Since we don't track specifically which batch was used, we'll return to the latest batch 
            // OR create a special RETURN batch to avoid messing with costs too much.
            foreach ($transaction->items as $oldItem) {
                if (!$oldItem->is_service && $oldItem->product_id) {
                    $disposition = $dispositions[$oldItem->product_id] ?? 'restock';
                    
                    if ($disposition === 'damage') {
                        // Log as defective
                        DefectiveProduct::create([
                            'product_id' => $oldItem->product_id,
                            'description' => 'Removed from Invoice #' . $transaction->id . ' during edit (Marked as damaged)',
                            'status' => 'open'
                        ]);
                    } else {
                        // Return to stock (find latest batch or create return batch)
                        $batch = ProductBatch::where('product_id', $oldItem->product_id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if ($batch) {
                            $batch->increment('qty', $oldItem->qty);
                        } else {
                            ProductBatch::create([
                                'product_id' => $oldItem->product_id,
                                'qty' => $oldItem->qty,
                                'unit_cost' => 0,
                                'batch_number' => 'REVERT-' . $transaction->id
                            ]);
                        }
                    }
                }
            }

            // 2. REVERSE CUSTOMER CREDIT BALANCE
            if ($transaction->customer_id && $transaction->due_amount > 0) {
                Customer::where('id', $transaction->customer_id)->decrement('credit_balance', $transaction->due_amount);
            }

            // 3. WIPE OLD ITEMS
            $transaction->items()->delete();

            // 4. APPLY NEW ITEMS & DEDUCT STOCK
            $subtotal = 0;
            $processedItemsForJson = [];
            
            foreach ($cartData as $item) {
                $subtotal += ($item['price'] * $item['qty']);
                
                $warrantyExpiry = null;
                if (!$item['isService']) {
                    $product = Product::find($item['id']);
                    if ($product && $product->has_warranty && $product->warranty_duration > 0) {
                        $warrantyExpiry = now()->addMonths($product->warranty_duration)->format('Y-m-d');
                    }
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['isService'] ? null : $item['id'],
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['price'] * $item['qty'],
                    'is_service' => $item['isService'] ? 1 : 0,
                    'warranty_expiry' => $warrantyExpiry,
                ]);

                $processedItemsForJson[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'warranty_expiry' => $warrantyExpiry,
                ];

                // Stock Deduction (FIFO)
                if (!$item['isService']) {
                    $qtyToDeduct = $item['qty'];
                    while ($qtyToDeduct > 0) {
                        $batch = ProductBatch::where('product_id', $item['id'])
                            ->where('qty', '>', 0)
                            ->orderBy('expiration_date', 'asc')
                            ->first();

                        if (!$batch) {
                            // If we run out of stock during edit, we throw error
                            throw new \Exception(__('Insufficient stock for ') . $item['name']);
                        }

                        if ($batch->qty >= $qtyToDeduct) {
                            $batch->decrement('qty', $qtyToDeduct);
                            $qtyToDeduct = 0;
                        } else {
                            $qtyToDeduct -= $batch->qty;
                            $batch->update(['qty' => 0]);
                        }
                    }
                }
            }

            // 5. UPDATE TRANSACTION TOTALS
            $taxToggle = DB::table('settings')->where('key', 'toggle_tax')->value('value') == '1';
            $taxRate = $taxToggle ? (float) DB::table('settings')->where('key', 'tax_rate')->value('value') : 0;
            $taxTotal = $subtotal * ($taxRate / 100);
            $grandTotal = $subtotal + $taxTotal;

            $paidAmount = (float) $request->input('paid_amount', $transaction->paid_amount);
            $dueAmount = max(0, $grandTotal - $paidAmount);

            $transaction->update([
                'customer_id' => $request->input('customer_id', $transaction->customer_id),
                'subtotal' => $subtotal,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'due_date' => $dueAmount > 0 ? $request->input('due_date') : null,
                'payment_method' => $dueAmount > 0 ? ($paidAmount > 0 ? 'partial' : 'debt') : 'cash',
                'items_snapshot' => $processedItemsForJson
            ]);

            // 6. UPDATE CUSTOMER CREDIT
            if ($transaction->customer_id && $dueAmount > 0) {
                Customer::where('id', $transaction->customer_id)->increment('credit_balance', $dueAmount);
            }

            DB::commit();
            return redirect()->route('reports')->with('success', __('Invoice updated successfully. Stock and balances synchronized.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Update failed: ') . $e->getMessage());
        }
    }
}
