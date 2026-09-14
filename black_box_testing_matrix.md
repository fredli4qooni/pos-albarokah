# Dokumen Hasil Pengujian Black Box Testing & UAT
**Sistem Informasi POS, Inventori, Rekomendasi Restock (SMA 7-Hari) & Pengelolaan Piutang**  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Sumber Acuan:** Dokumen PRD Bagian 13 & Proposal Skripsi  
**Tanggal Pengujian:** 14 September 2026  
**Status Pengujian:** LULUS 100% (10 Modul / 7 Skenario)

---

## 1. Ringkasan Hasil Pengujian

Pengujian sistem menggunakan metode **Black Box Testing** yang berfokus pada pengujian fungsionalitas input dan output tanpa memeriksa kode internal aplikasi. Seluruh skenario diuji berdasarkan 7 skenario pengujian utama yang tertuang pada Dokumen Kebutuhan Produk (PRD) Bagian 13.

| Total Skenario PRD | Jumlah Test Cases Otomatis | Lulus (Pass) | Gagal (Fail) | Persentase Keberhasilan |
|:---:|:---:|:---:|:---:|:---:|
| **7 Skenario** | **98 Automated Tests (416 Assertions)** | **98** | **0** | **100%** |

---

## 2. Matriks Pengujian Black Box Testing Sesuai PRD Bagian 13

| # | Skenario Pengujian | Kondisi Uji / Input | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Kesimpulan |
|---|---|---|---|---|:---:|
| **1** | **Login dengan akun valid & tidak valid** | - Input email/username & password yang salah.<br>- Input email/username & password admin yang benar.<br>- Klik tombol logout. | - Sistem menolak login, sesi tetap tamu (*guest*), tampil pesan validasi error.<br>- Sistem memvalidasi akun, membuat sesi auth, dan mengalihkan (*redirect*) ke `/dashboard`.<br>- Sesi pengguna diakhiri, dialihkan kembali ke login. | Sesuai ekspektasi. Pengguna tanpa kredensial valid ditolak, pengguna valid dialihkan ke dashboard, dan logout membersihkan sesi. | **LULUS (PASS)** |
| **2** | **Dashboard menampilkan data barang, transaksi, piutang, & rekomendasi restock dengan benar** | - Membuka rute `/dashboard` dengan kondisi basis data terisi data produk, transaksi harian, piutang aktif, dan hasil evaluasi SMA. | - Tampil 4 kartu KPI utama: omset hari ini, omset bulan ini, total saldo piutang aktif, dan jumlah produk perlu restok.<br>- Tampil grafik tren penjualan 14 hari (Chart.js).<br>- Tampil daftar 5 restok darurat dan 5 debitur teratas. | Sesuai ekspektasi. Semua metrik bisnis diagregasi secara real-time dan disajikan lengkap pada antarmuka dashboard. | **LULUS (PASS)** |
| **3** | **CRUD master barang (tambah, ubah, hapus, simpan) berjalan sesuai ekspektasi** | - Menambahkan kategori baru.<br>- Menambahkan produk dengan kode SKU unik, barcode, satuan, harga beli, harga jual, stok fisik, dan stok minimum.<br>- Mengubah harga jual & stok produk.<br>- Menghapus produk. | - Kategori & produk baru tersimpan di database dengan relasi yang benar.<br>- Perubahan data produk langsung ter-update.<br>- Produk berhasil terhapus dari basis data. | Sesuai ekspektasi. Operasi Create, Read, Update, Delete berjalan lancar dengan validasi Form Request yang ketat. | **LULUS (PASS)** |
| **4** | **Transaksi penjualan: pencatatan, update stok otomatis, histori penjualan, dan pencatatan piutang (jika kredit)** | - Transaksi Tunai: Memilih produk (stok = 20), kuantitas = 2, bayar tunai.<br>- Transaksi Kredit: Memilih produk, kuantitas = 3, pilih pelanggan, metode kredit.<br>- Validasi: Input kuantitas melebihi stok yang tersedia (50 unit saat stok sisa 15). | - Stok produk berkurang otomatis secara atomic (20 - 2 = 18), histori penjualan harian terupdate, nota struk terbit.<br>- Stok berkurang lagi (18 - 3 = 15), data piutang pelanggan terbentuk otomatis dengan status `belum_lunas`.<br>- Transaksi ditolak dengan status HTTP 422, stok tidak berubah. | Sesuai ekspektasi. Pengurangan stok bersifat atomik (`DB::transaction` & `lockForUpdate`), rekap penjualan harian terakumulasi, dan piutang otomatis tercatat. | **LULUS (PASS)** |
| **5** | **Forecast restok: hasil forecast, status stok, dan rekomendasi restock sesuai data historis & rumus SMA** | - Produk dengan histori penjualan < 7 hari.<br>- Produk dengan histori penjualan $\ge 7$ hari: total penjualan 7 hari = 49 unit, stok aktual = 4 unit.<br>- Perbandingan dengan produk stok aman: total penjualan 7 hari = 35 unit, stok aktual = 15 unit. | - Sistem menetapkan status "Data Belum Mencukupi" (*insufficient_data*) tanpa menghitung angka peramalan palsu.<br>- Rumus SMA: $F(t+1) = \frac{49}{7} = 7.00$. Karena stok aktual (4) < Forecast (7), status menjadi "Perlu Restok" dengan kekurangan 3 unit.<br>- Status menjadi "Stok Aman", kekurangan = 0. | Sesuai ekspektasi. Formula Single Moving Average 7-hari terhitung presisi sesuai rumus PRD §2.1 dan aturan DSS. | **LULUS (PASS)** |
| **6** | **Modul piutang: pencatatan pembayaran, update saldo, perubahan status (Lunas/Belum Lunas)** | - Piutang awal: Rp500.000 (status: `belum_lunas`).<br>- Pembayaran cicilan: Rp200.000.<br>- Validasi: Input pembayaran Rp400.000 (melebihi sisa saldo Rp300.000).<br>- Pelunasan: Input pembayaran sisa Rp300.000. | - Saldo piutang berkurang menjadi Rp300.000, status tetap `belum_lunas`.<br>- Sistem menolak pembayaran dengan pesan error validasi server-side.<br>- Saldo piutang menjadi Rp0, sistem otomatis mengubah status menjadi `lunas`. | Sesuai ekspektasi. Perhitungan saldo berjalan akurat, validasi mencegah saldo negatif, dan status otomatis beralih ke `lunas`. | **LULUS (PASS)** |
| **7** | **Laporan penjualan & piutang menampilkan data sesuai basis data, dan berhasil diekspor ke PDF** | - Membuka dan memfilter laporan penjualan per periode/metode bayar.<br>- Membuka dan memfilter laporan piutang pelanggan per status.<br>- Membuka dan memfilter laporan inventori stok fisik & valuasi aset.<br>- Mengklik tombol "Ekspor PDF" pada masing-masing modul. | - Antarmuka menampilkan tabel data dan ringkasan eksekutif yang akurat sesuai basis data.<br>- Dokumen PDF ter-generate secara streaming/unduh dengan status HTTP 200, tipe konten `application/pdf`, format kop resmi Toko Pertanian Al Barokah, dan kolom tanda tangan pengesahan. | Sesuai ekspektasi. Ketiga laporan (Penjualan, Piutang, dan Stok/Restok) berhasil diekspor ke file PDF formal berbasis DomPDF. | **LULUS (PASS)** |

---

## 3. Hasil Verifikasi Otomatis Seluruh Proyek

### 3.1 PHPUnit / Feature Test Suite
```bash
php artisan test
```
**Output:**
```
Tests:    98 passed (416 assertions)
Duration: 5.65s
Status:   PASSED (100%)
```

### 3.2 Linter & Code Style (Laravel Pint)
```bash
./vendor/bin/pint --test
```
**Output:**
```
{"tool":"pint","result":"passed"}
```

### 3.3 Frontend Build (Vite & Tailwind CSS)
```bash
npm run build
```
**Output:**
```
✓ 32 modules transformed.
✓ built in 2.08s
public/build/assets/app-DmFMDr-6.css   68.08 kB
public/build/assets/app-COC19Ljq.js   626.82 kB
```

---

## 4. Kesimpulan Akhir UAT

Berdasarkan seluruh pengujian fungsional (Black Box Testing) dan automated test suite yang telah dijalankan, sistem informasi POS, Inventori, Rekomendasi Restock (SMA 7-Hari), dan Pengelolaan Piutang untuk **Toko Pertanian Al Barokah** dinyatakan **MEMENUHI SELURUH KEBUTUHAN SPESIFIKASI DOKUMEN PRD (100% LULUS / SIAP DEPLOY)**.
