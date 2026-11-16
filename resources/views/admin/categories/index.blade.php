@extends('layouts.admin')

@section('title', 'Kategori — VELORA Admin')
@section('header', 'Manajemen Kategori')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-muted">Kelola kategori katalog VELORA. Kategori yang masih memiliki produk tidak dapat dihapus.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary btn-sm">+ Tambah Kategori</a>
    </div>

    <div class="mt-5 rounded-2xl border border-line bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-3">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="flex gap-2">
                <input type="search" name="q" value="{{ $search }}" placeholder="Cari kategori..." class="input input-sm w-56">
                <button type="submit" class="btn-soft btn-sm">Cari</button>
                @if ($search)
                    <a href="{{ route('admin.categories.index') }}" class="btn-ghost btn-sm">Reset</a>
                @endif
            </form>
            <p class="text-xs text-muted">{{ $categories->total() }} kategori</p>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Slug</th>
                        <th>Produk</th>
                        <th>Deskripsi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-cream-100 text-xs font-semibold text-burgundy-700">
                                        @if ($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $category->initials }}
                                        @endif
                                    </span>
                                    <span class="font-medium">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-muted">{{ $category->slug }}</td>
                            <td><span class="badge-neutral">{{ $category->products_count }}</span></td>
                            <td class="max-w-xs truncate text-xs text-muted">{{ $category->description ?: '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-soft btn-sm">Edit</a>
                                    @if ($category->products_count > 0)
                                        <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="text-xs text-muted hover:text-burgundy-700" title="Kategori masih memiliki produk">Produk terkait</a>
                                    @else
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $category->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost btn-sm text-danger hover:bg-red-50">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-sm text-muted">Tidak ada kategori ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line px-5 py-3">{{ $categories->links() }}</div>
    </div>
@endsection
