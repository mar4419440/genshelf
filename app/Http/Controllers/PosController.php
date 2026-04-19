<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Customer;

class PosController extends Controller
{
    public function index()
    {
        // Calculate dynamic stock utilizing DB logic and product data
        $products = DB::table('products')
            ->where('is_service', false)
            ->select('products.*')
            ->get()
            ->map(function ($product) {
                // Determine stock based on batches
                $product->current_stock = DB::table('product_batches')->where('product_id', $product->id)->sum('qty');
                return $product;
            })->filter(function ($product) {
                return $product->current_stock > 0;
            });

        $customers = Customer::all();

        $taxRateStr = DB::table('settings')->where('key', 'tax_rate')->value('value');
        $toggleTaxStr = DB::table('settings')->where('key', 'toggle_tax')->value('value');
        $toggleCreditStr = DB::table('settings')->where('key', 'toggle_credit')->value('value');

        $taxRate = $toggleTaxStr == '1' ? (float) $taxRateStr : 0;
        $toggleCredit = $toggleCreditStr == '1';

        return view('pages.pos.index', compact('products', 'customers', 'taxRate', 'toggleCredit'));
    }

    public function checkout(Request $request)
    {
        $cartData = json_decode($request->input('cart_data', '[]'), true);
        if (empty($cartData)) {
            return redirect()->back()->with('error', __('Cart is empty.'));
        }

        $customerId = $request->input('customer_id');
        $isCredit = $request->has('credit_sale');
        $taxToggle = DB::table('settings')->where('key', 'toggle_tax')->value('value') == '1';
        $taxRate = $taxToggle ? (float) DB::table('settings')->where('key', 'tax_rate')->value('value') : 0;

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($cartData as $item) {
                $subtotal += ($item['price'] * $item['qty']);
            }

            $taxTotal = $subtotal * ($taxRate / 100);
            $grandTotal = $subtotal + $taxTotal;

            // 1. Create Transaction
            $transaction = \App\Models\Transaction::create([
                'customer_id' => $customerId,
                'user_id' => auth()->id(),
                'storage_id' => $request->storage_id,
                'subtotal' => $subtotal,
                'tax' => $taxTotal,
                'total' => $grandTotal,
                'payment_method' => $isCredit ? 'credit' : 'cash',
            ]);

            // 2. Process Items
            $processedItemsForJson = [];
            foreach ($cartData as $item) {
                $warrantyExpiry = null;
                if (!$item['isService']) {
                    $product = \App\Models\Product::find($item['id']);
                    if ($product && $product->has_warranty && $product->warranty_duration > 0) {
                        $warrantyExpiry = now()->addMonths($product->warranty_duration)->format('Y-m-d');
                    }
                }

                \App\Models\TransactionItem::create([
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

                // 3. Stock Deduction (FIFO)
                if (!$item['isService']) {
                    $qtyToDeduct = $item['qty'];
                    $batches = \App\Models\ProductBatch::where('product_id', $item['id'])
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
                        throw new \Exception(__('Not enough stock for ') . $item['name']);
                    }
                }
            }

            // Sync items JSON to the transaction for report performance
            $transaction->update(['items' => json_encode($processedItemsForJson)]);

            // 4. Handle Payments (Full, Partial, or Debt)
            $paidInput = $request->input('paid_amount');
            $paidAmount = $grandTotal; // Default to full pay

            if ($isCredit) {
                $paidAmount = 0; // Debt sale
            } elseif ($request->filled('paid_amount')) {
                $paidAmount = (float) $paidInput;
            }

            $dueAmount = max(0, $grandTotal - $paidAmount);

            $transaction->update([
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'due_date' => $dueAmount > 0 ? $request->input('due_date') : null,
                'payment_method' => $dueAmount > 0 ? ($paidAmount > 0 ? 'partial' : 'debt') : 'cash'
            ]);

            // 5. Update Customer (Credit/Loyalty)
            if ($customerId) {
                $customer = \App\Models\Customer::find($customerId);
                if ($dueAmount > 0) {
                    $customer->increment('credit_balance', $dueAmount);
                }

                // Loyalty: 1 point per 10 total currency
                $loyaltyToggle = DB::table('settings')->where('key', 'toggle_loyalty')->value('value') == '1';
                if ($loyaltyToggle) {
                    $points = floor($grandTotal / 10);
                    $customer->increment('loyalty_points', $points);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', __('Sale completed successfully! Invoice #') . $transaction->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', __('Checkout failed: ') . $e->getMessage());
        }
    }
}
