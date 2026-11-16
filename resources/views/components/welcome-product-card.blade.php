@php
    $discountPercent = function ($product) {
        if (! $product->compare_price || $product->compare_price <= $product->price) {
            return null;
        }

        return (int) round((1 - ($product->price / $product->compare_price)) * 100);
    };
@endphp

<article class="vw-product">
    <a href="{{ route('products.show', $product) }}" class="vw-product__media" aria-label="Lihat {{ $product->name }}">
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="vw-product__fallback">{{ initials($product->name) }}</span>
        @endif

        @if ($product->is_sold_out)
            <span class="vw-product__tag vw-product__tag--ink">Habis</span>
        @elseif ($discountPercent($product))
            <span class="vw-product__tag">-{{ $discountPercent($product) }}%</span>
        @endif
    </a>

    <div class="vw-product__body">
        <p class="vw-product__cat">{{ $product->category?->name ?? 'VELORA' }}</p>
        <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="vw-product__price">
            <span>{{ rupiah($product->price) }}</span>
            @if ($discountPercent($product))
                <del>{{ rupiah($product->compare_price) }}</del>
            @endif
        </div>
    </div>
</article>
