<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\PaymentStatusService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePendingOrders extends Command
{
    protected $signature = 'orders:reconcile-pending {--limit=50 : Jumlah maksimal pesanan yang diperiksa}';

    protected $description = 'Rekonsiliasi pesanan pending yang melewati batas pembayaran dengan status Midtrans.';

    public function handle(MidtransService $midtrans, PaymentStatusService $statusService): int
    {
        if (! $midtrans->isConfigured()) {
            $this->warn('Kredensial Midtrans belum dikonfigurasi. Rekonsiliasi dilewati agar status tidak diubah tanpa verifikasi.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));

        $orders = Order::query()
            ->where('payment_status', 'pending')
            ->whereHas('payment', fn ($query) => $query->whereNotNull('expiry_at')->where('expiry_at', '<=', now()))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Tidak ada pesanan pending yang perlu direkonsiliasi.');

            return self::SUCCESS;
        }

        foreach ($orders as $order) {
            try {
                $status = $midtrans->fetchStatus($order->order_number);
                $result = $statusService->apply($order, $status);
                $this->line("{$order->order_number}: {$result['status']->value} — {$result['reason']}");
            } catch (Throwable $exception) {
                $this->warn("{$order->order_number}: gagal rekonsiliasi — {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
