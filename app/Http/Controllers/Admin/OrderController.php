<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\PaymentStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly PaymentStatusService $statusService,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['nullable', 'in:'.implode(',', array_column(PaymentStatus::cases(), 'value'))],
            'order_status' => ['nullable', 'in:'.implode(',', array_column(OrderStatus::cases(), 'value'))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $orders = Order::query()
            ->with(['user', 'items', 'payment'])
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', "%{$term}%")
                        ->orWhere('recipient_name', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $target = OrderStatus::from($request->validated('order_status'));

        if ($order->payment_status !== PaymentStatus::Paid) {
            return back()->with('error', 'Pesanan belum dibayar. Status pemrosesan hanya dapat diubah untuk pesanan yang sudah lunas.');
        }

        if (! $order->order_status->canTransitionTo($target)) {
            return back()->with('error', "Transisi status dari {$order->order_status->label()} ke {$target->label()} tidak diizinkan.");
        }

        $carrier = trim((string) $request->validated('carrier'));
        $trackingNumber = trim((string) $request->validated('tracking_number'));

        if ($target === OrderStatus::Shipped && ($carrier === '' || $trackingNumber === '')) {
            return back()->with('error', 'Jasa pengiriman dan nomor resi wajib diisi saat menandai pesanan dikirim.')->withInput();
        }

        DB::transaction(function () use ($order, $target, $carrier, $trackingNumber) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $locked->order_status = $target;

            if ($target === OrderStatus::Shipped) {
                $locked->carrier = $carrier;
                $locked->tracking_number = $trackingNumber;
                $locked->shipped_at = now();
            }

            if ($target === OrderStatus::Completed) {
                $locked->completed_at = now();
            }

            $locked->save();
        });

        return back()->with('success', "Status pesanan berhasil diubah menjadi {$target->label()}.");
    }

    public function refreshPayment(Order $order): RedirectResponse
    {
        if (! $this->midtrans->isConfigured()) {
            return back()->with('error', 'Kredensial Midtrans belum dikonfigurasi, status tidak dapat diverifikasi.');
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

        return back()->with($result['applied'] ? 'success' : 'warning', $result['reason']);
    }
}
