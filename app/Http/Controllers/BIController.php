<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BIController extends Controller
{
    // ========== MAIN DASHBOARD ==========

    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');
        [$startDate, $endDate] = $this->resolvePeriod($period, $request);

        // -- KPIs الرئيسية --
        $kpis = $this->calculateKPIs($startDate, $endDate);

        // -- مقارنة بالفترة السابقة --
        $previousKpis = $this->calculateKPIs(
            ...$this->getPreviousPeriod($startDate, $endDate)
        );

        // -- P&L مختصر --
        $pnl = $this->getProfitAndLoss($startDate, $endDate);

        // -- أفضل المنتجات --
        $topProducts = $this->getTopProducts($startDate, $endDate, 10);

        // -- أفضل العملاء --
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 10);

        // -- بيانات الرسم البياني --
        $revenueChart = $this->getRevenueChartData($startDate, $endDate);

        // -- BI السريع: معدل التحويل والمتوسطات --
        $salesIntelligence = $this->getSalesIntelligence($startDate, $endDate);

        return view('pages.bi.index', compact(
            'kpis', 'previousKpis', 'pnl', 'topProducts',
            'topCustomers', 'revenueChart', 'salesIntelligence',
            'startDate', 'endDate', 'period'
        ));
    }

    // ========== P&L Yearly ==========

    public function pnl(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyRevenue = DB::table('transactions')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as tx_count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()->keyBy('month');

        $monthlyCogs = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('product_batches', 'product_batches.product_id', '=', 'transaction_items.product_id')
            ->whereYear('transactions.created_at', $year)
            ->where('transaction_items.is_service', false)
            ->select(
                DB::raw('MONTH(transactions.created_at) as month'),
                DB::raw('SUM(product_batches.unit_cost * transaction_items.qty) as cogs')
            )
            ->groupBy(DB::raw('MONTH(transactions.created_at)'))
            ->get()->keyBy('month');

        $monthlyExpenses = Expense::approved()
            ->whereYear('expense_date', $year)
            ->select(DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as expenses'))
            ->groupBy(DB::raw('MONTH(expense_date)'))
            ->get()->keyBy('month');

        $pnlTable = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev   = (float) ($monthlyRevenue[$m]->revenue ?? 0);
            $cogs  = (float) ($monthlyCogs[$m]->cogs ?? 0);
            $exp   = (float) ($monthlyExpenses[$m]->expenses ?? 0);
            $gross = $rev - $cogs;
            $net   = $gross - $exp;

            $pnlTable[$m] = [
                'month'        => Carbon::createFromDate($year, $m, 1)->format('F'),
                'revenue'      => $rev,
                'cogs'         => $cogs,
                'gross_profit' => $gross,
                'expenses'     => $exp,
                'net_profit'   => $net,
                'tx_count'     => (int) ($monthlyRevenue[$m]->tx_count ?? 0),
            ];
        }

        return view('pages.bi.pnl', compact('pnlTable', 'year'));
    }

    // ========== PRODUCT ANALYTICS ==========

    public function products(Request $request)
    {
        $period = $request->get('period', 'this_month');
        [$startDate, $endDate] = $this->resolvePeriod($period, $request);

        $products = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'products.id', 'products.name', 'products.category',
                DB::raw('SUM(transaction_items.qty) as units_sold'),
                DB::raw('SUM(transaction_items.line_total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.category')
            ->orderByDesc('revenue')
            ->get();

        return view('pages.bi.products', compact('products', 'startDate', 'endDate', 'period'));
    }

    // ========== FORECASTING ==========

    public function forecast()
    {
        $historical = DB::table('transactions')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as revenue'))
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

        $revenues = $historical->pluck('revenue')->map(fn($v) => (float)$v)->toArray();
        $forecast = count($revenues) >= 3 ? $this->movingAverage($revenues, 3, 3) : [];

        return view('pages.bi.forecast', compact('historical', 'forecast'));
    }

    // ========== HELPERS ==========

    private function calculateKPIs($start, $end)
    {
        $revenue = DB::table('transactions')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total');
        $txCount = DB::table('transactions')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->count();
        $expenses = Expense::approved()->whereBetween('expense_date', [$start, $end])->sum('amount');

        return (object) [
            'revenue' => (float)$revenue,
            'tx_count' => $txCount,
            'expenses' => (float)$expenses,
            'aov' => $txCount > 0 ? round($revenue / $txCount, 2) : 0,
        ];
    }

    private function getProfitAndLoss($start, $end)
    {
        $rev = DB::table('transactions')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])->sum('total');
        $exp = Expense::approved()->whereBetween('expense_date', [$start, $end])->sum('amount');
        return (object) ['revenue' => $rev, 'expenses' => $exp, 'net' => $rev - $exp];
    }

    private function getTopProducts($start, $end, $limit)
    {
        return DB::table('transaction_items')->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->whereBetween('transactions.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->select('products.name', 'products.category', DB::raw('SUM(transaction_items.qty) as units'), DB::raw('SUM(transaction_items.line_total) as revenue'))
            ->groupBy('products.id', 'products.name', 'products.category')->orderByDesc('revenue')->limit($limit)->get();
    }

    private function getTopCustomers($start, $end, $limit)
    {
        return DB::table('transactions')->join('customers', 'customers.id', '=', 'transactions.customer_id')
            ->whereNotNull('transactions.customer_id')->whereBetween('transactions.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->select('customers.name', DB::raw('COUNT(transactions.id) as orders'), DB::raw('SUM(transactions.total) as total'))
            ->groupBy('customers.id', 'customers.name')->orderByDesc('total')->limit($limit)->get();
    }

    private function getRevenueChartData($start, $end)
    {
        $daily = DB::table('transactions')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy(DB::raw('DATE(created_at)'))->pluck('revenue', 'date');

        $labels = []; $data = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            $labels[] = $date->format('M d');
            $data[] = (float)($daily[$date->format('Y-m-d')] ?? 0);
        }
        return compact('labels', 'data');
    }

    private function getSalesIntelligence($start, $end)
    {
        return (object) [
           'peak_day' => DB::table('transactions')->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
                ->groupBy('date')->orderByDesc('revenue')->first()
        ];
    }

    private function movingAverage($data, $window, $periods)
    {
        $forecast = [];
        for($i=0; $i<$periods; $i++) {
            $slice = array_slice($data, -$window);
            $avg = array_sum($slice) / count($slice);
            $forecast[] = round($avg, 2);
            $data[] = $avg;
        }
        return $forecast;
    }

    private function resolvePeriod($period, Request $request)
    {
        return match ($period) {
            'today'        => [now()->toDateString(), now()->toDateString()],
            'this_week'    => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'this_month'   => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'this_year'    => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom'       => [$request->start_date, $request->end_date],
            default        => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }

    private function getPreviousPeriod($start, $end)
    {
        $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $prevEnd = Carbon::parse($start)->subDay()->toDateString();
        $prevStart = Carbon::parse($prevEnd)->subDays($days - 1)->toDateString();
        return [$prevStart, $prevEnd];
    }
}
