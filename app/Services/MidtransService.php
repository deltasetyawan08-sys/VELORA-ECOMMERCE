<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;
use Throwable;

class MidtransService
{
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'))
            && filled(config('midtrans.client_key'));
    }

    public function clientKey(): ?string
    {
        return config('midtrans.client_key') ?: null;
    }

    public function snapScriptUrl(): string
    {
        return (string) config('midtrans.snap_script_url');
    }

    public function isProduction(): bool
    {
        return (bool) config('midtrans.is_production');
    }

    public function createSnapToken(Order $order): Payment
    {
        $this->assertConfigured();

        $order->loadMissing(['items', 'payment', 'user']);

        $payment = $order->payment;

        if (! $payment) {
            throw new RuntimeException(
                'Data pembayaran untuk pesanan ini tidak ditemukan.'
            );
        }

        if (
            $order->payment_status !== PaymentStatus::Pending
            || $order->order_status !== OrderStatus::PendingPayment
        ) {
            throw new RuntimeException(
                'Pesanan ini tidak berada pada tahap menunggu pembayaran.'
            );
        }

        if ($payment->snap_token) {
            return $payment;
        }

        $this->configure();

        $items = $order->items->map(fn ($item) => [
            'id' => $item->sku,
            'price' => (int) $item->unit_price,
            'quantity' => (int) $item->quantity,
            'name' => Str::limit($item->product_name, 50, ''),
        ])->values()->all();

        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        $itemsTotal = collect($items)->sum(
            fn ($item) => $item['price'] * $item['quantity']
        );

        if ($itemsTotal !== (int) $order->total) {
            throw new RuntimeException(
                'Total item pembayaran tidak konsisten dengan total pesanan.'
            );
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],

            'item_details' => $items,

            'customer_details' => [
                'first_name' => Str::limit(
                    $order->recipient_name, 50, ''
                ),

                'email' => $order->user->email,
                'phone' => $order->phone,

                'shipping_address' => [
                    'first_name' => Str::limit(
                        $order->recipient_name, 50, ''
                    ),

                    'phone' => $order->phone,
                    'address' => $order->address,
                    'city' => $order->city,
                    'postal_code' => $order->postal_code,
                    'country_code' => 'IDN',
                ],
            ],

            'expiry' => [
                'unit' => 'minutes',
                'duration' => max(
                    1,
                    (int) config('shop.expiry_minutes')
                ),
            ],
        ];

        try {
            $transaction = Snap::createTransaction($params);
        } catch (Throwable $exception) {
            Log::error('Failed to create Midtrans Snap transaction.', [
                'order' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException(
                'Gagal membuat transaksi pembayaran. Silakan coba beberapa saat lagi.',
                0,
                $exception
            );
        }

        if (empty($transaction->token)) {
            throw new RuntimeException(
                'Midtrans tidak mengembalikan token pembayaran.'
            );
        }

        $payment->update([
            'snap_token' => $transaction->token,

            'redirect_url' => $transaction->redirect_url ?? null,

            'expiry_at' => $payment->expiry_at
                ?? now()->addMinutes(
                    (int) config('shop.expiry_minutes')
                ),
        ]);

        return $payment->refresh();
    }

    /**
     * Fetch verified transaction status directly
     * from Midtrans (server-to-server).
     *
     * @return array<string, mixed>
     */
    public function fetchStatus(string $orderNumber): array
    {
        $this->assertConfigured();

        $this->configure();

        try {
            $status = Transaction::status($orderNumber);
        } catch (Throwable $exception) {
            Log::warning('Failed to fetch Midtrans status.', [
                'order' => $orderNumber,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException(
                'Status pembayaran belum dapat diverifikasi dari Midtrans.',
                0,
                $exception
            );
        }

        return (array) $status;
    }

    /**
     * Verifikasi signature notifikasi Midtrans.
     */
    public function verifySignature(array $payload): bool
    {
        $secretKey = (string) (
            config('midtrans.signature_secret_key')
            ?: config('midtrans.server_key')
        );

        if ($secretKey === '') {
            return false;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if (
            $orderId === ''
            || $statusCode === ''
            || $grossAmount === ''
            || $signature === ''
        ) {
            return false;
        }

        $expected = hash(
            'sha512',
            $orderId.$statusCode.$grossAmount.$secretKey
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Konfigurasi Midtrans SDK.
     */
    private function configure(): void
    {
        MidtransConfig::$serverKey =
            (string) config('midtrans.server_key');

        MidtransConfig::$clientKey =
            (string) config('midtrans.client_key');

        MidtransConfig::$isProduction =
            (bool) config('midtrans.is_production');

        MidtransConfig::$isSanitized =
            (bool) config('midtrans.is_sanitized');

        MidtransConfig::$is3ds =
            (bool) config('midtrans.is_3ds');
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Kredensial Midtrans (Server Key & Client Key) belum dikonfigurasi pada .env.'
            );
        }
    }
}