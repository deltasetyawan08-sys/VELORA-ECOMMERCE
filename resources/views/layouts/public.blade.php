<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VELORA — Find Your Style, Wear Your Confidence')</title>
    <meta name="description" content="@yield('meta_description', 'VELORA adalah toko fashion pilihan dengan koleksi atasan, bawahan, dress, outerwear, dan aksesori premium.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white text-ink" x-data="{ mobileOpen: false, catOpen: false, accOpen: false }">

<div class="bg-burgundy-800 px-4 py-2 text-center text-[11px] font-medium tracking-wide text-cream-100 sm:text-xs">
    <span class="hidden sm:inline">Ongkir flat {{ rupiah((int) config('shop.shipping_cost')) }} ke seluruh Indonesia</span>
    <span class="sm:hidden">Ongkir flat {{ rupiah((int) config('shop.shipping_cost')) }}</span>
    <span class="mx-2 text-cream-300/60">•</span>
    <span>Pembayaran aman via Midtrans</span>
</div>

<header class="sticky top-0 z-40 border-b border-line/80 bg-white/95 backdrop-blur">
    <div class="container-shop">
        <div class="flex h-16 items-center justify-between gap-4 lg:h-20">
            <div class="flex items-center gap-3">
                <button type="button" class="btn-ghost -ml-2 p-2 lg:hidden" @click="mobileOpen = ! mobileOpen" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('home') }}" class="group flex items-baseline gap-2">
                    <span class="font-display text-2xl font-bold tracking-[0.18em] text-burgundy-700 sm:text-3xl">VELORA</span>
                    <span class="hidden text-[10px] font-medium uppercase tracking-[0.35em] text-muted sm:inline">Fashion</span>
                </a>
            </div>

            <nav class="hidden items-center gap-1 lg:flex">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Beranda</a>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}">Belanja</a>
                <div class="relative" @click.outside="catOpen = false">
                    <button type="button" class="nav-link flex items-center gap-1.5" @click="catOpen = ! catOpen">
                        Kategori
                        <svg class="h-4 w-4 transition" :class="catOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-cloak x-show="catOpen" x-transition class="absolute left-0 top-full mt-2 w-64 rounded-2xl border border-line bg-white p-2 shadow-xl">
                        @foreach ($navCategories as $category)
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm text-ink/80 transition hover:bg-cream-100 hover:text-burgundy-700">
                                <span>{{ $category->name }}</span>
                                <span class="text-xs text-muted">{{ $category->products_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>

            <form action="{{ route('products.index') }}" method="GET" class="hidden w-full max-w-sm flex-1 items-center lg:flex">
                <label for="nav-search" class="sr-only">Cari produk</label>
                <div class="relative w-full">
                    <input id="nav-search" type="search" name="q" value="{{ request()->routeIs('products.index') ? request('q') : '' }}" placeholder="Cari blouse, dress, outerwear..." class="input rounded-full bg-cream-50 pl-11">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                </div>
            </form>

            <div class="flex items-center gap-1 sm:gap-2">
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('cart.index') }}" class="relative rounded-full p-2.5 text-ink/80 transition hover:bg-cream-100 hover:text-burgundy-700" aria-label="Keranjang belanja">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                            @if ($cartCount > 0)
                                <span class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-burgundy-700 px-1 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @endif
                    <div class="relative" @click.outside="accOpen = false">
                        <button type="button" class="flex items-center gap-2 rounded-full p-1.5 pr-2.5 transition hover:bg-cream-100" @click="accOpen = ! accOpen">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-burgundy-700 text-xs font-bold text-white">{{ initials(auth()->user()->name) }}</span>
                            <span class="hidden max-w-24 truncate text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                            <svg class="hidden h-4 w-4 text-muted sm:block" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-cloak x-show="accOpen" x-transition class="absolute right-0 top-full mt-2 w-60 rounded-2xl border border-line bg-white p-2 shadow-xl">
                            <div class="border-b border-line px-3 py-2">
                                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                                <span class="badge-neutral mt-2">{{ auth()->user()->role->label() }}</span>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="mt-1 block rounded-xl px-3 py-2 text-sm hover:bg-cream-100">Dashboard Admin</a>
                            @else
                                <a href="{{ route('orders.index') }}" class="mt-1 block rounded-xl px-3 py-2 text-sm hover:bg-cream-100">Pesanan Saya</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-cream-100">Profil Saya</a>
                            <form action="{{ route('logout') }}" method="POST" class="mt-1 border-t border-line pt-1">
                                @csrf
                                <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm text-danger hover:bg-red-50">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost hidden sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm sm:px-5 sm:py-2.5">Daftar</a>
                @endauth
            </div>
        </div>
    </div>

    <div x-cloak x-show="mobileOpen" x-transition class="border-t border-line bg-white lg:hidden">
        <div class="container-shop space-y-3 py-4">
            <form action="{{ route('products.index') }}" method="GET">
                <div class="relative">
                    <input type="search" name="q" value="{{ request()->routeIs('products.index') ? request('q') : '' }}" placeholder="Cari produk..." class="input rounded-full bg-cream-50 pl-11">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                </div>
            </form>
            <nav class="flex flex-col gap-1">
                <a href="{{ route('home') }}" class="nav-link">Beranda</a>
                <a href="{{ route('products.index') }}" class="nav-link">Belanja</a>
                @foreach ($navCategories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="nav-link flex items-center gap-2 pl-6 text-muted">
                        <span class="h-1 w-1 rounded-full bg-burgundy-400"></span>{{ $category->name }}
                    </a>
                @endforeach
            </nav>
            @guest
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('login') }}" class="btn-outline flex-1">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary flex-1">Daftar</a>
                </div>
            @endguest
        </div>
    </div>
</header>

<main class="flex-1">
    <div class="container-shop pt-4">
        @include('components.flash')
    </div>
    @yield('content')
</main>

<footer class="mt-20 bg-burgundy-900 text-cream-100">
    <div class="container-shop py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="font-display text-3xl font-bold tracking-[0.18em] text-white">VELORA</p>
                <p class="mt-1 text-xs uppercase tracking-[0.3em] text-cream-300/70">Fashion</p>
                <p class="mt-5 max-w-xs text-sm leading-relaxed text-cream-100/70">{{ config('shop.tagline') }} Koleksi fashion modern dengan sentuhan elegan untuk menemani setiap momen Anda.</p>
                <div class="mt-6 flex items-center gap-2 text-xs text-cream-100/60">
                    <span class="flex h-2 w-2 rounded-full bg-green-400"></span>
                    Pembayaran diproses server-side via Midtrans
                </div>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-white">Belanja</p>
                <ul class="mt-4 space-y-2.5 text-sm text-cream-100/70">
                    <li><a href="{{ route('products.index') }}" class="transition hover:text-white">Semua Produk</a></li>
                    @foreach ($navCategories->take(5) as $category)
                        <li><a href="{{ route('products.index', ['category' => $category->slug]) }}" class="transition hover:text-white">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-white">Akun</p>
                <ul class="mt-4 space-y-2.5 text-sm text-cream-100/70">
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="transition hover:text-white">Profil Saya</a></li>
                        @if (auth()->user()->isCustomer())
                            <li><a href="{{ route('orders.index') }}" class="transition hover:text-white">Pesanan Saya</a></li>
                            <li><a href="{{ route('cart.index') }}" class="transition hover:text-white">Keranjang</a></li>
                        @else
                            <li><a href="{{ route('admin.dashboard') }}" class="transition hover:text-white">Dashboard Admin</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-white">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-white">Daftar Akun</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-white">Hubungi Kami</p>
                <ul class="mt-4 space-y-3 text-sm text-cream-100/70">
                    <li class="flex gap-2.5"><span class="text-cream-300">Alamat</span><span>{{ config('shop.address') }}</span></li>
                    <li class="flex gap-2.5"><span class="text-cream-300">Telepon</span><span>{{ config('shop.phone') }}</span></li>
                    <li class="flex gap-2.5"><span class="text-cream-300">Email</span><span>{{ config('shop.email') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-cream-100/50 sm:flex-row">
            <p>&copy; {{ now()->year }} VELORA. Seluruh hak cipta dilindungi.</p>
            <p>Dibuat untuk Tugas Besar Praktikum Pemrograman E-Commerce.</p>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
