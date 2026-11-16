<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_paid_order_can_be_rated_only_once(): void
    {
        [$user, $order, $item] = $this->makeOrder();
        $url = route('orders.items.rate', [$order, $item]);
        $this->actingAs($user)->get(route('orders.show', $order))->assertOk()->assertSee('Beri Penilaian Produk');
        $this->post($url, ['rating' => 5])->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'rating' => 5]);
        $this->post($url, ['rating' => 1])->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'rating' => 5]);
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Penilaian tersimpan');
    }

    public function test_pending_or_unpaid_order_cannot_be_rated(): void
    {
        [$user, $order, $item] = $this->makeOrder();
        $order->update(['order_status' => OrderStatus::Shipped]);
        $url = route('orders.items.rate', [$order, $item]);
        $this->actingAs($user)->get(route('orders.show', $order))->assertOk()->assertDontSee('Beri Penilaian Produk');
        $this->post($url, ['rating' => 5])->assertForbidden();
        $order->update(['order_status' => OrderStatus::Completed, 'payment_status' => PaymentStatus::Pending]);
        $this->post($url, ['rating' => 5])->assertForbidden();
        $this->assertNull($item->fresh()->rating);
    }

    public function test_invalid_rating_and_unauthorized_access_are_rejected(): void
    {
        [$user, $order, $item] = $this->makeOrder();
        $url = route('orders.items.rate', [$order, $item]);
        $this->post($url, ['rating' => 5])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post($url, ['rating' => 5])->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->post($url, ['rating' => 5])->assertForbidden();
        $this->actingAs($user);
        foreach ([0, 6, 2.5, 'bad'] as $invalid) {
            $this->from(route('orders.show', $order))->post($url, ['rating' => $invalid])->assertSessionHasErrors('rating', null, 'rating');
        }
        [, , $otherItem] = $this->makeOrder();
        $this->post(route('orders.items.rate', [$order, $otherItem]), ['rating' => 5])->assertNotFound();
        $this->assertNull($item->fresh()->rating);
        $this->assertNull($otherItem->fresh()->rating);
    }

    private function makeOrder(): array
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'VLR-RATING-'.Str::random(10),
            'checkout_key' => Str::uuid()->toString(),
            'recipient_name' => $user->name,
            'phone' => '081200000000',
            'province' => 'Bengkulu',
            'city' => 'Bengkulu',
            'address' => 'Alamat contoh',
            'postal_code' => '38111',
            'subtotal' => 119000,
            'shipping_cost' => 15000,
            'total' => 134000,
            'order_status' => OrderStatus::Completed,
            'payment_status' => PaymentStatus::Paid,
            'completed_at' => now(),
            'paid_at' => now(),
        ]);
        $item = $order->items()->create([
            'product_name' => 'Ikat Pinggang Classic',
            'sku' => 'VLR-RATING',
            'size' => 'All Size',
            'color' => 'Hitam',
            'unit_price' => 119000,
            'quantity' => 1,
            'subtotal' => 119000,
        ]);
        return [$user, $order, $item];
    }
}
