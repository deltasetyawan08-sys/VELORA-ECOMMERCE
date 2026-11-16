<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        if (! in_array($status, array_merge(['all'], array_column(OrderStatus::cases(), 'value')), true)) {
            $status = 'all';
        }

        $query = $request->user()->orders()
            ->with(['items.variant.product', 'payment'])
            ->latest();

        if ($status !== 'all') {
            $query->where('order_status', $status);
        }

        $orders = $query->paginate(8)->withQueryString();

        $counts = $request->user()->orders()
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return view('orders.index', [
            'orders' => $orders,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.variant.product', 'payment']);

        return view('orders.show', ['order' => $order]);
    }

    public function rate(Request $request, Order $order, OrderItem $orderItem): RedirectResponse
    {
        Gate::authorize('view', $order);
        abort_unless($request->user()->isCustomer() && $order->user_id === $request->user()->id, 403);
        abort_unless($orderItem->order_id === $order->id, 404);
    
        $validated = $request->validateWithBag('rating', [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ], [
            'rating.required' => 'Pilih jumlah bintang terlebih dahulu.',
            'rating.integer' => 'Penilaian harus berupa bilangan bulat.',
            'rating.min' => 'Penilaian minimal 1 bintang.',
            'rating.max' => 'Penilaian maksimal 5 bintang.',
        ]);
    
        $saved = DB::transaction(function () use ($order, $orderItem, $validated) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->order_status === OrderStatus::Completed
                && $locked->payment_status === PaymentStatus::Paid, 403, 'Penilaian hanya tersedia setelah pesanan lunas dan selesai.');
    
            return $locked->items()->whereKey($orderItem->id)->whereNull('rating')
                ->update(['rating' => (int) $validated['rating']]) === 1;
        });
    
        return redirect()->route('orders.show', $order)
            ->with($saved ? 'success' : 'warning', $saved
                ? 'Terima kasih! Penilaian produk Anda berhasil disimpan.'
                : 'Produk ini sudah Anda beri penilaian.');
    }

    public function invoice(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items', 'payment', 'user']);

        return view('orders.invoice', ['order' => $order]);
    }
}
