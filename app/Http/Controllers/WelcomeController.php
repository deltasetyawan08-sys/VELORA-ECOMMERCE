<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $relations = [
            'category',
            'variants' => fn ($query) => $query->where('is_active', true),
        ];

        $latestProducts = Product::query()
            ->active()
            ->with($relations)
            ->latest()
            ->take(8)
            ->get();

        $bestSellerIds = OrderItem::query()
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('product_variants.product_id as product_id, SUM(order_items.quantity) as sold')
            ->groupBy('product_variants.product_id')
            ->orderByDesc('sold')
            ->limit(8)
            ->pluck('product_id');

        $bestSellers = Product::query()
            ->active()
            ->with($relations)
            ->whereIn('id', $bestSellerIds)
            ->get()
            ->sortBy(fn (Product $product) => $bestSellerIds->search($product->id))
            ->values()
            ->take(4);

        $hasVerifiedSales = $bestSellers->isNotEmpty();

        if (! $hasVerifiedSales) {
            $bestSellers = $latestProducts->take(4)->values();
        }

        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('id')
            ->get();

        $categoryVisuals = [
            'atasan' => asset('welcome/category-atasan.jpg'),
            'bawahan' => asset('welcome/category-bawahan.jpg'),
            'dress' => asset('welcome/category-dress.jpg'),
            'outerwear' => asset('welcome/category-outerwear.jpg'),
            'pakaian-pria' => asset('welcome/category-pakaian-pria.jpg'),
            'aksesori' => asset('welcome/category-aksesori.jpg'),
        ];

        $fallbackCategoryVisual = asset('welcome/category-dress.jpg');

        $cartCount = 0;
        $user = auth()->user();

        if ($user && $user->isCustomer()) {
            $cartCount = (int) ($user->cart?->items()->sum('quantity') ?? 0);
        }

        return view('welcome', [
            'latestProducts' => $latestProducts,
            'bestSellers' => $bestSellers,
            'hasVerifiedSales' => $hasVerifiedSales,
            'categories' => $categories,
            'categoryVisuals' => $categoryVisuals,
            'fallbackCategoryVisual' => $fallbackCategoryVisual,
            'cartCount' => $cartCount,
        ]);
    }
}
