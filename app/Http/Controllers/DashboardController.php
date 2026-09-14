<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ForecastResult;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;

class DashboardController extends Controller
{
    /**
     * Display the Executive Analytics Dashboard with real-time KPIs,
     * 14-day sales trend (Chart.js), restock alerts, and debtor rankings.
     */
    public function index()
    {
        $today = now()->toDateString();

        // 1. KPI Metrics
        $stats = [
            'today_sales_revenue' => (float) Sale::whereDate('sold_at', $today)->sum('total'),
            'today_sales_count' => Sale::whereDate('sold_at', $today)->count(),
            'month_sales_revenue' => (float) Sale::whereMonth('sold_at', now()->month)
                ->whereYear('sold_at', now()->year)
                ->sum('total'),
            'month_sales_count' => Sale::whereMonth('sold_at', now()->month)
                ->whereYear('sold_at', now()->year)
                ->count(),
            'total_active_debt' => (float) Receivable::where('status', 'belum_lunas')->sum('remaining_balance'),
            'total_active_debtors' => Receivable::where('status', 'belum_lunas')->distinct('customer_id')->count('customer_id'),
            'sma_needs_restock_count' => ForecastResult::where('status', 'perlu_restok')->count(),
            'low_stock_count' => Product::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count(),
            'out_of_stock_count' => Product::where('stock', '<=', 0)->count(),
            'total_products_count' => Product::count(),
        ];

        // 2. 14-Day Sales Trend Data for Chart.js
        $chartLabels = [];
        $chartTotalSales = [];
        $chartCashSales = [];
        $chartCreditSales = [];

        $sales14Days = Sale::where('sold_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['sold_at', 'total', 'payment_method']);

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartLabels[] = now()->subDays($i)->format('d M');

            $daysSales = $sales14Days->filter(fn ($s) => $s->sold_at->toDateString() === $date);
            $chartTotalSales[] = (float) $daysSales->sum('total');
            $chartCashSales[] = (float) $daysSales->where('payment_method', 'cash')->sum('total');
            $chartCreditSales[] = (float) $daysSales->where('payment_method', 'credit')->sum('total');
        }

        $chartData = [
            'labels' => $chartLabels,
            'total' => $chartTotalSales,
            'cash' => $chartCashSales,
            'credit' => $chartCreditSales,
        ];

        // 3. Urgent Restock Alerts (SMA Needs Restock or Stock <= min_stock)
        $urgentRestocks = Product::with(['category', 'forecastResults'])
            ->where(function ($q) {
                $q->whereColumn('stock', '<=', 'min_stock')
                    ->orWhereHas('forecastResults', fn ($fq) => $fq->where('status', 'perlu_restok'));
            })
            ->orderBy('stock')
            ->take(5)
            ->get();

        // 4. Top 5 Debtors (Customers with highest unpaid balance)
        $topDebtors = Customer::whereHas('receivables', function ($q) {
            $q->where('status', 'belum_lunas');
        })
            ->withSum(['receivables' => function ($q) {
                $q->where('status', 'belum_lunas');
            }], 'remaining_balance')
            ->orderByDesc('receivables_sum_remaining_balance')
            ->take(5)
            ->get();

        // 5. Recent 5 Sales
        $recentSales = Sale::with(['customer', 'user'])
            ->latest('sold_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'chartData',
            'urgentRestocks',
            'topDebtors',
            'recentSales'
        ));
    }
}
