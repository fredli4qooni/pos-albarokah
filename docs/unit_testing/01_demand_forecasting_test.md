# Lampiran Pengujian Unit (Unit Testing)
## Modul 1: Algoritma Demand Forecasting (Single Moving Average 7-Hari & Ceiling Rounding)

**Proyek:** Sistem Informasi POS, Inventori, Rekomendasi Restock & Pengelolaan Piutang  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Berkas Pengujian:** [`tests/Unit/DemandForecastingTest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/tests/Unit/DemandForecastingTest.php)  
**Berkas Sumber/Target:** [`app/Services/ForecastService.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Services/ForecastService.php)  
**Metode:** *White-Box Testing* / *Automated Unit Testing*  
**Framework Pengujian:** PHPUnit 11 / Laravel Test Framework  
**Status Pengujian:** **LULUS 100% (4 Test Cases / 25 Assertions)**

---

## 1. Penjelasan Konsep & Tujuan Pengujian

### 1.1 Apa Itu Unit Testing pada Modul Ini?
Unit testing adalah metode pengujian perangkat lunak di mana komponen kode terkecil (fungsi/metode/rumus logika) diuji secara mandiri dan terisolasi tanpa bergantung pada antarmuka pengguna (UI/browser). 

Pada modul peramalan permintaan stok (*Demand Forecasting*), unit testing difokuskan untuk membuktikan bahwa **otak algoritma matematika Single Moving Average (SMA) dan fungsi pembulatan ceiling** menghasilkan keluaran angka yang 100% akurat sesuai rumus yang diajukan dalam penelitian skripsi.

### 1.2 Landasan Teori & Aturan Bisnis (PRD §2.1)
1. **Formula Single Moving Average (SMA)**:
   $$F(t+1) = \frac{\sum_{i=t-n+1}^{t} X_i}{n}$$
   dengan parameter jendela waktu tetap ($n = 7\text{ hari}$).
2. **Aturan Pembulatan ke Atas (*Ceiling Rounding*)**:
   Hasil perhitungan SMA menghasilkan bilangan pecahan desimal. Dalam konteks barang pertanian fisik (sak pupuk, botol herbisida, bungkus benih), kuantitas barang dagang harus bilangan bulat bulat positif. Fungsi pembulatan ke atas $\lceil F(t+1) \rceil$ (`ceil()`) diterapkan agar estimasi pasokan aman dan menghindari kekurangan stok (*out-of-stock risk*).
3. **Deteksi dan Validasi Data Historis**:
   Bila riwayat penjualan harian produk tercatat kurang dari 7 hari ($n < 7$), sistem dilarang keras melakukan ekstrapolasi/peramalan spekulatif, dan wajib mengembalikan status `"Data Belum Cukup"` atau `"Data Belum Mencukupi"`.
4. **Kalkulasi Rekomendasi Restock (*Shortage*)**:
   $$\text{Kekurangan Stok} = \begin{cases} \lceil F(t+1) - \text{Stok Aktual} \rceil, & \text{jika Stok Aktual} < F(t+1) \\ 0, & \text{jika Stok Aktual} \ge F(t+1) \end{cases}$$

---

## 2. Matriks Kasus Uji (Test Case Matrix)

| Kode Kasus Uji | Nama Fungsi Uji | Skenario Masukan (*Test Input*) | Logika & Asersi Uji | Hasil yang Diharapkan (*Expected*) | Hasil Aktual (*Actual*) | Status |
|:---:|---|---|---|---|---|:---:|
| **UT-DF-01** | `test_calculate_sma_7_days_with_ceiling_rounding` | Array data dummy 7 hari:<br>`[10, 12, 11, 15, 9, 11, 12]`<br>(Total penjualan = 80 unit) | • Total = 80.0<br>• SMA = 80 / 7 = 11.4285...<br>• ceil(11.4285...) = 12 | • `has_enough_data` = true<br>• `status` = 'Data Cukup'<br>• `forecast_value` = 11.43<br>• `forecast_ceil` = **12** | Sesuai ekspektasi matematis | **LULUS (PASS)** |
| **UT-DF-02** | `test_calculate_sma_detects_insufficient_data` | Array data penjualan 5 hari:<br>`[10, 14, 12, 8, 16]`<br>($n = 5 < 7$ hari) | Pengecekan syarat ambang batas minimal hari ($5 < 7$) | • `has_enough_data` = false<br>• `status` = **"Data Belum Cukup"**<br>• `forecast_value` = null<br>• `forecast_ceil` = null | Sesuai ekspektasi sistem | **LULUS (PASS)** |
| **UT-DF-03** | `test_calculate_for_product_returns_insufficient_data_status` | Data produk pada database SQLite dengan riwayat 4 catatan penjualan harian (`DailySalesSummary`) | Query basis data memverifikasi jumlah hari tercatat ($4 < 7$) | • `has_enough_data` = false<br>• `status` = 'insufficient_data'<br>• `status_label` = **"Data Belum Mencukupi"**<br>• `shortage_qty` = 0 | Sesuai ekspektasi sistem | **LULUS (PASS)** |
| **UT-DF-04** | `test_calculate_for_product_calculates_shortage_with_ceil` | Produk riil: Stok fisik = 5 unit. Riwayat 7 hari total 80 unit (rata-rata 11.43 unit/hari) | • Forecast = 11.43 unit<br>• Stok (5) < Forecast (11.43)<br>• Shortage = ceil(11.43 - 5) = 7 unit | • `status` = 'perlu_restok'<br>• `status_label` = 'Perlu Restok'<br>• `shortage_qty` = **7 unit**<br>• Tersimpan di `forecast_results` | Sesuai ekspektasi sistem | **LULUS (PASS)** |

---

## 3. Pembahasan Rinci Hasil Pengujian

### 3.1 Kasus Uji UT-DF-01: Verifikasi Akurasi Rumus SMA 7-Hari & Pembulatan Ceil
* **Tujuan**: Memastikan fungsi inti `calculateSMA()` memproses deret angka penjualan harian dan menerapkan pembulatan ke atas secara presisi.
* **Analisis Data Pengujian**:
  $$\sum_{i=1}^{7} X_i = 10 + 12 + 11 + 15 + 9 + 11 + 12 = 80\text{ unit}$$
  $$\text{SMA}_{\text{mentah}} = \frac{80}{7} = 11,4285714285714...$$
  $$\text{SMA}_{\text{tampil}} = \text{round}(11,4285714..., 2) = 11,43\text{ unit}$$
  $$\text{SMA}_{\text{ceil}} = \lceil 11,4285714... \rceil = 12\text{ unit}$$
* **Hasil Asersi**:
  1. `assertTrue($result['has_enough_data'])` $\rightarrow$ Valid.
  2. `assertEquals(80.0, $result['total'])` $\rightarrow$ Valid.
  3. `assertEqualsWithDelta(80/7, $result['raw_sma'], 0.0001)` $\rightarrow$ Valid.
  4. `assertEquals(12, $result['forecast_ceil'])` $\rightarrow$ Valid (tepat bernilai 12).

### 3.2 Kasus Uji UT-DF-02 & UT-DF-03: Deteksi Data Tidak Cukup ($n < 7$)
* **Tujuan**: Memastikan keamanan logika bisnis ketika suatu barang baru mulai dijual dan belum memiliki histori yang cukup untuk diramal.
* **Analisis Data Pengujian**:
  Sistem menerima data berukuran 5 elemen pada pengujian algoritma murni, serta 4 entri pada simulasi database SQLite.
* **Hasil Asersi**:
  1. Sistem menghentikan eksekusi peramalan sebelum membagi angka ($n$).
  2. Nilai `forecast_value` dan `forecast_ceil` bernilai `null` (tidak menghasilkan angka peramalan halusinatif).
  3. Status mengembalikan teks resmi `"Data Belum Cukup"` pada fungsi algoritma dan `"Data Belum Mencukupi"` pada integrasi database/UI.

### 3.3 Kasus Uji UT-DF-04: Kalkulasi Kekurangan Pasokan (*Shortage Quantity*)
* **Tujuan**: Menguji integrasi antara hasil peramalan dengan ketersediaan stok fisik di gudang.
* **Analisis Data Pengujian**:
  $$\text{Stok Fisik Saat Ini} = 5\text{ unit}$$
  $$\text{Peramalan Kebutuhan (SMA)} = 11,43\text{ unit}$$
  $$\text{Selisih Kebutuhan} = 11,43 - 5 = 6,43\text{ unit}$$
  $$\text{Rekomendasi Restok (Ceil)} = \lceil 6,43 \rceil = 7\text{ unit}$$
* **Hasil Asersi**:
  1. Status evaluasi otomatis beralih menjadi `'perlu_restok'`.
  2. Angka kekurangan pasokan persis `7` unit.
  3. Baris data berhasil tercatat pada tabel relasional `forecast_results`.

---

## 4. Bukti Log Eksekusi Terminal (PHPUnit Execution Log)

Berikut adalah rekaman keluaran asli dari konsol terminal saat perintah pengujian dijalankan:

```text
PS C:\Users\fredl\PROJECTS\pos-albarokah> php artisan test tests/Unit/DemandForecastingTest.php

   PASS  Tests\Unit\DemandForecastingTest
  ✓ calculate sma 7 days with ceiling rounding                         0.02s  
  ✓ calculate sma detects insufficient data                            0.01s  
  ✓ calculate for product returns insufficient data status             0.26s  
  ✓ calculate for product calculates shortage with ceil                0.23s  

  Tests:    4 passed (25 assertions)
  Duration: 0.52s
```

---

## 5. Kesimpulan Pengujian

Berdasarkan pengujian unit pada berkas `tests/Unit/DemandForecastingTest.php`, seluruh 4 skenario uji dengan 25 asersi dinyatakan **LULUS 100% (PASS)** tanpa ada kegagalan maupun galat (*error*).

Dapat disimpulkan bahwa:
1. Algoritma peramalan *Single Moving Average* (SMA) 7-hari pada sistem Toko Pertanian Al Barokah terbukti akurat secara matematis.
2. Logika pembulatan ke atas (`ceil`) berhasil mencegah timbulnya rekomendasi restok desimal/pecahan dan menjamin kecukupan kuantitas barang fisik di toko.
3. Batasan keamanan data historis $n \ge 7$ hari efektif mencegah kesalahan estimasi pada barang dagangan baru.
