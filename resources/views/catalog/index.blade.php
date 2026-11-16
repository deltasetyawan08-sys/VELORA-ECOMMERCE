@extends('layouts.public')

@section('title', ($activeCategory?->name ? $activeCategory->name.' — ' : '').'Belanja — VELORA')

@section('content')
    <section class="border-b border-line bg-cream-100">
        <div class="container-shop py-10 lg:py-14">
            <nav class="flex flex-wrap items-center gap-2 text-xs text-muted">
                <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="hover:text-burgundy-700">Belanja</a>
                @if ($activeCategory)
                    <span>/</span>
                    <span class="font-medium text-burgundy-700">{{ $activeCategory->name }}</span>
                @endif
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="font-display text-3xl text-ink sm:text-4xl">{{ $activeCategory?->name ?? 'Semua Produk' }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-muted">{{ $activeCategory?->description ?? 'Jelajahi seluruh koleksi fashion VELORA. Gunakan filter untuk menemukan gaya favorit Anda.' }}</p>
                </div>
                <p class="text-sm text-muted"><span class="font-semibold text-ink">{{ $products->total() }}</span> produk ditemukan</p>
            </div>
        </div>
    </section>

    <section class="container-shop py-8 lg:py-12" x-data="{ filtersOpen: false }">
        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            <aside>
                <button type="button" class="btn-soft w-full lg:hidden" @click="filtersOpen = ! filtersOpen">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M7 12h10M10 18h4"/></svg>
                    Filter Produk
                </button>

                <form id="filter-form" action="{{ route('products.index') }}" method="GET" class="mt-4 space-y-6 rounded-2xl border border-line bg-white p-5 lg:sticky lg:top-24 lg:mt-0" :class="filtersOpen ? 'block' : 'hidden lg:block'">
                    <div>
                        <label for="filter-q" class="label">Pencarian</label>
                        <input id="filter-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari produk..." class="input">
                    </div>

                    <div>
                        <p class="label">Kategori</p>
                        <div class="space-y-1.5">
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-cream-50">
                                <input type="radio" name="category" value="" class="h-4 w-4 text-burgundy-700 focus:ring-burgundy-400" @checked(empty($filters['category']))>
                                <span class="flex-1">Semua kategori</span>
                            </label>
                            @foreach ($categories as $category)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-cream-50">
                                    <input type="radio" name="category" value="{{ $category->slug }}" class="h-4 w-4 text-burgundy-700 focus:ring-burgundy-400" @checked(($filters['category'] ?? '') === $category->slug)>
                                    <span class="flex-1">{{ $category->name }}</span>
                                    <span class="text-xs text-muted">{{ $category->products_count }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="label">Rentang Harga</p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" min="0" step="1000" placeholder="{{ $bounds?->min_price ? rupiah($bounds->min_price, false) : 'Min' }}" class="input input-sm" aria-label="Harga minimum">
                            <span class="text-muted">—</span>
                            <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" min="0" step="1000" placeholder="{{ $bounds?->max_price ? rupiah($bounds->max_price, false) : 'Max' }}" class="input input-sm" aria-label="Harga maksimum">
                        </div>
                        <p class="mt-1.5 text-[11px] text-muted">Harga dalam rupiah, tanpa titik.</p>
                    </div>

                    <div>
                        <label for="filter-size" class="label">Ukuran</label>
                        <select id="filter-size" name="size" class="input">
                            <option value="">Semua ukuran</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}" @selected(($filters['size'] ?? '') === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="filter-sort" class="label">Urutkan</label>
                        <select id="filter-sort" name="sort" class="input">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Terbaru</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Harga termurah</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Harga termahal</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary flex-1">Terapkan</button>
                        <a href="{{ route('products.index') }}" class="btn-soft">Reset</a>
                    </div>
                </form>
            </aside>

            <div>
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if (! empty($filters['q']))
                            <span class="badge-info">Pencarian: {{ $filters['q'] }}</span>
                        @endif
                        @if ($activeCategory)
                            <span class="badge-info">Kategori: {{ $activeCategory->name }}</span>
                        @endif
                        @if (! empty($filters['size']))
                            <span class="badge-info">Ukuran: {{ $filters['size'] }}</span>
                        @endif
                        @if (isset($filters['min_price']) || isset($filters['max_price']))
                            <span class="badge-info">
                                Harga:
                                {{ isset($filters['min_price']) ? rupiah($filters['min_price']) : 'Min' }}
                                —
                                {{ isset($filters['max_price']) ? rupiah($filters['max_price']) : 'Max' }}
                            </span>
                        @endif
                    </div>
                    <label class="flex items-center gap-2 text-xs text-muted">
                        Urutkan
                        <select name="sort" form="filter-form" class="input input-sm w-auto py-1.5" onchange="document.getElementById('filter-form').submit()">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Terbaru</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Termurah</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Termahal</option>
                        </select>
                    </label>
                </div>

                @if ($products->isEmpty())
                    <x-empty-state title="Produk tidak ditemukan" description="Coba ubah kata kunci atau hapus beberapa filter untuk melihat lebih banyak produk.">
                        <a href="{{ route('products.index') }}" class="btn-primary">Lihat Semua Produk</a>
                    </x-empty-state>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
