<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'user']);

        // --- FILTERING ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        } elseif ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('created_at', now()->today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', now()->yesterday());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'this_quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Specific Selectors
        if ($request->filled('specific_year')) {
            $query->whereYear('created_at', $request->specific_year);
        }
        if ($request->filled('specific_month')) {
            $query->whereMonth('created_at', $request->specific_month);
        }
        if ($request->filled('specific_quarter')) {
            $quarter = $request->specific_quarter;
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $query->whereMonth('created_at', '>=', $startMonth)->whereMonth('created_at', '<=', $endMonth);
        }

        // --- SEARCH ---
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('total', 'like', "%$search%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%$search%")
                            ->orWhere('display_name', 'like', "%$search%");
                    })
                    ->orWhere('items', 'like', "%$search%");
            });
        }

        $transactions = $query->latest()->get();
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
