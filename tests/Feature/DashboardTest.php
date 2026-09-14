<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\ForecastResult;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Pemilik Toko',
            'role' => 'admin',
        ]);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_page_can_be_rendered_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Operasional Toko Pertanian');
        $response->assertSee('Penjualan Hari Ini');
        $response->assertSee('Tren Omset Penjualan 14 Hari Terakhir');
        $response->assertSee('Peringatan Restok Kritis');
        $response->assertSee('Piutang Petani Terbesar');
    }

    public function test_dashboard_displays_real_time_kpi_metrics(): void
    {
        $category = Category::create([
            'name' => 'Fungisida',
            'slug' => 'fungisida',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'FNG-001',
            'barcode' => '8991230009991',
            'name' => 'Antracol 70 WP 250g',
            'unit' => 'Bungkus',
            'purchase_price' => 35000,
            'selling_price' => 45000,
            'stock' => 2, // Low stock (<= min_stock 5)
            'min_stock' => 5,
        ]);

        $customer = Customer::create([
            'name' => 'Pak Supardi Tani',
            'phone' => '081233445566',
        ]);

        $sale = Sale::create([
            'invoice_no' => 'INV-20260914-7777',
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'payment_method' => 'credit',
            'subtotal' => 150000,
            'total' => 150000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_amount' => 150000,
            'paid_amount' => 50000,
            'remaining_balance' => 100000,
            'status' => 'belum_lunas',
        ]);

        ForecastResult::create([
            'product_id' => $product->id,
            'period_used' => 7,
            'forecast_value' => 10.00,
            'actual_stock' => 2,
            'status' => 'perlu_restok',
            'shortage_qty' => 8,
            'calculated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('150.000'); // Total sales today
        $response->assertSee('100.000'); // Remaining debt
        $response->assertSee('Pak Supardi Tani');
        $response->assertSee('Antracol 70 WP 250g');
    }

    public function test_dashboard_supplies_14_day_chart_data(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('chartData');

        $chartData = $response->viewData('chartData');
        $this->assertIsArray($chartData);
        $this->assertCount(14, $chartData['labels']);
        $this->assertCount(14, $chartData['total']);
        $this->assertCount(14, $chartData['cash']);
        $this->assertCount(14, $chartData['credit']);
    }
}
