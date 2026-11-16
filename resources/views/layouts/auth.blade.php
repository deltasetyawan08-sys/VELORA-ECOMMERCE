<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk — VELORA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white">
<div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
    <aside class="relative hidden overflow-hidden bg-burgundy-800 p-12 text-cream-100 lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/10"></div>
        <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-burgundy-700/60 blur-2xl"></div>
        <div class="absolute right-16 top-1/3 h-40 w-40 rounded-full border border-cream-100/20"></div>

        <a href="{{ route('home') }}" class="relative">
            <span class="font-display text-4xl font-bold tracking-[0.2em] text-white">VELORA</span>
            <span class="mt-2 block text-[10px] uppercase tracking-[0.45em] text-cream-200/70">Fashion E-Commerce</span>
        </a>

        <div class="relative max-w-md">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cream-300/80">Find Your Style</p>
            <h1 class="mt-4 font-display text-5xl leading-tight text-white">Wear Your<br>Confidence.</h1>
            <p class="mt-5 text-sm leading-relaxed text-cream-100/75">Temukan koleksi fashion pilihan yang dirancang untuk kenyamanan dan gaya sehari-hari. Belanja mudah, pembayaran aman, status pesanan transparan.</p>
            <div class="mt-8 space-y-3 text-sm text-cream-100/80">
                <p class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">✓</span> Koleksi premium dengan varian lengkap</p>
                <p class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">✓</span> Pembayaran digital via Midtrans Sandbox</p>
                <p class="flex items-center gap-3"><span class="h-6 w-6 text-center leading-6 rounded-full bg-white/10">✓</span> Invoice dan riwayat transaksi tersimpan</p>
            </div>
        </div>

        <p class="relative text-xs text-cream-100/40">&copy; {{ now()->year }} VELORA. Seluruh hak cipta dilindungi.</p>
    </aside>

    <main class="flex items-center justify-center px-5 py-12 sm:px-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-8 inline-flex items-baseline gap-2 lg:hidden">
                <span class="font-display text-3xl font-bold tracking-[0.18em] text-burgundy-700">VELORA</span>
            </a>
            @include('components.flash')
            @yield('content')
            <p class="mt-8 text-center text-xs text-muted">
                <a href="{{ route('home') }}" class="hover:text-burgundy-700">&larr; Kembali ke beranda</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>
