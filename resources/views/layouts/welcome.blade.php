<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VELORA — Discover Your Everyday Style')</title>
    <meta name="description" content="@yield('meta_description', 'VELORA adalah toko fashion online dengan koleksi atasan, bawahan, dress, outerwear, pakaian pria, dan aksesori yang terkurasi untuk gaya sehari-hari.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/welcome.css', 'resources/js/app.js'])
</head>
<body class="vw-body" x-data="{ mobileOpen: false, accountOpen: false }" :class="{ 'vw-no-scroll': mobileOpen }" @keydown.escape.window="mobileOpen = false; accountOpen = false">
<a href="#main" class="vw-skip">Langsung ke konten</a>

<div class="vw-announcement">
    <div class="vw-container vw-announcement__inner">
        <span>New Season 2026 &mdash; The VELORA Edit</span>
        <span class="vw-announcement__right">Ongkir flat {{ rupiah((int) config('shop.shipping_cost')) }} ke seluruh Indonesia</span>
    </div>
</div>

<header class="vw-header" id="top">
    <div class="vw-container vw-header__inner">
        <button type="button" class="vw-icon-btn vw-menu-toggle" @click="mobileOpen = ! mobileOpen" :aria-expanded="mobileOpen.toString()" :aria-label="mobileOpen ? 'Tutup menu' : 'Buka menu'" aria-controls="vw-mobile-menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <a href="{{ route('home') }}" class="vw-logo" aria-label="VELORA, kembali ke beranda">
            <span class="vw-logo__word">VELORA</span>
            <span class="vw-logo__sub">Fashion</span>
        </a>

        <nav class="vw-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Beranda</a>
            <a href="#koleksi">Koleksi</a>
            <a href="#tentang">Tentang Kami</a>
        </nav>

        <div class="vw-header__actions">
            @auth
                @if (auth()->user()->isCustomer())
                    <a href="{{ route('cart.index') }}" class="vw-icon-btn vw-cart" aria-label="Keranjang belanja">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.1 11.1a1.5 1.5 0 0 1-1.5 1.9H6.4a1.5 1.5 0 0 1-1.5-1.9L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                        @if ($cartCount > 0)
                            <span class="vw-cart__count">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <div class="vw-account" @click.outside="accountOpen = false">
                    <button type="button" class="vw-account__trigger" @click="accountOpen = ! accountOpen" :aria-expanded="accountOpen.toString()" aria-haspopup="true">
                        <span class="vw-account__avatar">{{ initials(auth()->user()->name) }}</span>
                        <span class="vw-account__name">{{ auth()->user()->name }}</span>
                        <svg class="vw-account__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div x-cloak x-show="accountOpen" x-transition.opacity class="vw-account__menu">
                        <div class="vw-account__meta">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                        </div>

                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                        @else
                            <a href="{{ route('orders.index') }}">Pesanan Saya</a>
                            <a href="{{ route('cart.index') }}">Keranjang</a>
                        @endif

                        <a href="{{ route('profile.edit') }}">Profil Saya</a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('cart.index') }}" class="vw-icon-btn vw-cart" aria-label="Keranjang belanja, masuk terlebih dahulu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.1 11.1a1.5 1.5 0 0 1-1.5 1.9H6.4a1.5 1.5 0 0 1-1.5-1.9L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
                </a>
                <a href="{{ route('login') }}" class="vw-login-link">Masuk</a>
                <a href="{{ route('register') }}" class="vw-btn vw-btn--primary vw-btn--small">Daftar</a>
            @endauth
        </div>
    </div>

    <div id="vw-mobile-menu" x-cloak x-show="mobileOpen" x-transition.opacity class="vw-mobile-nav">
        <nav class="vw-container" aria-label="Navigasi seluler">
            <a href="{{ route('home') }}" @click="mobileOpen = false">Beranda</a>
            <a href="#koleksi" @click="mobileOpen = false">Koleksi</a>
            <a href="#tentang" @click="mobileOpen = false">Tentang Kami</a>

            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false">Dashboard Admin</a>
                @else
                    <a href="{{ route('cart.index') }}" @click="mobileOpen = false">Keranjang</a>
                    <a href="{{ route('orders.index') }}" @click="mobileOpen = false">Pesanan Saya</a>
                @endif
                <a href="{{ route('profile.edit') }}" @click="mobileOpen = false">Profil Saya</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            @else
                <div class="vw-mobile-nav__actions">
                    <a href="{{ route('login') }}" class="vw-btn vw-btn--outline" @click="mobileOpen = false">Masuk</a>
                    <a href="{{ route('register') }}" class="vw-btn vw-btn--primary" @click="mobileOpen = false">Daftar</a>
                </div>
            @endauth
        </nav>
    </div>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="vw-footer">
    <div class="vw-container">
        <div class="vw-footer__lead">
            <p class="vw-eyebrow vw-eyebrow--light">The VELORA way</p>
            <p class="vw-footer__statement">Find your style.<br><em>Wear your confidence.</em></p>
            <a href="{{ route('products.index') }}" class="vw-btn vw-btn--light">Shop the collection</a>
        </div>

        <div class="vw-footer__grid">
            <div class="vw-footer__brand">
                <a href="{{ route('home') }}" class="vw-logo vw-logo--footer" aria-label="VELORA, kembali ke beranda">
                    <span class="vw-logo__word">VELORA</span>
                    <span class="vw-logo__sub">Fashion</span>
                </a>
                <p>Fashion terkurasi untuk menemani hari-hari Anda. Potongan yang nyaman, detail yang tenang, dan gaya yang terasa personal.</p>
                <p class="vw-footer__location">Jakarta, Indonesia</p>
            </div>

            <div class="vw-footer__col">
                <p class="vw-footer__heading">Quick Links</p>
                <ul>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="#koleksi">Koleksi</a></li>
                    <li><a href="{{ route('products.index', ['sort' => 'latest']) }}">New Arrivals</a></li>
                    <li><a href="#best-sellers">Best Sellers</a></li>
                    <li><a href="#tentang">Tentang Kami</a></li>
                </ul>
            </div>

            <div class="vw-footer__col">
                <p class="vw-footer__heading">Customer Service</p>
                <ul>
                    <li><a href="mailto:{{ config('shop.email') }}">Hubungi Kami</a></li>
                    <li><a href="#keunggulan">Cara Belanja</a></li>
                    <li><a href="#keunggulan">Pengiriman &amp; Pembayaran</a></li>
                    @auth
                        <li><a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('orders.index') }}">Lacak Pesanan</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Lacak Pesanan</a></li>
                    @endauth
                </ul>
            </div>

            <div class="vw-footer__col">
                <p class="vw-footer__heading">Hubungi</p>
                <ul>
                    <li><a href="mailto:{{ config('shop.email') }}">{{ config('shop.email') }}</a></li>
                    <li><a href="tel:{{ preg_replace('/\s+/', '', config('shop.phone')) }}">{{ config('shop.phone') }}</a></li>
                    <li><span>{{ config('shop.address') }}</span></li>
                </ul>
            </div>
        </div>

        <a href="{{ route('home') }}" class="ve-footer-word" aria-label="VELORA, kembali ke beranda">VELORA<span aria-hidden="true">.</span></a>

        <div class="vw-footer__bottom">
            <p>&copy; {{ now()->year }} VELORA. Seluruh hak cipta dilindungi.</p>
            <p class="vw-footer__credits">Fotografi editorial: <a href="https://www.pexels.com/license/" target="_blank" rel="noopener">Pexels</a> &mdash; Konstantin Mishchenko, Vika Glitter, Rulo Davila, L&ecirc; &#272;&#7913;c, CENGIZ AKTURK, Criativa Pix Fotografia, Vitaly Gorbachev, elena sher, Ayumi Photo, Karina Karzhavina.</p>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
