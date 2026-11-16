<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $baseRelations = [
            'category',
            'variants' => fn ($query) => $query->where('is_active', true),
        ];

        $latestProducts = Product::query()
            ->active()
            ->with($baseRelations)
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

        $hasVerifiedSales = $bestSellerIds->isNotEmpty();

        $featuredProducts = $hasVerifiedSales
            ? Product::query()->active()->with($baseRelations)->whereIn('id', $bestSellerIds)->get()
                ->sortBy(fn (Product $product) => $bestSellerIds->search($product->id))->values()
            : $latestProducts->take(4);

        $categories = Category::query()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('id')
            ->get();

        return view('home', [
            'latestProducts' => $latestProducts,
            'featuredProducts' => $featuredProducts,
            'hasVerifiedSales' => $hasVerifiedSales,
            'categories' => $categories,
        ]);
    }
}
