<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — VELORA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream-50 text-ink" x-data="{ sidebarOpen: false, adminMenu: false }" @keydown.escape.window="sidebarOpen = false; adminMenu = false">

<aside class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full bg-burgundy-900 text-cream-100 transition-transform duration-300 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-baseline gap-2">
            <span class="font-display text-2xl font-bold tracking-[0.18em] text-white">VELORA</span>
            <span class="text-[9px] uppercase tracking-[0.3em] text-cream-300/70">Admin</span>
        </a>
        <button type="button" class="rounded-lg p-1.5 text-cream-100/70 hover:bg-white/10 lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <nav class="space-y-1 px-3 py-5">
        <a href="{{ route('admin.dashboard') }}" class="admin-link {{ request()->routeIs('admin.dashboard') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-12h6V4h-6v4Z"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.categories.index') }}" class="admin-link {{ request()->routeIs('admin.categories.*') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
            Kategori
        </a>
        <a href="{{ route('admin.products.index') }}" class="admin-link {{ request()->routeIs('admin.products.*') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1.2 12.2a1.5 1.5 0 0 1-1.5 1.8H6.3a1.5 1.5 0 0 1-1.5-1.8L6 7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10V6a3 3 0 1 1 6 0v4"/></svg>
            Produk
        </a>
        <a href="{{ route('admin.orders.index') }}" class="admin-link {{ request()->routeIs('admin.orders.*') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z"/><path stroke-linecap="round" d="M8 10h8M8 14h5"/></svg>
            <span class="flex-1">Pesanan</span>
            @if (($adminPendingOrders ?? 0) > 0)
                <span class="rounded-full bg-cream-100 px-2 py-0.5 text-[10px] font-bold text-burgundy-800">{{ $adminPendingOrders }}</span>
            @endif
        </a>
        <a href="{{ route('admin.customers.index') }}" class="admin-link {{ request()->routeIs('admin.customers.*') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5A3.5 3.5 0 0 0 12.5 14h-5A3.5 3.5 0 0 0 4 17.5V19M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 8v-1.5a3.5 3.5 0 0 0-2.5-3.35M15 3.6a4 4 0 0 1 0 7.8"/></svg>
            Pelanggan
        </a>
        <a href="{{ route('admin.profile') }}" class="admin-link {{ request()->routeIs('admin.profile') ? 'admin-link-active' : '' }}">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 1 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
            Profil Admin
        </a>
    </nav>

    <div class="mx-3 mt-2 border-t border-white/10 pt-3">
        <a href="{{ route('home') }}" class="admin-link" target="_blank" rel="noopener">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/></svg>
            Lihat Toko
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="admin-link w-full text-left text-red-200 hover:text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17l5-5-5-5M20 12H9M12 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6"/></svg>
                Keluar
            </button>
        </form>
    </div>

    <div class="absolute inset-x-0 bottom-0 px-5 py-4 text-[10px] text-cream-100/40">
        VELORA Admin Panel &copy; {{ now()->year }}
    </div>
</aside>

<div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-ink/40 lg:hidden" @click="sidebarOpen = false"></div>

<div class="lg:pl-64">
    <header class="sticky top-0 z-30 border-b border-line bg-white/95 backdrop-blur">
        <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" class="rounded-lg p-2 text-muted hover:bg-cream-100 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu admin">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-muted">Admin Panel</p>
                    <h1 class="truncate text-lg font-semibold text-ink">@yield('header', 'Dashboard')</h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('products.index') }}" class="btn-ghost hidden text-xs sm:inline-flex" target="_blank" rel="noopener">Kunjungi Toko</a>
                <div class="relative" @click.outside="adminMenu = false">
                    <button type="button" class="flex items-center gap-2 rounded-full p-1.5 pr-2.5 transition hover:bg-cream-100" @click="adminMenu = ! adminMenu">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-burgundy-700 text-xs font-bold text-white">{{ initials(auth()->user()->name) }}</span>
                        <span class="hidden max-w-32 truncate text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                        <svg class="hidden h-4 w-4 text-muted sm:block" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-cloak x-show="adminMenu" x-transition class="absolute right-0 top-full mt-2 w-56 rounded-2xl border border-line bg-white p-2 shadow-xl">
                        <div class="border-b border-line px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                            <span class="badge-info mt-2">Administrator</span>
                        </div>
                        <a href="{{ route('admin.profile') }}" class="mt-1 block rounded-xl px-3 py-2 text-sm hover:bg-cream-100">Profil Admin</a>
                        <a href="{{ route('home') }}" class="block rounded-xl px-3 py-2 text-sm hover:bg-cream-100">Lihat Toko</a>
                        <form action="{{ route('logout') }}" method="POST" class="mt-1 border-t border-line pt-1">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2 text-left text-sm text-danger hover:bg-red-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        @include('components.flash')
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
