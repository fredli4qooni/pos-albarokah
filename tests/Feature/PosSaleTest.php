<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DailySalesSummary;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Product $product;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Kasir Utama',
            'role' => 'admin',
        ]);

        $this->category = Category::create([
            'name' => 'Insektisida',
            'slug' => 'insektisida',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'INS-001',
            'barcode' => '8991234567890',
            'name' => 'Prevathon 50 SC 100ml',
            'unit' => 'Botol',
            'purchase_price' => 75000,
            'selling_price' => 90000,
            'stock' => 15,
            'min_stock' => 3,
        ]);

        $this->customer = Customer::create([
            'name' => 'Pak Joko Santoso',
            'phone' => '081298765432',
            'address' => 'Desa Sumber Makmur RT 01/02',
        ]);
    }

    public function test_cashier_pos_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('sales.create'));

        $response->assertOk();
        $response->assertSee('Terminal Kasir POS');
        $response->assertSee('Prevathon 50 SC 100ml');
        $response->assertSee('Pak Joko Santoso');
    }

    public function test_cash_sale_transaction_successful_atomic_and_decrements_stock(): void
    {
        $payload = [
            'payment_method' => 'cash',
            'customer_id' => null,
            'cash_amount' => 200000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'total' => 180000,
            'change' => 20000,
        ]);

        // Assert sale record
        $this->assertDatabaseHas('sales', [
            'payment_method' => 'cash',
            'subtotal' => 180000,
            'total' => 180000,
            'status' => 'completed',
        ]);

        // Assert stock decremented from 15 to 13
        $this->assertEquals(13, $this->product->fresh()->stock);

        // Assert daily_sales_summaries updated for forecasting
        $summary = DailySalesSummary::where('product_id', $this->product->id)->first();
        $this->assertNotNull($summary);
        $this->assertEquals(now()->toDateString(), $summary->sale_date->format('Y-m-d'));
        $this->assertEquals(2, $summary->quantity_sold);

        // Assert no receivable for cash sale
        $this->assertDatabaseCount('receivables', 0);
    }

    public function test_credit_sale_transaction_creates_receivable_with_unpaid_status(): void
    {
        $payload = [
            'payment_method' => 'credit',
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('sales.store'), $payload);

        $sale = Sale::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($sale);
        $response->assertRedirect(route('sales.show', $sale));

        // Assert sale details
        $this->assertEquals(270000, (float) $sale->total);
        $this->assertEquals('credit', $sale->payment_method);

        // Assert stock decremented from 15 to 12
        $this->assertEquals(12, $this->product->fresh()->stock);

        // Assert receivable created
        $this->assertDatabaseHas('receivables', [
            'sale_id' => $sale->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 270000,
            'paid_amount' => 0,
            'remaining_balance' => 270000,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_credit_sale_without_customer_fails_validation(): void
    {
        $payload = [
            'payment_method' => 'credit',
            'customer_id' => null, // missing customer
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['customer_id']);

        // Assert no sale or receivable created
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('receivables', 0);
        $this->assertEquals(15, $this->product->fresh()->stock);
    }

    public function test_sale_transaction_fails_and_rolls_back_when_stock_is_insufficient(): void
    {
        // Product has stock = 15, request 20
        $payload = [
            'payment_method' => 'cash',
            'cash_amount' => 2000000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);

        // Assert complete DB rollback
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertEquals(15, $this->product->fresh()->stock);
        $this->assertDatabaseCount('daily_sales_summaries', 0);
    }

    public function test_cash_sale_fails_when_cash_amount_is_insufficient(): void
    {
        // 1 item = 90.000, but cash paid is only 50.000
        $payload = [
            'payment_method' => 'cash',
            'cash_amount' => 50000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cash_amount']);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_sales_history_listing_and_filtering(): void
    {
        // Create 2 sales
        Sale::create([
            'invoice_no' => 'INV-20260914-0001',
            'user_id' => $this->user->id,
            'customer_id' => null,
            'payment_method' => 'cash',
            'subtotal' => 90000,
            'total' => 90000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        Sale::create([
            'invoice_no' => 'INV-20260914-0002',
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'credit',
            'subtotal' => 180000,
            'total' => 180000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.index'));
        $response->assertOk();
        $response->assertSee('INV-20260914-0001');
        $response->assertSee('INV-20260914-0002');

        // Filter by credit
        $filterResponse = $this->actingAs($this->user)->get(route('sales.index', ['payment_method' => 'credit']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('INV-20260914-0002');
        $filterResponse->assertDontSee('INV-20260914-0001');
    }

    public function test_sale_detail_receipt_and_pdf_download(): void
    {
        $sale = Sale::create([
            'invoice_no' => 'INV-20260914-0003',
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'credit',
            'subtotal' => 90000,
            'total' => 90000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $sale->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 90000,
            'subtotal' => 90000,
        ]);

        Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 90000,
            'paid_amount' => 0,
            'remaining_balance' => 90000,
            'status' => 'belum_lunas',
        ]);

        // Test show
        $showResponse = $this->actingAs($this->user)->get(route('sales.show', $sale));
        $showResponse->assertOk();
        $showResponse->assertSee('INV-20260914-0003');
        $showResponse->assertSee('Prevathon 50 SC 100ml');

        // Test thermal receipt
        $receiptResponse = $this->actingAs($this->user)->get(route('sales.print', $sale));
        $receiptResponse->assertOk();
        $receiptResponse->assertSee('AL BAROKAH');
        $receiptResponse->assertSee('INV-20260914-0003');

        // Test PDF download
        $pdfResponse = $this->actingAs($this->user)->get(route('sales.download-pdf', $sale));
        $pdfResponse->assertOk();
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }
}
