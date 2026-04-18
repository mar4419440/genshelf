<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $transactions = Transaction::latest()->get();
        $totalRev = $transactions->sum('total');
        $totalTxCount = $transactions->count();
        $avgOrder = $totalTxCount > 0 ? $totalRev / $totalTxCount : 0;

        // Calculate Top Selling
        $productSales = [];
        foreach ($transactions as $tx) {
            $items = is_string($tx->items) ? json_decode($tx->items, true) : $tx->items;
            if (is_array($items)) {
                foreach ($items as $item) {
                    $name = $item['name'] ?? 'Unknown';
                    if (!isset($productSales[$name])) {
                        $productSales[$name] = ['name' => $name, 'units' => 0, 'revenue' => 0];
                    }
                    $qty = $item['qty'] ?? 0;
                    $price = $item['price'] ?? 0;
                    $productSales[$name]['units'] += $qty;
                    $productSales[$name]['revenue'] += $price * $qty;
                }
            }
        }
        
        usort($productSales, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        $topSelling = array_slice($productSales, 0, 10);

        return view('pages.reports.index', compact('transactions', 'totalRev', 'totalTxCount', 'avgOrder', 'topSelling'));
    }
}
