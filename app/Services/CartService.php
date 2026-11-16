<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function cartFor(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function add(User $user, int $variantId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($user, $variantId, $quantity) {
            $variant = ProductVariant::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($variantId);

            $this->assertPurchasable($variant, $quantity);

            $cart = $this->cartFor($user);

            $item = CartItem::query()->firstOrNew([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
            ]);

            $newQuantity = (int) ($item->quantity ?? 0) + $quantity;

            if ($newQuantity > $variant->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok tersedia hanya {$variant->stock} item untuk varian ini.",
                ]);
            }

            $item->quantity = $newQuantity;
            $item->save();

            return $item;
        });
    }

    public function update(CartItem $item, int $quantity): CartItem
    {
        return DB::transaction(function () use ($item, $quantity) {
            $variant = ProductVariant::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($item->product_variant_id);

            $this->assertPurchasable($variant, $quantity);

            $item->quantity = $quantity;
            $item->save();

            return $item;
        });
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * @return array{subtotal:int, total:int, count:int}
     */
    public function totals(Cart $cart): array
    {
        $items = $cart->items()->with('variant.product')->get();

        $subtotal = $items->sum(fn (CartItem $item) => $item->subtotal);

        return [
            'subtotal' => (int) $subtotal,
            'total' => (int) $subtotal,
            'count' => (int) $items->sum('quantity'),
        ];
    }

    private function assertPurchasable(ProductVariant $variant, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah minimal 1 item.']);
        }

        if (! $variant->is_active || ! $variant->product || ! $variant->product->is_active || $variant->product->trashed()) {
            throw ValidationException::withMessages(['quantity' => 'Produk ini sedang tidak tersedia.']);
        }

        if ($variant->stock < 1) {
            throw ValidationException::withMessages(['quantity' => 'Varian produk ini sedang habis.']);
        }

        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok tersedia hanya {$variant->stock} item untuk varian ini.",
            ]);
        }
    }
}
