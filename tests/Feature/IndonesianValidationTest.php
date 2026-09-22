<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndonesianValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
    }

    public function test_login_fails_with_indonesian_error_message_on_wrong_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'kasir@albarokah.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'kasir@albarokah.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'Username/email atau kata sandi yang Anda masukkan salah.',
        ]);
        $this->assertGuest();
    }

    public function test_login_validation_requires_fields_in_indonesian(): void
    {
        $response = $this->post('/login', [
            'login' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'Username atau email wajib diisi.',
            'password' => 'Kata sandi wajib diisi.',
        ]);
    }

    public function test_password_reset_fails_with_indonesian_error_when_account_not_found(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'tidakada@albarokah.com',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Akun dengan alamat email tersebut tidak ditemukan.',
        ]);
    }

    public function test_password_reset_requires_email_in_indonesian(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Alamat email wajib diisi.',
        ]);
    }

    public function test_password_update_fails_with_indonesian_error_on_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrongcurrent',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', [
            'current_password' => 'Kata sandi saat ini salah.',
        ]);
    }

    public function test_confirm_password_fails_with_indonesian_error(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'Kata sandi yang Anda masukkan tidak sesuai.',
        ]);
    }
}
