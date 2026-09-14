<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\ForecastResult;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display Report Center navigation hub.
     */
    public function index()
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        // Quick Highlights for Report Hub Cards
        $salesStats = [
            'today_sales' => (float) Sale::whereDate('sold_at', $today)->sum('total'),
            'month_sales' => (float) Sale::whereMonth('sold_at', $thisMonth)->whereYear('sold_at', $thisYear)->sum('total'),
            'month_transactions' => Sale::whereMonth('sold_at', $thisMonth)->whereYear('sold_at', $thisYear)->count(),
        ];

        $receivableStats = [
            'total_active_balance' => (float) Receivable::where('status', 'belum_lunas')->sum('remaining_balance'),
            'active_debtors_count' => Receivable::where('status', 'belum_lunas')->distinct('customer_id')->count('customer_id'),
            'total_credit_issued' => (float) Receivable::sum('total_amount'),
        ];

        $inventoryStats = [
            'total_products' => Product::count(),
            'total_stock_units' => (int) Product::sum('stock'),
            'low_stock_count' => Product::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count(),
            'out_of_stock_count' => Product::where('stock', '<=', 0)->count(),
            'sma_restock_count' => ForecastResult::where('status', 'perlu_restok')->count(),
            'total_asset_value' => (float) Product::all()->sum(fn ($p) => $p->purchase_price * max(0, $p->stock)),
        ];

        return view('reports.index', compact('salesStats', 'receivableStats', 'inventoryStats'));
    }

    /**
     * Sales Report (Web Preview with Filters).
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method', 'all');

        $query = Sale::with(['customer', 'user', 'items.product'])
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate);

        if ($paymentMethod !== 'all' && in_array($paymentMethod, ['cash', 'credit'])) {
            $query->where('payment_method', $paymentMethod);
        }

        // Calculate summary metrics on full filtered query
        $allFilteredSales = (clone $query)->get();
        $saleIds = $allFilteredSales->pluck('id');

        $summary = [
            'total_sales' => (float) $allFilteredSales->sum('total'),
            'cash_sales' => (float) $allFilteredSales->where('payment_method', 'cash')->sum('total'),
            'credit_sales' => (float) $allFilteredSales->where('payment_method', 'credit')->sum('total'),
            'total_transactions' => $allFilteredSales->count(),
            'total_items_sold' => (int) SaleItem::whereIn('sale_id', $saleIds)->sum('quantity'),
        ];

        $sales = $query->latest('sold_at')->paginate(15)->withQueryString();

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_method' => $paymentMethod,
        ];

        return view('reports.sales', compact('sales', 'summary', 'filters'));
    }

    /**
     * Sales Report (Export PDF).
     */
    public function salesPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method', 'all');

        $query = Sale::with(['customer', 'user', 'items.product'])
            ->whereDate('sold_at', '>=', $startDate)
            ->whereDate('sold_at', '<=', $endDate);

        if ($paymentMethod !== 'all' && in_array($paymentMethod, ['cash', 'credit'])) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->orderBy('sold_at')->get();
        $saleIds = $sales->pluck('id');

        $summary = [
            'total_sales' => (float) $sales->sum('total'),
            'cash_sales' => (float) $sales->where('payment_method', 'cash')->sum('total'),
            'credit_sales' => (float) $sales->where('payment_method', 'credit')->sum('total'),
            'total_transactions' => $sales->count(),
            'total_items_sold' => (int) SaleItem::whereIn('sale_id', $saleIds)->sum('quantity'),
        ];

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_method' => $paymentMethod,
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('reports.sales-pdf', compact('sales', 'summary', 'filters', 'generatedAt'))
            ->setPaper('a4', 'landscape');

        $filename = "laporan-penjualan-{$startDate}-sd-{$endDate}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Receivables Report (Web Preview with Filters).
     */
    public function receivables(Request $request)
    {
        $status = $request->input('status', 'all');
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Receivable::with(['customer', 'sale', 'payments']);

        if ($status !== 'all' && in_array($status, ['belum_lunas', 'lunas'])) {
            $query->where('status', $status);
        }

        if (! empty($customerId)) {
            $query->where('customer_id', $customerId);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $query->whereHas('sale', function ($sq) use ($startDate, $endDate) {
                $sq->whereDate('sold_at', '>=', $startDate)->whereDate('sold_at', '<=', $endDate);
            });
        }

        // Summary calculations
        $allFiltered = (clone $query)->get();
        $summary = [
            'total_credit_issued' => (float) $allFiltered->sum('total_amount'),
            'total_paid' => (float) $allFiltered->sum('paid_amount'),
            'total_remaining_balance' => (float) $allFiltered->sum('remaining_balance'),
            'total_records' => $allFiltered->count(),
            'unpaid_count' => $allFiltered->where('status', 'belum_lunas')->count(),
            'paid_count' => $allFiltered->where('status', 'lunas')->count(),
        ];

        $receivables = $query->orderByDesc('remaining_balance')
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get();
        $filters = [
            'status' => $status,
            'customer_id' => $customerId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return view('reports.receivables', compact('receivables', 'summary', 'filters', 'customers'));
    }

    /**
     * Receivables Report (Export PDF).
     */
    public function receivablesPdf(Request $request)
    {
        $status = $request->input('status', 'all');
        $customerId = $request->input('customer_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Receivable::with(['customer', 'sale', 'payments']);

        if ($status !== 'all' && in_array($status, ['belum_lunas', 'lunas'])) {
            $query->where('status', $status);
        }

        if (! empty($customerId)) {
            $query->where('customer_id', $customerId);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $query->whereHas('sale', function ($sq) use ($startDate, $endDate) {
                $sq->whereDate('sold_at', '>=', $startDate)->whereDate('sold_at', '<=', $endDate);
            });
        }

        $receivables = $query->orderByDesc('remaining_balance')
            ->orderBy('due_date')
            ->get();

        $summary = [
            'total_credit_issued' => (float) $receivables->sum('total_amount'),
            'total_paid' => (float) $receivables->sum('paid_amount'),
            'total_remaining_balance' => (float) $receivables->sum('remaining_balance'),
            'total_records' => $receivables->count(),
            'unpaid_count' => $receivables->where('status', 'belum_lunas')->count(),
            'paid_count' => $receivables->where('status', 'lunas')->count(),
        ];

        $selectedCustomer = ! empty($customerId) ? Customer::find($customerId) : null;
        $filters = [
            'status' => $status,
            'customer_id' => $customerId,
            'customer_name' => $selectedCustomer?->name ?? 'Semua Petani / Pelanggan',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('reports.receivables-pdf', compact('receivables', 'summary', 'filters', 'generatedAt'))
            ->setPaper('a4', 'landscape');

        $dateStr = now()->format('Ymd');
        $filename = "laporan-piutang-petani-{$dateStr}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Product Inventory & SMA Restock Report (Web Preview with Filters).
     */
    public function products(Request $request)
    {
        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('stock_status', 'all');

        $query = Product::with(['category', 'forecastResults']);

        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'low_stock') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        } elseif ($stockStatus === 'sma_needs_restock') {
            $query->whereHas('forecastResults', fn ($q) => $q->where('status', 'perlu_restok'));
        }

        // Summary calculations
        $allFiltered = (clone $query)->get();

        $summary = [
            'total_products' => $allFiltered->count(),
            'total_units' => (int) $allFiltered->sum('stock'),
            'total_purchase_valuation' => (float) $allFiltered->sum(fn ($p) => $p->purchase_price * max(0, $p->stock)),
            'total_selling_valuation' => (float) $allFiltered->sum(fn ($p) => $p->selling_price * max(0, $p->stock)),
            'low_stock_count' => $allFiltered->filter(fn ($p) => $p->stock <= $p->min_stock && $p->stock > 0)->count(),
            'out_of_stock_count' => $allFiltered->filter(fn ($p) => $p->stock <= 0)->count(),
            'sma_needs_restock_count' => $allFiltered->filter(fn ($p) => $p->forecastResults->first()?->status === 'perlu_restok')->count(),
        ];

        $products = $query->orderBy('category_id')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $filters = [
            'category_id' => $categoryId,
            'stock_status' => $stockStatus,
        ];

        return view('reports.products', compact('products', 'summary', 'filters', 'categories'));
    }

    /**
     * Product Inventory & SMA Restock Report (Export PDF).
     */
    public function productsPdf(Request $request)
    {
        $categoryId = $request->input('category_id');
        $stockStatus = $request->input('stock_status', 'all');

        $query = Product::with(['category', 'forecastResults']);

        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'low_stock') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        } elseif ($stockStatus === 'sma_needs_restock') {
            $query->whereHas('forecastResults', fn ($q) => $q->where('status', 'perlu_restok'));
        }

        $products = $query->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $summary = [
            'total_products' => $products->count(),
            'total_units' => (int) $products->sum('stock'),
            'total_purchase_valuation' => (float) $products->sum(fn ($p) => $p->purchase_price * max(0, $p->stock)),
            'total_selling_valuation' => (float) $products->sum(fn ($p) => $p->selling_price * max(0, $p->stock)),
            'low_stock_count' => $products->filter(fn ($p) => $p->stock <= $p->min_stock && $p->stock > 0)->count(),
            'out_of_stock_count' => $products->filter(fn ($p) => $p->stock <= 0)->count(),
            'sma_needs_restock_count' => $products->filter(fn ($p) => $p->forecastResults->first()?->status === 'perlu_restok')->count(),
        ];

        $selectedCategory = ! empty($categoryId) ? Category::find($categoryId) : null;
        $filters = [
            'category_id' => $categoryId,
            'category_name' => $selectedCategory?->name ?? 'Semua Kategori',
            'stock_status' => $stockStatus,
        ];

        $generatedAt = now();

        $pdf = Pdf::loadView('reports.products-pdf', compact('products', 'summary', 'filters', 'generatedAt'))
            ->setPaper('a4', 'landscape');

        $dateStr = now()->format('Ymd');
        $filename = "laporan-inventori-stok-{$dateStr}.pdf";

        return $pdf->stream($filename);
    }
}
