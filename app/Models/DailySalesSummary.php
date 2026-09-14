<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySalesSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sale_date',
        'quantity_sold',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'quantity_sold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
