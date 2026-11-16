<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_login_to_see_orders(): void
    {
        $this->get(route('orders.index'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_another_customers_order(): void
    {
        [$owner, $order] = $this->orderForAnotherCustomer();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($intruder)->get(route('orders.invoice', $order))->assertForbidden();
        $this->actingAs($intruder)->get(route('orders.payment', $order))->assertForbidden();
        $this->actingAs($intruder)->post(route('orders.payment.refresh', $order))->assertForbidden();

        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk()->assertSee($order->order_number);
    }

    public function test_snapshot_remains_visible_after_product_is_deactivated(): void
    {
        [$owner, $order] = $this->orderForAnotherCustomer();

        $order->items()->first()->variant->product->update(['is_active' => false]);

        $this->actingAs($owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Snapshot Blouse')
            ->assertSee($order->order_number);
    }

    /**
     * @return array{0: User, 1: Order}
     */
    private function orderForAnotherCustomer(): array
    {
        $owner = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Snapshot Blouse',
            'price' => 120000,
        ]);
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'VLR-SNAP-M-BRG',
            'size' => 'M',
            'color' => 'Burgundy',
        ]);

        $order = Order::create([
            'user_id' => $owner->id,
            'order_number' => 'VLR-OWN-'.strtoupper(Str::random(8)),
            'checkout_key' => Str::uuid()->toString(),
            'recipient_name' => $owner->name,
            'phone' => '081200000000',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'postal_code' => '10110',
            'subtotal' => 120000,
            'shipping_cost' => 15000,
            'total' => 135000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'sku' => $variant->sku,
            'size' => $variant->size,
            'color' => $variant->color,
            'unit_price' => 120000,
            'quantity' => 1,
            'subtotal' => 120000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->order_number,
            'gross_amount' => $order->total,
            'expiry_at' => now()->addHour(),
        ]);

        return [$owner, $order->fresh()];
    }
}
