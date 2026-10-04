# Lampiran Pengujian Unit (Unit Testing)
## Modul 3: Kalkulasi Transaksi POS & Pemutakhiran Stok Fisik

**Proyek:** Sistem Informasi POS, Inventori, Rekomendasi Restock & Pengelolaan Piutang  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Berkas Pengujian:** [`tests/Unit/PosTransactionStockTest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/tests/Unit/PosTransactionStockTest.php)  
**Berkas Sumber/Target:** [`app/Http/Controllers/SaleController.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Http/Controllers/SaleController.php), [`app/Models/Product.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Models/Product.php)  
**Metode:** *White-Box Testing* / *Automated Unit Testing*  
**Framework Pengujian:** PHPUnit 11 / Laravel Test Framework  
**Status Pengujian:** **LULUS 100% (3 Test Cases / 10 Assertions)**

---

## 1. Penjelasan Konsep & Tujuan Pengujian

### 1.1 Apa Itu Unit Testing pada Modul Ini?
Unit testing pada modul kasir (*Point of Sale*) dan pemutakhiran stok difokuskan untuk menguji **keandalan aritmatika dan konsistensi data stok fisik**:
1. **Presisi Aritmatika Transaksi**: Memastikan tidak ada galat pembulatan (*rounding errors*) atau perbedaan nominal sepeser pun pada perhitungan subtotal item, akumulasi total tagihan belanja, dan sisa uang kembalian kasir.
2. **Otomatisasi Pemotongan Stok Fisik (*Stock Decrement*)**: Memastikan pemanggilan fungsi pengurangan stok otomatis (`decrement`) memotong kuantitas stok riil pada tabel `products` persis sesuai jumlah kuantitas barang yang ditransaksikan.

### 1.2 Landasan Teori & Aturan Bisnis (PRD §2.2)
1. **Formula Subtotal Item**:
   $$\text{Subtotal}_i = \text{Harga Satuan}_i \times \text{Kuantitas}_i$$
2. **Formula Total Belanja**:
   $$\text{Total Belanja} = \sum_{i=1}^{k} \text{Subtotal}_i$$
3. **Formula Uang Kembalian Tunai**:
   $$\text{Kembalian} = \max(0, \text{Uang Diterima} - \text{Total Belanja})$$
   Sistem memastikan nilai kembalian selalu non-negatif dan presisi hingga satuan rupiah.
4. **Aturan Konsistensi Pengurangan Stok**:
   $$\text{Stok Akhir} = \text{Stok Awal} - \sum \text{Kuantitas Terjual}$$
   Pengurangan wajib termutakhirkan secara langsung (*real-time*) ke basis data agar tidak terjadi selisih stok fisik (*stock discrepancy*).

---

## 2. Matriks Kasus Uji (Test Case Matrix)

| Kode Kasus Uji | Nama Fungsi Uji | Skenario Masukan (*Test Input*) | Logika & Asersi Uji | Hasil yang Diharapkan (*Expected*) | Hasil Aktual (*Actual*) | Status |
|:---:|---|---|---|---|---|:---:|
| **UT-POS-01** | `test_subtotal_and_total_calculation_precision` | 3 item barang belanja:<br>• 4 botol @ Rp85.000<br>• 3 bungkus @ Rp45.000<br>• 1 sak @ Rp275.000 | • Subtotal 1 = 4 x 85.000 = 340.000<br>• Subtotal 2 = 3 x 45.000 = 135.000<br>• Subtotal 3 = 1 x 275.000 = 275.000<br>• Total = 340.000 + 135.000 + 275.000 | • Subtotal 1 = Rp340.000<br>• Subtotal 2 = Rp135.000<br>• Subtotal 3 = Rp275.000<br>• Total = **Rp750.000** | Sesuai ekspektasi matematika | **LULUS (PASS)** |
| **UT-POS-02** | `test_cash_change_calculation_precision` | Total belanja = Rp750.000<br>• Skenario A: Diterima = Rp800.000<br>• Skenario B: Diterima = Rp750.000 (uang pas) | • Kembalian A = 800.000 - 750.000 = 50.000<br>• Kembalian B = 750.000 - 750.000 = 0 | • Kembalian A = **Rp50.000**<br>• Kembalian B = **Rp0** | Sesuai ekspektasi kasir | **LULUS (PASS)** |
| **UT-POS-03** | `test_automatic_stock_decrement_reduces_physical_stock` | Produk awal: Stok = 50 botol.<br>• Transaksi 1: Beli 8 botol<br>• Transaksi 2: Beli 12 botol | • decrement(8) $\rightarrow$ 50 - 8 = 42<br>• decrement(12) $\rightarrow$ 42 - 12 = 30<br>• Cek database `products` | • Stok setelah Trx 1 = **42 unit**<br>• Stok setelah Trx 2 = **30 unit**<br>• Tercatat di DB `products` | Sesuai ekspektasi inventori | **LULUS (PASS)** |

---

## 3. Pembahasan Rinci Hasil Pengujian

### 3.1 Kasus Uji UT-POS-01: Verifikasi Presisi Subtotal dan Akumulasi Tagihan
* **Tujuan**: Memastikan integritas perhitungan transaksi belanja majemuk (*multi-item*) bebas dari kesalahan floating-point aritmatika.
* **Analisis Data Pengujian**:
  $$\text{Subtotal}_1 = 4 \times 85.000 = 340.000$$
  $$\text{Subtotal}_2 = 3 \times 45.000 = 135.000$$
  $$\text{Subtotal}_3 = 1 \times 275.000 = 275.000$$
  $$\text{Total Belanja} = 340.000 + 135.000 + 275.000 = 750.000$$
* **Hasil Asersi**:
  1. `assertEquals(340000.0, $calculatedSubtotals[0])` $\rightarrow$ Valid.
  2. `assertEquals(135000.0, $calculatedSubtotals[1])` $\rightarrow$ Valid.
  3. `assertEquals(275000.0, $calculatedSubtotals[2])` $\rightarrow$ Valid.
  4. `assertEquals(750000.0, $totalBelanja)` $\rightarrow$ Valid (tepat Rp 750.000).

### 3.2 Kasus Uji UT-POS-02: Verifikasi Perhitungan Sisa Uang Kembalian
* **Tujuan**: Memastikan perhitungan uang kembalian konsumen akurat baik saat menerima pembayaran tunai pecahan lebih besar maupun saat pembayaran pas.
* **Analisis Data Pengujian**:
  $$\text{Skenario A (Uang Lebih): } \text{Kembalian} = 800.000 - 750.000 = 50.000$$
  $$\text{Skenario B (Uang Pas): } \text{Kembalian} = 750.000 - 750.000 = 0$$
* **Hasil Asersi**:
  1. `assertEquals(50000.0, $kembalianSkenario1)` $\rightarrow$ Valid.
  2. `assertEquals(0.0, $kembalianSkenario2)` $\rightarrow$ Valid.

### 3.3 Kasus Uji UT-POS-03: Otomatisasi Pemotongan Kuantitas Stok Gudang
* **Tujuan**: Membuktikan bahwa fungsi pengurangan stok otomatis (`decrement`) memutakhirkan nilai kuantitas fisik pada tabel `products` secara tepat waktu dan konsisten.
* **Analisis Data Pengujian**:
  Produk sampel: *Dharmabas 500 EC* dengan stok awal 50 botol.
  1. Transaksi pertama sebesar 8 botol:
     $$\text{Stok Tersisa} = 50 - 8 = 42\text{ botol}$$
  2. Transaksi kedua berturut-turut sebesar 12 botol:
     $$\text{Stok Tersisa} = 42 - 12 = 30\text{ botol}$$
* **Hasil Asersi**:
  1. `assertEquals(42, $product->fresh()->stock)` dan `assertDatabaseHas('products', ['stock' => 42])` $\rightarrow$ Valid.
  2. `assertEquals(30, $product->fresh()->stock)` dan `assertDatabaseHas('products', ['stock' => 30])` $\rightarrow$ Valid.

---

## 4. Bukti Log Eksekusi Terminal (PHPUnit Execution Log)

Berikut adalah rekaman keluaran konsol terminal saat perintah pengujian dijalankan:

```text
PS C:\Users\fredl\PROJECTS\pos-albarokah> php artisan test tests/Unit/PosTransactionStockTest.php

   PASS  Tests\Unit\PosTransactionStockTest
  ✓ subtotal and total calculation precision                           0.02s  
  ✓ cash change calculation precision                                  0.01s  
  ✓ automatic stock decrement reduces physical stock                   0.26s  

  Tests:    3 passed (10 assertions)
  Duration: 0.55s
```

---

## 5. Kesimpulan Pengujian

Berdasarkan pengujian unit otomatis pada berkas `tests/Unit/PosTransactionStockTest.php`, seluruh 3 skenario uji dengan 10 asersi dinyatakan **LULUS 100% (PASS)** tanpa ada kegagalan maupun galat (*error*).

Dapat disimpulkan bahwa:
1. Logika aritmatika POS kasir pada Toko Pertanian Al Barokah terbukti akurat dalam menghitung subtotal belanja, total tagihan nota, serta sisa uang kembalian tunai.
2. Pemotongan stok otomatis (`decrement`) berjalan konsisten dan sinkron secara langsung (*real-time*) dengan data fisik di basis data gudang.
