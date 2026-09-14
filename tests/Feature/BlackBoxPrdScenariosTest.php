<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\DailySalesSummary;
use App\Models\ForecastResult;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use App\Services\ForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian Sistematis Black Box Testing sesuai PRD Bagian 13 (7 Skenario)
 * Sistem Informasi POS, Inventori, Rekomendasi Restock (SMA) & Piutang
 * Studi Kasus: Toko Pertanian Al Barokah
 */
class BlackBoxPrdScenariosTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'name' => 'Owner Toko Al Barokah',
            'email' => 'admin@albarokah.com',
            'username' => 'admin',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * Skenario 1: Login dengan akun valid & tidak valid
     * Sesuai PRD §13 Skenario 1 & §7.1 Modul Autentikasi
     */
    public function test_scenario_1_authentication_valid_and_invalid_login(): void
    {
        // 1.1 Login dengan password tidak valid
        $responseFail = $this->post('/login', [
            'email' => 'admin@albarokah.com',
            'password' => 'wrongpassword',
        ]);
        $responseFail->assertSessionHasErrors();
        $this->assertGuest();

        // 1.2 Login dengan akun valid
        $responseSuccess = $this->post('/login', [
            'email' => 'admin@albarokah.com',
            'password' => 'password123',
        ]);
        $responseSuccess->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);

        // 1.3 Logout sesi pengguna
        $responseLogout = $this->actingAs($this->admin)->post('/logout');
        $responseLogout->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Skenario 2: Dashboard menampilkan data barang, transaksi, piutang, & rekomendasi restock dengan benar
     * Sesuai PRD §13 Skenario 2 & §7.2 Modul Dashboard
     */
    public function test_scenario_2_dashboard_kpis_and_analytics_display(): void
    {
        // Setup master kategori & produk
        $category = Category::create(['name' => 'Herbisida']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'HRB-001',
            'name' => 'Roundup 1 Liter',
            'unit' => 'Botol',
            'purchase_price' => 80000,
            'selling_price' => 95000,
            'stock' => 2,
            'min_stock' => 5, // Low stock condition
        ]);

        // Setup transaksi penjualan hari ini
        Sale::create([
            'invoice_no' => 'INV-20260914-0101',
            'user_id' => $this->admin->id,
            'customer_id' => null,
            'sold_at' => now(),
            'subtotal' => 95000,
            'total' => 95000,
            'payment_method' => 'cash',
        ]);

        // Setup pelanggan & piutang aktif
        $customer = Customer::create(['name' => 'Pak Haji Komar', 'phone' => '081299881122']);
        $creditSale = Sale::create([
            'invoice_no' => 'INV-20260914-0102',
            'user_id' => $this->admin->id,
            'customer_id' => $customer->id,
            'sold_at' => now(),
            'subtotal' => 190000,
            'total' => 190000,
            'payment_method' => 'credit',
        ]);

        Receivable::create([
            'sale_id' => $creditSale->id,
            'customer_id' => $customer->id,
            'total_amount' => 190000,
            'paid_amount' => 0,
            'remaining_balance' => 190000,
            'status' => 'belum_lunas',
        ]);

        // Setup rekomendasi restock SMA
        ForecastResult::create([
            'product_id' => $product->id,
            'period_used' => 7,
            'forecast_value' => 8.00,
            'actual_stock' => 2,
            'shortage_qty' => 6,
            'status' => 'perlu_restok',
            'calculated_at' => now(),
        ]);

        // Akses dashboard
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertOk();

        // Verifikasi keberadaan komponen kunci PRD pada dashboard
        $response->assertSee('Dashboard Operasional Toko Pertanian');
        $response->assertSee('Pak Haji Komar');
        $response->assertSee('Roundup 1 Liter');
        $response->assertSee('Peringatan Restok Kritis');
    }

    /**
     * Skenario 3: CRUD master barang (tambah, ubah, hapus, simpan) berjalan sesuai ekspektasi
     * Sesuai PRD §13 Skenario 3 & §7.3 Modul Master Barang
     */
    public function test_scenario_3_master_data_crud_operations(): void
    {
        // 3.1 Buat Kategori
        $category = Category::create([
            'name' => 'Fungisida',
            'description' => 'Obat anti jamur tanaman',
        ]);
        $this->assertDatabaseHas('categories', ['name' => 'Fungisida']);

        // 3.2 Tambah Produk Baru (Create)
        $productData = [
            'category_id' => $category->id,
            'code' => 'FNG-001',
            'barcode' => '8991234567890',
            'name' => 'Antracol 70WP 500gr',
            'unit' => 'Bungkus',
            'purchase_price' => 50000,
            'selling_price' => 62000,
            'stock' => 20,
            'min_stock' => 5,
        ];

        $responseStore = $this->actingAs($this->admin)->post('/products', $productData);
        $responseStore->assertRedirect();
        $this->assertDatabaseHas('products', ['code' => 'FNG-001', 'name' => 'Antracol 70WP 500gr']);

        $product = Product::where('code', 'FNG-001')->firstOrFail();

        // 3.3 Ubah Data Produk (Update)
        $responseUpdate = $this->actingAs($this->admin)->put("/products/{$product->id}", array_merge($productData, [
            'name' => 'Antracol 70WP 500gr (Formula Baru)',
            'selling_price' => 65000,
            'stock' => 25,
        ]));
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Antracol 70WP 500gr (Formula Baru)',
            'selling_price' => 65000,
            'stock' => 25,
        ]);

        // 3.4 Hapus Produk (Delete)
        $responseDelete = $this->actingAs($this->admin)->delete("/products/{$product->id}");
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Skenario 4: Transaksi penjualan: pencatatan, update stok otomatis, histori penjualan, dan pencatatan piutang (jika kredit)
     * Sesuai PRD §13 Skenario 4 & §7.4 Modul Transaksi Penjualan
     */
    public function test_scenario_4_pos_sales_transactions_cash_and_credit_with_atomic_stock(): void
    {
        $category = Category::create(['name' => 'Pupuk']);
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PPK-NPK',
            'name' => 'Pupuk NPK Mutiara 16-16-16 1kg',
            'unit' => 'Bungkus',
            'purchase_price' => 15000,
            'selling_price' => 18000,
            'stock' => 20,
            'min_stock' => 5,
        ]);

        // 4.1 Transaksi Penjualan Tunai (Cash)
        $cashTransactionPayload = [
            'payment_method' => 'cash',
            'cash_amount' => 50000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $responseCash = $this->actingAs($this->admin)->postJson('/sales', $cashTransactionPayload);
        $responseCash->assertOk();
        $responseCash->assertJson(['success' => true]);

        // Verifikasi stok berkurang otomatis: 20 - 2 = 18
        $this->assertEquals(18, $product->fresh()->stock);

        // Verifikasi histori penjualan harian tersimpan
        $this->assertDatabaseHas('daily_sales_summaries', [
            'product_id' => $product->id,
            'quantity_sold' => 2,
        ]);

        // 4.2 Transaksi Penjualan Piutang (Kredit)
        $customer = Customer::create(['name' => 'Petani Sugeng', 'phone' => '085211223344']);
        $creditTransactionPayload = [
            'customer_id' => $customer->id,
            'payment_method' => 'credit',
            'due_date' => now()->addDays(14)->toDateString(),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ],
        ];

        $responseCredit = $this->actingAs($this->admin)->postJson('/sales', $creditTransactionPayload);
        $responseCredit->assertOk();
        $responseCredit->assertJson(['success' => true]);

        // Verifikasi stok berkurang lagi secara atomic: 18 - 3 = 15
        $this->assertEquals(15, $product->fresh()->stock);

        // Verifikasi pencatatan piutang otomatis terbentuk
        $this->assertDatabaseHas('receivables', [
            'customer_id' => $customer->id,
            'total_amount' => 54000, // 3 * 18.000
            'remaining_balance' => 54000,
            'status' => 'belum_lunas',
        ]);

        // 4.3 Validasi Pencegahan Stok Tidak Cukup
        $excessivePayload = [
            'payment_method' => 'cash',
            'cash_amount' => 1000000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 50, // Stok hanya ada 15
                ],
            ],
        ];

        $responseExcessive = $this->actingAs($this->admin)->postJson('/sales', $excessivePayload);
        $responseExcessive->assertStatus(422); // Unprocessable Entity
        $this->assertEquals(15, $product->fresh()->stock); // Stok tidak berubah
    }

    /**
     * Skenario 5: Forecast restok: hasil forecast, status stok, dan rekomendasi restock sesuai data historis & rumus SMA
     * Sesuai PRD §13 Skenario 5 & §7.5 Modul Forecast & Rekomendasi Restock
     */
    public function test_scenario_5_forecast_sma_7_days_and_restock_recommendation(): void
    {
        $category = Category::create(['name' => 'Insektisida']);
        $service = app(ForecastService::class);

        // 5.1 Produk dengan Histori < 7 Hari -> "Data Belum Mencukupi"
        $insufficientProduct = Product::create([
            'category_id' => $category->id,
            'code' => 'INS-NEW',
            'name' => 'Produk Baru Kurang Histori',
            'unit' => 'Botol',
            'purchase_price' => 30000,
            'selling_price' => 38000,
            'stock' => 5,
            'min_stock' => 3,
        ]);

        // Catat penjualan hanya 3 hari
        for ($i = 1; $i <= 3; $i++) {
            DailySalesSummary::create([
                'product_id' => $insufficientProduct->id,
                'sale_date' => now()->subDays($i)->toDateString(),
                'quantity_sold' => 4,
            ]);
        }

        $insufficientResult = $service->calculateForProduct($insufficientProduct);
        $this->assertEquals('insufficient_data', $insufficientResult['status']);
        $this->assertNull($insufficientResult['forecast_value']);

        // 5.2 Produk dengan Histori Tepat 7 Hari -> Kalkulasi SMA 7-Hari Akurat
        $readyProduct = Product::create([
            'category_id' => $category->id,
            'code' => 'INS-READY',
            'name' => 'Insektisida Regent 50SC 100ml',
            'unit' => 'Botol',
            'purchase_price' => 42000,
            'selling_price' => 50000,
            'stock' => 4, // Stok fisik saat ini
            'min_stock' => 5,
        ]);

        // Catat penjualan 7 hari: 6, 8, 7, 9, 5, 8, 6 (Total = 49)
        // Formula SMA: F(t+1) = 49 / 7 = 7.00
        $quantities = [6, 8, 7, 9, 5, 8, 6];
        foreach ($quantities as $index => $qty) {
            DailySalesSummary::create([
                'product_id' => $readyProduct->id,
                'sale_date' => now()->subDays(7 - $index)->toDateString(),
                'quantity_sold' => $qty,
            ]);
        }

        $readyResult = $service->calculateForProduct($readyProduct);

        $this->assertEquals(7.00, $readyResult['forecast_value']);
        $this->assertEquals(7, $readyResult['days_recorded']);
        // Stok aktual (4) < Forecast (7.00) -> Status: "perlu_restok", shortage = 7 - 4 = 3
        $this->assertEquals('perlu_restok', $readyResult['status']);
        $this->assertEquals(3, $readyResult['shortage_qty']);
    }

    /**
     * Skenario 6: Modul piutang: pencatatan pembayaran, update saldo, perubahan status (Lunas/Belum Lunas)
     * Sesuai PRD §13 Skenario 6 & §7.7 Modul Piutang
     */
    public function test_scenario_6_receivables_management_payment_and_auto_status(): void
    {
        $customer = Customer::create(['name' => 'Bapak Mulyono', 'phone' => '081377889900']);

        $sale = Sale::create([
            'invoice_no' => 'INV-20260914-0201',
            'user_id' => $this->admin->id,
            'customer_id' => $customer->id,
            'sold_at' => now(),
            'subtotal' => 500000,
            'total' => 500000,
            'payment_method' => 'credit',
        ]);

        $receivable = Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'remaining_balance' => 500000,
            'status' => 'belum_lunas',
        ]);

        // 6.1 Pembayaran Sebagian (Cicilan Rp200.000)
        $responsePartial = $this->actingAs($this->admin)->post("/receivables/{$receivable->id}/payments", [
            'amount' => 200000,
            'paid_at' => now()->toDateString(),
            'notes' => 'Cicilan panen pertama',
        ]);
        $responsePartial->assertRedirect();

        $receivable->refresh();
        $this->assertEquals(200000, $receivable->paid_amount);
        $this->assertEquals(300000, $receivable->remaining_balance);
        $this->assertEquals('belum_lunas', $receivable->status);

        // 6.2 Validasi Penolakan Pembayaran Melebihi Sisa Saldo
        $responseInvalid = $this->actingAs($this->admin)->post("/receivables/{$receivable->id}/payments", [
            'amount' => 400000, // Melebihi sisa 300.000
            'paid_at' => now()->toDateString(),
        ]);
        $responseInvalid->assertSessionHasErrors(['amount']);
        $this->assertEquals(300000, $receivable->fresh()->remaining_balance);

        // 6.3 Pelunasan Penuh (Rp300.000) -> Status Otomatis "Lunas"
        $responseFull = $this->actingAs($this->admin)->post("/receivables/{$receivable->id}/payments", [
            'amount' => 300000,
            'paid_at' => now()->toDateString(),
            'notes' => 'Pelunasan sisa tagihan',
        ]);
        $responseFull->assertRedirect();

        $receivable->refresh();
        $this->assertEquals(500000, $receivable->paid_amount);
        $this->assertEquals(0, $receivable->remaining_balance);
        $this->assertEquals('lunas', $receivable->status);
    }

    /**
     * Skenario 7: Laporan penjualan & piutang menampilkan data sesuai basis data, dan berhasil diekspor ke PDF
     * Sesuai PRD §13 Skenario 7 & §7.8 Modul Laporan
     */
    public function test_scenario_7_reports_and_dompdf_export(): void
    {
        // 7.1 Laporan Penjualan (Tampilan Web & Ekspor PDF)
        $responseSalesWeb = $this->actingAs($this->admin)->get('/reports/sales');
        $responseSalesWeb->assertOk();
        $responseSalesWeb->assertSee('Rincian Transaksi Penjualan');

        $responseSalesPdf = $this->actingAs($this->admin)->get('/reports/sales/pdf');
        $responseSalesPdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responseSalesPdf->headers->get('content-type'));

        // 7.2 Laporan Piutang Petani (Tampilan Web & Ekspor PDF)
        $responseRecWeb = $this->actingAs($this->admin)->get('/reports/receivables');
        $responseRecWeb->assertOk();
        $responseRecWeb->assertSee('Daftar Piutang');

        $responseRecPdf = $this->actingAs($this->admin)->get('/reports/receivables/pdf');
        $responseRecPdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responseRecPdf->headers->get('content-type'));

        // 7.3 Laporan Stok Barang & Rekomendasi Restok SMA (Tampilan Web & Ekspor PDF)
        $responseProdWeb = $this->actingAs($this->admin)->get('/reports/products');
        $responseProdWeb->assertOk();
        $responseProdWeb->assertSee('Daftar Stok Produk');

        $responseProdPdf = $this->actingAs($this->admin)->get('/reports/products/pdf');
        $responseProdPdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responseProdPdf->headers->get('content-type'));
    }
}
