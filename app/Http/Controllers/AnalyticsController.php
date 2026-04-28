<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\ReturnModel;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AnalyticsController extends Controller
{
    protected $currency;
    protected $taxRate;

    public function __construct()
    {
        $this->currency = DB::table('settings')->where('key', 'currency')->value('value') ?: 'EGP';
        $this->taxRate = DB::table('settings')->where('key', 'tax_rate')->value('value') ?: 14;
        
        view()->share('currency', $this->currency);
        view()->share('taxRate', $this->taxRate);
    }

    public function executive(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $kpis = $this->calculateExecutiveKPIs($start, $end);
        [$prevStart, $prevEnd] = $this->getPreviousPeriod($start, $end);
        $prevKpis = $this->calculateExecutiveKPIs($prevStart, $prevEnd);
        $trend = $this->getRevenueTrend($start, $end);
        $forecast = $this->getForecastData();
        $alerts = [
            'overdue_debts' => DB::table('transactions')
                ->where('due_amount', '>', 0)
                ->where('due_date', '<', now())
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('customers')
                        ->whereRaw('customers.id = transactions.customer_id')
                        ->where('credit_balance', '>', 0);
                })
                ->count(),
            'low_stock' => DB::table('products')
                ->where('is_service', false)
                ->whereRaw("(SELECT COALESCE(SUM(qty), 0) FROM product_batches WHERE product_id = products.id) <= (CASE WHEN products.low_stock_threshold > 0 THEN products.low_stock_threshold ELSE (SELECT CAST(value AS UNSIGNED) FROM settings WHERE `key` = 'low_stock_default' LIMIT 1) END)")
                ->count()
        ];
        $miniCharts = [
            'top_products' => $this->getTopProducts($start, $end, 5),
            'payment_distribution' => $this->getPaymentDistribution($start, $end),
            'return_rate' => $this->getReturnRate($start, $end)
        ];
        return view('pages.analytics.executive', compact('start', 'end', 'period', 'kpis', 'prevKpis', 'trend', 'forecast', 'miniCharts', 'alerts'));
    }

    public function sales(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $salesData = $this->getSalesBreakdown($start, $end);
        $abcAnalysis = $this->getABCAnalysis($salesData);
        $heatmap = $this->getSalesHeatmap($start, $end);
        $paymentTrend = $this->getPaymentTrend($start, $end);
        $offerImpact = $this->getOfferImpact($start, $end);
        return view('pages.analytics.sales', compact('start', 'end', 'period', 'salesData', 'abcAnalysis', 'heatmap', 'paymentTrend', 'offerImpact'));
    }

    public function inventory(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $stockHealth = $this->getStockHealth();
        $valuation = $this->getInventoryValuation();
        $reorderPrediction = $this->getReorderPrediction();
        $storageDistribution = $this->getStorageDistribution();
        $expiryTracker = $this->getExpiryTracker();
        return view('pages.analytics.inventory', compact('start', 'end', 'period', 'stockHealth', 'valuation', 'reorderPrediction', 'storageDistribution', 'expiryTracker'));
    }

    public function finance(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $pnl = $this->getDetailedPNL($start, $end);
        $cashFlow = $this->getCashFlowData($start, $end);
        $expenseStats = $this->getExpenseAnalysis($start, $end);
        $debtors = $this->getDebtTracker();
        $taxStats = $this->getTaxSummary($start, $end);
        return view('pages.analytics.finance', compact('start', 'end', 'period', 'pnl', 'cashFlow', 'expenseStats', 'debtors', 'taxStats'));
    }

    public function customers(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $rfm = $this->getRFMAnalysis($start, $end);
        $clv = $this->getCLVData($start, $end);
        $loyalty = $this->getLoyaltyStats();
        $churn = $this->getChurnRiskData();
        return view('pages.analytics.customers', compact('start', 'end', 'period', 'rfm', 'clv', 'loyalty', 'churn'));
    }

    public function operations(Request $request)
    {
        [$start, $end, $period] = $this->resolveDates($request);
        $returns = $this->getReturnsAnalysis($start, $end);
        $defective = $this->getDefectiveTracker();
        $warranty = $this->getWarrantyStats();
        $staff = $this->getStaffPerformance($start, $end);
        $audit = $this->getAuditSummary();
        return view('pages.analytics.operations', compact('start', 'end', 'period', 'returns', 'defective', 'warranty', 'staff', 'audit'));
    }

    // ========== PRIVATE HELPERS ==========

    private function calculateExecutiveKPIs($start, $end)
    {
        $revenue = DB::table('transactions')->whereBetween('created_at', [$start, $end])->sum('total');
        $invoicesCount = DB::table('transactions')->whereBetween('created_at', [$start, $end])->count();
        $activeCustomers = DB::table('transactions')->whereBetween('created_at', [$start, $end])->distinct('customer_id')->count('customer_id');
        $expenses = DB::table('expenses')->where('status', 'approved')->whereBetween('expense_date', [substr($start, 0, 10), substr($end, 0, 10)])->sum('amount');
        $cogs = DB::table('transaction_items')->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')->join('product_batches', 'product_batches.product_id', '=', 'transaction_items.product_id')->whereBetween('transactions.created_at', [$start, $end])->where('transaction_items.is_service', false)->sum(DB::raw('product_batches.unit_cost * transaction_items.qty'));
        $netProfit = $revenue - $cogs - $expenses;
        $aov = $invoicesCount > 0 ? $revenue / $invoicesCount : 0;
        return (object) [
            'revenue' => (float)$revenue, 'net_profit' => (float)$netProfit, 'invoices' => $invoicesCount,
            'aov' => (float)$aov, 'active_customers' => $activeCustomers, 'cogs' => (float)$cogs, 'expenses' => (float)$expenses
        ];
    }

    private function getPreviousPeriod($start, $end)
    {
        $startDate = Carbon::parse($start); $endDate = Carbon::parse($end);
        $days = $startDate->diffInDays($endDate) + 1;
        $prevEnd = $startDate->copy()->subSecond(); $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();
        return [$prevStart->toDateTimeString(), $prevEnd->toDateTimeString()];
    }

    private function getRevenueTrend($start, $end)
    {
        $startDate = Carbon::parse($start); $endDate = Carbon::parse($end); $days = $startDate->diffInDays($endDate) + 1;
        $groupBy = $days <= 60 ? 'DATE(created_at)' : 'YEARWEEK(created_at)';
        $format = $days <= 60 ? 'Y-m-d' : 'oW';
        $current = DB::table('transactions')->whereBetween('created_at', [$start, $end])->select(DB::raw("$groupBy as period"), DB::raw('SUM(total) as revenue'))->groupBy('period')->get()->pluck('revenue', 'period');
        $lyStart = $startDate->copy()->subYear(); $lyEnd = $endDate->copy()->subYear();
        $lastYear = DB::table('transactions')->whereBetween('created_at', [$lyStart, $lyEnd])->select(DB::raw("$groupBy as period"), DB::raw('SUM(total) as revenue'))->groupBy('period')->get()->pluck('revenue', 'period');
        $labels = []; $currentData = []; $lastYearData = [];
        foreach (CarbonPeriod::create($startDate, ($days <= 60 ? '1 day' : '1 week'), $endDate) as $date) {
            $key = $date->format($format); $labels[] = $days <= 60 ? $date->format('M d') : 'Week ' . $date->format('W');
            $currentData[] = (float)($current[$key] ?? 0);
            $lyKey = $date->copy()->subYear()->format($format); $lastYearData[] = (float)($lastYear[$lyKey] ?? 0);
        }
        return compact('labels', 'currentData', 'lastYearData');
    }

    private function getForecastData()
    {
        $historyStart = now()->subDays(90)->startOfDay();
        $dailyRevenue = DB::table('transactions')->where('created_at', '>=', $historyStart)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))->groupBy('date')->orderBy('date')->get();
        if ($dailyRevenue->count() < 10) return ['forecast' => [], 'labels' => []];
        $y = $dailyRevenue->pluck('revenue')->toArray(); $x = range(1, count($y)); $n = count($x);
        $sumX = array_sum($x); $sumY = array_sum($y); $sumXX = 0; $sumXY = 0;
        for ($i = 0; $i < $n; $i++) { $sumXX += ($x[$i] * $x[$i]); $sumXY += ($x[$i] * $y[$i]); }
        $m = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $b = ($sumY - $m * $sumX) / $n;
        $forecast = []; $labels = []; $lastDay = Carbon::parse($dailyRevenue->last()->date);
        for ($i = 1; $i <= 30; $i++) { $nextX = $n + $i; $val = ($m * $nextX) + $b; $forecast[] = round(max(0, $val), 2); $labels[] = $lastDay->copy()->addDays($i)->format('M d'); }
        return compact('labels', 'forecast');
    }

    private function getTopProducts($start, $end, $limit)
    {
        return DB::table('transaction_items')->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')->join('products', 'products.id', '=', 'transaction_items.product_id')->whereBetween('transactions.created_at', [$start, $end])->select('products.name', DB::raw('SUM(transaction_items.line_total) as revenue'))->groupBy('products.id', 'products.name')->orderByDesc('revenue')->limit($limit)->get();
    }

    private function getPaymentDistribution($start, $end)
    {
        return DB::table('transactions')->whereBetween('created_at', [$start, $end])->select('payment_method', DB::raw('COUNT(id) as count'), DB::raw('SUM(total) as total'))->groupBy('payment_method')->get();
    }

    private function getReturnRate($start, $end)
    {
        $revenue = DB::table('transactions')->whereBetween('created_at', [$start, $end])->sum('total');
        $returns = DB::table('returns')->whereBetween('created_at', [$start, $end])->sum('refund_amount');
        return $revenue > 0 ? ($returns / $revenue) * 100 : 0;
    }

    private function getSalesBreakdown($start, $end)
    {
        $totalRevenue = DB::table('transactions')->whereBetween('created_at', [$start, $end])->sum('total');
        return DB::table('transaction_items')->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')->join('products', 'products.id', '=', 'transaction_items.product_id')->leftJoin('product_batches', function($join) { $join->on('product_batches.product_id', '=', 'transaction_items.product_id')->whereRaw('product_batches.id = (SELECT id FROM product_batches pb WHERE pb.product_id = products.id ORDER BY id DESC LIMIT 1)'); })->whereBetween('transactions.created_at', [$start, $end])->select('products.name', 'products.category', DB::raw('SUM(transaction_items.qty) as units_sold'), DB::raw('SUM(transaction_items.line_total) as revenue'), DB::raw('AVG(product_batches.unit_cost) as avg_unit_cost'))->groupBy('products.id', 'products.name', 'products.category')->orderByDesc('revenue')->get()->map(function($item) use ($totalRevenue) { $item->cogs = $item->units_sold * $item->avg_unit_cost; $item->gross_margin = $item->revenue > 0 ? (($item->revenue - $item->cogs) / $item->revenue) * 100 : 0; $item->contribution = $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0; return $item; });
    }

    private function getABCAnalysis($salesData)
    {
        $cumulative = 0; $totalRevenue = $salesData->sum('revenue');
        return $salesData->map(function($item) use (&$cumulative, $totalRevenue) { $cumulative += $item->revenue; $pct = $totalRevenue > 0 ? ($cumulative / $totalRevenue) * 100 : 0; if ($pct <= 80) $item->class = 'A'; elseif ($pct <= 95) $item->class = 'B'; else $item->class = 'C'; $item->cumulative_pct = $pct; return $item; });
    }

    private function getSalesHeatmap($start, $end)
    {
        $raw = DB::table('transactions')->whereBetween('created_at', [$start, $end])->select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(id) as count'))->groupBy('day', 'hour')->get();
        $heatmap = []; $dayMap = [7, 1, 2, 3, 4, 5, 6]; foreach ($dayMap as $d) { $heatmap[$d] = array_fill(0, 24, 0); }
        foreach ($raw as $r) { $heatmap[$r->day][$r->hour] = $r->count; }
        return $heatmap;
    }

    private function getPaymentTrend($start, $end)
    {
        $startDate = Carbon::parse($start); $endDate = Carbon::parse($end);
        $raw = DB::table('transactions')->whereBetween('created_at', [$start, $end])->select(DB::raw('DATE(created_at) as date'), 'payment_method', DB::raw('SUM(total) as revenue'))->groupBy('date', 'payment_method')->get()->groupBy('date');
        $methods = ['cash', 'credit', 'card', 'partial', 'debt']; $labels = []; $raw_dates = []; $datasets = []; foreach ($methods as $m) { $datasets[$m] = []; }
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) { $dayStr = $date->toDateString(); $labels[] = $date->format('M d'); $raw_dates[] = $dayStr; $dayData = $raw->get($dayStr, collect()); foreach ($methods as $m) { $datasets[$m][] = (float)($dayData->where('payment_method', $m)->first()->revenue ?? 0); } }
        return ['labels' => collect($labels), 'raw_dates' => collect($raw_dates), 'datasets' => $datasets];
    }

    private function getOfferImpact($start, $end)
    {
        $withOffers = DB::table('transactions')->whereBetween('created_at', [$start, $end])->whereRaw('total < subtotal')->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('SUM(subtotal - total) as discount'))->groupBy('date')->get()->keyBy('date');
        $withoutOffers = DB::table('transactions')->whereBetween('created_at', [$start, $end])->whereRaw('total >= subtotal')->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))->groupBy('date')->get()->keyBy('date');
        return compact('withOffers', 'withoutOffers');
    }

    private function getStockHealth()
    {
        $products = DB::table('products')->leftJoin('product_batches', 'product_batches.product_id', '=', 'products.id')->select('products.id', 'products.low_stock_threshold', DB::raw('SUM(product_batches.qty) as total_qty'))->groupBy('products.id', 'products.low_stock_threshold')->get();
        $expiringCount = DB::table('product_batches')->whereNotNull('expiration_date')->where('qty', '>', 0)->whereBetween('expiration_date', [now()->toDateString(), now()->addDays(30)->toDateString()])->count();
        return (object) ['normal' => $products->where('total_qty', '>', 0)->filter(fn($p) => $p->total_qty > $p->low_stock_threshold)->count(), 'low' => $products->where('total_qty', '>', 0)->filter(fn($p) => $p->total_qty <= $p->low_stock_threshold)->count(), 'out' => $products->where('total_qty', '<=', 0)->count(), 'expiring' => $expiringCount];
    }

    private function getInventoryValuation()
    {
        return DB::table('products')->join('product_batches', 'product_batches.product_id', '=', 'products.id')->select('products.name', 'products.category', 'products.default_price', DB::raw('SUM(product_batches.qty) as current_stock'), DB::raw('AVG(product_batches.unit_cost) as avg_cost'))->where('product_batches.qty', '>', 0)->groupBy('products.id', 'products.name', 'products.category', 'products.default_price')->orderByDesc(DB::raw('SUM(product_batches.qty * product_batches.unit_cost)'))->get()->map(function($p) { $p->total_cost = $p->current_stock * $p->avg_cost; $p->potential_revenue = $p->current_stock * $p->default_price; $p->potential_profit = $p->potential_revenue - $p->total_cost; return $p; });
    }

    private function getReorderPrediction()
    {
        $sales = DB::table('transaction_items')->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')->where('transactions.created_at', '>=', now()->subDays(60))->select('product_id', DB::raw('SUM(qty) / 60 as daily_rate'))->groupBy('product_id')->get()->keyBy('product_id');
        return DB::table('products')->leftJoin('product_batches', 'product_batches.product_id', '=', 'products.id')->leftJoin('purchase_orders', function($join) { $join->on('purchase_orders.product_id', '=', 'products.id')->whereRaw('purchase_orders.id = (SELECT id FROM purchase_orders po WHERE po.product_id = products.id ORDER BY id DESC LIMIT 1)'); })->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->select('products.id', 'products.name', 'suppliers.name as supplier', DB::raw('SUM(product_batches.qty) as current_stock'))->groupBy('products.id', 'products.name', 'suppliers.name')->get()->map(function($p) use ($sales) { $p->daily_rate = (float)($sales[$p->id]->daily_rate ?? 0); $p->days_remaining = $p->daily_rate > 0 ? floor($p->current_stock / $p->daily_rate) : 999; $p->suggested_date = $p->days_remaining < 999 ? now()->addDays($p->days_remaining - 7)->toDateString() : 'N/A'; return $p; })->sortBy('days_remaining');
    }

    private function getStorageDistribution()
    {
        return DB::table('product_batches')->join('products', 'products.id', '=', 'product_batches.product_id')->join('storages', 'storages.id', '=', 'product_batches.storage_id')->select('products.name as product', 'storages.name as storage', DB::raw('SUM(product_batches.qty) as qty'))->groupBy('products.id', 'products.name', 'storages.id', 'storages.name')->orderByRaw('SUM(product_batches.qty) DESC')->limit(20)->get();
    }

    private function getExpiryTracker()
    {
        return DB::table('product_batches')->join('products', 'products.id', '=', 'product_batches.product_id')->join('storages', 'storages.id', '=', 'product_batches.storage_id')->whereNotNull('product_batches.expiration_date')->where('product_batches.qty', '>', 0)->select('products.name as product', 'product_batches.batch_number', 'storages.name as storage', 'product_batches.qty', 'product_batches.expiration_date', DB::raw('DATEDIFF(product_batches.expiration_date, NOW()) as days_until'))->orderBy('product_batches.expiration_date')->get();
    }

    private function getDetailedPNL($start, $end)
    {
        $current = $this->calculateExecutiveKPIs($start, $end);
        [$prevStart, $prevEnd] = $this->getPreviousPeriod($start, $end); $prev = $this->calculateExecutiveKPIs($prevStart, $prevEnd);
        $thisYearStart = now()->startOfYear()->toDateTimeString(); $thisYear = $this->calculateExecutiveKPIs($thisYearStart, now()->toDateTimeString());
        $lastYearStart = now()->subYear()->startOfYear()->toDateTimeString(); $lastYearEnd = now()->subYear()->endOfYear()->toDateTimeString(); $lastYear = $this->calculateExecutiveKPIs($lastYearStart, $lastYearEnd);
        return compact('current', 'prev', 'thisYear', 'lastYear');
    }

    private function getCashFlowData($start, $end)
    {
        $inflows = DB::table('cash_flow_entries')->whereBetween('entry_date', [$start, $end])->where('direction', 'inflow');
        $outflows = DB::table('cash_flow_entries')->whereBetween('entry_date', [$start, $end])->where('direction', 'outflow');
        return ['operating' => ['in' => (float)$inflows->clone()->where('type', 'operating')->sum('amount'), 'out' => (float)$outflows->clone()->where('type', 'operating')->sum('amount')], 'investing' => ['in' => (float)$inflows->clone()->where('type', 'investing')->sum('amount'), 'out' => (float)$outflows->clone()->where('type', 'investing')->sum('amount')], 'financing' => ['in' => (float)$inflows->clone()->where('type', 'financing')->sum('amount'), 'out' => (float)$outflows->clone()->where('type', 'financing')->sum('amount')]];
    }

    private function getExpenseAnalysis($start, $end)
    {
        $treemap = DB::table('expenses')->whereBetween('expense_date', [substr($start, 0, 10), substr($end, 0, 10)])->where('status', 'approved')->select('category', DB::raw('SUM(amount) as total'))->groupBy('category')->get();
        $budgets = DB::table('expense_budgets')->where('year', Carbon::parse($start)->year)->where('month', Carbon::parse($start)->month)->get();
        return compact('treemap', 'budgets');
    }

    private function getDebtTracker()
    {
        return DB::table('customers')->where('credit_balance', '>', 0)->select('name', 'phone', 'credit_balance as outstanding')->orderByDesc('outstanding')->get();
    }

    private function getTaxSummary($start, $end)
    {
        return DB::table('tax_entries')->whereBetween('created_at', [$start, $end])->select('status', DB::raw('SUM(tax_amount) as total'))->groupBy('status')->get()->pluck('total', 'status');
    }

    private function getRFMAnalysis($start, $end)
    {
        $customers = DB::table('transactions')->join('customers', 'customers.id', '=', 'transactions.customer_id')->whereNotNull('transactions.customer_id')->select('customers.id', 'customers.name', DB::raw('DATEDIFF(NOW(), MAX(transactions.created_at)) as recency'), DB::raw('COUNT(transactions.id) as frequency'), DB::raw('SUM(transactions.total) as monetary'))->groupBy('customers.id', 'customers.name')->get();
        if ($customers->isEmpty()) return collect();
        $r_quintiles = $this->getQuintiles($customers->pluck('recency')->toArray(), true); $f_quintiles = $this->getQuintiles($customers->pluck('frequency')->toArray()); $m_quintiles = $this->getQuintiles($customers->pluck('monetary')->toArray());
        return $customers->map(function($c) use ($r_quintiles, $f_quintiles, $m_quintiles) { $r_score = $this->getScore($c->recency, $r_quintiles, true); $f_score = $this->getScore($c->frequency, $f_quintiles); $m_score = $this->getScore($c->monetary, $m_quintiles); $c->r_score = $r_score; $c->f_score = $f_score; $c->m_score = $m_score; $c->segment = $this->getSegment($r_score, $f_score, $m_score); return $c; });
    }

    private function getCLVData($start, $end)
    {
        return DB::table('transactions')->join('customers', 'customers.id', '=', 'transactions.customer_id')->select('customers.name', 'customers.phone', DB::raw('MIN(transactions.created_at) as first_purchase'), DB::raw('MAX(transactions.created_at) as last_purchase'), DB::raw('COUNT(transactions.id) as total_orders'), DB::raw('SUM(transactions.total) as total_spent'), DB::raw('AVG(transactions.total) * COUNT(transactions.id) as clv'))->groupBy('customers.id', 'customers.name', 'customers.phone')->orderByDesc('clv')->limit(20)->get();
    }

    private function getLoyaltyStats()
    {
        return DB::table('customers')->select('name', 'loyalty_points')->orderByDesc('loyalty_points')->limit(10)->get();
    }

    private function getChurnRiskData()
    {
        $txs = DB::table('transactions')->whereNotNull('customer_id')->orderBy('customer_id')->orderBy('created_at')->get()->groupBy('customer_id');
        $risky = collect();
        foreach ($txs as $custId => $customerTxs) {
            if ($customerTxs->count() < 2) continue;
            $gaps = []; for ($i = 1; $i < $customerTxs->count(); $i++) { $gaps[] = Carbon::parse($customerTxs[$i-1]->created_at)->diffInDays(Carbon::parse($customerTxs[$i]->created_at)); }
            $avgGap = array_sum($gaps) / count($gaps); $lastTx = Carbon::parse($customerTxs->last()->created_at); $daysSince = $lastTx->diffInDays(now());
            if ($daysSince > ($avgGap * 2) && $avgGap > 0) { $customer = DB::table('customers')->where('id', $custId)->first(); $risky->push((object)['name' => $customer->name, 'last_purchase' => $lastTx->toDateString(), 'avg_gap' => round($avgGap, 1), 'days_overdue' => round($daysSince - $avgGap, 1)]); }
        }
        return $risky->sortByDesc('days_overdue');
    }

    private function getQuintiles($data, $reverse = false)
    {
        sort($data); if ($reverse) $data = array_reverse($data); $n = count($data); if ($n == 0) return [0,0,0,0];
        return [$data[floor($n * 0.2)] ?? 0, $data[floor($n * 0.4)] ?? 0, $data[floor($n * 0.6)] ?? 0, $data[floor($n * 0.8)] ?? 0];
    }

    private function getScore($val, $quintiles, $reverse = false)
    {
        if ($reverse) { if ($val <= $quintiles[3]) return 5; if ($val <= $quintiles[2]) return 4; if ($val <= $quintiles[1]) return 3; if ($val <= $quintiles[0]) return 2; return 1; }
        if ($val >= $quintiles[3]) return 5; if ($val >= $quintiles[2]) return 4; if ($val >= $quintiles[1]) return 3; if ($val >= $quintiles[0]) return 2; return 1;
    }

    private function getSegment($r, $f, $m)
    {
        if ($r == 5 && $f == 5 && $m == 5) return 'Champions';
        if ($r >= 4 && $f >= 4) return 'Loyal';
        if ($r >= 4 && $f <= 3) return 'Potential Loyalists';
        if ($r <= 2 && $f >= 3) return 'At Risk';
        if ($r <= 2 && $f <= 2) return 'Hibernating';
        return 'About To Sleep';
    }

    private function getReturnsAnalysis($start, $end)
    {
        $kpis = DB::table('returns')->whereBetween('returns.created_at', [$start, $end])->select(DB::raw('COUNT(returns.id) as count'), DB::raw('SUM(returns.refund_amount) as total'))->first();
        $revenue = DB::table('transactions')->whereBetween('transactions.created_at', [$start, $end])->sum('total');
        $rate = $revenue > 0 ? ($kpis->total / $revenue) * 100 : 0;
        $topReturned = DB::table('return_items')->join('returns', 'returns.id', '=', 'return_items.return_id')->join('products', 'products.id', '=', 'return_items.product_id')->whereBetween('returns.created_at', [$start, $end])->select('products.name', DB::raw('COUNT(return_items.id) as count'), DB::raw('SUM(return_items.qty) as qty'))->groupBy('products.id', 'products.name')->orderByDesc('count')->limit(10)->get();
        return compact('kpis', 'rate', 'topReturned');
    }

    private function getDefectiveTracker()
    {
        return DB::table('defective_products')->join('products', 'products.id', '=', 'defective_products.product_id')->join('suppliers', 'suppliers.id', '=', 'defective_products.supplier_id')->select('products.name as product', 'suppliers.name as supplier', 'defective_products.status', 'defective_products.created_at')->orderByDesc('defective_products.created_at')->limit(20)->get();
    }

    private function getWarrantyStats()
    {
        $now = now()->toDateString(); $soon = now()->addDays(30)->toDateString();
        $expiring = DB::table('warranties')->whereBetween('end_date', [$now, $soon])->count();
        $claims = DB::table('warranty_claims')->count();
        return (object) ['expiring' => $expiring, 'claims' => $claims];
    }

    private function getStaffPerformance($start, $end)
    {
        return DB::table('transactions')->join('users', 'users.id', '=', 'transactions.user_id')->whereBetween('transactions.created_at', [$start, $end])->select('users.name', DB::raw('COUNT(transactions.id) as invoices'), DB::raw('SUM(transactions.total) as revenue'), DB::raw('AVG(transactions.total) as aov'))->groupBy('users.id', 'users.name')->orderByDesc('revenue')->get();
    }

    private function getAuditSummary()
    {
        return DB::table('audit_logs')->join('users', 'users.id', '=', 'audit_logs.user_id')->select('users.name', 'audit_logs.action', 'audit_logs.created_at')->orderByDesc('audit_logs.created_at')->limit(20)->get();
    }

    private function resolveDates(Request $request)
    {
        $period = $request->get('period', 'this_month'); $start = null; $end = null;
        switch ($period) {
            case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); break;
            case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); break;
            case 'this_month': $start = now()->startOfMonth(); $end = now()->endOfMonth(); break;
            case 'last_month': $start = now()->subMonth()->startOfMonth(); $end = now()->subMonth()->endOfMonth(); break;
            case 'this_quarter': $start = now()->startOfQuarter(); $end = now()->endOfQuarter(); break;
            case 'this_year': $start = now()->startOfYear(); $end = now()->endOfYear(); break;
            case 'custom': $start = Carbon::parse($request->get('start_date'))->startOfDay(); $end = Carbon::parse($request->get('end_date'))->endOfDay(); break;
            default: $start = now()->startOfMonth(); $end = now()->endOfMonth(); break;
        }
        return [$start->toDateTimeString(), $end->toDateTimeString(), $period];
    }
}
