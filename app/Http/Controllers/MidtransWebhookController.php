<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\PaymentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        MidtransService $midtrans,
        PaymentStatusService $statusService,
    ): JsonResponse {
        $payload = $request->all();

        if (! $midtrans->isConfigured()) {
            Log::warning('Midtrans notification received but gateway is not configured.');

            return response()->json(['message' => 'Gateway belum dikonfigurasi.'], 503);
        }

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Midtrans notification signature invalid.', [
                'order' => $payload['order_id'] ?? null,
            ]);

            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $orderNumber = (string) ($payload['order_id'] ?? '');

        $order = Order::query()->where('order_number', $orderNumber)->first();

        if (! $order) {
            Log::warning('Midtrans notification for unknown order.', ['order' => $orderNumber]);

            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        try {
            $verifiedStatus = $midtrans->fetchStatus($order->order_number);
        } catch (Throwable $exception) {
            Log::warning('Midtrans notification could not be confirmed via status API.', [
                'order' => $order->order_number,
            ]);

            return response()->json(['message' => 'Status belum dapat diverifikasi, coba lagi nanti.'], 503);
        }

        if (($verifiedStatus['order_id'] ?? null) !== $order->order_number) {
            return response()->json(['message' => 'Data gateway tidak cocok.'], 400);
        }

        $result = $statusService->apply($order, $verifiedStatus);

        return response()->json([
            'message' => $result['reason'],
            'payment_status' => $result['status']->value,
        ]);
    }
}
