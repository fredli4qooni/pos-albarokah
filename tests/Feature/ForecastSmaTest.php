<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Models\User;
use App\Services\ForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastSmaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Product $productLowStock;

    private Product $productSafeStock;

    private Product $productNew;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Toko',
            'role' => 'admin',
        ]);

        $this->category = Category::create([
            'name' => 'Insektisida',
            'slug' => 'insektisida',
        ]);

        // 1. Product with 7 days sales history and low stock (needs restock)
        $this->productLowStock = Product::create([
            'category_id' => $this->category->id,
            'code' => 'INS-010',
            'barcode' => '8999999000101',
            'name' => 'Prevathon 50 SC 250ml',
            'unit' => 'Botol',
            'purchase_price' => 150000,
            'selling_price' => 180000,
            'stock' => 3, // Less than forecast (7)
            'min_stock' => 2,
        ]);

        // Insert 7 days of sales: 5, 7, 6, 8, 9, 4, 10 => Sum = 49 => SMA = 7.00
        $quantities1 = [5, 7, 6, 8, 9, 4, 10];
        foreach ($quantities1 as $idx => $qty) {
            DailySalesSummary::create([
                'product_id' => $this->productLowStock->id,
                'sale_date' => now()->subDays(7 - $idx)->toDateString(),
                'quantity_sold' => $qty,
            ]);
        }

        // 2. Product with 7 days sales history and safe stock
        $this->productSafeStock = Product::create([
            'category_id' => $this->category->id,
            'code' => 'INS-020',
            'barcode' => '8999999000202',
            'name' => 'Virtako 300 SC 100ml',
            'unit' => 'Botol',
            'purchase_price' => 120000,
            'selling_price' => 145000,
            'stock' => 15, // Greater than forecast (5)
            'min_stock' => 3,
        ]);

        // Insert 7 days of sales: 4, 5, 6, 5, 6, 4, 5 => Sum = 35 => SMA = 5.00
        $quantities2 = [4, 5, 6, 5, 6, 4, 5];
        foreach ($quantities2 as $idx => $qty) {
            DailySalesSummary::create([
                'product_id' => $this->productSafeStock->id,
                'sale_date' => now()->subDays(7 - $idx)->toDateString(),
                'quantity_sold' => $qty,
            ]);
        }

        // 3. Product with only 3 days sales (< 7 days => insufficient data)
        $this->productNew = Product::create([
            'category_id' => $this->category->id,
            'code' => 'INS-030',
            'barcode' => '8999999000303',
            'name' => 'Alika 247 ZC 100ml',
            'unit' => 'Botol',
            'purchase_price' => 55000,
            'selling_price' => 68000,
            'stock' => 20,
            'min_stock' => 5,
        ]);

        $quantities3 = [3, 4, 2]; // only 3 days
        foreach ($quantities3 as $idx => $qty) {
            DailySalesSummary::create([
                'product_id' => $this->productNew->id,
                'sale_date' => now()->subDays(3 - $idx)->toDateString(),
                'quantity_sold' => $qty,
            ]);
        }
    }

    public function test_forecast_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('forecast.index'));

        $response->assertOk();
        $response->assertSee('Peramalan Permintaan');
        $response->assertSee('Decision Support System');
        $response->assertSee('Prevathon 50 SC 250ml');
        $response->assertSee('Virtako 300 SC 100ml');
        $response->assertSee('Alika 247 ZC 100ml');
    }

    public function test_product_with_at_least_7_days_history_calculates_accurate_sma_and_restock_need(): void
    {
        $service = app(ForecastService::class);
        $result = $service->calculateForProduct($this->productLowStock);

        $this->assertTrue($result['has_enough_data']);
        $this->assertEquals(7, $result['days_recorded']);
        $this->assertEquals(49, $result['total_quantity']);
        $this->assertEquals(7.00, $result['forecast_value']);
        $this->assertEquals(3, $result['actual_stock']);
        $this->assertEquals('perlu_restok', $result['status']);
        $this->assertEquals(4, $result['shortage_qty']); // 7.00 - 3 = 4

        // Assert persisted in forecast_results snapshot
        $this->assertDatabaseHas('forecast_results', [
            'product_id' => $this->productLowStock->id,
            'period_used' => 7,
            'forecast_value' => 7.00,
            'actual_stock' => 3,
            'status' => 'perlu_restok',
            'shortage_qty' => 4,
        ]);
    }

    public function test_product_with_safe_stock_gets_safe_status_and_zero_shortage(): void
    {
        $service = app(ForecastService::class);
        $result = $service->calculateForProduct($this->productSafeStock);

        $this->assertTrue($result['has_enough_data']);
        $this->assertEquals(7, $result['days_recorded']);
        $this->assertEquals(35, $result['total_quantity']);
        $this->assertEquals(5.00, $result['forecast_value']);
        $this->assertEquals(15, $result['actual_stock']);
        $this->assertEquals('aman', $result['status']);
        $this->assertEquals(0, $result['shortage_qty']);

        // Assert persisted in forecast_results snapshot
        $this->assertDatabaseHas('forecast_results', [
            'product_id' => $this->productSafeStock->id,
            'status' => 'aman',
            'shortage_qty' => 0,
        ]);
    }

    public function test_product_with_less_than_7_days_history_strictly_returns_insufficient_data(): void
    {
        $service = app(ForecastService::class);
        $result = $service->calculateForProduct($this->productNew);

        // Business Rule PRD §2.1 & AGENTS.md §2.1:
        // Do NOT compute forecast when data < 7 days
        $this->assertFalse($result['has_enough_data']);
        $this->assertEquals(3, $result['days_recorded']);
        $this->assertNull($result['forecast_value']);
        $this->assertEquals('insufficient_data', $result['status']);
        $this->assertEquals(0, $result['shortage_qty']);

        // Assert snapshot was NOT created with fake numbers
        $this->assertDatabaseMissing('forecast_results', [
            'product_id' => $this->productNew->id,
        ]);
    }

    public function test_forecast_on_demand_calculate_endpoint(): void
    {
        $response = $this->actingAs($this->user)->post(route('forecast.calculate'));

        $response->assertRedirect(route('forecast.index'));
        $response->assertSessionHas('success');

        // Both products with >= 7 days should have snapshots
        $this->assertDatabaseHas('forecast_results', ['product_id' => $this->productLowStock->id]);
        $this->assertDatabaseHas('forecast_results', ['product_id' => $this->productSafeStock->id]);
    }

    public function test_forecast_history_json_api(): void
    {
        $response = $this->actingAs($this->user)->get(route('forecast.history', $this->productLowStock));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'has_enough_data' => true,
                'forecast_value' => 7.00,
                'status' => 'perlu_restok',
                'shortage_qty' => 4,
            ],
        ]);
    }
}
