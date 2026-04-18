<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStr = now()->format('Y-m-d');
        
        $todayRevenue = DB::table('transactions')
            ->whereDate('created_at', $todayStr)
            ->sum('total');
            
        $totalStock = DB::table('product_batches')->sum('qty');
        
        // This query counts how many products have stock lower than their threshold
        $lowStockDefault = DB::table('settings')->where('key', 'low_stock_default')->value('value') ?? 5;
        $lowAlerts = DB::table('products')
            ->where('is_service', false)
            ->whereExists(function ($query) use ($lowStockDefault) {
                $query->select(DB::raw(1))
                      ->from('product_batches')
                      ->whereColumn('product_batches.product_id', 'products.id')
                      ->havingRaw('SUM(product_batches.qty) <= products.low_stock_threshold')
                      ->orHavingRaw("SUM(product_batches.qty) <= {$lowStockDefault}");
            })
            ->count();
            
        $pendingPO = DB::table('purchase_orders')->where('status', 'pending')->count();
        
        // Weekly Data (Sun, Mon, Tue, Wed, Thu, Fri, Sat)
        $weekStart = now()->startOfWeek(0); // Sunday
        $weekData = array_fill(0, 7, 0);
        $transactions = DB::table('transactions')
            ->where('created_at', '>=', $weekStart)
            ->get();
            
        foreach ($transactions as $tx) {
            $dayIndex = (new \Carbon\Carbon($tx->created_at))->dayOfWeek;
            $weekData[$dayIndex] += $tx->total;
        }
        
        $maxSale = max($weekData) ?: 1;
        $todayDay = now()->dayOfWeek;
        $days = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
        
        $recentTx = \App\Models\Transaction::with('items')->latest()->take(5)->get(); // Will need Eloquent Models

        return view('pages.dashboard.index', compact(
            'todayRevenue', 'totalStock', 'lowAlerts', 'pendingPO',
            'weekData', 'maxSale', 'todayDay', 'days', 'recentTx'
        ));
    }
}
