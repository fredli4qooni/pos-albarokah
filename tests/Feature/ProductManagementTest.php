<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->category = Category::create(['name' => 'Herbisida', 'slug' => 'herbisida']);
    }

    public function test_admin_can_view_product_catalog(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'code' => 'HRB-GRM-01',
            'name' => 'Gramoxone 1 Liter',
            'unit' => 'Botol',
            'purchase_price' => 80000,
            'selling_price' => 95000,
            'stock' => 15,
            'min_stock' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Gramoxone 1 Liter');
    }

    public function test_admin_can_filter_products_by_category(): void
    {
        $catPupuk = Category::create(['name' => 'Pupuk', 'slug' => 'pupuk']);

        $productHerbisida = Product::create([
            'category_id' => $this->category->id,
            'code' => 'HRB-001',
            'name' => 'Produk Obat Rumput',
            'unit' => 'Botol',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $productPupuk = Product::create([
            'category_id' => $catPupuk->id,
            'code' => 'PPK-001',
            'name' => 'Produk Pupuk Daun',
            'unit' => 'Sak',
            'purchase_price' => 20000,
            'selling_price' => 25000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index', ['category_id' => $catPupuk->id]));

        $response->assertOk();
        $response->assertSee('Produk Pupuk Daun');
        $response->assertDontSee('Produk Obat Rumput');
    }

    public function test_admin_can_create_product(): void
    {
        $payload = [
            'category_id' => $this->category->id,
            'code' => 'HRB-RND-01',
            'barcode' => '8991234567890',
            'name' => 'Roundup 1 Liter',
            'unit' => 'Botol',
            'purchase_price' => 95000,
            'selling_price' => 110000,
            'stock' => 20,
            'min_stock' => 5,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $payload);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['code' => 'HRB-RND-01']);
    }

    public function test_product_validation_prevents_duplicate_code(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'code' => 'DUP-001',
            'name' => 'Produk Asli',
            'unit' => 'Botol',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'code' => 'DUP-001',
            'name' => 'Produk Duplikat',
            'unit' => 'Botol',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'stock' => 5,
            'min_stock' => 2,
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_can_view_product_detail_and_barcode(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'HRB-BC-01',
            'barcode' => '1234567890123',
            'name' => 'Produk Uji Barcode',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $showResponse = $this->actingAs($this->admin)->get(route('products.show', $product));
        $showResponse->assertOk();
        $showResponse->assertSee('Produk Uji Barcode');

        $barcodeResponse = $this->actingAs($this->admin)->get(route('products.barcode', $product));
        $barcodeResponse->assertOk();
        $barcodeResponse->assertSee('1234567890123');
    }

    public function test_admin_cannot_delete_product_with_sale_history(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'HRB-TRANS-01',
            'name' => 'Produk Terjual',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $sale = Sale::create([
            'invoice_no' => 'INV-TEST-PROD-01',
            'user_id' => $this->admin->id,
            'customer_id' => null,
            'payment_method' => 'cash',
            'subtotal' => 60000,
            'total' => 60000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 60000,
            'subtotal' => 60000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
