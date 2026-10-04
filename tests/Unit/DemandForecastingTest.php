<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Services\ForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemandForecastingTest extends TestCase
{
    use RefreshDatabase;

    private ForecastService $forecastService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastService = new ForecastService;
    }

    /**
     * Uji Poin 1: Algoritma Demand Forecasting (SMA 7 Hari & Ceiling Rounding)
     * Skenario: Data dummy 7 hari dengan total penjualan = 80 unit.
     * SMA = 80 / 7 = 11.4285...
     * Memastikan fungsi mengembalikan nilai persis 12 setelah pembulatan ceil().
     */
    public function test_calculate_sma_7_days_with_ceiling_rounding(): void
    {
        // 7 hari data historis: total = 10 + 12 + 11 + 15 + 9 + 11 + 12 = 80
        $salesData = [10, 12, 11, 15, 9, 11, 12];
        $totalExpected = 80.0;
        $window = 7;

        $result = $this->forecastService->calculateSMA($salesData, $window);

        // 1. Verifikasi kecukupan data
        $this->assertTrue($result['has_enough_data']);
        $this->assertEquals('Data Cukup', $result['status']);
        $this->assertEquals(7, $result['count']);
        $this->assertEquals($totalExpected, $result['total']);

        // 2. Verifikasi nilai SMA matematika mentah: 80 / 7 = 11.42857...
        $expectedRawSma = 80 / 7;
        $this->assertEqualsWithDelta($expectedRawSma, $result['raw_sma'], 0.0001);
        $this->assertEquals(round($expectedRawSma, 2), $result['forecast_value']);

        // 3. Verifikasi pembulatan ke atas (ceiling) persis bernilai 12
        $this->assertEquals(12, $result['forecast_ceil']);
        $this->assertEquals(12, ceil($result['raw_sma']));
    }

    /**
     * Uji Poin 2: Deteksi Data Kurang (n < 7 Hari)
     * Skenario: Memasukkan array data historis kurang dari 7 hari (misal 5 hari).
     * Memastikan fungsi mengembalikan status "Data Belum Cukup" dan has_enough_data = false.
     */
    public function test_calculate_sma_detects_insufficient_data(): void
    {
        // Hanya 5 hari data (< 7 hari)
        $insufficientSalesData = [10, 14, 12, 8, 16];

        $result = $this->forecastService->calculateSMA($insufficientSalesData, 7);

        $this->assertFalse($result['has_enough_data']);
        $this->assertEquals('Data Belum Cukup', $result['status']);
        $this->assertEquals(5, $result['count']);
        $this->assertNull($result['forecast_value']);
        $this->assertNull($result['forecast_ceil']);
        $this->assertStringContainsString('Minimal 7 hari data histori diperlukan', $result['message']);
    }

    /**
     * Uji Poin 2 (Integrasi Model & DB): Verifikasi calculateForProduct ketika data historis < 7 hari.
     */
    public function test_calculate_for_product_returns_insufficient_data_status(): void
    {
        $category = Category::create(['name' => 'Fungisida', 'slug' => 'fungisida']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'FNG-001',
            'name' => 'Antracol 70 WP 250gr',
            'unit' => 'Bungkus',
            'purchase_price' => 35000,
            'selling_price' => 42000,
            'stock' => 10,
            'min_stock' => 5,
        ]);

        // Catat hanya 4 hari penjualan di database
        for ($i = 1; $i <= 4; $i++) {
            DailySalesSummary::create([
                'product_id' => $product->id,
                'sale_date' => now()->subDays($i)->toDateString(),
                'quantity_sold' => 3,
            ]);
        }

        $result = $this->forecastService->calculateForProduct($product);

        $this->assertFalse($result['has_enough_data']);
        $this->assertEquals('insufficient_data', $result['status']);
        $this->assertEquals('Data Belum Mencukupi', $result['status_label']);
        $this->assertNull($result['forecast_value']);
        $this->assertEquals(0, $result['shortage_qty']);
    }

    /**
     * Uji Poin 1 & Rekomendasi Restok: Verifikasi perhitungan restock shortage dengan pembulatan ceil().
     */
    public function test_calculate_for_product_calculates_shortage_with_ceil(): void
    {
        $category = Category::create(['name' => 'Insektisida', 'slug' => 'insektisida']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'INS-001',
            'name' => 'Regent 50 SC 100ml',
            'unit' => 'Botol',
            'purchase_price' => 45000,
            'selling_price' => 55000,
            'stock' => 5, // Stok aktual 5 unit
            'min_stock' => 2,
        ]);

        // Simpan data 7 hari dengan total penjualan = 80 unit
        // Rata-rata per hari = 80 / 7 = 11.43 unit
        $dailyQuantities = [10, 12, 11, 15, 9, 11, 12];
        foreach ($dailyQuantities as $idx => $qty) {
            DailySalesSummary::create([
                'product_id' => $product->id,
                'sale_date' => now()->subDays($idx + 1)->toDateString(),
                'quantity_sold' => $qty,
            ]);
        }

        $result = $this->forecastService->calculateForProduct($product);

        $this->assertTrue($result['has_enough_data']);
        $this->assertEquals(11.43, $result['forecast_value']);
        $this->assertEquals('perlu_restok', $result['status']);
        $this->assertEquals('Perlu Restok', $result['status_label']);

        // shortage_qty = ceil(11.43 - 5) = ceil(6.43) = 7 unit
        $this->assertEquals(7, $result['shortage_qty']);
        $this->assertDatabaseHas('forecast_results', [
            'product_id' => $product->id,
            'forecast_value' => 11.43,
            'actual_stock' => 5,
            'status' => 'perlu_restok',
            'shortage_qty' => 7,
        ]);
    }
}
