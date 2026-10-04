# Lampiran Pengujian Unit (Unit Testing)
## Modul 4: Transaksi Atomik Basis Data (Database Transaction Rollback / ACID)

**Proyek:** Sistem Informasi POS, Inventori, Rekomendasi Restock & Pengelolaan Piutang  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Berkas Pengujian:** [`tests/Unit/DatabaseTransactionRollbackTest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/tests/Unit/DatabaseTransactionRollbackTest.php)  
**Berkas Sumber/Target:** [`app/Http/Controllers/SaleController.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Http/Controllers/SaleController.php), `Illuminate\Support\Facades\DB`  
**Metode:** *White-Box Testing* / *Automated Unit Testing*  
**Framework Pengujian:** PHPUnit 11 / Laravel Test Framework  
**Status Pengujian:** **LULUS 100% (2 Test Cases / 10 Assertions)**

---

## 1. Penjelasan Konsep & Tujuan Pengujian

### 1.1 Apa Itu Unit Testing pada Modul Ini?
Unit testing pada modul transaksi basis data difokuskan untuk menguji **kepatuhan sistem terhadap prinsip Atomisitas (*Atomicity*)** dalam standar ACID (*Atomicity, Consistency, Isolation, Durability*):
* **Konsep *All-or-Nothing***: Sebuah transaksi penjualan kasir melibatkan beberapa operasi berantai: (1) mencatat nota di tabel `sales`, (2) memotong kuantitas stok di tabel `products`, (3) mencatat rincian barang di tabel `sale_items`, dan (4) mencatat piutang di tabel `receivables` jika pembayaran kredit.
* **Mekanisme Rollback Otomatis**: Jika terjadi kegagalan sistem pada salah satu tahapan (misal koneksi jaringan terputus, galat query, atau kegagalan disk saat menyimpan item), **seluruh perubahan yang mendahuluinya wajib dibatalkan (*rollback*) secara otomatis** sehingga basis data tidak berada dalam kondisi korup atau tidak konsisten (*inconsistent state*).

### 1.2 Landasan Teori & Aturan Bisnis (PRD §2.2 & AGENTS.md §2.2)
1. **Prinsip Atomicity Transaksi**:
   $$\text{Transaksi Penjualan} = \{ \Delta \text{Sales}, \Delta \text{Stock}, \Delta \text{SaleItems}, \Delta \text{Receivables} \}$$
   $$\text{Status Akhir} = \begin{cases} \text{COMMIT} & \iff \text{Semua operasi berhasil 100\%} \\ \text{ROLLBACK} & \iff \exists \text{ satu operasi yang gagal/error} \end{cases}$$
2. **Pencegahan Galat Stok Gaib (*Phantom Stock Reduction*)**:
   Jika transaksi gagal di tengah jalan, stok barang yang sempat dikurangi wajib dikembalikan utuh ke angka sebelum transaksi dimulai.

---

## 2. Matriks Kasus Uji (Test Case Matrix)

| Kode Kasus Uji | Nama Fungsi Uji | Skenario Masukan (*Test Input*) | Logika & Asersi Uji | Hasil yang Diharapkan (*Expected*) | Hasil Aktual (*Actual*) | Status |
|:---:|---|---|---|---|---|:---:|
| **UT-ACID-01** | `test_db_transaction_rolls_back_sale_and_stock_on_item_failure` | • Stok awal = 25 unit<br>• Simpan Sale (sukses)<br>• Kurangi stok 5 unit<br>• Simulasikan Exception gagal query pada `sale_items` | • Eksepsi ditangkap<br>• Cek tabel `sales`<br>• Cek stok produk `products`<br>• Cek tabel `sale_items` | • Transaksi di-rollback<br>• Data `sales` tidak tersimpan<br>• Stok kembali utuh **25 unit**<br>• Tabel `sale_items` kosong (0) | Sesuai ekspektasi atomisitas | **LULUS (PASS)** |
| **UT-ACID-02** | `test_db_transaction_rolls_back_on_database_integrity_violation` | • Stok awal = 10 unit<br>• Kurangi stok 2 unit<br>• Simpan transaksi dengan `customer_id` tidak valid (ID: 999999) | • Foreign key violation<br>• QueryException terjadi<br>• Verifikasi stok produk | • Transaksi dibatalkan<br>• Data `sales` tidak tersimpan<br>• Stok kembali utuh **10 unit** | Sesuai ekspektasi integritas data | **LULUS (PASS)** |

---

## 3. Pembahasan Rinci Hasil Pengujian

### 3.1 Kasus Uji UT-ACID-01: Pembatalan Transaksi saat Penulisan Item Gagal
* **Tujuan**: Membuktikan bahwa `DB::transaction()` mengembalikan data ke titik awal (*restore*) ketika operasi penulisan rincian item gagal tereksekusi.
* **Kronologi Eksekusi Pengujian**:
  1. Produk pupuk *NPK Mutiara 16-16-16* dibuat dengan kuantitas stok awal $= 25\text{ unit}$.
  2. Masuk ke blok `DB::transaction()`:
     - Header penjualan dengan nomor invoice `INV-SIMULASI-FAIL-001` berhasil dibuat sementara.
     - Stok produk dipotong 5 unit sehingga kuantitas sementara menjadi $25 - 5 = 20\text{ unit}$.
     - Sebelum penulisan ke tabel `sale_items` selesai, program memicu eksepsi simulasi kegagalan fatal: `throw new Exception(...)`.
  3. Blok `DB::transaction()` mendeteksi eksepsi yang tidak tertangani dan langsung mengeksekusi perintah SQL `ROLLBACK`.
* **Hasil Asersi**:
  1. `assertDatabaseMissing('sales', ['invoice_no' => 'INV-SIMULASI-FAIL-001'])` $\rightarrow$ Valid. Nota tidak pernah ada di database.
  2. `assertEquals(25, $product->fresh()->stock)` $\rightarrow$ Valid. Stok fisik produk yang sempat turun ke 20 unit otomatis pulih kembali ke **25 unit**.
  3. `assertDatabaseCount('sale_items', 0)` $\rightarrow$ Valid. Tidak ada sampah data sisa transaksi.

### 3.2 Kasus Uji UT-ACID-02: Pembatalan saat Terjadi Pelanggaran Relasi Kunci Asing (*Foreign Key*)
* **Tujuan**: Menguji ketahanan mekanisme atomisitas saat terjadi pelanggaran integritas referensial level database MySQL/SQLite.
* **Hasil Pengujian**:
  1. Saat sistem mencoba memasukkan `customer_id` yang tidak terdaftar, database melempar galat pelanggaran integritas referensial (*foreign key violation*).
  2. Pengurangan stok produk sebesar 2 unit yang terjadi sebelum query error seketika dibatalkan, dan kuantitas stok produk tetap utuh sebesar **10 unit**.

---

## 4. Bukti Log Eksekusi Terminal (PHPUnit Execution Log)

Berikut adalah rekaman keluaran konsol terminal saat perintah pengujian dijalankan:

```text
PS C:\Users\fredl\PROJECTS\pos-albarokah> php artisan test tests/Unit/DatabaseTransactionRollbackTest.php

   PASS  Tests\Unit\DatabaseTransactionRollbackTest
  ✓ db transaction rolls back sale and stock on item failure           0.24s  
  ✓ db transaction rolls back on database integrity violation          0.23s  

  Tests:    2 passed (10 assertions)
  Duration: 0.47s
```

---

## 5. Kesimpulan Pengujian

Berdasarkan pengujian unit otomatis pada berkas `tests/Unit/DatabaseTransactionRollbackTest.php`, seluruh 2 skenario uji dengan 10 asersi dinyatakan **LULUS 100% (PASS)** tanpa ada galat (*error*).

Dapat disimpulkan bahwa:
1. Arsitektur transaksi penjualan pada Toko Pertanian Al Barokah terbukti memenuhi standar **Atomicity (ACID)**.
2. Mekanisme `DB::transaction()` berhasil menjamin bahwa pemotongan stok barang, pembuatan faktur penjualan, dan pencatatan rincian item bersifat satu kesatuan utuh (*atomic*).
3. Basis data terbukti kebal dari kerusakan data parsial (*half-saved data*) saat terjadi gangguan jaringan atau eksepsi mendadak.
