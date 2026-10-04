<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('admin@albarokah.com|127.0.0.1');
        RateLimiter::clear('test_login|127.0.0.1');
        parent::tearDown();
    }

    /**
     * Uji Poin 3: Enkripsi dan Verifikasi Kata Sandi Berbasis Bcrypt
     * Skenario: Memastikan Hash::check() bernilai true untuk password yang benar
     * dan bernilai false untuk password yang salah.
     */
    public function test_bcrypt_password_hashing_and_verification(): void
    {
        $plainPassword = 'PasswordPetani#2026';
        $hashedPassword = Hash::make($plainPassword);

        // 1. Memastikan password yang disimpan di-hash dan bukan plaintext
        $this->assertNotEquals($plainPassword, $hashedPassword);
        $this->assertStringStartsWith('$2y$', $hashedPassword); // Prefix algoritma Bcrypt

        // 2. Memastikan Hash::check bernilai true untuk password yang cocok
        $this->assertTrue(
            Hash::check($plainPassword, $hashedPassword),
            'Verifikasi Bcrypt harus bernilai true untuk kata sandi yang sesuai.'
        );

        // 3. Memastikan Hash::check bernilai false untuk kata sandi yang keliru
        $this->assertFalse(
            Hash::check('PasswordSalahTotal', $hashedPassword),
            'Verifikasi Bcrypt harus bernilai false untuk kata sandi yang salah.'
        );
        $this->assertFalse(
            Hash::check(strtolower($plainPassword), $hashedPassword),
            'Verifikasi Bcrypt harus case-sensitive (peka huruf besar-kecil).'
        );
    }

    /**
     * Uji Poin 3: Logika Rate Limiter 5 Kali Percobaan Gagal
     * Skenario: Memastikan fungsi rate-limiting memblokir masukan setelah
     * 5 kali percobaan gagal berturut-turut.
     */
    public function test_rate_limiter_blocks_input_after_five_failed_attempts(): void
    {
        $throttleKey = 'test_login|127.0.0.1';
        $maxAttempts = 5;

        // Pastikan kondisi awal bersih
        RateLimiter::clear($throttleKey);
        $this->assertFalse(RateLimiter::tooManyAttempts($throttleKey, $maxAttempts));
        $this->assertEquals(5, RateLimiter::remaining($throttleKey, $maxAttempts));

        // Simulasikan 5 kali percobaan gagal berturut-turut
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            RateLimiter::hit($throttleKey, 60);
        }

        // Setelah 5 kali percobaan gagal, rate limiter harus memblokir masukan
        $this->assertTrue(
            RateLimiter::tooManyAttempts($throttleKey, $maxAttempts),
            'Rate Limiter harus memblokir setelah 5 kali percobaan gagal berturut-turut.'
        );
        $this->assertEquals(0, RateLimiter::remaining($throttleKey, $maxAttempts));
        $this->assertGreaterThan(0, RateLimiter::availableIn($throttleKey));

        // Verifikasi reset / pembersihan rate limiter (misalnya setelah login sukses)
        RateLimiter::clear($throttleKey);
        $this->assertFalse(
            RateLimiter::tooManyAttempts($throttleKey, $maxAttempts),
            'Setelah dibersihkan (clear), pemblokiran harus terbuka kembali.'
        );
    }

    /**
     * Uji Poin 3 (Integrasi LoginRequest): Memastikan LoginRequest melempar ValidationException
     * throttle ketika percobaan telah mencapai batas maksimum 5 kali.
     */
    public function test_login_request_throws_validation_exception_when_rate_limited(): void
    {
        $throttleKey = 'admin@albarokah.com|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // Isi rate limiter hingga limit 5 kali
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $request = new LoginRequest;
        $request->merge([
            'email' => 'admin@albarokah.com',
            'password' => 'wrongpassword',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $this->expectException(ValidationException::class);

        $request->ensureIsNotRateLimited();
    }
}
