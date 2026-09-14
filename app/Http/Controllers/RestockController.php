<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestockRequest;
use App\Models\Product;
use App\Models\Restock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestockController extends Controller
{
    /**
     * Display a listing of incoming stock restocks with KPI stats and filters.
     */
    public function index(Request $request)
    {
        $query = Restock::with(['product.category'])->latest('restock_date')->latest('id');

        // Filter search (Product Name, SKU, Notes)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
            });
        }

        // Filter specific product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter date range
        if ($request->filled('start_date')) {
            $query->whereDate('restock_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('restock_date', '<=', $request->end_date);
        }

        $restocks = $query->paginate(15)->withQueryString();

        // KPI Summary Stats
        $stats = [
            'total_records' => Restock::count(),
            'this_month_units' => (int) Restock::whereMonth('restock_date', now()->month)
                ->whereYear('restock_date', now()->year)
                ->sum('quantity'),
            'total_stocked_products' => Restock::distinct('product_id')->count('product_id'),
        ];

        $products = Product::orderBy('name')->get(['id', 'name', 'code', 'unit', 'stock']);

        return view('restocks.index', compact('restocks', 'stats', 'products'));
    }

    /**
     * Show the form for creating a new restock record.
     * Supports pre-selecting product & pre-filling quantity from SMA Forecast recommendation.
     */
    public function create(Request $request)
    {
        $products = Product::with('category:id,name')
            ->select(['id', 'category_id', 'code', 'name', 'unit', 'stock', 'purchase_price'])
            ->orderBy('name')
            ->get();

        $selectedProductId = $request->query('product_id');
        $suggestedQuantity = max(1, (int) $request->query('quantity', 1));

        return view('restocks.create', compact('products', 'selectedProductId', 'suggestedQuantity'));
    }

    /**
     * Store a newly created restock in storage and increment product stock atomically.
     */
    public function store(StoreRestockRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::where('id', $request->product_id)->lockForUpdate()->firstOrFail();

            Restock::create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'restock_date' => $request->restock_date,
                'notes' => $request->notes,
            ]);

            // Atomic stock increment
            $product->increment('stock', $request->quantity);

            return $product;
        });

        return redirect()->route('restocks.index')
            ->with('success', "Pasokan barang '{$product->name}' sebanyak {$request->quantity} {$product->unit} berhasil dicatat. Stok saat ini: {$product->fresh()->stock} {$product->unit}.");
    }

    /**
     * Display the specified restock document.
     */
    public function show(Restock $restock)
    {
        $restock->load(['product.category']);

        return view('restocks.show', compact('restock'));
    }
}
