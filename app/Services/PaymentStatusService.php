<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentStatusService
{
    /**
     * Apply a verified gateway status payload to an order in an idempotent way.
     *
     * @param  array<string, mixed>  $gateway
     * @return array{applied: bool, reason: string, status: PaymentStatus}
     */
    public function apply(Order $order, array $gateway): array
    {
        $transactionStatus = strtolower((string) ($gateway['transaction_status'] ?? ''));
        $fraudStatus = strtolower((string) ($gateway['fraud_status'] ?? ''));
        $grossAmount = $this->normalizeAmount($gateway['gross_amount'] ?? null);

        if ($grossAmount === null || $grossAmount !== (int) $order->total) {
            Log::warning('Midtrans amount mismatch, status not applied.', [
                'order' => $order->order_number,
                'gateway_amount' => $gateway['gross_amount'] ?? null,
                'order_total' => $order->total,
            ]);

            return [
                'applied' => false,
                'reason' => 'Nominal pembayaran tidak sesuai dengan total pesanan.',
                'status' => $order->payment_status,
            ];
        }

        $newStatus = $this->mapStatus($transactionStatus, $fraudStatus);

        return DB::transaction(function () use ($order, $gateway, $newStatus, $transactionStatus, $fraudStatus) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $locked->payment()->lockForUpdate()->first();

            if (! $payment) {
                return ['applied' => false, 'reason' => 'Data pembayaran tidak ditemukan.', 'status' => $locked->payment_status];
            }

            $incomingTransactionId = $gateway['transaction_id'] ?? null;

            if ($payment->midtrans_transaction_id
                && $incomingTransactionId
                && $payment->midtrans_transaction_id !== $incomingTransactionId) {
                $payment->update([
                    'requires_review' => true,
                    'gateway_status' => $transactionStatus ?: $payment->gateway_status,
                ]);

                Log::error('Midtrans transaction id conflict.', ['order' => $locked->order_number]);

                return ['applied' => false, 'reason' => 'Transaksi gateway tidak cocok, perlu tinjauan manual.', 'status' => $locked->payment_status];
            }

            $payment->fill([
                'gateway_status' => $transactionStatus ?: $payment->gateway_status,
                'fraud_status' => $fraudStatus ?: $payment->fraud_status,
                'payment_type' => $gateway['payment_type'] ?? $payment->payment_type,
                'midtrans_transaction_id' => $incomingTransactionId ?: $payment->midtrans_transaction_id,
            ]);

            if (! empty($gateway['transaction_time'])) {
                $payment->paid_at = $payment->paid_at;
            }

            if ($newStatus === PaymentStatus::Review) {
                $payment->requires_review = true;
                $payment->save();

                return ['applied' => false, 'reason' => 'Status gateway perlu rekonsiliasi manual.', 'status' => $locked->payment_status];
            }

            if ($locked->payment_status === PaymentStatus::Paid) {
                if ($newStatus !== PaymentStatus::Paid) {
                    $payment->requires_review = true;
                    $payment->save();

                    Log::warning('Received non-paid gateway status for a paid order; no downgrade applied.', [
                        'order' => $locked->order_number,
                        'gateway_status' => $transactionStatus,
                    ]);

                    return ['applied' => false, 'reason' => 'Pembayaran sudah lunas; status tidak diturunkan.', 'status' => $locked->payment_status];
                }

                $payment->save();

                return ['applied' => false, 'reason' => 'Pembayaran sudah tercatat lunas.', 'status' => PaymentStatus::Paid];
            }

            if ($newStatus === PaymentStatus::Paid) {
                $payment->paid_at = $payment->paid_at ?? now();
                $payment->requires_review = false;
                $payment->save();

                $locked->payment_status = PaymentStatus::Paid;
                $locked->paid_at = $locked->paid_at ?? now();

                if ($locked->order_status === OrderStatus::PendingPayment) {
                    $locked->order_status = OrderStatus::Paid;
                }

                if ($locked->reservation_status->value === 'held') {
                    $locked->reservation_status = 'consumed';
                }

                $locked->save();

                return ['applied' => true, 'reason' => 'Pembayaran berhasil diverifikasi.', 'status' => PaymentStatus::Paid];
            }

            if ($newStatus->isUnsuccessfulFinal()) {
                $payment->save();

                if ($locked->order_status === OrderStatus::PendingPayment) {
                    $this->releaseStock($locked);

                    $locked->payment_status = $newStatus;
                    $locked->order_status = OrderStatus::Cancelled;
                    $locked->cancelled_at = now();
                    $locked->save();
                } else {
                    $locked->payment_status = $newStatus;
                    $locked->save();
                }

                return [
                    'applied' => true,
                    'reason' => $newStatus === PaymentStatus::Expired
                        ? 'Transaksi kedaluwarsa; stok dikembalikan.'
                        : 'Pembayaran gagal/dibatalkan; stok dikembalikan.',
                    'status' => $newStatus,
                ];
            }

            $payment->save();

            if ($locked->payment_status === PaymentStatus::Pending) {
                $locked->save();
            }

            return ['applied' => true, 'reason' => 'Status pembayaran menunggu.', 'status' => $newStatus];
        });
    }

    public function normalizeAmount(mixed $amount): ?int
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $value = trim((string) $amount);

        if (! preg_match('/^\d+(\.0{1,2})?$/', $value)) {
            return null;
        }

        return (int) explode('.', $value)[0];
    }

    private function mapStatus(string $transactionStatus, string $fraudStatus): PaymentStatus
    {
        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'capture' => $fraudStatus === 'accept' ? PaymentStatus::Paid : PaymentStatus::Pending,
            'pending', 'authorize' => PaymentStatus::Pending,
            'expire' => PaymentStatus::Expired,
            'deny', 'failure' => PaymentStatus::Failed,
            'cancel' => PaymentStatus::Cancelled,
            default => PaymentStatus::Review,
        };
    }

    private function releaseStock(Order $order): void
    {
        if ($order->reservation_status->value !== 'held') {
            return;
        }

        $quantities = $order->items()
            ->whereNotNull('product_variant_id')
            ->selectRaw('product_variant_id, SUM(quantity) as total')
            ->groupBy('product_variant_id')
            ->pluck('total', 'product_variant_id');

        $variants = ProductVariant::query()
            ->whereIn('id', $quantities->keys()->all())
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($variants as $variant) {
            $variant->increment('stock', (int) $quantities[$variant->id]);
        }

        $order->reservation_status = 'released';
        $order->save();
    }
}
