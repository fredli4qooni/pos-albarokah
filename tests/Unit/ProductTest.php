<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_low_stock_helper(): void
    {
        $category = Category::create(['name' => 'Herbisida', 'slug' => 'herbisida']);

        $productLow = Product::create([
            'category_id' => $category->id,
            'code' => 'TEST-001',
            'name' => 'Produk Stok Menipis',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 3,
            'min_stock' => 5,
        ]);

        $productSafe = Product::create([
            'category_id' => $category->id,
            'code' => 'TEST-002',
            'name' => 'Produk Stok Aman',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 15,
            'min_stock' => 5,
        ]);

        $productOut = Product::create([
            'category_id' => $category->id,
            'code' => 'TEST-003',
            'name' => 'Produk Habis',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 60000,
            'stock' => 0,
            'min_stock' => 5,
        ]);

        $this->assertTrue($productLow->isLowStock());
        $this->assertFalse($productLow->isOutOfStock());

        $this->assertFalse($productSafe->isLowStock());
        $this->assertFalse($productSafe->isOutOfStock());

        $this->assertFalse($productOut->isLowStock());
        $this->assertTrue($productOut->isOutOfStock());
    }

    public function test_product_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Pupuk', 'slug' => 'pupuk']);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PPK-001',
            'name' => 'Pupuk Urea',
            'unit' => 'Sak',
            'purchase_price' => 200000,
            'selling_price' => 220000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $this->assertEquals('Pupuk', $product->category->name);
        $this->assertTrue($category->products->contains($product));
    }
}
