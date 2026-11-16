<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $products = Product::query()
            ->with('category')
            ->withSum('variants as total_stock', 'stock')
            ->withCount('variants')
            ->search($filters['q'] ?? null)
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'filters'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $variantRows = old('variants', [
            ['id' => null, 'size' => 'All Size', 'color' => 'Default', 'sku' => '', 'stock' => 0, 'is_active' => true],
        ]);

        return view('admin.products.create', compact('categories', 'variantRows'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->validateVariants($validated['variants']);

        $product = DB::transaction(function () use ($request, $validated) {
            $data = collect($validated)->except(['variants', 'image'])->all();
            $data['slug'] = Product::generateSlug($validated['name']);
            $data['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('image')) {
                $data['image'] = $this->images->store($request->file('image'), 'products');
            }

            $product = Product::create($data);

            foreach ($validated['variants'] as $index => $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => trim($variant['size']),
                    'color' => trim($variant['color']),
                    'sku' => trim($variant['sku']),
                    'stock' => (int) $variant['stock'],
                    'is_active' => $request->boolean("variants.{$index}.is_active"),
                ]);
            }

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $product->load('variants');

        $variantRows = old('variants', $product->variants->map(fn (ProductVariant $variant) => [
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'sku' => $variant->sku,
            'stock' => $variant->stock,
            'is_active' => $variant->is_active,
        ])->values()->all());

        return view('admin.products.edit', compact('product', 'categories', 'variantRows'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $this->validateVariants($validated['variants'], $product);

        DB::transaction(function () use ($request, $validated, $product) {
            $data = collect($validated)->except(['variants', 'image'])->all();
            $data['slug'] = Product::generateSlug($validated['name'], $product->id);
            $data['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('image')) {
                $this->images->delete($product->image);
                $data['image'] = $this->images->store($request->file('image'), 'products');
            }

            $product->update($data);

            $submittedIds = collect($validated['variants'])
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($product->variants as $variant) {
                if (in_array($variant->id, $submittedIds, true)) {
                    continue;
                }

                if ($variant->orderItems()->exists() || $variant->cartItems()->exists()) {
                    $variant->update(['is_active' => false]);
                } else {
                    $variant->delete();
                }
            }

            foreach ($validated['variants'] as $index => $row) {
                $attributes = [
                    'size' => trim($row['size']),
                    'color' => trim($row['color']),
                    'sku' => trim($row['sku']),
                    'stock' => (int) $row['stock'],
                    'is_active' => $request->boolean("variants.{$index}.is_active"),
                ];

                if (! empty($row['id'])) {
                    $variant = $product->variants()->whereKey($row['id'])->first();

                    if (! $variant) {
                        throw ValidationException::withMessages([
                            "variants.{$index}.sku" => 'Varian tidak ditemukan pada produk ini.',
                        ]);
                    }

                    $variant->update($attributes);
                } else {
                    $product->variants()->create($attributes);
                }
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product) {
            $product->variants()->update(['is_active' => false]);
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk dinonaktifkan dan dihapus dari katalog.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function validateVariants(array $variants, ?Product $product = null): void
    {
        $errors = [];
        $combos = [];

        $existingByCombo = collect();
        if ($product) {
            $existingByCombo = $product->variants->keyBy(fn (ProductVariant $variant) => mb_strtolower(trim($variant->size)).'|'.mb_strtolower(trim($variant->color)));
        }

        foreach ($variants as $index => $row) {
            $combo = mb_strtolower(trim((string) $row['size'])).'|'.mb_strtolower(trim((string) $row['color']));

            if (isset($combos[$combo])) {
                $errors["variants.{$index}.size"] = 'Kombinasi ukuran dan warna tidak boleh duplikat.';
            }
            $combos[$combo] = true;

            $existingSku = ProductVariant::query()->where('sku', trim((string) $row['sku']))->first();
            $rowId = ! empty($row['id']) ? (int) $row['id'] : null;

            if ($existingSku && ($rowId === null || $existingSku->id !== $rowId)) {
                $errors["variants.{$index}.sku"] = 'SKU sudah digunakan oleh varian lain.';
            }

            if ($rowId !== null) {
                $owned = ProductVariant::query()->find($rowId);

                if (! $owned || ($product && $owned->product_id !== $product->id)) {
                    $errors["variants.{$index}.sku"] = 'Varian tidak valid untuk produk ini.';
                }
            } elseif ($product && $existingByCombo->has($combo)) {
                $errors["variants.{$index}.size"] = 'Kombinasi ini sudah ada. Edit baris varian yang sudah ada.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
