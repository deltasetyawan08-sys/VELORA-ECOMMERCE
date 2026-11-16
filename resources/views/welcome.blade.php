@extends('layouts.welcome')

@section('title', 'VELORA — Discover Your Everyday Style')
@section('meta_description', 'Temukan gaya sehari-hari Anda bersama VELORA. Koleksi fashion yang terkurasi dengan nyaman dan elegan.')

@section('content')
<div class="ve-editorial">
    <section class="ve-hero" aria-labelledby="ve-hero-title">
        <div class="ve-hero__photos" aria-hidden="true">
            <img src="{{ asset('welcome/category-pakaian-pria.jpg') }}" alt="" fetchpriority="high">
            <img src="{{ asset('welcome/hero.jpg') }}" alt="" fetchpriority="high">
            <img src="{{ asset('welcome/hero-inset.jpg') }}" alt="" loading="eager">
        </div>
        <div class="ve-hero__top"><span>THE NEW SEASON EDIT</span><span>FASHION / INDONESIA</span></div>
        <p class="ve-hero__word" aria-hidden="true">VELORA</p>
        <canvas class="ve-hero__foreground" aria-hidden="true" hidden></canvas>
        <script src="{{ asset('welcome/hero-overlap.js') }}" defer></script>
        <div class="ve-hero__bottom">
            <div class="ve-hero__intro">
                <p>01 / DISCOVER YOUR EVERYDAY STYLE</p>
                <h1 id="ve-hero-title">Find your style.<br>Wear your confidence.</h1>
            </div>
            <div class="ve-hero__actions">
                <a href="{{ route('products.index') }}" class="ve-button ve-button--cream">Shop Now <span aria-hidden="true">↗</span></a>
                <a href="#koleksi" class="ve-button ve-button--glass">Explore Collection <span aria-hidden="true">↓</span></a>
            </div>
        </div>
    </section>
    <div class="ve-strip"><span>EVERYDAY STYLE</span><span aria-hidden="true">✳</span><span>THE VELORA EDIT</span><span aria-hidden="true">✳</span><span>MADE FOR YOUR MOMENTS</span></div>

    <section class="ve-section ve-new" id="new-arrivals" aria-labelledby="ve-new-title">
        <div class="vw-container">
            <header class="ve-heading ve-heading--center">
                <p class="ve-kicker">01 / The latest drop</p>
                <h2 id="ve-new-title">Discover our<br><em>new arrivals.</em></h2>
                <p class="ve-sub">Potongan pilihan untuk ritme dan gaya Anda sehari-hari.</p>
            </header>
            @if ($latestProducts->isEmpty())
                <p class="vw-empty">Koleksi terbaru sedang disiapkan. Kembali lagi sebentar lagi.</p>
            @else
                <div class="ve-rail" tabindex="0" role="region" aria-label="Produk terbaru, geser untuk melihat lebih banyak">
                    @foreach ($latestProducts as $product)
                        <x-welcome-product-card :product="$product" />
                    @endforeach
                </div>
                <p class="ve-rail__hint">Geser untuk melihat koleksi lainnya <span aria-hidden="true">→</span></p>
            @endif
            <div class="ve-center"><a href="{{ route('products.index', ['sort' => 'latest']) }}" class="ve-button ve-button--outline">View all products <span aria-hidden="true">↗</span></a></div>
        </div>
    </section>

    <section class="ve-manifesto" aria-labelledby="ve-manifesto-title">
        <div class="ve-manifesto__band" aria-hidden="true">FIND YOUR STYLE.</div>
        <div class="vw-container ve-manifesto__grid">
            <figure class="ve-manifesto__image"><img src="{{ asset('welcome/category-bawahan.jpg') }}" alt="Inspirasi gaya dengan atasan putih dan celana burgundy" loading="lazy"><figcaption>THE VELORA EDIT / 01</figcaption></figure>
            <article class="ve-manifesto__card">
                <p class="ve-kicker">A note from VELORA</p>
                <h2 id="ve-manifesto-title">Style is<br>personal.</h2>
                <p>Bukan tentang mengikuti semua tren. Tentang menemukan potongan yang tepat, merasa nyaman, dan menjadi diri sendiri.</p>
                <a href="#tentang" class="ve-button ve-button--cream">Our story <span aria-hidden="true">↗</span></a>
            </article>
        </div>
    </section>

    <section class="ve-section ve-collection" id="koleksi" aria-labelledby="ve-cat-title">
        <div class="vw-container">
            <header class="ve-heading ve-heading--center">
                <p class="ve-kicker">02 / Shop by category</p>
                <h2 id="ve-cat-title">Spotted now.<br><em>The style edit.</em></h2>
                <p class="ve-sub">Jelajahi koleksi sesuai gaya Anda.</p>
            </header>
            @if ($categories->isEmpty())
                <p class="vw-empty">Kategori akan tampil di sini setelah koleksi pertama ditambahkan.</p>
            @else
                <div class="ve-category-grid">
                    @foreach ($categories as $category)
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="ve-category{{ $loop->first ? ' ve-category--lead' : '' }}">
                            <img src="{{ $categoryVisuals[$category->slug] ?? $fallbackCategoryVisual }}" alt="Inspirasi gaya {{ $category->name }}" loading="lazy">
                            <div class="ve-category__label"><div><span>{{ $category->products_count }} produk</span><h3>{{ $category->name }}</h3></div><span class="ve-category__arrow" aria-hidden="true">↗</span></div>
                        </a>
                    @endforeach
                </div>
            @endif
            <div class="ve-center"><a href="{{ route('products.index') }}" class="ve-button ve-button--outline">Lihat semua koleksi <span aria-hidden="true">↗</span></a></div>
        </div>
    </section>

    <section class="ve-campaign" aria-labelledby="ve-campaign-title">
        <img src="{{ asset('welcome/promo.jpg') }}" alt="" loading="lazy" aria-hidden="true">
        <div class="ve-campaign__copy">
            <p class="ve-kicker">The VELORA Edit / 2026</p>
            <h2 id="ve-campaign-title">Elevate your<br>everyday look.</h2>
            <p>Potongan yang mudah dipadupadankan, dan detail yang membuat setiap penampilan terasa istimewa.</p>
            <a href="{{ route('products.index') }}" class="ve-button ve-button--cream">Explore Collection <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <section class="ve-section ve-featured" id="best-sellers" aria-labelledby="ve-best-title">
        <div class="vw-container">
            <header class="ve-heading ve-heading--center">
                <p class="ve-kicker">03 / {{ $hasVerifiedSales ? 'Best sellers' : 'Featured collection' }}</p>
                <h2 id="ve-best-title">{{ $hasVerifiedSales ? 'Most loved pieces.' : 'A curated beginning.' }}</h2>
                @unless ($hasVerifiedSales)
                    <p class="ve-sub">Pilihan dari koleksi terbaru VELORA. Produk terlaris akan tampil setelah ada transaksi terverifikasi.</p>
                @endunless
            </header>
            @if ($bestSellers->isEmpty())
                <p class="vw-empty">Koleksi pilihan sedang disiapkan untuk Anda.</p>
            @else
                <div class="ve-featured__grid">
                    @foreach ($bestSellers as $product)
                        <x-welcome-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
            <div class="ve-center"><a href="{{ route('products.index') }}" class="ve-button ve-button--outline">Belanja sekarang <span aria-hidden="true">↗</span></a></div>
        </div>
    </section>

    <section class="ve-about" id="tentang" aria-labelledby="ve-about-title">
        <figure><img src="{{ asset('welcome/about.jpg') }}" alt="Model dengan pakaian putih dalam sesi editorial VELORA" loading="lazy"></figure>
        <div class="ve-about__copy">
            <p class="ve-kicker">04 / About VELORA</p>
            <h2 id="ve-about-title">Join the<br>VELORA way.</h2>
            <p>VELORA adalah toko fashion online yang percaya bahwa gaya terbaik adalah gaya yang membuat Anda nyaman. Kami mengurasi atasan, bawahan, dress, outerwear, pakaian pria dan aksesori dengan perhatian pada bahan, potongan dan detail.</p>
            <a href="{{ route('products.index') }}" class="ve-button ve-button--primary">Find your style <span aria-hidden="true">↗</span></a>
        </div>
    </section>
    <section class="ve-statement" aria-label="Filosofi VELORA">
        <p class="ve-statement__outline" aria-hidden="true">EVERYDAY<br>IS A RUNWAY.</p>
        <img src="{{ asset('welcome/category-dress.jpg') }}" alt="Inspirasi fashion VELORA" loading="lazy">
        <p class="ve-statement__solid">BE YOUR<br><em>OWN STYLE.</em></p>
    </section>
    <section class="vw-section" id="keunggulan" aria-labelledby="vw-advantages-title">
        <div class="vw-container">
            <div class="vw-section__head vw-section__head--center">
                <div>
                    <p class="vw-eyebrow">Our Advantages</p>
                    <h2 id="vw-advantages-title">Thoughtful by <em>design.</em></h2>
                </div>
            </div>

            <div class="vw-advantages">
                <article class="vw-advantage">
                    <span class="vw-advantage__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.1 11.1a1.5 1.5 0 0 1-1.5 1.9H6.4a1.5 1.5 0 0 1-1.5-1.9L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                    </span>
                    <h3>Easy Shopping</h3>
                    <p>Temukan potongan favorit dengan pencarian, filter kategori, dan pilihan varian yang jelas.</p>
                </article>

                <article class="vw-advantage">
                    <span class="vw-advantage__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 19 6v5.6c0 4-2.8 7.1-7 8.9-4.2-1.8-7-4.9-7-8.9V6l7-2.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <h3>Secure Payment</h3>
                    <p>Transaksi diproses aman di sisi server. Status pembayaran hanya diperbarui dari sumber terverifikasi.</p>
                </article>

                <article class="vw-advantage">
                    <span class="vw-advantage__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4a2 2 0 0 1 2 2c0 .7-.4 1.4-1 1.7V9h4.5a1.5 1.5 0 0 1 1.4 1l1 3.5H4.1l1-3.5a1.5 1.5 0 0 1 1.4-1H11V7.7c-.6-.3-1-1-1-1.7a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" d="M4.5 15h15v3.5a1.5 1.5 0 0 1-1.5 1.5H6a1.5 1.5 0 0 1-1.5-1.5V15Z"/></svg>
                    </span>
                    <h3>Fashion Collection</h3>
                    <p>Koleksi terkurasi dari atasan hingga aksesori, dengan pilihan warna dan ukuran yang lengkap.</p>
                </article>

                <article class="vw-advantage">
                    <span class="vw-advantage__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7.5h10v9h-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10h4l3 3v3.5h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/><path stroke-linecap="round" d="M8.5 10.5h3"/></svg>
                    </span>
                    <h3>Order Tracking</h3>
                    <p>Pantau status pesanan dari pembayaran hingga pengiriman, lengkap dengan riwayat dan invoice digital.</p>
                </article>
            </div>
        </div>
    </section>

</div>
@endsection
