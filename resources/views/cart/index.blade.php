@extends('layouts.public')

@section('title', 'Keranjang Belanja — VELORA')

@section('content')
    @php
        $selectionData = $items->map(fn ($item) => [
            'id' => $item->id,
            'price' => $item->unit_price,
            'qty' => $item->quantity,
            'stock' => (int) ($item->variant?->stock ?? 0),
            'valid' => $item->is_valid,
        ])->values();
    @endphp

    <div class="container-shop py-8 lg:py-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <nav class="flex items-center gap-2 text-xs text-muted">
                    <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a><span>/</span><span class="text-burgundy-700">Keranjang</span>
                </nav>
                <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Keranjang Belanja</h1>
                <p class="mt-1 text-sm text-muted">{{ $totals['count'] }} item di keranjang Anda.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn-outline">&larr; Lanjut Belanja</a>
        </div>

        @if ($items->isEmpty())
            <x-empty-state title="Keranjang Anda masih kosong" description="Yuk jelajahi koleksi VELORA dan temukan gaya favorit Anda." class="mt-8">
                <a href="{{ route('products.index') }}" class="btn-primary">Mulai Belanja</a>
            </x-empty-state>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]"
                 data-cart-items="{{ $selectionData->toJson() }}"
                 x-data='{
                    selected: [],
                    items: JSON.parse($el.dataset.cartItems),
                    init() { this.selected = this.validItems.map(i => String(i.id)); },
                    get validItems() { return this.items.filter(i => i.valid); },
                    get selectedIds() { return this.selected.map(Number); },
                    get selectedCount() { return this.selected.length; },
                    get selectedSubtotal() { return this.items.filter(i => this.selectedIds.includes(Number(i.id))).reduce((sum, i) => sum + (i.price * i.qty), 0); },
                    get allSelected() { return this.validItems.length > 0 && this.selected.length === this.validItems.length; },
                    toggleAll(event) { this.selected = event.target.checked ? this.validItems.map(i => String(i.id)) : []; },
                    money(value) { return "Rp" + new Intl.NumberFormat("id-ID").format(value); }
                 }'>
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-2xl border border-line bg-white px-5 py-3">
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-medium">
                            <input type="checkbox" class="h-4 w-4 rounded border-line text-burgundy-700 focus:ring-burgundy-400" :checked="allSelected" @change="toggleAll($event)">
                            Pilih semua item yang tersedia
                        </label>
                        <span class="text-xs text-muted" x-text="selectedCount + ' dipilih'"></span>
                    </div>

                    @foreach ($items as $item)
                        @php($variant = $item->variant)
                        @php($product = $variant?->product)
                        <div class="rounded-2xl border border-line bg-white p-4 sm:p-5">
                            <div class="flex gap-3 sm:gap-4">
                                <div class="flex items-start pt-8">
                                    @if ($item->is_valid)
                                        <input type="checkbox" value="{{ $item->id }}" x-model="selected" class="h-4 w-4 rounded border-line text-burgundy-700 focus:ring-burgundy-400" aria-label="Pilih {{ $product?->name }}">
                                    @else
                                        <input type="checkbox" disabled class="h-4 w-4 rounded border-line bg-cream-100" aria-label="Item tidak tersedia">
                                    @endif
                                </div>

                                <a href="{{ $product ? route('products.show', $product) : '#' }}" class="block h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-cream-100 sm:h-28 sm:w-24">
                                    @if ($product?->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs text-muted">No image</div>
                                    @endif
                                </a>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <a href="{{ $product ? route('products.show', $product) : '#' }}" class="line-clamp-2 text-sm font-medium text-ink hover:text-burgundy-700">{{ $product?->name ?? 'Produk tidak tersedia' }}</a>
                                            <p class="mt-1 text-xs text-muted">{{ $product?->category?->name }}</p>
                                            <p class="mt-1 text-xs text-muted">Varian: <span class="font-medium text-ink">{{ $variant?->size }} / {{ $variant?->color }}</span></p>
                                        </div>
                                        <p class="text-sm font-semibold text-burgundy-700">{{ rupiah($item->unit_price) }}</p>
                                    </div>

                                    @if (! $item->is_valid)
                                        <p class="mt-2 text-xs font-medium text-danger">
                                            @if (! $product || ! $product->is_active)
                                                Produk sudah tidak aktif. Silakan hapus item ini.
                                            @elseif (($variant?->stock ?? 0) < 1)
                                                Stok varian ini habis. Silakan hapus item ini.
                                            @else
                                                Stok tersedia hanya {{ $variant?->stock }} item. Sesuaikan jumlah sebelum checkout.
                                            @endif
                                        </p>
                                    @endif

                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                        <form action="{{ route('cart.items.update', $item) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center rounded-full border border-line bg-white">
                                                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-l-full text-muted hover:bg-cream-100" onclick="const i=this.nextElementSibling; if(i.value > 1){i.value--; i.form.requestSubmit();}">−</button>
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="w-12 border-0 bg-transparent py-1.5 text-center text-xs font-semibold focus:outline-none" aria-label="Jumlah {{ $product?->name }}">
                                                <button type="button" class="flex h-8 w-8 items-center justify-center rounded-r-full text-muted hover:bg-cream-100" onclick="const i=this.previousElementSibling; i.value++; i.form.requestSubmit();">+</button>
                                            </div>
                                            <button type="submit" class="btn-soft btn-sm">Perbarui</button>
                                        </form>

                                        <div class="flex items-center gap-4">
                                            <p class="text-sm font-semibold">Subtotal: {{ rupiah($item->subtotal) }}</p>
                                            <form action="{{ route('cart.items.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-danger hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <aside class="lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-semibold">Ringkasan Belanja</h2>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between text-muted">
                                <span>Item dipilih</span>
                                <span class="font-medium text-ink" x-text="selectedCount + ' item'"></span>
                            </div>
                            <div class="flex justify-between text-muted">
                                <span>Subtotal</span>
                                <span class="font-semibold text-ink" x-text="money(selectedSubtotal)"></span>
                            </div>
                            <div class="flex justify-between text-muted">
                                <span>Ongkos kirim</span>
                                <span>Dihitung saat checkout</span>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between border-t border-line pt-4">
                            <span class="font-medium">Total sementara</span>
                            <span class="text-xl font-semibold text-burgundy-700" x-text="money(selectedSubtotal)"></span>
                        </div>

                        <form action="{{ route('checkout.show') }}" method="GET" class="mt-5">
                            <template x-for="id in selected" :key="id">
                                <input type="hidden" name="items[]" :value="id">
                            </template>
                            <button type="submit" class="btn-primary w-full" :disabled="selectedCount === 0">
                                Checkout Item Terpilih
                            </button>
                        </form>
                        <p class="mt-3 text-center text-xs text-muted">Harga dan stok akan divalidasi ulang di server sebelum pesanan dibuat.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
