<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'size', 'color', 'stock', 'is_active'];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getLabelAttribute(): string
    {
        return "{$this->size} / {$this->color}";
    }

    public function isPurchasable(): bool
    {
        return $this->is_active
            && $this->stock > 0
            && $this->product
            && $this->product->is_active
            && ! $this->product->trashed();
    }
}
