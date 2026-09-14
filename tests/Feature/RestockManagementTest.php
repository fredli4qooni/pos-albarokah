<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Restock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin Gudang',
            'role' => 'admin',
        ]);

        $this->category = Category::create([
            'name' => 'Herbisida',
            'slug' => 'herbisida',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'HRB-001',
            'barcode' => '8991234500011',
            'name' => 'Gramoxone 276 SL 1L',
            'unit' => 'Botol',
            'purchase_price' => 70000,
            'selling_price' => 85000,
            'stock' => 10,
            'min_stock' => 5,
        ]);
    }

    public function test_restocks_index_page_can_be_rendered_with_metrics(): void
    {
        Restock::create([
            'product_id' => $this->product->id,
            'quantity' => 20,
            'restock_date' => now()->toDateString(),
            'notes' => 'Pasokan pertama CV Agronusa',
        ]);

        $response = $this->actingAs($this->user)->get(route('restocks.index'));

        $response->assertOk();
        $response->assertSee('Barang Masuk');
        $response->assertSee('Gramoxone 276 SL 1L');
        $response->assertSee('Pasokan pertama CV Agronusa');
    }

    public function test_restocks_create_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)->get(route('restocks.create'));

        $response->assertOk();
        $response->assertSee('Penerimaan Barang Masuk');
        $response->assertSee('Gramoxone 276 SL 1L');
    }

    public function test_restocks_create_page_supports_prefilled_product_and_quantity(): void
    {
        $response = $this->actingAs($this->user)->get(route('restocks.create', [
            'product_id' => $this->product->id,
            'quantity' => 35,
        ]));

        $response->assertOk();
        $response->assertSee('35');
    }

    public function test_successful_restock_increments_product_stock_atomically(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'quantity' => 15,
            'restock_date' => now()->toDateString(),
            'notes' => 'Distributor PT Syngenta - SJ #88912',
        ];

        $response = $this->actingAs($this->user)->post(route('restocks.store'), $payload);

        $response->assertRedirect(route('restocks.index'));
        $response->assertSessionHas('success');

        // Assert restocks table record
        $this->assertDatabaseHas('restocks', [
            'product_id' => $this->product->id,
            'quantity' => 15,
            'notes' => 'Distributor PT Syngenta - SJ #88912',
        ]);

        // Assert product stock incremented from 10 to 25
        $this->assertEquals(25, $this->product->fresh()->stock);
    }

    public function test_restock_fails_when_product_is_invalid(): void
    {
        $payload = [
            'product_id' => 9999,
            'quantity' => 10,
            'restock_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post(route('restocks.store'), $payload);

        $response->assertSessionHasErrors(['product_id']);
        $this->assertDatabaseCount('restocks', 0);
    }

    public function test_restock_fails_when_quantity_is_zero_or_negative(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'quantity' => 0,
            'restock_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post(route('restocks.store'), $payload);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    public function test_restock_fails_when_restock_date_is_missing(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'restock_date' => '',
        ];

        $response = $this->actingAs($this->user)->post(route('restocks.store'), $payload);

        $response->assertSessionHasErrors(['restock_date']);
    }

    public function test_restock_show_page_displays_document_details(): void
    {
        $restock = Restock::create([
            'product_id' => $this->product->id,
            'quantity' => 40,
            'restock_date' => now()->toDateString(),
            'notes' => 'Pengiriman batch kedua',
        ]);

        $response = $this->actingAs($this->user)->get(route('restocks.show', $restock));

        $response->assertOk();
        $response->assertSee('Dokumen Penerimaan Barang #'.$restock->id);
        $response->assertSee('Gramoxone 276 SL 1L');
        $response->assertSee('40');
        $response->assertSee('Pengiriman batch kedua');
    }
}
