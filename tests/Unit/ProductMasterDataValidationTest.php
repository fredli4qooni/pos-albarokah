<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductMasterDataValidationTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Herbisida', 'slug' => 'herbisida']);
    }

    private function validateProductData(array $data): \Illuminate\Validation\Validator
    {
        $request = new StoreProductRequest;

        return Validator::make($data, $request->rules(), $request->messages());
    }

    /**
     * Uji Poin 6: Validasi Keunikan Kode SKU (Code 128)
     * Skenario: Menolak penyimpanan master barang jika kode SKU yang dimasukkan
     * sudah terdaftar pada produk lain di basis data.
     */
    public function test_code_128_sku_uniqueness_validation(): void
    {
        // 1. Simpan produk pertama dengan SKU tertentu
        Product::create([
            'category_id' => $this->category->id,
            'code' => 'SKU-C128-001',
            'barcode' => '8991112223334',
            'name' => 'Roundup 1L Original',
            'unit' => 'Botol',
            'purchase_price' => 85000,
            'selling_price' => 95000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        // 2. Coba validasi payload dengan kode SKU yang sama persis
        $duplicatePayload = [
            'category_id' => $this->category->id,
            'code' => 'SKU-C128-001', // Duplikat SKU
            'barcode' => '8999998887776',
            'name' => 'Roundup 1L Kemasan Baru',
            'unit' => 'Botol',
            'purchase_price' => 86000,
            'selling_price' => 98000,
            'stock' => 5,
            'min_stock' => 1,
        ];

        $validator = $this->validateProductData($duplicatePayload);

        $this->assertTrue($validator->fails(), 'Validasi harus gagal saat kode SKU sudah terdaftar.');
        $this->assertTrue($validator->errors()->has('code'));
        $this->assertEquals(
            'Kode SKU sudah digunakan produk lain.',
            $validator->errors()->first('code')
        );
    }

    /**
     * Uji Poin 6: Validasi Keunikan Barcode Produk
     */
    public function test_barcode_uniqueness_validation(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'code' => 'SKU-GRAM-01',
            'barcode' => '8997012345678',
            'name' => 'Gramoxone 1 Liter',
            'unit' => 'Botol',
            'purchase_price' => 70000,
            'selling_price' => 80000,
            'stock' => 15,
            'min_stock' => 3,
        ]);

        $duplicateBarcodePayload = [
            'category_id' => $this->category->id,
            'code' => 'SKU-GRAM-02',
            'barcode' => '8997012345678', // Barcode duplikat
            'name' => 'Gramoxone Refill',
            'unit' => 'Botol',
            'purchase_price' => 68000,
            'selling_price' => 78000,
            'stock' => 10,
            'min_stock' => 2,
        ];

        $validator = $this->validateProductData($duplicateBarcodePayload);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('barcode'));
        $this->assertEquals(
            'Barcode sudah terdaftar pada produk lain.',
            $validator->errors()->first('barcode')
        );
    }

    /**
     * Uji Poin 6: Validasi Aturan Harga (harga_jual > harga_beli)
     * Skenario 1: harga_jual < harga_beli (Rugi) -> Harus DITOLAK
     * Skenario 2: harga_jual == harga_beli (Nol Margin) -> Harus DITOLAK
     * Skenario 3: harga_jual > harga_beli (Ada Margin) -> DITERIMA (Lolos)
     */
    public function test_selling_price_must_be_strictly_greater_than_purchase_price(): void
    {
        $basePayload = [
            'category_id' => $this->category->id,
            'code' => 'SKU-TEST-PRICE',
            'barcode' => '8995556667778',
            'name' => 'Produk Uji Aturan Harga',
            'unit' => 'Botol',
            'stock' => 10,
            'min_stock' => 2,
        ];

        // Skenario 1: Harga Jual (Rp 45.000) < Harga Beli (Rp 50.000)
        $payloadRugi = array_merge($basePayload, [
            'purchase_price' => 50000,
            'selling_price' => 45000,
        ]);
        $validatorRugi = $this->validateProductData($payloadRugi);
        $this->assertTrue($validatorRugi->fails(), 'Validasi harus gagal saat harga jual lebih kecil dari harga beli.');
        $this->assertTrue($validatorRugi->errors()->has('selling_price'));
        $this->assertEquals(
            'Harga jual harus lebih besar dari harga beli.',
            $validatorRugi->errors()->first('selling_price')
        );

        // Skenario 2: Harga Jual (Rp 50.000) == Harga Beli (Rp 50.000)
        $payloadSama = array_merge($basePayload, [
            'purchase_price' => 50000,
            'selling_price' => 50000,
        ]);
        $validatorSama = $this->validateProductData($payloadSama);
        $this->assertTrue($validatorSama->fails(), 'Validasi harus gagal saat harga jual sama dengan harga beli.');
        $this->assertTrue($validatorSama->errors()->has('selling_price'));
        $this->assertEquals(
            'Harga jual harus lebih besar dari harga beli.',
            $validatorSama->errors()->first('selling_price')
        );

        // Skenario 3: Harga Jual (Rp 60.000) > Harga Beli (Rp 50.000)
        $payloadUntung = array_merge($basePayload, [
            'purchase_price' => 50000,
            'selling_price' => 60000,
        ]);
        $validatorUntung = $this->validateProductData($payloadUntung);
        $this->assertFalse($validatorUntung->fails(), 'Validasi harus lolos saat harga jual lebih besar dari harga beli.');
        $this->assertFalse($validatorUntung->errors()->has('selling_price'));
    }
}
