<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'test-server-key';

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'midtrans.server_key' => self::SERVER_KEY,
            'midtrans.client_key' => 'test-client-key',
        ]);

        $customer = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create(['price' => 100000]);
        $variant = ProductVariant::factory()->for($product)->create(['sku' => 'VLR-HOOK-001']);

        $this->order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'VLR-HOOK-'.strtoupper(Str::random(6)),
            'checkout_key' => Str::uuid()->toString(),
            'recipient_name' => $customer->name,
            'phone' => '081200000000',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'postal_code' => '10110',
            'subtotal' => 100000,
            'shipping_cost' => 15000,
            'total' => 115000,
        ]);

        $this->order->items()->create([
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'sku' => $variant->sku,
            'size' => $variant->size,
            'color' => $variant->color,
            'unit_price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        Payment::create([
            'order_id' => $this->order->id,
            'midtrans_order_id' => $this->order->order_number,
            'gross_amount' => $this->order->total,
            'expiry_at' => now()->addHour(),
        ]);
    }

    public function test_valid_settlement_notification_marks_order_paid(): void
    {
        $payload = $this->signedPayload('settlement');

        $this->partialMock(MidtransService::class, function ($mock) use ($payload) {
            $mock->shouldReceive('fetchStatus')
                ->once()
                ->with($this->order->order_number)
                ->andReturn($payload);
        });

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertOk()
            ->assertJsonPath('payment_status', 'paid');

        $order = $this->order->fresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('consumed', $order->reservation_status->value);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = $this->signedPayload('settlement');
        $payload['signature_key'] = str_repeat('0', 128);

        $this->partialMock(MidtransService::class, function ($mock) {
            $mock->shouldNotReceive('fetchStatus');
        });

        $this->postJson(route('midtrans.notification'), $payload)->assertForbidden();

        $this->assertSame(PaymentStatus::Pending, $this->order->fresh()->payment_status);
    }

    public function test_unknown_order_is_not_processed(): void
    {
        $payload = $this->signedPayload('settlement', 'VLR-UNKNOWN-000');

        $this->postJson(route('midtrans.notification'), $payload)->assertNotFound();
    }

    public function test_unconfigured_gateway_returns_service_unavailable(): void
    {
        config(['midtrans.server_key' => null, 'midtrans.client_key' => null]);

        $this->postJson(route('midtrans.notification'), $this->signedPayload('settlement'))
            ->assertStatus(503);
    }

    /**
     * @return array<string, mixed>
     */
    private function signedPayload(string $transactionStatus, ?string $orderNumber = null): array
    {
        $orderNumber ??= $this->order->order_number;
        $statusCode = '200';
        $grossAmount = '115000.00';

        return [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-'.Str::random(8),
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $orderNumber.$statusCode.$grossAmount.self::SERVER_KEY),
        ];
    }
}
