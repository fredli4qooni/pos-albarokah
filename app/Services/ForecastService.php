<?php

namespace App\Services;

use App\Models\DailySalesSummary;
use App\Models\ForecastResult;
use App\Models\Product;
use Illuminate\Support\Collection;

class ForecastService
{
    /**
     * Get the configured SMA window size in days (default: 7 days).
     */
    public function getWindowDays(): int
    {
        return (int) config('forecast.sma_window_days', 7);
    }

    /**
     * Calculate Demand Forecasting using Single Moving Average (SMA) from raw daily sales array.
     *
     * Business Rules:
     * - Minimum window days (default 7 days).
     * - Less than 7 days -> returns 'status' => 'Data Belum Cukup'.
     * - 7 days or more -> returns SMA calculation and ceiling rounding ceil(SMA).
     *
     * @param  array<int|float>  $salesData  Array of sold quantities.
     * @param  int|null  $window  Window days (default: config or 7 days).
     * @return array<string, mixed>
     */
    public function calculateSMA(array $salesData, ?int $window = null): array
    {
        $window = $window ?? $this->getWindowDays();
        $count = count($salesData);

        if ($count < $window) {
            return [
                'has_enough_data' => false,
                'status' => 'Data Belum Cukup',
                'count' => $count,
                'window' => $window,
                'total' => (float) array_sum($salesData),
                'raw_sma' => null,
                'forecast_value' => null,
                'forecast_ceil' => null,
                'message' => "Data histori penjualan baru {$count} hari. Minimal {$window} hari data histori diperlukan untuk menghitung SMA.",
            ];
        }

        // Take the latest $window entries
        $relevantData = array_slice($salesData, -$window);
        $total = (float) array_sum($relevantData);
        $rawSma = $total / $window;
        $forecastValue = round($rawSma, 2);
        $forecastCeil = (int) ceil($rawSma);

        return [
            'has_enough_data' => true,
            'status' => 'Data Cukup',
            'count' => $count,
            'window' => $window,
            'total' => $total,
            'raw_sma' => $rawSma,
            'forecast_value' => $forecastValue,
            'forecast_ceil' => $forecastCeil,
            'message' => "Peramalan SMA {$window} hari berhasil dihitung dengan total {$total} unit.",
        ];
    }

    /**
     * Calculate Demand Forecasting using Single Moving Average (SMA)
     * for a specific product.
     *
     * Business Rules (PRD §2.1 & AGENTS.md §2.1):
     * 1. Window n = 7 days.
     * 2. Requires minimal 7 days sales history. Less than 7 -> 'insufficient_data'.
     * 3. actual_stock >= forecast -> 'aman', shortage = 0.
     * 4. actual_stock < forecast -> 'perlu_restok', shortage = ceil(forecast - actual_stock).
     */
    public function calculateForProduct(Product $product): array
    {
        $window = $this->getWindowDays();

        // Get recent sales history up to window size
        $salesHistory = DailySalesSummary::where('product_id', $product->id)
            ->orderByDesc('sale_date')
            ->take($window)
            ->get();

        $daysCount = $salesHistory->count();

        // Business Rule: If sales data < 7 days, DO NOT calculate forecast
        if ($daysCount < $window) {
            return [
                'product' => $product,
                'has_enough_data' => false,
                'days_recorded' => $daysCount,
                'window_days' => $window,
                'total_quantity' => (float) $salesHistory->sum('quantity_sold'),
                'forecast_value' => null,
                'actual_stock' => $product->stock,
                'status' => 'insufficient_data',
                'status_label' => 'Data Belum Mencukupi',
                'shortage_qty' => 0,
                'sales_history' => $salesHistory->sortBy('sale_date')->values(),
                'message' => "Riwayat penjualan baru {$daysCount} hari tercatat. Sesuai PRD §2.1, peramalan SMA memerlukan minimal {$window} hari data.",
            ];
        }

        // Calculate SMA: F(t+1) = Sum(Xi) / n
        $totalQuantity = (float) $salesHistory->sum('quantity_sold');
        $forecastValue = round($totalQuantity / $window, 2);
        $actualStock = $product->stock;

        // Determine stock sufficiency
        $needsRestock = $actualStock < $forecastValue;
        $status = $needsRestock ? 'perlu_restok' : 'aman';
        $statusLabel = $needsRestock ? 'Perlu Restok' : 'Stok Aman';
        $shortageQty = $needsRestock ? (int) ceil($forecastValue - $actualStock) : 0;

        // Persist or update the snapshot in forecast_results table
        ForecastResult::updateOrCreate(
            ['product_id' => $product->id],
            [
                'period_used' => $window,
                'forecast_value' => $forecastValue,
                'actual_stock' => $actualStock,
                'status' => $status,
                'shortage_qty' => $shortageQty,
                'calculated_at' => now(),
            ]
        );

        return [
            'product' => $product,
            'has_enough_data' => true,
            'days_recorded' => $daysCount,
            'window_days' => $window,
            'total_quantity' => $totalQuantity,
            'forecast_value' => $forecastValue,
            'actual_stock' => $actualStock,
            'status' => $status,
            'status_label' => $statusLabel,
            'shortage_qty' => $shortageQty,
            'sales_history' => $salesHistory->sortBy('sale_date')->values(),
            'message' => $needsRestock
                ? "Stok saat ini ({$actualStock} {$product->unit}) berada di bawah estimasi permintaan ({$forecastValue} {$product->unit}). Disarankan menambah pasokan {$shortageQty} {$product->unit}."
                : "Stok saat ini ({$actualStock} {$product->unit}) mencukupi proyeksi kebutuhan permintaan ({$forecastValue} {$product->unit}).",
        ];
    }

    /**
     * Calculate SMA forecasting for all active products.
     *
     * @return Collection<int, array>
     */
    public function calculateAll(): Collection
    {
        $products = Product::with('category')->orderBy('name')->get();

        return $products->map(fn (Product $product) => $this->calculateForProduct($product));
    }
}
