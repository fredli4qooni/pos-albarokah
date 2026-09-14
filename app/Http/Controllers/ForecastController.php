<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    /**
     * Display the Demand Forecasting (SMA 7-Day) and Restock Recommendation dashboard.
     */
    public function index(Request $request, ForecastService $forecastService)
    {
        $allResults = $forecastService->calculateAll();

        // Summary Stats across all products
        $stats = [
            'total_products' => $allResults->count(),
            'needs_restock_count' => $allResults->where('status', 'perlu_restok')->count(),
            'safe_stock_count' => $allResults->where('status', 'aman')->count(),
            'insufficient_data_count' => $allResults->where('status', 'insufficient_data')->count(),
        ];

        // Apply filters on the collection
        $results = $allResults;

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $results = $results->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['product']->name), $search)
                    || str_contains(strtolower($item['product']->code), $search)
                    || str_contains(strtolower($item['product']->barcode ?? ''), $search);
            });
        }

        if ($request->filled('category_id')) {
            $catId = (int) $request->category_id;
            $results = $results->filter(function ($item) use ($catId) {
                return $item['product']->category_id === $catId;
            });
        }

        if ($request->filled('status') && in_array($request->status, ['perlu_restok', 'aman', 'insufficient_data'])) {
            $status = $request->status;
            $results = $results->filter(function ($item) use ($status) {
                return $item['status'] === $status;
            });
        }

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $windowDays = $forecastService->getWindowDays();

        return view('forecast.index', compact('results', 'stats', 'categories', 'windowDays'));
    }

    /**
     * Trigger an on-demand recalculation for all products.
     */
    public function calculate(ForecastService $forecastService)
    {
        $forecastService->calculateAll();

        return redirect()->route('forecast.index')
            ->with('success', 'Perhitungan peramalan permintaan SMA 7-Hari dan evaluasi restok stok fisik berhasil diperbarui!');
    }

    /**
     * Get detailed 7-day sales breakdown and formula math for a specific product.
     */
    public function history(Product $product, ForecastService $forecastService)
    {
        $product->load('category');
        $result = $forecastService->calculateForProduct($product);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
