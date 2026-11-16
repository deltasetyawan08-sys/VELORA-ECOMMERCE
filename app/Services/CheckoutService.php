<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly CartService $cartService) {}

    public function checkoutKey(): string
    {
        if (! session()->has('checkout_key')) {
            session(['checkout_key' => Str::uuid()->toString()]);
        }

        return (string) session('checkout_key');
    }

    public function startBuyNow(int $variantId, int $quantity): void
    {
        session([
            'buy_now' => [
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ],
        ]);
        session()->forget('checkout_items');
    }

    /**
     * Resolve selected checkout lines from the session. Never trust client prices.
     *
     * @param  array<int, int>|null  $requestedCartItemIds
     * @return Collection<int, array<string, mixed>>
     */
    public function resolveItems(User $user, ?array $requestedCartItemIds = null): Collection
    {
        if (session()->has('buy_now')) {
            $buyNow = session('buy_now');
            $variant = ProductVariant::query()->with(['product.category'])->find($buyNow['variant_id'] ?? 0);

            if (! $variant || ! $variant->isPurchasable()) {
                session()->forget('buy_now');

                return collect();
            }

            $quantity = max(1, min((int) ($buyNow['quantity'] ?? 1), $variant->stock));

            return collect([$this->lineFromVariant($variant, $quantity, null)]);
        }

        $cart = $this->cartService->cartFor($user);

        $sessionIds = session('checkout_items');

        if (is_array($requestedCartItemIds)) {
            $requestedCartItemIds = array_values(array_unique(array_filter(array_map('intval', $requestedCartItemIds))));
            session(['checkout_items' => $requestedCartItemIds]);
            $ids = $requestedCartItemIds;
        } elseif (is_array($sessionIds) && count($sessionIds)) {
            $ids = array_map('intval', $sessionIds);
        } else {
            $ids = [];
        }

        $query = $cart->items()->with(['variant.product.category']);

        if (count($ids)) {
            $query->whereIn('id', $ids);
        }

        return $query->get()
            ->filter(fn (CartItem $item) => $item->is_valid)
            ->map(fn (CartItem $item) => $this->lineFromCartItem($item))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOrder(User $user, array $data): Order
    {
        $checkoutKey = $this->checkoutKey();

        try {
            return DB::transaction(function () use ($user, $data, $checkoutKey) {
                $existing = Order::query()
                    ->where('user_id', $user->id)
                    ->where('checkout_key', $checkoutKey)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $usingBuyNow = session()->has('buy_now');
                $lines = $this->resolveItems($user);

                if ($lines->isEmpty()) {
                    throw ValidationException::withMessages([
                        'checkout' => 'Tidak ada item valid untuk diproses. Silakan periksa keranjang Anda.',
                    ]);
                }

                $variantIds = $lines->pluck('variant_id')->unique()->sort()->values()->all();

                $variants = ProductVariant::query()
                    ->with('product')
                    ->whereIn('id', $variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $subtotal = 0;
                $snapshots = [];

                foreach ($lines as $line) {
                    /** @var ProductVariant|null $variant */
                    $variant = $variants->get($line['variant_id']);
                    $quantity = (int) $line['quantity'];

                    if (! $variant || ! $variant->product || ! $variant->product->is_active || $variant->product->trashed()) {
                        throw ValidationException::withMessages([
                            'checkout' => 'Salah satu produk sudah tidak tersedia. Silakan kembali ke keranjang.',
                        ]);
                    }

                    if (! $variant->is_active) {
                        throw ValidationException::withMessages([
                            'checkout' => "Varian {$variant->label} sudah tidak aktif.",
                        ]);
                    }

                    if ($variant->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'checkout' => "Stok {$variant->product->name} ({$variant->label}) hanya tersisa {$variant->stock}.",
                        ]);
                    }

                    $unitPrice = (int) $variant->product->price;
                    $lineSubtotal = $unitPrice * $quantity;
                    $subtotal += $lineSubtotal;

                    $snapshots[] = [
                        'variant' => $variant,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $shipping = (int) config('shop.shipping_cost');
                $total = $subtotal + $shipping;

                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $this->generateOrderNumber(),
                    'checkout_key' => $checkoutKey,
                    'recipient_name' => $data['recipient_name'],
                    'phone' => $data['phone'],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'postal_code' => $data['postal_code'],
                    'notes' => $data['notes'] ?? null,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'total' => $total,
                    'payment_status' => 'pending',
                    'order_status' => 'pending_payment',
                    'reservation_status' => 'held',
                ]);

                foreach ($snapshots as $snapshot) {
                    $variant = $snapshot['variant'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'sku' => $variant->sku,
                        'size' => $variant->size,
                        'color' => $variant->color,
                        'unit_price' => $snapshot['unit_price'],
                        'quantity' => $snapshot['quantity'],
                        'subtotal' => $snapshot['subtotal'],
                    ]);

                    $variant->decrement('stock', $snapshot['quantity']);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'midtrans_order_id' => $order->order_number,
                    'gross_amount' => $total,
                    'expiry_at' => now()->addMinutes((int) config('shop.expiry_minutes')),
                ]);

                if (! $usingBuyNow) {
                    $cartItemIds = $lines->pluck('cart_item_id')->filter()->all();

                    if (count($cartItemIds)) {
                        CartItem::query()
                            ->whereIn('id', $cartItemIds)
                            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
                            ->delete();
                    }
                }

                session()->forget(['buy_now', 'checkout_items']);
                session(['checkout_key' => Str::uuid()->toString()]);

                return $order;
            });
        } catch (QueryException $exception) {
            $existing = Order::query()
                ->where('user_id', $user->id)
                ->where('checkout_key', $checkoutKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function lineFromCartItem(CartItem $item): array
    {
        $variant = $item->variant;

        return $this->lineFromVariant($variant, $item->quantity, $item->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function lineFromVariant(ProductVariant $variant, int $quantity, ?int $cartItemId): array
    {
        $price = (int) $variant->product->price;

        return [
            'cart_item_id' => $cartItemId,
            'variant_id' => $variant->id,
            'name' => $variant->product->name,
            'image' => $variant->product->image_url,
            'size' => $variant->size,
            'color' => $variant->color,
            'sku' => $variant->sku,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'stock' => (int) $variant->stock,
        ];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'VLR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
