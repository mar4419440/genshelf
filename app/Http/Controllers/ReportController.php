<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'user', 'items']);

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
                    ->orWhere('items_snapshot', 'like', "%$search%");
            });
        }

        $transactions = $query->latest()->get();
        $summary = $this->getSummary($transactions);

        $topSelling = $this->aggregateSales($transactions, 'desc');
        $leastSelling = $this->aggregateSales($transactions, 'asc');

        $duePayments = Transaction::where('due_amount', '>', 0)
            ->with('customer')
            ->latest('due_date')
            ->get();

        $salesByPOS = Transaction::select('storage_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('storage_id')
            ->with('storage')
            ->get();

        // Dashboard Data (Unified BI)
        $dashboard = $this->getDashboardData();

        // Hierarchical Grouping (Year > Month > Date) for the list
        $groupedTransactions = $transactions->groupBy(function ($tx) {
            return $tx->created_at->format('Y-m');
        });

        return view('pages.reports.index', compact(
            'transactions',
            'summary',
            'topSelling',
            'leastSelling',
            'duePayments',
            'salesByPOS',
            'dashboard',
            'groupedTransactions'
        ));
    }

    public function export(Request $request)
    {
        $query = Transaction::query();
        // Apply same filters as index
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $transactions = $query->latest()->get();

        $csvFileName = 'transactions_export_' . now()->format('YmdHis') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [__('ID'), __('Date'), __('Customer'), __('Total'), __('Paid'), __('Due'), __('Payment Method'), __('Store')];

        $callback = function () use ($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($transactions as $tx) {
                fputcsv($file, [
                    $tx->id,
                    $tx->created_at->format('Y-m-d H:i'),
                    $tx->customer->name ?? 'Walk-in',
                    $tx->total,
                    $tx->paid_amount,
                    $tx->due_amount,
                    $tx->payment_method,
                    $tx->storage->name ?? 'Unknown'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDashboardData()
    {
        $todayStr = now()->format('Y-m-d');

        $todayRevenue = DB::table('transactions')
            ->whereDate('created_at', $todayStr)
            ->sum('total');

        $totalStock = DB::table('product_batches')->sum('qty');

        $lowStockDefault = DB::table('settings')->where('key', 'low_stock_default')->value('value') ?? 5;
        $lowAlerts = DB::table('products')
            ->where('is_service', false)
            ->whereRaw("(SELECT COALESCE(SUM(qty), 0) FROM product_batches WHERE product_id = products.id) <= (CASE WHEN products.low_stock_threshold > 0 THEN products.low_stock_threshold ELSE ? END)", [$lowStockDefault])
            ->count();

        $pendingPO = DB::table('purchase_orders')->where('status', 'pending')->count();

        $weekStart = now()->startOfWeek(0); // Sunday
        $weekData = array_fill(0, 7, 0);
        $txs = DB::table('transactions')
            ->where('created_at', '>=', $weekStart)
            ->get();

        foreach ($txs as $item) {
            $dayIndex = (new \Carbon\Carbon($item->created_at))->dayOfWeek;
            $weekData[$dayIndex] += $item->total;
        }

        $overdueDebts = Transaction::where('due_amount', '>', 0)
            ->where('due_date', '<', now())
            ->whereHas('customer', function ($query) {
                $query->where('credit_balance', '>', 0);
            })
            ->with('customer')
            ->get();

        $storeTotalCost = DB::table('product_batches')->sum(DB::raw('qty * unit_cost'));
        $storeTotalSelling = DB::table('product_batches')
            ->join('products', 'product_batches.product_id', '=', 'products.id')
            ->sum(DB::raw('product_batches.qty * products.default_price'));

        return (object) [
            'todayRevenue' => $todayRevenue,
            'totalStock' => $totalStock,
            'lowAlerts' => $lowAlerts,
            'pendingPO' => $pendingPO,
            'weekData' => $weekData,
            'maxSale' => max($weekData) ?: 1,
            'days' => [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')],
            'todayDay' => now()->dayOfWeek,
            'overdueCount' => $overdueDebts->count(),
            'overdueList' => $overdueDebts,
            'storeTotalCost' => $storeTotalCost,
            'storeTotalSelling' => $storeTotalSelling
        ];
    }

    private function getSummary($transactions)
    {
        $totalRev = $transactions->sum('total');
        $count = $transactions->count();

        $productCosts = DB::table('product_batches')
            ->select('product_id', DB::raw('AVG(unit_cost) as avg_cost'))
            ->groupBy('product_id')
            ->pluck('avg_cost', 'product_id');

        $totalCOGS = 0;
        foreach ($transactions as $tx) {
            $txCogs = 0;
            foreach ($tx->items as $item) {
                if ($item->product_id && isset($productCosts[$item->product_id])) {
                    $txCogs += $productCosts[$item->product_id] * $item->qty;
                }
            }
            $tx->calculated_cogs = $txCogs;
            $totalCOGS += $txCogs;
        }

        return (object) [
            'revenue' => $totalRev,
            'count' => $count,
            'avg' => $count > 0 ? $totalRev / $count : 0,
            'cogs' => $totalCOGS,
            'net_profit' => $totalRev - $totalCOGS
        ];
    }

    private function aggregateSales($transactions, $order = 'desc')
    {
        $productSales = [];
        foreach ($transactions as $tx) {
            $items = $tx->items_snapshot;
            if (is_array($items)) {
                foreach ($items as $item) {
                    $name = $item['name'] ?? 'Unknown';
                    if (!isset($productSales[$name])) {
                        $productSales[$name] = ['name' => $name, 'units' => 0, 'revenue' => 0];
                    }
                    $qty = (int) ($item['qty'] ?? 0);
                    $price = (float) ($item['price'] ?? 0);
                    $productSales[$name]['units'] += $qty;
                    $productSales[$name]['revenue'] += $price * $qty;
                }
            }
        }

        usort($productSales, function ($a, $b) use ($order) {
            return $order === 'desc'
                ? $b['revenue'] <=> $a['revenue']
                : $a['revenue'] <=> $b['revenue'];
        });

        return array_slice($productSales, 0, 10);
    }
}
