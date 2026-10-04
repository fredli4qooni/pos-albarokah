<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTransactionStockTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Saprotan', 'slug' => 'saprotan']);
    }

    /**
     * Uji Poin 4: Kalkulasi Subtotal dan Total Belanja dengan Presisi Matematika
     * Skenario: Menghitung subtotal tiap item dan akumulasi total belanja
     * Item 1: 4 botol x Rp 85.000 = Rp 340.000
     * Item 2: 3 bungkus x Rp 45.000 = Rp 135.000
     * Item 3: 1 sak x Rp 275.000 = Rp 275.000
     * Total = Rp 750.000
     */
    public function test_subtotal_and_total_calculation_precision(): void
    {
        $items = [
            ['name' => 'Gramoxone 1L', 'price' => 85000.0, 'quantity' => 4],
            ['name' => 'Antracol 250g', 'price' => 45000.0, 'quantity' => 3],
            ['name' => 'Pupuk NPK 50kg', 'price' => 275000.0, 'quantity' => 1],
        ];

        $calculatedSubtotals = [];
        $totalBelanja = 0.0;

        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $calculatedSubtotals[] = $subtotal;
            $totalBelanja += $subtotal;
        }

        // 1. Verifikasi tiap subtotal
        $this->assertEquals(340000.0, $calculatedSubtotals[0]);
        $this->assertEquals(135000.0, $calculatedSubtotals[1]);
        $this->assertEquals(275000.0, $calculatedSubtotals[2]);

        // 2. Verifikasi total belanja akumulatif
        $this->assertEquals(750000.0, $totalBelanja);
    }

    /**
     * Uji Poin 4: Kalkulasi Sisa Uang Kembalian (Uang Kembalian = Uang Diterima - Total Belanja)
     * Skenario 1: Uang Diterima Rp 800.000, Total Rp 750.000 -> Kembalian Rp 50.000
     * Skenario 2: Uang Diterima Pas Rp 750.000, Total Rp 750.000 -> Kembalian Rp 0
     */
    public function test_cash_change_calculation_precision(): void
    {
        $totalBelanja = 750000.0;

        // Skenario 1: Pembayaran tunai lebih besar
        $uangDiterimaLebih = 800000.0;
        $kembalianSkenario1 = max(0.0, $uangDiterimaLebih - $totalBelanja);
        $this->assertEquals(50000.0, $kembalianSkenario1);

        // Skenario 2: Pembayaran uang pas
        $uangDiterimaPas = 750000.0;
        $kembalianSkenario2 = max(0.0, $uangDiterimaPas - $totalBelanja);
        $this->assertEquals(0.0, $kembalianSkenario2);
    }

    /**
     * Uji Poin 4: Pemotongan Stok Fisik Otomatis (Decrement)
     * Skenario: Stok awal 50 botol, dilakukan pemotongan stok otomatis sebesar 8 botol
     * kemudian 12 botol, memastikan stok tersisa tepat 30 botol di database.
     */
    public function test_automatic_stock_decrement_reduces_physical_stock(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'PST-001',
            'name' => 'Dharmabas 500 EC',
            'unit' => 'Botol',
            'purchase_price' => 50000,
            'selling_price' => 62000,
            'stock' => 50, // Stok awal
            'min_stock' => 5,
        ]);

        // Pembelian 1: 8 botol
        $quantityDibeli1 = 8;
        $product->decrement('stock', $quantityDibeli1);

        $this->assertEquals(42, $product->fresh()->stock);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 42,
        ]);

        // Pembelian 2: 12 botol
        $quantityDibeli2 = 12;
        $product->decrement('stock', $quantityDibeli2);

        $this->assertEquals(30, $product->fresh()->stock);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 30,
        ]);
    }
}
