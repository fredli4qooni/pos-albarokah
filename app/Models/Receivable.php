<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Receivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'customer_id',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    public function isPaidOff(): bool
    {
        return $this->status === 'lunas' || (float) $this->remaining_balance <= 0;
    }

    /**
     * Record a payment toward this receivable and update balances/status.
     * Business rule AGENTS.md §2.3: amount > 0, amount <= remaining_balance.
     */
    public function recordPayment(float $amount, string $paidAt, ?string $notes = null): ReceivablePayment
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pembayaran harus lebih dari 0.');
        }

        if ($amount > (float) $this->remaining_balance) {
            throw new InvalidArgumentException('Nominal pembayaran tidak boleh melebihi sisa saldo piutang.');
        }

        $payment = $this->payments()->create([
            'amount' => $amount,
            'paid_at' => $paidAt,
            'notes' => $notes,
        ]);

        $this->paid_amount = (float) $this->paid_amount + $amount;
        $this->remaining_balance = (float) $this->total_amount - (float) $this->paid_amount;
        $this->status = (float) $this->remaining_balance <= 0 ? 'lunas' : 'belum_lunas';
        $this->save();

        return $payment;
    }
}
