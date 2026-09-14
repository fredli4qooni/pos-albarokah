<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_unauthenticated_user_redirected_from_reports(): void
    {
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/reports/sales')->assertRedirect('/login');
        $this->get('/reports/sales/pdf')->assertRedirect('/login');
        $this->get('/reports/receivables')->assertRedirect('/login');
        $this->get('/reports/receivables/pdf')->assertRedirect('/login');
        $this->get('/reports/products')->assertRedirect('/login');
        $this->get('/reports/products/pdf')->assertRedirect('/login');
    }

    public function test_reports_hub_index_page_rendered_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/reports');

        $response->assertOk();
        $response->assertSee('Pusat Laporan');
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('Laporan Piutang');
        $response->assertSee('Laporan Stok');
    }

    public function test_sales_report_page_and_filtering(): void
    {
        $customer = Customer::create([
            'name' => 'Pak Joko Santoso',
            'phone' => '08123456789',
        ]);

        $category = Category::create([
            'name' => 'Pupuk',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PPK-001',
            'name' => 'Pupuk Urea 50kg',
            'unit' => 'karung',
            'purchase_price' => 100000,
            'selling_price' => 120000,
            'stock' => 50,
            'min_stock' => 10,
        ]);

        // Create a cash sale
        $cashSale = Sale::create([
            'invoice_no' => 'INV-20260914-0001',
            'user_id' => $this->user->id,
            'customer_id' => null,
            'sold_at' => now(),
            'subtotal' => 240000,
            'discount' => 0,
            'tax' => 0,
            'total' => 240000,
            'payment_method' => 'cash',
            'cash_amount' => 250000,
            'change_amount' => 10000,
        ]);

        SaleItem::create([
            'sale_id' => $cashSale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 120000,
            'subtotal' => 240000,
        ]);

        // Create a credit sale
        $creditSale = Sale::create([
            'invoice_no' => 'INV-20260914-0002',
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'sold_at' => now(),
            'subtotal' => 360000,
            'discount' => 0,
            'tax' => 0,
            'total' => 360000,
            'payment_method' => 'credit',
            'cash_amount' => 0,
            'change_amount' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $creditSale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 120000,
            'subtotal' => 360000,
        ]);

        // 1. Check all sales
        $response = $this->actingAs($this->user)->get('/reports/sales');
        $response->assertOk();
        $response->assertSee('INV-20260914-0001');
        $response->assertSee('INV-20260914-0002');
        $response->assertSee('600.000'); // total sum 240k + 360k

        // 2. Filter cash sales only
        $responseCash = $this->actingAs($this->user)->get('/reports/sales?payment_method=cash');
        $responseCash->assertOk();
        $responseCash->assertSee('INV-20260914-0001');
        $responseCash->assertDontSee('INV-20260914-0002');
    }

    public function test_sales_report_pdf_generation(): void
    {
        $response = $this->actingAs($this->user)->get('/reports/sales/pdf?start_date='.now()->startOfMonth()->toDateString().'&end_date='.now()->toDateString());

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_receivables_report_page_and_filtering(): void
    {
        $customer = Customer::create([
            'name' => 'Bapak Slamet Subur',
            'phone' => '082199887766',
        ]);

        $sale = Sale::create([
            'invoice_no' => 'INV-20260914-0003',
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'sold_at' => now(),
            'subtotal' => 500000,
            'discount' => 0,
            'tax' => 0,
            'total' => 500000,
            'payment_method' => 'credit',
            'cash_amount' => 0,
            'change_amount' => 0,
        ]);

        $receivable = Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_amount' => 500000,
            'paid_amount' => 200000,
            'remaining_balance' => 300000,
            'due_date' => now()->addDays(14),
            'status' => 'belum_lunas',
        ]);

        $response = $this->actingAs($this->user)->get('/reports/receivables?status=belum_lunas');

        $response->assertOk();
        $response->assertSee('Bapak Slamet Subur');
        $response->assertSee('300.000');
        $response->assertSee('Belum Lunas');
    }

    public function test_receivables_report_pdf_generation(): void
    {
        $response = $this->actingAs($this->user)->get('/reports/receivables/pdf?status=all');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_products_inventory_report_page_and_filtering(): void
    {
        $category = Category::create([
            'name' => 'Insektisida',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'INS-001',
            'name' => 'Dursban 200EC',
            'unit' => 'botol',
            'purchase_price' => 45000,
            'selling_price' => 55000,
            'stock' => 25,
            'min_stock' => 5,
        ]);

        $response = $this->actingAs($this->user)->get('/reports/products?category_id='.$category->id);

        $response->assertOk();
        $response->assertSee('INS-001');
        $response->assertSee('Dursban 200EC');
        $response->assertSee('Insektisida');
        $response->assertSee('1.125.000'); // 45000 * 25
    }

    public function test_products_inventory_report_pdf_generation(): void
    {
        $response = $this->actingAs($this->user)->get('/reports/products/pdf?stock_status=all');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
