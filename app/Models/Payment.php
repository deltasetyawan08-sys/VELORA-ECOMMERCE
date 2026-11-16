<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'snap_token',
        'redirect_url',
        'gross_amount',
        'gateway_status',
        'fraud_status',
        'payment_type',
        'expiry_at',
        'paid_at',
        'requires_review',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'integer',
            'expiry_at' => 'datetime',
            'paid_at' => 'datetime',
            'requires_review' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
