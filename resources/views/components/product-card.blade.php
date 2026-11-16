@props(['product', 'showCategory' => true])

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
    <a href="{{ route('products.show', $product) }}" class="relative block aspect-[4/5] overflow-hidden bg-cream-100">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cream-200 to-cream-300">
                <span class="font-display text-4xl text-burgundy-700/40">{{ $product->initials ?? substr($product->name, 0, 2) }}</span>
            </div>
        @endif

        @if ($product->is_sold_out)
            <span class="absolute left-3 top-3 rounded-full bg-ink/80 px-3 py-1 text-[11px] font-semibold text-white">Habis</span>
        @elseif ($product->total_stock <= 5)
            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold text-burgundy-700 shadow-sm">Stok terbatas</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        @if ($showCategory && $product->category)
            <p class="text-[11px] font-semibold uppercase tracking-wider text-burgundy-500">{{ $product->category->name }}</p>
        @endif
        <h3 class="mt-1 line-clamp-2 text-sm font-medium leading-snug text-ink">
            <a href="{{ route('products.show', $product) }}" class="transition hover:text-burgundy-700">{{ $product->name }}</a>
        </h3>
        <div class="mt-auto flex items-end justify-between gap-2 pt-3">
            <div>
                <p class="text-base font-semibold text-burgundy-700">{{ rupiah($product->price) }}</p>
                <p class="mt-0.5 text-[11px] text-muted">Stok {{ $product->total_stock }}</p>
            </div>
            <a href="{{ route('products.show', $product) }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-cream-100 text-burgundy-700 transition hover:bg-burgundy-700 hover:text-white" aria-label="Lihat {{ $product->name }}">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
            </a>
        </div>
    </div>
</article>
