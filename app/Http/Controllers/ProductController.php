<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    private array $agriculturalUnits = [
        'Botol',
        'Sak',
        'Kg',
        'Liter',
        'Bungkus',
        'Pcs',
        'Sachet',
        'Roll',
    ];

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockStatus = $request->query('stock_status');

        $query = Product::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($stockStatus === 'out') {
            $query->where('stock', '<=', 0);
        } elseif ($stockStatus === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
        } elseif ($stockStatus === 'safe') {
            $query->whereColumn('stock', '>', 'min_stock');
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        // Metrics for summary badges
        $totalProductsCount = Product::count();
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();

        return view('products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'stockStatus',
            'totalProductsCount',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $units = $this->agriculturalUnits;

        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', "Produk '{$product->name}' berhasil ditambahkan ke inventori.");
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'restocks' => fn ($q) => $q->latest()->take(5)]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $units = $this->agriculturalUnits;

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', "Data produk '{$product->name}' berhasil diperbarui.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Prevent deletion if product already has sale transaction history
        if ($product->saleItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', "Produk '{$product->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi penjualan.");
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Produk '{$name}' berhasil dihapus dari sistem.");
    }

    public function barcode(Product $product): View
    {
        return view('products.barcode', compact('product'));
    }
}
