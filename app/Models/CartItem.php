<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_variant_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getUnitPriceAttribute(): int
    {
        return (int) ($this->variant?->product?->price ?? 0);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->unit_price * $this->quantity;
    }

    public function getIsValidAttribute(): bool
    {
        return $this->variant
            && $this->variant->isPurchasable()
            && $this->quantityValid();
    }

    private function quantityValid(): bool
    {
        return $this->quantity >= 1 && $this->quantity <= $this->variant->stock;
    }
}
