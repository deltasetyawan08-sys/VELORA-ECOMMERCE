<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\PaymentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private ProductVariant $variant;

    private PaymentStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PaymentStatusService::class);
        $this->customer = User::factory()->create();

        $product = Product::factory()
            ->for(Category::factory())
            ->create(['name' => 'Laras Pleated Skirt', 'price' => 150000]);

        $this->variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'VLR-LRS-M-BRG',
            'stock' => 10,
        ]);
    }

    public function test_settlement_marks_order_paid_and_consumes_reservation(): void
    {
        $order = $this->makeOrder(quantity: 2, remainingStock: 8);

        $result = $this->service->apply($order, $this->gatewayPayload($order, 'settlement', ['transaction_id' => 'trx-001']));

        $this->assertTrue($result['applied']);

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->order_status);
        $this->assertSame('consumed', $order->reservation_status->value);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->payment->paid_at);
        $this->assertSame(8, $this->variant->fresh()->stock);

        $second = $this->service->apply($order->fresh(), $this->gatewayPayload($order, 'settlement', ['transaction_id' => 'trx-001']));
        $this->assertFalse($second['applied']);
        $this->assertSame(8, $this->variant->fresh()->stock);
    }

    public function test_expire_releases_stock_exactly_once(): void
    {
        $order = $this->makeOrder(quantity: 2, remainingStock: 8);

        $result = $this->service->apply($order, $this->gatewayPayload($order, 'expire'));

        $this->assertTrue($result['applied']);
        $order->refresh();
        $this->assertSame(PaymentStatus::Expired, $order->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
        $this->assertSame('released', $order->reservation_status->value);
        $this->assertSame(10, $this->variant->fresh()->stock);

        $this->service->apply($order->fresh(), $this->gatewayPayload($order, 'expire'));
        $this->assertSame(10, $this->variant->fresh()->stock);
    }

    public function test_paid_order_is_never_downgraded_by_late_failure_notification(): void
    {
        $order = $this->makeOrder(quantity: 1, remainingStock: 9);

        $this->service->apply($order, $this->gatewayPayload($order, 'settlement', ['transaction_id' => 'trx-002']));

        $result = $this->service->apply($order->fresh(), $this->gatewayPayload($order, 'expire'));

        $this->assertFalse($result['applied']);
        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertTrue($order->payment->requires_review);
    }

    public function test_duplicate_settlement_notification_does_not_consume_stock_twice(): void
    {
        $order = $this->makeOrder(quantity: 2, remainingStock: 8);
        $payload = $this->gatewayPayload($order, 'settlement', ['transaction_id' => 'trx-duplicate']);

        $first = $this->service->apply($order, $payload);
        $second = $this->service->apply($order->fresh(['payment', 'items']), $payload);

        $this->assertTrue($first['applied']);
        $this->assertFalse($second['applied']);
        $this->assertSame(8, $this->variant->fresh()->stock);
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $order = $this->makeOrder(quantity: 1, remainingStock: 9);

        $result = $this->service->apply($order, $this->gatewayPayload($order, 'settlement', ['gross_amount' => '1000.00']));

        $this->assertFalse($result['applied']);
        $order->refresh();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
    }

    public function test_transaction_id_conflict_requires_review(): void
    {
        $order = $this->makeOrder(quantity: 1, remainingStock: 9);

        $this->service->apply($order, $this->gatewayPayload($order, 'pending', ['transaction_id' => 'trx-aaa']));

        $result = $this->service->apply($order->fresh(), $this->gatewayPayload($order, 'settlement', ['transaction_id' => 'trx-bbb']));

        $this->assertFalse($result['applied']);
        $order->refresh();
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertTrue($order->payment->requires_review);
    }

    public function test_refresh_without_gateway_credentials_is_informative_and_changes_nothing(): void
    {
        config([
            'midtrans.server_key' => '',
            'midtrans.client_key' => '',
        ]);

        $order = $this->makeOrder(quantity: 2, remainingStock: 8);

        $this->actingAs($this->customer)
            ->post(route('orders.payment.refresh', $order))
            ->assertSessionHas('error');

        $order->refresh();

        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(OrderStatus::PendingPayment, $order->order_status);
        $this->assertSame('held', $order->reservation_status->value);
        $this->assertSame(8, $this->variant->fresh()->stock);
    }

    private function makeOrder(int $quantity, int $remainingStock): Order
    {
        $subtotal = 150000 * $quantity;
        $shipping = (int) config('shop.shipping_cost');

        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_number' => 'VLR-TEST-'.strtoupper(Str::random(8)),
            'checkout_key' => Str::uuid()->toString(),
            'recipient_name' => 'Nadia Putri',
            'phone' => '081234567890',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Kemang Raya No. 10',
            'postal_code' => '12730',
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'total' => $subtotal + $shipping,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $this->variant->id,
            'product_name' => 'Laras Pleated Skirt',
            'sku' => $this->variant->sku,
            'size' => $this->variant->size,
            'color' => $this->variant->color,
            'unit_price' => 150000,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $order->order_number,
            'gross_amount' => $order->total,
            'expiry_at' => now()->addHour(),
        ]);

        $this->variant->update(['stock' => $remainingStock]);

        return $order->fresh(['payment', 'items']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function gatewayPayload(Order $order, string $transactionStatus, array $overrides = []): array
    {
        return array_merge([
            'order_id' => $order->order_number,
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'payment_type' => 'bank_transfer',
        ], $overrides);
    }
}
