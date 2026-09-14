<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ReceivableTest extends TestCase
{
    use RefreshDatabase;

    private function createDummyReceivable(float $totalAmount = 500000): Receivable
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Petani Test',
            'phone' => '08123456789',
        ]);

        $sale = Sale::create([
            'invoice_no' => 'INV-TEST-001',
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'payment_method' => 'credit',
            'subtotal' => $totalAmount,
            'total' => $totalAmount,
            'sold_at' => now(),
        ]);

        return Receivable::create([
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_balance' => $totalAmount,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_partial_payment_reduces_remaining_balance(): void
    {
        $receivable = $this->createDummyReceivable(500000);

        $payment = $receivable->recordPayment(200000, now()->toDateTimeString(), 'Cicilan 1');

        $this->assertEquals(200000, (float) $receivable->paid_amount);
        $this->assertEquals(300000, (float) $receivable->remaining_balance);
        $this->assertEquals('belum_lunas', $receivable->status);
        $this->assertDatabaseHas('receivable_payments', [
            'id' => $payment->id,
            'amount' => 200000,
        ]);
    }

    public function test_full_payment_updates_status_to_lunas(): void
    {
        $receivable = $this->createDummyReceivable(300000);

        $receivable->recordPayment(300000, now()->toDateTimeString(), 'Pelunasan');

        $this->assertEquals(300000, (float) $receivable->paid_amount);
        $this->assertEquals(0, (float) $receivable->remaining_balance);
        $this->assertEquals('lunas', $receivable->status);
        $this->assertTrue($receivable->isPaidOff());
    }

    public function test_payment_exceeding_remaining_balance_throws_exception(): void
    {
        $receivable = $this->createDummyReceivable(100000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nominal pembayaran tidak boleh melebihi sisa saldo piutang.');

        $receivable->recordPayment(150000, now()->toDateTimeString());
    }

    public function test_payment_zero_or_negative_throws_exception(): void
    {
        $receivable = $this->createDummyReceivable(100000);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nominal pembayaran harus lebih dari 0.');

        $receivable->recordPayment(0, now()->toDateTimeString());
    }
}
