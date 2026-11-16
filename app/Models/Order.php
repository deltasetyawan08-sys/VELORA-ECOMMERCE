<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'checkout_key',
        'recipient_name',
        'phone',
        'province',
        'city',
        'address',
        'postal_code',
        'notes',
        'subtotal',
        'shipping_cost',
        'total',
        'payment_status',
        'order_status',
        'reservation_status',
        'carrier',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'payment_status' => PaymentStatus::class,
            'order_status' => OrderStatus::class,
            'reservation_status' => ReservationStatus::class,
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function getCanBePaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::Pending
            && $this->order_status === OrderStatus::PendingPayment;
    }

    public function getStatusSummaryAttribute(): string
    {
        return $this->order_status->label();
    }

    public function canTransitionTo(OrderStatus $target): bool
    {
        return $this->order_status->canTransitionTo($target);
    }
}
