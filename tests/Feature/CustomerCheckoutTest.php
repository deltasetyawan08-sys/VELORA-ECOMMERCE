<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();

        $product = Product::factory()
            ->for(Category::factory()->state(['name' => 'Atasan']))
            ->create(['name' => 'Kirana Satin Shirt', 'price' => 150000]);

        $this->variant = ProductVariant::factory()->for($product)->create([
            'size' => 'M',
            'color' => 'Burgundy',
            'sku' => 'VLR-KIR-M-BRG',
            'stock' => 10,
        ]);
    }

    public function test_customer_can_add_items_to_cart_and_update_or_remove_them(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'product_variant_id' => $this->variant->id,
                'quantity' => 2,
            ])
            ->assertRedirect();

        $cart = Cart::query()->where('user_id', $this->customer->id)->firstOrFail();
        $item = $cart->items()->firstOrFail();

        $this->actingAs($this->customer)->get(route('cart.index'))->assertOk()->assertSee('Kirana Satin Shirt');

        $this->actingAs($this->customer)
            ->patch(route('cart.items.update', $item), ['quantity' => 3])
            ->assertRedirect();

        $this->assertSame(3, $item->fresh()->quantity);

        $this->actingAs($this->customer)
            ->delete(route('cart.items.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_checkout_from_cart_creates_order_and_holds_stock(): void
    {
        $this->actingAs($this->customer)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->customer)->get(route('checkout.show'))->assertOk()->assertSee('Kirana Satin Shirt');

        $response = $this->actingAs($this->customer)->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->where('user_id', $this->customer->id)->firstOrFail();

        $response->assertRedirect(route('orders.payment', $order));

        $this->assertSame(1, Order::query()->count());
        $this->assertSame(2, $order->items()->sum('quantity'));
        $this->assertSame(300000, $order->subtotal);
        $this->assertSame((int) config('shop.shipping_cost'), $order->shipping_cost);
        $this->assertSame('pending', $order->payment_status->value);
        $this->assertSame('held', $order->reservation_status->value);
        $this->assertNotNull($order->payment);
        $this->assertSame(8, $this->variant->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($this->customer)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($this->customer)->get(route('orders.payment', $order))->assertOk();
        $this->actingAs($this->customer)->get(route('orders.invoice', $order))->assertOk();
    }

    public function test_customer_cannot_modify_another_customers_cart_item(): void
    {
        $this->actingAs($this->customer)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $item = CartItem::query()->firstOrFail();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->patch(route('cart.items.update', $item), ['quantity' => 5])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('cart.items.destroy', $item))
            ->assertForbidden();

        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_buy_now_flow_creates_order_without_touching_cart(): void
    {
        $this->actingAs($this->customer)
            ->post(route('checkout.buy-now'), [
                'product_variant_id' => $this->variant->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('checkout.show'));

        $this->actingAs($this->customer)->get(route('checkout.show'))->assertOk();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertSame(1, $order->items()->sum('quantity'));
        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertNull(session('buy_now'));
    }

    public function test_checkout_rejects_quantity_above_stock(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'product_variant_id' => $this->variant->id,
                'quantity' => 5,
            ]);

        $this->variant->update(['stock' => 3]);

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors('checkout');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_requires_complete_shipping_address(): void
    {
        $this->actingAs($this->customer)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $payload = $this->checkoutPayload();
        unset($payload['province'], $payload['postal_code']);

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors(['province', 'postal_code']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $this->variant->fresh()->stock);
    }

    public function test_client_supplied_prices_are_ignored(): void
    {
        $this->actingAs($this->customer)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->customer)->post(route('checkout.store'), $this->checkoutPayload() + [
            'price' => 1,
            'subtotal' => 2,
            'shipping_cost' => 0,
            'total' => 2,
        ]);

        $order = Order::query()->firstOrFail();

        $this->assertSame(300000, $order->subtotal);
        $this->assertSame((int) config('shop.shipping_cost'), $order->shipping_cost);
        $this->assertSame(315000, $order->total);
        $this->assertSame(300000, (int) $order->items()->sum('subtotal'));
    }

    public function test_repeated_checkout_does_not_create_duplicate_order_or_reserve_stock_twice(): void
    {
        $this->actingAs($this->customer)->post(route('cart.items.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors('checkout');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(8, $this->variant->fresh()->stock);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(): array
    {
        return [
            'recipient_name' => 'Nadia Putri',
            'phone' => '081234567890',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Kemang Raya No. 10',
            'postal_code' => '12730',
            'notes' => 'Tolong dibungkus rapi.',
        ];
    }
}
