<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\PaymentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly PaymentStatusService $statusService,
    ) {}

    public function show(Order $order): View|RedirectResponse
    {
        Gate::authorize('view', $order);

        $order->load(['items', 'payment']);

        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()->route('orders.show', $order)
                ->with('status', 'Pembayaran pesanan ini sudah lunas.');
        }

        if (! $order->can_be_paid) {
            return redirect()->route('orders.show', $order)
                ->with('warning', 'Pesanan ini tidak berada pada tahap menunggu pembayaran.');
        }

        return view('orders.payment', [
            'order' => $order,
            'configured' => $this->midtrans->isConfigured(),
            'clientKey' => $this->midtrans->clientKey(),
            'snapScriptUrl' => $this->midtrans->snapScriptUrl(),
            'snapToken' => $order->payment?->snap_token,
            'isProduction' => $this->midtrans->isProduction(),
        ]);
    }

    public function token(Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        if (! $order->can_be_paid) {
            return back()->with('error', 'Pesanan ini tidak dapat dibayar pada status saat ini.');
        }

        try {
            $this->midtrans->createSnapToken($order);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Token pembayaran berhasil dibuat. Klik "Bayar Sekarang" untuk melanjutkan.');
    }

    public function refresh(Order $order, Request $request): RedirectResponse
    {
        Gate::authorize('view', $order);

        if ($order->payment_status === PaymentStatus::Paid) {
            return back()->with('status', 'Pembayaran sudah tercatat lunas.');
        }

        if (! $this->midtrans->isConfigured()) {
            return back()->with('error', 'Kredensial Midtrans belum dikonfigurasi, status belum dapat diverifikasi.');
        }

        try {
            $status = $this->midtrans->fetchStatus($order->order_number);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (($status['order_id'] ?? null) !== $order->order_number) {
            return back()->with('error', 'Data status gateway tidak cocok dengan pesanan.');
        }

        $result = $this->statusService->apply($order, $status);

        return back()->with(
            $result['applied'] ? 'success' : 'warning',
            $result['reason']
        );
    }
}
