@extends('layouts.admin')

@section('title', 'Produk — VELORA Admin')
@section('header', 'Manajemen Produk')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">Kelola produk, harga, foto, dan varian stok. Produk yang dihapus akan hilang dari katalog.</p>
        <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm">+ Tambah Produk</a>
    </div>

    <div class="mt-5 rounded-2xl border border-line bg-white shadow-sm">
        <form action="{{ route('admin.products.index') }}" method="GET" class="grid gap-2 border-b border-line px-5 py-3 sm:grid-cols-[1fr_auto_auto_auto_auto]">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama produk..." class="input input-sm">
            <select name="category" class="input input-sm">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) ($filters['category'] ?? 0) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="input input-sm">
                <option value="">Semua status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
            <button type="submit" class="btn-soft btn-sm">Terapkan</button>
            <a href="{{ route('admin.products.index') }}" class="btn-ghost btn-sm text-center">Reset</a>
        </form>

        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Varian</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-cream-100">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[10px] font-semibold text-burgundy-700">VELORA</span>
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="max-w-56 truncate font-medium">{{ $product->name }}</p>
                                        <p class="text-[11px] text-muted">{{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-muted">{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                <p class="font-medium">{{ rupiah($product->price) }}</p>
                                @if ($product->compare_price)
                                    <p class="text-[11px] text-muted line-through">{{ rupiah($product->compare_price) }}</p>
                                @endif
                            </td>
                            <td><span class="badge-neutral">{{ $product->variants_count }}</span></td>
                            <td>
                                <span class="{{ ($product->total_stock ?? 0) > 0 ? 'text-ink' : 'text-danger' }} font-medium">{{ $product->total_stock ?? 0 }}</span>
                            </td>
                            <td>
                                @if ($product->is_active)
                                    <span class="badge-success">Aktif</span>
                                @else
                                    <span class="badge-neutral">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ $product->is_active ? route('products.show', $product->slug) : '#' }}" class="btn-ghost btn-sm {{ $product->is_active ? '' : 'pointer-events-none opacity-40' }}" target="_blank" rel="noopener">Lihat</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-soft btn-sm">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-muted">Tidak ada produk ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line px-5 py-3">{{ $products->links() }}</div>
    </div>
@endsection
