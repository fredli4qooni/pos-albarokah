<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'period_used',
        'forecast_value',
        'actual_stock',
        'status',
        'shortage_qty',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_used' => 'integer',
            'forecast_value' => 'decimal:2',
            'actual_stock' => 'integer',
            'shortage_qty' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function needsRestock(): bool
    {
        return $this->status === 'perlu_restok';
    }
}
