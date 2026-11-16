<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly ImageService $images) {}

    public function index(Request $request): View
    {
        $search = $request->query('q');

        $categories = Category::query()
            ->withCount('products')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Category::generateSlug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'), 'categories');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Category::generateSlug($data['name'], $category->id);

        if ($request->hasFile('image')) {
            $this->images->delete($category->image);
            $data['image'] = $this->images->store($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori masih memiliki produk (termasuk produk yang dihapus sementara). Pindahkan atau hapus permanen produk terlebih dahulu.');
        }

        $this->images->delete($category->image);
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
