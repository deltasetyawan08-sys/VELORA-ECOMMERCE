@extends('layouts.public')

@section('title', $product->name.' — VELORA')

@section('meta_description', Str::limit(strip_tags($product->description), 150))

@section('content')
    @php
        $variantData = $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'size' => $variant->size,
            'color' => $variant->color,
            'stock' => (int) $variant->stock,
            'sku' => $variant->sku,
        ])->values();
    @endphp

    <div class="container-shop py-6">
        <nav class="flex flex-wrap items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-burgundy-700">Belanja</a>
            <span>/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-burgundy-700">{{ $product->category->name }}</a>
            <span>/</span>
            <span class="font-medium text-burgundy-700">{{ $product->name }}</span>
        </nav>
    </div>

    <section class="container-shop pb-14">
        <div class="grid gap-8 lg:grid-cols-[1.05fr_1fr] lg:gap-12">
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="relative overflow-hidden rounded-[1.75rem] border border-line bg-cream-100">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="aspect-[4/5] w-full object-cover">
                    @else
                        <div class="flex aspect-[4/5] w-full items-center justify-center bg-gradient-to-br from-cream-200 to-burgundy-100">
                            <span class="font-display text-5xl tracking-[0.2em] text-burgundy-600/40">VELORA</span>
                        </div>
                    @endif
                    <span class="absolute left-4 top-4 badge-neutral bg-white/90">{{ $product->category->name }}</span>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center text-[11px] text-muted">
                    <div class="rounded-xl border border-line bg-cream-50 px-2 py-3">Ongkir flat {{ rupiah((int) config('shop.shipping_cost')) }}</div>
                    <div class="rounded-xl border border-line bg-cream-50 px-2 py-3">Pembayaran Midtrans</div>
                    <div class="rounded-xl border border-line bg-cream-50 px-2 py-3">Invoice digital</div>
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-info">{{ $product->category->name }}</span>
                    @if ($product->is_sold_out)
                        <span class="badge-danger">Stok Habis</span>
                    @else
                        <span class="badge-success">Tersedia</span>
                    @endif
                </div>

                <h1 class="mt-3 font-display text-3xl leading-tight text-ink sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-3 text-3xl font-semibold text-burgundy-700">{{ rupiah($product->price) }}</p>

                <div class="mt-5 rounded-2xl border border-line bg-white p-5"
                     data-variants="{{ $variantData->toJson() }}"
                     x-data='{
                        variants: JSON.parse($el.dataset.variants),
                        size: null,
                        color: null,
                        qty: 1,
                        get sizes() { return [...new Set(this.variants.map(v => v.size))]; },
                        get colors() { return [...new Set(this.variants.filter(v => v.size === this.size).map(v => v.color))]; },
                        get variant() { return this.variants.find(v => v.size === this.size && v.color === this.color) || null; },
                        get stock() { return this.variant ? this.variant.stock : 0; },
                        get soldOut() { return ! this.variant || this.variant.stock < 1; },
                        get maxQty() { return Math.max(1, Math.min(10, this.stock)); },
                        init() {
                            const first = this.variants.find(v => v.stock > 0) || this.variants[0];
                            if (first) { this.size = first.size; this.color = first.color; }
                        },
                        sizeAvailable(s) { return this.variants.some(v => v.size === s && v.stock > 0); },
                        chooseSize(s) {
                            this.size = s;
                            const available = [...new Set(this.variants.filter(v => v.size === s && v.stock > 0).map(v => v.color))];
                            if (! available.includes(this.color)) {
                                const fallback = this.variants.find(v => v.size === s);
                                this.color = available[0] || (fallback ? fallback.color : null);
                            }
                            this.qty = 1;
                        },
                        chooseColor(c) { this.color = c; this.qty = 1; },
                        inc() { if (this.qty < this.maxQty) this.qty++; },
                        dec() { if (this.qty > 1) this.qty--; }
                     }'>
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold">Pilih Ukuran</p>
                        <p class="text-xs text-muted" x-text="variant ? 'SKU: ' + variant.sku : ''"></p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="s in sizes" :key="s">
                            <button type="button" @click="chooseSize(s)" :disabled="! sizeAvailable(s)"
                                    :class="size === s ? 'border-burgundy-700 bg-burgundy-700 text-white' : 'border-line bg-white text-ink hover:border-burgundy-300'"
                                    class="min-w-11 rounded-xl border px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-35"
                                    x-text="s"></button>
                        </template>
                    </div>

                    <p class="mt-5 text-sm font-semibold">Pilih Warna</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="c in colors" :key="c">
                            <button type="button" @click="chooseColor(c)"
                                    :class="color === c ? 'border-burgundy-700 bg-burgundy-50 text-burgundy-800' : 'border-line bg-white text-ink hover:border-burgundy-300'"
                                    class="rounded-xl border px-4 py-2 text-sm font-medium transition"
                                    x-text="c"></button>
                        </template>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-cream-50 px-4 py-3">
                        <p class="text-sm text-muted">Stok tersedia: <span class="font-semibold text-ink" x-text="stock"></span></p>
                        <p x-show="soldOut" x-cloak class="badge-danger">Varian habis</p>
                    </div>

                    @error('quantity')
                        <p class="field-error mt-3">{{ $message }}</p>
                    @enderror
                    @error('product_variant_id')
                        <p class="field-error mt-3">{{ $message }}</p>
                    @enderror

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <div class="flex items-center rounded-full border border-line">
                            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-l-full text-lg text-muted transition hover:bg-cream-100 disabled:opacity-40" @click="dec()" :disabled="qty <= 1">−</button>
                            <span class="w-10 text-center text-sm font-semibold" x-text="qty"></span>
                            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-r-full text-lg text-muted transition hover:bg-cream-100 disabled:opacity-40" @click="inc()" :disabled="qty >= maxQty">+</button>
                        </div>
                        <p class="text-xs text-muted">Maks. <span x-text="maxQty"></span> per pembelian</p>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <form action="{{ route('cart.items.store') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="product_variant_id" :value="variant ? variant.id : ''">
                                <input type="hidden" name="quantity" :value="qty">
                                <button type="submit" class="btn-primary w-full" :disabled="soldOut">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                                    Tambah ke Keranjang
                                </button>
                            </form>
                            <form action="{{ route('checkout.buy-now') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="product_variant_id" :value="variant ? variant.id : ''">
                                <input type="hidden" name="quantity" :value="qty">
                                <button type="submit" class="btn-outline w-full" :disabled="soldOut">Beli Sekarang</button>
                            </form>
                        @else
                            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn-primary flex-1">Masuk untuk Membeli</a>
                            <a href="{{ route('register') }}" class="btn-outline flex-1">Daftar Akun</a>
                        @endauth
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-line bg-white">
                    <div class="border-b border-line px-5 py-3 text-sm font-semibold">Deskripsi Produk</div>
                    <div class="px-5 py-4 text-sm leading-relaxed text-muted">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>

                <details class="group mt-4 rounded-2xl border border-line bg-white">
                    <summary class="flex cursor-pointer items-center justify-between px-5 py-3 text-sm font-semibold">
                        Panduan Ukuran Sederhana
                        <svg class="h-4 w-4 text-muted transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="border-t border-line px-5 py-4">
                        <p class="text-xs text-muted">Ukuran dapat sedikit berbeda tergantung model dan bahan. Toleransi ±1–2 cm.</p>
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-left text-xs">
                                <thead class="text-muted">
                                    <tr>
                                        <th class="py-2 pr-4 font-semibold">Ukuran</th>
                                        <th class="py-2 pr-4 font-semibold">Lebar Dada (cm)</th>
                                        <th class="py-2 pr-4 font-semibold">Panjang (cm)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    <tr><td class="py-2 pr-4">S</td><td class="py-2 pr-4">46</td><td class="py-2 pr-4">62</td></tr>
                                    <tr><td class="py-2 pr-4">M</td><td class="py-2 pr-4">49</td><td class="py-2 pr-4">64</td></tr>
                                    <tr><td class="py-2 pr-4">L</td><td class="py-2 pr-4">52</td><td class="py-2 pr-4">66</td></tr>
                                    <tr><td class="py-2 pr-4">XL</td><td class="py-2 pr-4">55</td><td class="py-2 pr-4">68</td></tr>
                                    <tr><td class="py-2 pr-4">XXL</td><td class="py-2 pr-4">58</td><td class="py-2 pr-4">70</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-cream-50 py-14">
            <div class="container-shop">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="section-eyebrow">Anda Mungkin Suka</p>
                        <h2 class="section-title">Produk terkait</h2>
                    </div>
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-sm font-semibold text-burgundy-700 hover:underline">Lihat kategori {{ $product->category->name }} &rarr;</a>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
