@extends('layouts.public')

@section('title', 'VELORA — Find Your Style, Wear Your Confidence')

@section('content')
    <section class="relative overflow-hidden bg-cream-100">
        <div class="pointer-events-none absolute -right-24 top-10 h-72 w-72 rounded-full border border-burgundy-200/60"></div>
        <div class="pointer-events-none absolute -left-16 bottom-0 h-56 w-56 rounded-full bg-burgundy-100/40 blur-2xl"></div>

        <div class="container-shop relative grid items-center gap-12 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:py-24">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-burgundy-200 bg-white/70 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.2em] text-burgundy-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-burgundy-600"></span> New Season Collection
                </span>
                <h1 class="mt-6 font-display text-4xl leading-[1.1] text-ink sm:text-5xl lg:text-6xl">
                    Discover Your<br><span class="text-burgundy-700">Everyday Style</span>
                </h1>
                <p class="mt-5 max-w-md text-sm leading-relaxed text-muted sm:text-base">
                    Koleksi pakaian modern dengan bahan nyaman dan potongan elegan. Temukan gaya yang membuat Anda percaya diri setiap hari bersama VELORA.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.index') }}" class="btn-primary btn-lg">Belanja Sekarang</a>
                    <a href="#kategori" class="btn-outline btn-lg">Jelajahi Kategori</a>
                </div>
                <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-xs text-muted">
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-burgundy-500"></span> Pembayaran aman Midtrans</span>
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-burgundy-500"></span> Varian ukuran lengkap</span>
                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-burgundy-500"></span> Invoice digital</span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($latestProducts->take(2) as $index => $product)
                        <a href="{{ route('products.show', $product) }}" class="group relative block overflow-hidden rounded-[1.75rem] border border-white/60 bg-white shadow-xl transition duration-300 hover:-translate-y-1 {{ $index === 0 ? 'mt-8 aspect-[4/5]' : 'aspect-[4/5]' }}">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cream-200 to-burgundy-100 font-display text-4xl text-burgundy-600/50">VELORA</div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/70 to-transparent p-4">
                                <p class="text-xs font-medium text-white">{{ $product->name }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-cream-100">{{ rupiah($product->price) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($latestProducts->take(2)->isEmpty())
                    <div class="flex aspect-[4/5] items-center justify-center rounded-[2rem] bg-gradient-to-br from-cream-200 to-burgundy-100">
                        <span class="font-display text-5xl tracking-[0.2em] text-burgundy-600/40">VELORA</span>
                    </div>
                @endif

                <div class="absolute -bottom-5 left-1/2 flex -translate-x-1/2 items-center gap-3 rounded-2xl border border-line bg-white px-5 py-3 shadow-xl">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-burgundy-50 text-burgundy-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7 9 18l-5-5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-ink">Kualitas terkurasi</p>
                        <p class="text-[11px] text-muted">Bahan nyaman untuk harian</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="kategori" class="container-shop py-16 lg:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">Kategori Favorit</p>
                <h2 class="section-title">Temukan gaya sesuai kebutuhan</h2>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-burgundy-700 hover:underline">Lihat semua produk &rarr;</a>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="group flex flex-col items-center rounded-2xl border border-line bg-white p-4 text-center transition hover:-translate-y-1 hover:border-burgundy-200 hover:shadow-lg">
                    <span class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full bg-cream-100 text-lg font-semibold text-burgundy-700 ring-1 ring-cream-300 transition group-hover:ring-burgundy-200">
                        @if ($category->image_url)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                        @else
                            {{ $category->initials }}
                        @endif
                    </span>
                    <span class="mt-3 text-sm font-medium text-ink group-hover:text-burgundy-700">{{ $category->name }}</span>
                    <span class="text-[11px] text-muted">{{ $category->products_count }} produk</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="bg-cream-50 py-16 lg:py-20">
        <div class="container-shop">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="section-eyebrow">Koleksi Terbaru</p>
                    <h2 class="section-title">Baru tiba di VELORA</h2>
                </div>
                <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="text-sm font-semibold text-burgundy-700 hover:underline">Lihat semua &rarr;</a>
            </div>

            @if ($latestProducts->isEmpty())
                <x-empty-state title="Belum ada produk" description="Produk akan muncul di sini setelah ditambahkan oleh admin." class="mt-8" />
            @else
                <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($latestProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="container-shop py-16 lg:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">{{ $hasVerifiedSales ? 'Paling Dicari' : 'Kurasi VELORA' }}</p>
                <h2 class="section-title">{{ $hasVerifiedSales ? 'Produk terlaris pilihan pelanggan' : 'Koleksi pilihan untuk Anda' }}</h2>
                @unless ($hasVerifiedSales)
                    <p class="mt-2 max-w-xl text-sm text-muted">Rekomendasi kurasi dari koleksi terbaru VELORA. Urutan terlaris akan muncul setelah ada transaksi terverifikasi.</p>
                @endunless
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-burgundy-700 hover:underline">Belanja sekarang &rarr;</a>
        </div>

        @if ($featuredProducts->isEmpty())
            <x-empty-state title="Koleksi sedang disiapkan" description="Kembali lagi nanti untuk melihat koleksi pilihan VELORA." class="mt-8" />
        @else
            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($categories->isNotEmpty())
        @php($bannerCategory = $categories->firstWhere('slug', 'dress') ?? $categories->first())
        <section class="container-shop">
            <div class="relative overflow-hidden rounded-[2rem] bg-burgundy-900">
                @if ($bannerCategory?->image_url)
                    <img src="{{ $bannerCategory->image_url }}" alt="{{ $bannerCategory->name }}" class="absolute inset-0 h-full w-full object-cover opacity-40">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-burgundy-900 via-burgundy-900/80 to-burgundy-900/20"></div>
                <div class="relative grid gap-6 px-6 py-14 sm:px-12 lg:grid-cols-[1.1fr_0.9fr] lg:py-20">
                    <div class="max-w-lg text-cream-100">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cream-300/80">Signature Collection</p>
                        <h2 class="mt-3 font-display text-3xl leading-tight text-white sm:text-4xl">Elegan untuk setiap momen istimewa</h2>
                        <p class="mt-4 text-sm leading-relaxed text-cream-100/75">Jelajahi koleksi {{ $bannerCategory?->name }} dengan siluet modern, warna netral, dan detail yang dibuat untuk tampil percaya diri.</p>
                        @if ($bannerCategory)
                            <a href="{{ route('products.index', ['category' => $bannerCategory->slug]) }}" class="btn mt-7 bg-white text-burgundy-800 hover:bg-cream-100">Lihat Koleksi {{ $bannerCategory->name }}</a>
                        @endif
                    </div>
                    <div class="flex items-end justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="group hidden w-40 overflow-hidden rounded-2xl border border-white/20 sm:block">
                            @if ($latestProducts->first()?->image_url)
                                <img src="{{ $latestProducts->first()->image_url }}" alt="Koleksi VELORA" class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex aspect-[4/5] items-center justify-center bg-burgundy-700 font-display text-2xl text-cream-100/60">VELORA</div>
                            @endif
                        </a>
                        <a href="{{ route('products.index') }}" class="group hidden w-40 overflow-hidden rounded-2xl border border-white/20 md:block">
                            @if ($latestProducts->get(1)?->image_url)
                                <img src="{{ $latestProducts->get(1)->image_url }}" alt="Koleksi VELORA" class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex aspect-[4/5] items-center justify-center bg-burgundy-700 font-display text-2xl text-cream-100/60">VELORA</div>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="container-shop py-16 lg:py-20">
        <div class="mx-auto max-w-2xl text-center">
            <p class="section-eyebrow">Mengapa VELORA</p>
            <h2 class="section-title">Belanja fashion yang mudah dan aman</h2>
        </div>
        <div class="mt-10 grid gap-5 md:grid-cols-3">
            <div class="rounded-2xl border border-line bg-white p-6 transition hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-burgundy-50 text-burgundy-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                </span>
                <h3 class="mt-5 text-lg font-semibold">Belanja mudah</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted">Cari berdasarkan nama, filter kategori, harga, dan ukuran. Pilih varian langsung dari halaman produk.</p>
            </div>
            <div class="rounded-2xl border border-line bg-white p-6 transition hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-burgundy-50 text-burgundy-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path stroke-linecap="round" d="M2.5 10h19M6 15h4"/></svg>
                </span>
                <h3 class="mt-5 text-lg font-semibold">Pembayaran via Midtrans</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted">Transaksi diproses melalui Midtrans Sandbox dengan verifikasi server-side. Status pembayaran tidak dapat dipalsukan.</p>
            </div>
            <div class="rounded-2xl border border-line bg-white p-6 transition hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-burgundy-50 text-burgundy-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z"/><path stroke-linecap="round" d="M8 10h8M8 14h5"/></svg>
                </span>
                <h3 class="mt-5 text-lg font-semibold">Status pesanan transparan</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted">Pantau pembayaran, pemrosesan, pengiriman, hingga pesanan selesai. Invoice tersedia dan siap dicetak.</p>
            </div>
        </div>
    </section>
@endsection
