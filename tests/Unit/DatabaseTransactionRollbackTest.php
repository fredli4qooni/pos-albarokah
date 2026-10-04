<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseTransactionRollbackTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::create(['name' => 'Pupuk', 'slug' => 'pupuk']);
    }

    /**
     * Uji Poin 5: Mekanisme Atomisitas DB::transaction() saat Terjadi Kesalahan Query
     * Skenario Uji: Mensimulasikan kegagalan eksepsi/query saat penulisan tabel sale_items.
     * Memastikan seluruh perubahan pada tabel sales dan pengurangan stok pada products
     * otomatis dibatalkan (rollback) ke kondisi awal sehingga data tidak korup/inkonsisten.
     */
    public function test_db_transaction_rolls_back_sale_and_stock_on_item_failure(): void
    {
        $initialStock = 25;
        $product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'PPK-NPK-01',
            'name' => 'Pupuk NPK Mutiara 16-16-16 1kg',
            'unit' => 'Bungkus',
            'purchase_price' => 20000,
            'selling_price' => 25000,
            'stock' => $initialStock,
            'min_stock' => 5,
        ]);

        $customer = Customer::create([
            'name' => 'Petani Joko Susilo',
            'phone' => '082199887766',
        ]);

        $invoiceNo = 'INV-SIMULASI-FAIL-001';
        $exceptionCaught = false;

        try {
            DB::transaction(function () use ($product, $customer, $invoiceNo) {
                // 1. Catat header penjualan
                $sale = Sale::create([
                    'invoice_no' => $invoiceNo,
                    'user_id' => $this->user->id,
                    'customer_id' => $customer->id,
                    'payment_method' => 'cash',
                    'subtotal' => 125000,
                    'total' => 125000,
                    'status' => 'completed',
                    'sold_at' => now(),
                ]);

                // 2. Kurangi stok fisik produk sebanyak 5 unit
                $product->decrement('stock', 5);

                // 3. Simulasikan kegagalan fatal query saat penulisan ke tabel sale_items
                // (misal: koneksi terputus, disk full, atau query error)
                throw new Exception('Simulasi kegagalan query pada penulisan tabel sale_items.');
                // Kode berikut tidak pernah tereksekusi karena exception
                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'price' => 25000,
                    'subtotal' => 125000,
                ]);
            });
        } catch (Exception $e) {
            $exceptionCaught = true;
            $this->assertEquals('Simulasi kegagalan query pada penulisan tabel sale_items.', $e->getMessage());
        }

        // Verifikasi bahwa eksepsi benar-benar tertangkap
        $this->assertTrue($exceptionCaught, 'Eksepsi kegagalan query harus tertangkap.');

        // 1. Verifikasi tabel sales: Transaksi penjualan dibatalkan (rollback)
        $this->assertDatabaseMissing('sales', [
            'invoice_no' => $invoiceNo,
        ]);
        $this->assertEquals(0, Sale::count());

        // 2. Verifikasi tabel products: Stok fisik produk tetap utuh tidak terpotong (kembali ke 25)
        $this->assertEquals(
            $initialStock,
            $product->fresh()->stock,
            'Stok produk harus kembali ke angka awal (25) setelah rollback transaksi.'
        );
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => $initialStock,
        ]);

        // 3. Verifikasi tabel sale_items: Tidak ada item sampah yang tersimpan
        $this->assertDatabaseCount('sale_items', 0);
    }

    /**
     * Uji Poin 5: Verifikasi rollback ketika terjadi pelanggaran integritas foreign key
     */
    public function test_db_transaction_rolls_back_on_database_integrity_violation(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'code' => 'PPK-TS-01',
            'name' => 'Pupuk TSP 50kg',
            'unit' => 'Sak',
            'purchase_price' => 250000,
            'selling_price' => 300000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $invoiceNo = 'INV-SIMULASI-INTEGRITY-002';
        $errorHappened = false;

        try {
            DB::transaction(function () use ($product, $invoiceNo) {
                // Potong stok
                $product->decrement('stock', 2);

                // Buat sale
                Sale::create([
                    'invoice_no' => $invoiceNo,
                    'user_id' => $this->user->id,
                    'customer_id' => 999999, // Customer ID tidak ada di database (Foreign key violation)
                    'payment_method' => 'cash',
                    'subtotal' => 600000,
                    'total' => 600000,
                    'status' => 'completed',
                    'sold_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            $errorHappened = true;
        }

        $this->assertTrue($errorHappened);
        $this->assertEquals(10, $product->fresh()->stock);
        $this->assertDatabaseMissing('sales', ['invoice_no' => $invoiceNo]);
    }
}
