<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): View
    {
        $cart = $this->cartService->cartFor($request->user());

        $items = $cart->items()
            ->with(['variant.product.category'])
            ->latest()
            ->get();

        return view('cart.index', [
            'cart' => $cart,
            'items' => $items,
            'totals' => $this->cartService->totals($cart),
        ]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $item = $this->cartService->add(
            $request->user(),
            (int) $request->validated('product_variant_id'),
            (int) $request->validated('quantity'),
        );

        return back()->with('success', $item->variant->product->name.' berhasil ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        Gate::authorize('modify', $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cartService->update($cartItem, (int) $validated['quantity']);

        return back()->with('success', 'Jumlah item keranjang diperbarui.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        Gate::authorize('modify', $cartItem);

        $this->cartService->remove($cartItem);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
