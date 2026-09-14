<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Display a listing of sales transactions with filters & stats.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'items.product'])->latest('sold_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('payment_method') && in_array($request->payment_method, ['cash', 'credit'])) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('sold_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('sold_at', '<=', $request->end_date);
        }

        $sales = $query->paginate(15)->withQueryString();

        // Today's summary metrics
        $today = now()->toDateString();
        $stats = [
            'today_count' => Sale::whereDate('sold_at', $today)->count(),
            'today_revenue' => (float) Sale::whereDate('sold_at', $today)->sum('total'),
            'today_cash_revenue' => (float) Sale::whereDate('sold_at', $today)->where('payment_method', 'cash')->sum('total'),
            'today_credit_count' => Sale::whereDate('sold_at', $today)->where('payment_method', 'credit')->count(),
            'today_credit_amount' => (float) Sale::whereDate('sold_at', $today)->where('payment_method', 'credit')->sum('total'),
        ];

        return view('sales.index', compact('sales', 'stats'));
    }

    /**
     * Show the interactive POS cashier interface.
     */
    public function create()
    {
        $products = Product::with('category:id,name')
            ->select(['id', 'category_id', 'code', 'barcode', 'name', 'unit', 'selling_price', 'stock', 'min_stock'])
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'address']);

        // Generate suggested sequential invoice number
        $todayPrefix = 'INV-'.date('Ymd').'-';
        $lastSale = Sale::where('invoice_no', 'like', $todayPrefix.'%')->latest('id')->first();
        $sequence = 1;
        if ($lastSale) {
            $lastSeq = (int) substr($lastSale->invoice_no, -4);
            $sequence = $lastSeq + 1;
        }
        $suggestedInvoiceNo = $todayPrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return view('sales.create', compact('products', 'categories', 'customers', 'suggestedInvoiceNo'));
    }

    /**
     * Store a new sale transaction atomically with stock decrement,
     * daily sales aggregation, and receivable creation if credit.
     */
    public function store(StoreSaleRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // Aggregate quantities by product_id
            $groupedItems = [];
            foreach ($request->items as $item) {
                $pid = (int) $item['product_id'];
                $qty = (int) $item['quantity'];
                if ($qty > 0) {
                    $groupedItems[$pid] = ($groupedItems[$pid] ?? 0) + $qty;
                }
            }

            if (empty($groupedItems)) {
                throw ValidationException::withMessages(['items' => 'Keranjang belanja tidak boleh kosong.']);
            }

            // Lock products for update and validate stock
            $productIds = array_keys($groupedItems);
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $itemsData = [];
            $subtotal = 0;

            foreach ($groupedItems as $productId => $qty) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Produk dengan ID {$productId} tidak ditemukan.",
                    ]);
                }

                // Business Rule PRD §2.2: Check available stock before proceeding
                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock} {$product->unit}, Diminta: {$qty} {$product->unit}).",
                    ]);
                }

                $itemPrice = (float) $product->selling_price;
                $itemSubtotal = $itemPrice * $qty;
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $itemPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = $subtotal;

            // Validate cash payment amount if cash
            if ($request->payment_method === 'cash') {
                $cashAmount = (float) ($request->cash_amount ?? 0);
                if ($cashAmount < $total) {
                    throw ValidationException::withMessages([
                        'cash_amount' => 'Nominal uang tunai yang diterima (Rp '.number_format($cashAmount, 0, ',', '.').') kurang dari total tagihan (Rp '.number_format($total, 0, ',', '.').').',
                    ]);
                }
            }

            // Generate unique sequential invoice number
            $todayPrefix = 'INV-'.date('Ymd').'-';
            $lastSale = Sale::where('invoice_no', 'like', $todayPrefix.'%')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $sequence = 1;
            if ($lastSale) {
                $lastSeq = (int) substr($lastSale->invoice_no, -4);
                $sequence = $lastSeq + 1;
            }
            $invoiceNo = $todayPrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            // 1. Create Sale Header
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'user_id' => Auth::id() ?? 1,
                'customer_id' => $request->customer_id ?: null,
                'payment_method' => $request->payment_method,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            // 2. Create Sale Items, Decrement Product Stock, Update Daily Sales Summary
            $saleDate = $sale->sold_at->toDateString();

            foreach ($itemsData as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Atomic stock decrement
                $item['product']->decrement('stock', $item['quantity']);

                // Accumulate daily sales for Demand Forecasting (SMA 7-Day)
                $summary = DailySalesSummary::firstOrCreate(
                    ['product_id' => $item['product_id'], 'sale_date' => $saleDate],
                    ['quantity_sold' => 0]
                );
                $summary->increment('quantity_sold', $item['quantity']);
            }

            // 3. Create Receivable if payment method is credit
            if ($request->payment_method === 'credit') {
                Receivable::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $request->customer_id,
                    'total_amount' => $total,
                    'paid_amount' => 0,
                    'remaining_balance' => $total,
                    'status' => 'belum_lunas',
                ]);
            }

            $cashPaid = (float) ($request->cash_amount ?? 0);
            $change = $request->payment_method === 'cash' ? max(0, $cashPaid - $total) : 0;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$sale->invoice_no} berhasil disimpan.",
                    'sale_id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'total' => $total,
                    'cash_amount' => $cashPaid,
                    'change' => $change,
                    'redirect_url' => route('sales.show', $sale),
                    'print_url' => route('sales.print', $sale),
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', "Transaksi {$sale->invoice_no} berhasil diselesaikan!");
        });
    }

    /**
     * Display the specified sale details.
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product.category', 'receivable.payments']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Render printable thermal receipt view.
     */
    public function printReceipt(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'receivable']);

        return view('sales.receipt', compact('sale'));
    }

    /**
     * Download official sales invoice as PDF.
     */
    public function downloadPdf(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product.category', 'receivable']);

        $pdf = Pdf::loadView('sales.receipt-pdf', compact('sale'))
            ->setPaper('a5', 'portrait');

        return $pdf->download("Faktur-{$sale->invoice_no}.pdf");
    }
}
