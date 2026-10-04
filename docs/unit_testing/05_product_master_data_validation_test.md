# Lampiran Pengujian Unit (Unit Testing)
## Modul 5: Validasi Atribut Master Data (SKU Code 128, Barcode & Aturan Harga Jual)

**Proyek:** Sistem Informasi POS, Inventori, Rekomendasi Restock & Pengelolaan Piutang  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Berkas Pengujian:** [`tests/Unit/ProductMasterDataValidationTest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/tests/Unit/ProductMasterDataValidationTest.php)  
**Berkas Sumber/Target:** [`app/Http/Requests/StoreProductRequest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Http/Requests/StoreProductRequest.php), [`app/Http/Requests/UpdateProductRequest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Http/Requests/UpdateProductRequest.php), [`app/Models/Product.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Models/Product.php)  
**Metode:** *White-Box Testing* / *Automated Unit Testing*  
**Framework Pengujian:** PHPUnit 11 / Laravel Test Framework  
**Status Pengujian:** **LULUS 100% (3 Test Cases / 14 Assertions)**

---

## 1. Penjelasan Konsep & Tujuan Pengujian

### 1.1 Apa Itu Unit Testing pada Modul Ini?
Unit testing pada modul validasi master data difokuskan untuk menguji **dua aturan integritas bisnis penting sebelum data barang dagangan tersimpan di basis data**:
1. **Keunikan Identifikasi Barang (*Identity Uniqueness*)**: Memastikan setiap produk memiliki kode SKU (berbasis standar barcode Code 128) dan kode Barcode fisik yang unik, sehingga tidak terjadi duplikasi entitas barang yang dapat mengacaukan pemindaian barcode kasir maupun pelacakan stok inventori.
2. **Integritas Finansial Aturan Harga (*Profit Margin Rule*)**: Memastikan sistem secara aktif menolak input kasir/admin yang menetapkan harga jual barang lebih kecil atau sama dengan harga beli ($\text{Harga Jual} \le \text{Harga Beli}$). Hal ini mencegah potensi kerugian finansial akibat kesalahan manusia (*human error*).

### 1.2 Landasan Teori & Aturan Bisnis (PRD §7.2)
1. **Aturan Keunikan Identifikasi SKU & Barcode**:
   $$\forall p_1, p_2 \in \text{Products}, \quad p_1 \ne p_2 \implies \text{code}(p_1) \ne \text{code}(p_2) \;\land\; \text{barcode}(p_1) \ne \text{barcode}(p_2)$$
2. **Aturan Validasi Margin Keuntungan**:
   $$\text{Margin Keuntungan} = \text{Harga Jual} - \text{Harga Beli}$$
   $$\text{Status Validasi} = \begin{cases} \text{VALID (Lolos)}, & \text{jika Harga Jual} > \text{Harga Beli} \\ \text{INVALID (Ditolak)}, & \text{jika Harga Jual} \le \text{Harga Beli} \end{cases}$$
   Diimplementasikan menggunakan aturan validasi deklaratif Laravel: `'selling_price' => ['required', 'numeric', 'min:0', 'gt:purchase_price']`.

---

## 2. Matriks Kasus Uji (Test Case Matrix)

| Kode Kasus Uji | Nama Fungsi Uji | Skenario Masukan (*Test Input*) | Logika & Asersi Uji | Hasil yang Diharapkan (*Expected*) | Hasil Aktual (*Actual*) | Status |
|:---:|---|---|---|---|---|:---:|
| **UT-MD-01** | `test_code_128_sku_uniqueness_validation` | • Produk eksis: SKU = `'SKU-C128-001'`<br>• Input baru: SKU = `'SKU-C128-001'` (kembar) | Evaluasi aturan `unique:products,code` via Validator | • Validasi gagal (`fails() = true`)<br>• Field `code` error<br>• Pesan: "Kode SKU sudah digunakan produk lain." | Sesuai ekspektasi keunikan SKU | **LULUS (PASS)** |
| **UT-MD-02** | `test_barcode_uniqueness_validation` | • Produk eksis: Barcode = `'8997012345678'`<br>• Input baru: Barcode = `'8997012345678'` (kembar) | Evaluasi aturan `unique:products,barcode` | • Validasi gagal (`fails() = true`)<br>• Field `barcode` error<br>• Pesan: "Barcode sudah terdaftar pada produk lain." | Sesuai ekspektasi barcode | **LULUS (PASS)** |
| **UT-MD-03** | `test_selling_price_must_be_strictly_greater_than_purchase_price` | Beli = Rp50.000<br>• Kasus 1: Jual = Rp45.000 (Rugi)<br>• Kasus 2: Jual = Rp50.000 (Impas)<br>• Kasus 3: Jual = Rp60.000 (Untung) | Evaluasi aturan `gt:purchase_price` | • Kasus 1: Gagal, pesan "Harga jual harus lebih besar dari harga beli."<br>• Kasus 2: Gagal, pesan identik<br>• Kasus 3: **Lolos (Valid)** | Sesuai ekspektasi aturan harga | **LULUS (PASS)** |

---

## 3. Pembahasan Rinci Hasil Pengujian

### 3.1 Kasus Uji UT-MD-01: Validasi Keunikan Kode SKU (Code 128)
* **Tujuan**: Mencegah redundansi dan ambiguitas identitas barang saat kasir memindai label barcode Code 128 di meja transaksi.
* **Hasil Pengujian**:
  1. `assertTrue($validator->fails())` $\rightarrow$ Valid.
  2. `assertTrue($validator->errors()->has('code'))` $\rightarrow$ Valid.
  3. `assertEquals('Kode SKU sudah digunakan produk lain.', $validator->errors()->first('code'))` $\rightarrow$ Valid. Pesan kesalahan berbahasa Indonesia tersampaikan jelas kepada pengguna.

### 3.2 Kasus Uji UT-MD-02: Validasi Keunikan Barcode Produk
* **Tujuan**: Menjamin nomor barcode pabrikan (EAN-13/UPC) bersifat tunggal di katalog barang.
* **Hasil Pengujian**:
  Sistem menolak entri kedua yang menggunakan barcode `8997012345678` yang sudah dipakai oleh produk *Gramoxone 1 Liter*.

### 3.3 Kasus Uji UT-MD-03: Penegakan Aturan Batas Harga ($\text{Harga Jual} > \text{Harga Beli}$)
* **Tujuan**: Memastikan integritas penetapan harga barang dagangan toko pertanian agar selalu menghasilkan laba kotor positif.
* **Analisis Data Pengujian**:
  1. **Skenario Rugi ($\text{Jual } 45.000 < \text{Beli } 50.000$)**:
     Sistem mendeteksi selisih $-\text{Rp } 5.000$ dan langsung membatalkan penyimpanan dengan pesan: *"Harga jual harus lebih besar dari harga beli."*.
  2. **Skenario Nol Margin ($\text{Jual } 50.000 == \text{Beli } 50.000$)**:
     Sistem tetap menolak karena operator tidak menetapkan margin laba ($\text{Margin} = 0$).
  3. **Skenario Laba Wajar ($\text{Jual } 60.000 > \text{Beli } 50.000$)**:
     Sistem meloloskan validasi data (`$validator->fails() === false`), margin keuntungan tercatat $\text{Rp } 10.000$.

---

## 4. Bukti Log Eksekusi Terminal (PHPUnit Execution Log)

Berikut adalah rekaman keluaran konsol terminal saat perintah pengujian dijalankan:

```text
PS C:\Users\fredl\PROJECTS\pos-albarokah> php artisan test tests/Unit/ProductMasterDataValidationTest.php

   PASS  Tests\Unit\ProductMasterDataValidationTest
  ✓ code 128 sku uniqueness validation                                0.23s  
  ✓ barcode uniqueness validation                                      0.23s  
  ✓ selling price must be strictly greater than purchase price         0.03s  

  Tests:    3 passed (14 assertions)
  Duration: 0.50s
```

---

## 5. Kesimpulan Pengujian

Berdasarkan pengujian unit otomatis pada berkas `tests/Unit/ProductMasterDataValidationTest.php`, seluruh 3 skenario uji dengan 14 asersi dinyatakan **LULUS 100% (PASS)** tanpa ada galat (*error*).

Dapat disimpulkan bahwa:
1. Sistem POS Toko Pertanian Al Barokah berhasil mencegah duplikasi data master barang melalui validasi keunikan kode SKU (Code 128) dan Barcode secara ketat.
2. Aturan validasi harga `gt:purchase_price` terbukti efektif melindungi toko dari potensi kerugian finansial akibat kekeliruan kasir dalam memasukkan harga jual barang dagangan.
