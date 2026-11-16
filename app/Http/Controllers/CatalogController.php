<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'size' => ['nullable', 'string', 'max:20'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc'],
        ]);

        $query = Product::query()
            ->active()
            ->with([
                'category',
                'variants' => fn ($q) => $q->where('is_active', true),
            ])
            ->search($filters['q'] ?? null);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['size'])) {
            $query->whereHas('variants', function ($q) use ($filters) {
                $q->where('is_active', true)
                    ->where('size', $filters['size'])
                    ->where('stock', '>', 0);
            });
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        $sizes = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->active())
            ->distinct()
            ->orderBy('size')
            ->pluck('size');

        $bounds = Product::query()->active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return view('catalog.index', [
            'products' => $products,
            'categories' => $categories,
            'sizes' => $sizes,
            'filters' => $filters,
            'bounds' => $bounds,
            'activeCategory' => $categories->firstWhere('slug', $filters['category'] ?? null),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category',
            'variants' => fn ($query) => $query->where('is_active', true)->orderBy('size')->orderBy('color'),
        ]);

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['category', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($related->count() < 4) {
            $related = $related->merge(
                Product::query()
                    ->active()
                    ->whereKeyNot($product->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->with(['category', 'variants' => fn ($q) => $q->where('is_active', true)])
                    ->latest()
                    ->take(4 - $related->count())
                    ->get()
            );
        }

        $sizes = $product->variants->pluck('size')->unique()->values();

        return view('catalog.show', [
            'product' => $product,
            'related' => $related,
            'sizes' => $sizes,
        ]);
    }
}
