<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function buyNow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = ProductVariant::query()->with('product')->findOrFail($validated['product_variant_id']);

        if (! $variant->isPurchasable()) {
            return back()->with('error', 'Varian produk ini sedang tidak tersedia.');
        }

        if ((int) $validated['quantity'] > $variant->stock) {
            return back()->with('error', "Stok tersedia hanya {$variant->stock} item.");
        }

        $this->checkoutService->startBuyNow($variant->id, (int) $validated['quantity']);

        return redirect()->route('checkout.show');
    }

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->has('items')) {
            session()->forget('buy_now');
        }

        $this->checkoutService->checkoutKey();

        $requested = $request->input('items');
        $requestedIds = is_array($requested) ? $requested : null;

        $items = $this->checkoutService->resolveItems($request->user(), $requestedIds);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Tidak ada item valid yang dipilih untuk checkout.');
        }

        $subtotal = (int) $items->sum('subtotal');
        $shipping = (int) config('shop.shipping_cost');

        return view('checkout.show', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'user' => $request->user(),
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkoutService->createOrder($request->user(), $request->validated());

        return redirect()->route('orders.payment', $order)
            ->with('success', 'Pesanan '.$order->order_number.' berhasil dibuat. Silakan lanjutkan pembayaran.');
    }
}
