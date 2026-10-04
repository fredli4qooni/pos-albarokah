# Lampiran Pengujian Unit (Unit Testing)
## Modul 2: Autentikasi & Keamanan (Bcrypt Hashing & Rate Limiting 5 Kali Gagal)

**Proyek:** Sistem Informasi POS, Inventori, Rekomendasi Restock & Pengelolaan Piutang  
**Studi Kasus:** Toko Pertanian Al Barokah  
**Berkas Pengujian:** [`tests/Unit/SecurityAuthTest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/tests/Unit/SecurityAuthTest.php)  
**Berkas Sumber/Target:** [`app/Http/Requests/Auth/LoginRequest.php`](file:///c:/Users/fredl/PROJECTS/pos-albarokah/app/Http/Requests/Auth/LoginRequest.php), `Illuminate\Support\Facades\Hash`, `Illuminate\Support\Facades\RateLimiter`  
**Metode:** *White-Box Testing* / *Automated Unit Testing*  
**Framework Pengujian:** PHPUnit 11 / Laravel Test Framework  
**Status Pengujian:** **LULUS 100% (3 Test Cases / 12 Assertions)**

---

## 1. Penjelasan Konsep & Tujuan Pengujian

### 1.1 Apa Itu Unit Testing pada Modul Ini?
Unit testing pada modul keamanan dan autentikasi difokuskan untuk menguji **dua lapisan pertahanan kritis sistem**:
1. **Kriptografi Penyimpanan Kata Sandi**: Memastikan kata sandi pengguna tidak pernah disimpan dalam bentuk teks mentah (*plaintext*), melainkan melalui fungsi *hashing* satu arah algoritma **Bcrypt** ($2y$), serta memastikan mekanisme verifikasi matematisnya peka huruf (*case-sensitive*).
2. **Mitigasi Serangan Tebak Kata Sandi (*Brute Force Protection*)**: Menguji mekanisme *Rate Limiter* yang membatasi toleransi kesalahan login maksimal **5 kali percobaan gagal berturut-turut** sebelum sistem mengunci input (*lockout*) demi melindungi akun kasir/pemilik toko.

### 1.2 Landasan Teori & Aturan Bisnis
1. **Algoritma Hashing Bcrypt**:
   Bcrypt adalah fungsi derivasi kunci berbasis cipher Blowfish yang adaptif (*salted and keyed*). Rumus pembentukan hash:
   $$\text{Hash} = \text{Bcrypt}(\text{Password}, \text{Salt}, \text{Cost})$$
   Karakteristik kunci:
   - Satu arah (*irreversible*): Nilai hash tidak dapat didekripsi kembali menjadi plaintext.
   - Verifikasi berbasis nilai matematis `Hash::check($input, $hash)`.
2. **Aturan Rate Limiting Login (Maksimal 5 Kali)**:
   - Setiap kegagalan kredensial memicu `RateLimiter::hit($key)`.
   - Ambang batas ($T_{\text{max}} = 5$).
   - Jika $\text{Percobaan Gagal} \ge 5$, fungsi `tooManyAttempts()` bernilai `true` dan melempar `ValidationException` dengan pesan penguncian sementara (*throttle lockout*).
   - Pembersihan (*clear*) dilakukan secara otomatis hanya jika proses login berhasil diverifikasi.

---

## 2. Matriks Kasus Uji (Test Case Matrix)

| Kode Kasus Uji | Nama Fungsi Uji | Skenario Masukan (*Test Input*) | Logika & Asersi Uji | Hasil yang Diharapkan (*Expected*) | Hasil Aktual (*Actual*) | Status |
|:---:|---|---|---|---|---|:---:|
| **UT-SEC-01** | `test_bcrypt_password_hashing_and_verification` | Plaintext: `'PasswordPetani#2026'`<br>Hash: `Hash::make(...)`<br>Uji sandi salah: `'PasswordSalahTotal'` & `'passwordpetani#2026'` | • Password $\ne$ Hash<br>• Prefix hash diawali `$2y$`<br>• Hash::check(benar) = true<br>• Hash::check(salah) = false | • Hash tidak sama dengan teks mentah<br>• Verifikasi password cocok bernilai `true`<br>• Verifikasi password salah & beda kapitalisasi bernilai `false` | Sesuai ekspektasi kriptografi | **LULUS (PASS)** |
| **UT-SEC-02** | `test_rate_limiter_blocks_input_after_five_failed_attempts` | Throttle Key: `'test_login\|127.0.0.1'`<br>Simulasi kegagalan: `RateLimiter::hit()` sebanyak 5 kali | • Awal: remaining = 5, blocked = false<br>• Hit 5x berurutan<br>• remaining = 0, blocked = true<br>• Clear: blocked = false | • Sebelum 5x: `tooManyAttempts()` = false<br>• Tepat setelah 5x: `tooManyAttempts()` = **true**<br>• Waktu tunggu (`availableIn`) > 0 detik<br>• Saat di-reset: kembali terbuka | Sesuai ekspektasi rate limiter | **LULUS (PASS)** |
| **UT-SEC-03** | `test_login_request_throws_validation_exception_when_rate_limited` | Request login dari IP `127.0.0.1` dengan status rate limit telah terakumulasi 5 kali | Memanggil fungsi proteksi internal `LoginRequest::ensureIsNotRateLimited()` | Sistem memblokir eksekusi login dan melempar eksepsi `ValidationException` (Throttle lockout) | Melempar `ValidationException` | **LULUS (PASS)** |

---

## 3. Pembahasan Rinci Hasil Pengujian

### 3.1 Kasus Uji UT-SEC-01: Enkripsi Bcrypt & Verifikasi Password
* **Tujuan**: Memastikan keamanan kredensial pengguna tersimpan aman di database dan tidak dapat dibobol dengan perbandingan string biasa.
* **Hasil Pengujian**:
  1. `assertNotEquals('PasswordPetani#2026', $hashedPassword)` $\rightarrow$ Valid. Nilai hash acak dan aman.
  2. `assertStringStartsWith('$2y$', $hashedPassword)` $\rightarrow$ Valid. Terbukti menggunakan implementasi standar Bcrypt.
  3. `assertTrue(Hash::check('PasswordPetani#2026', $hashedPassword))` $\rightarrow$ Valid. Pengguna dengan password sah berhasil diautentikasi.
  4. `assertFalse(Hash::check('PasswordSalahTotal', $hashedPassword))` $\rightarrow$ Valid. Percobaan password salah ditolak seketika.
  5. `assertFalse(Hash::check('passwordpetani#2026', $hashedPassword))` $\rightarrow$ Valid. Terbukti peka huruf besar-kecil (*case-sensitive*).

### 3.2 Kasus Uji UT-SEC-02: Algoritma Rate Limiting 5 Percobaan Gagal
* **Tujuan**: Membuktikan keandalan logika penjaga pintu (*gatekeeper*) terhadap serangan tebak sandi otomatis (*dictionary/brute-force attack*).
* **Alur Perhitungan Percobaan**:
  $$\text{Percobaan Awal: Kuota Sisa} = 5, \quad \text{Status Terblokir} = \text{false}$$
  $$\text{Gagal 1: Sisa} = 4, \quad \text{Gagal 2: Sisa} = 3, \quad \text{Gagal 3: Sisa} = 2, \quad \text{Gagal 4: Sisa} = 1$$
  $$\text{Gagal 5: Sisa} = 0, \quad \text{Status Terblokir} = \text{true}$$
* **Hasil Pengujian**:
  Sistem secara konsisten menurunkan sisa kesempatan hingga mencapai angka `0`, dan pada percobaan ke-6 sistem langsung mengunci akses pengguna serta memberikan waktu penundaan (*cooldown*).

### 3.3 Kasus Uji UT-SEC-03: Penanganan Eksepsi pada Lapisan HTTP Form Request
* **Tujuan**: Memastikan bahwa fungsi pengendali request (`LoginRequest`) merespons kondisi terblokir dengan melempar eksepsi resmi Laravel (`ValidationException`).
* **Hasil Pengujian**:
  Sistem berhasil melempar eksepsi dan menghentikan alur program sebelum query verifikasi database dijalankan, menghemat beban server (*resource protection*).

---

## 4. Bukti Log Eksekusi Terminal (PHPUnit Execution Log)

Berikut adalah rekaman keluaran konsol terminal saat perintah pengujian dijalankan:

```text
PS C:\Users\fredl\PROJECTS\pos-albarokah> php artisan test tests/Unit/SecurityAuthTest.php

   PASS  Tests\Unit\SecurityAuthTest
  ✓ bcrypt password hashing and verification                           0.48s  
  ✓ rate limiter blocks input after five failed attempts               0.02s  
  ✓ login request throws validation exception when rate limited        0.02s  

  Tests:    3 passed (12 assertions)
  Duration: 0.89s
```

---

## 5. Kesimpulan Pengujian

Berdasarkan pengujian unit otomatis pada berkas `tests/Unit/SecurityAuthTest.php`, seluruh 3 skenario uji dengan 12 asersi logika dinyatakan **LULUS 100% (PASS)** tanpa ada kegagalan ataupun galat (*error*).

Dapat disimpulkan bahwa:
1. Skema pengamanan kata sandi sistem POS Toko Pertanian Al Barokah telah memenuhi standar keamanan modern berbasis algoritma Bcrypt.
2. Mekanisme pertahanan aktif *Rate Limiter* berhasil membatasi kesalahan login maksimal 5 kali secara presisi dan efektif mencegah eksploitasi serangan *Brute Force*.
