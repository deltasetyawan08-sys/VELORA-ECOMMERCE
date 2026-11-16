@extends('layouts.public')

@section('title', 'Detail Pesanan '.$order->order_number.' — VELORA')

@section('content')
    @php
        $steps = ['pending_payment', 'paid', 'processing', 'shipped', 'completed'];
        $currentStep = array_search($order->order_status->value, $steps, true);
        $isCancelled = $order->order_status->value === 'cancelled';
    @endphp

    <div class="container-shop py-8 lg:py-12">
        <nav class="flex flex-wrap items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a><span>/</span>
            <a href="{{ route('orders.index') }}" class="hover:text-burgundy-700">Pesanan Saya</a><span>/</span>
            <span class="text-burgundy-700">{{ $order->order_number }}</span>
        </nav>

        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="font-display text-2xl text-ink sm:text-3xl">Pesanan {{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-muted">Dibuat {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($order->can_be_paid)
                    <a href="{{ route('orders.payment', $order) }}" class="btn-primary btn-sm">Bayar Sekarang</a>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" class="btn-outline btn-sm">Lihat Invoice</a>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="{{ $order->payment_status->badgeClass() }}">Pembayaran: {{ $order->payment_status->label() }}</span>
            <span class="{{ $order->order_status->badgeClass() }}">Pesanan: {{ $order->order_status->label() }}</span>
            @if ($order->tracking_number)
                <span class="badge-info">Resi: {{ $order->carrier }} · {{ $order->tracking_number }}</span>
            @endif
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <div class="rounded-2xl border border-line bg-white p-5">
                    @if ($isCancelled)
                        <div class="alert-error">
                            Pesanan ini dibatalkan. Stok telah dikembalikan ke inventori. Silakan buat pesanan baru jika ingin melanjutkan pembelian.
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted">Status Pesanan</h2>
                            <span class="text-xs text-muted">Diperbarui {{ $order->updated_at->diffForHumans() }}</span>
                        </div>
                        <ol class="mt-5 grid grid-cols-5 gap-1">
                            @foreach ($steps as $index => $step)
                                @php
                                    $labels = ['Menunggu<br>Pembayaran', 'Dibayar', 'Diproses', 'Dikirim', 'Selesai'];
                                    $isDone = $index <= $currentStep && $currentStep !== false;
                                    $isCurrent = $index === $currentStep;
                                @endphp
                                <li class="flex flex-col items-center text-center">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full border text-xs font-bold {{ $isDone ? 'border-burgundy-700 bg-burgundy-700 text-white' : 'border-line bg-white text-muted' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="mt-2 text-[10px] leading-tight sm:text-[11px] {{ $isCurrent ? 'font-semibold text-burgundy-700' : 'text-muted' }}">{!! $labels[$index] !!}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                <div class="rounded-2xl border border-line bg-white">
                    <div id="penilaian-produk" class="border-b border-line px-5 py-3">
                        <h2 class="text-sm font-semibold">Produk Dipesan</h2>
                        @if ($order->order_status === \App\Enums\OrderStatus::Completed && $order->is_paid)
                            <p class="mt-1 text-xs text-muted">Bagikan pengalaman Anda dengan memberi 1–5 bintang untuk setiap produk.</p>
                            @if ($errors->rating->any())
                                <p class="alert-error mt-2" role="alert">{{ $errors->rating->first() }}</p>
                            @endif
                        @endif
                    </div>
                    <div class="divide-y divide-line px-5">
                        @foreach ($order->items as $item)
                            <div class="flex flex-wrap items-center gap-3 py-4">
                                <div class="h-20 w-16 shrink-0 overflow-hidden rounded-lg bg-cream-100">
                                    @if ($item->variant?->product?->image_url)
                                        <img src="{{ $item->variant->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-[10px] text-muted">VELORA</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium">{{ $item->product_name }}</p>
                                    <p class="mt-0.5 text-xs text-muted">SKU {{ $item->sku }}</p>
                                    <p class="text-xs text-muted">Ukuran {{ $item->size }} · Warna {{ $item->color }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium">{{ rupiah($item->unit_price) }} × {{ $item->quantity }}</p>
                                    <p class="text-sm font-semibold text-burgundy-700">{{ rupiah($item->subtotal) }}</p>
                                </div>
                            </div>
                            @if ($order->order_status === \App\Enums\OrderStatus::Completed && $order->is_paid)
                                @include('orders.rating', ['item' => $item, 'order' => $order])
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="rounded-2xl border border-line bg-white p-5">
                        <h2 class="text-sm font-semibold">Alamat Pengiriman</h2>
                        <p class="mt-3 text-sm font-medium">{{ $order->recipient_name }}</p>
                        <p class="text-sm text-muted">{{ $order->phone }}</p>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $order->address }}<br>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
                        @if ($order->notes)
                            <p class="mt-3 rounded-lg bg-cream-50 p-2.5 text-xs text-muted">Catatan: {{ $order->notes }}</p>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-line bg-white p-5">
                        <h2 class="text-sm font-semibold">Pengiriman</h2>
                        @if ($order->tracking_number)
                            <p class="mt-3 text-sm">Jasa kirim: <span class="font-medium">{{ $order->carrier }}</span></p>
                            <p class="text-sm">Nomor resi: <span class="font-medium">{{ $order->tracking_number }}</span></p>
                            <p class="mt-2 text-xs text-muted">Dikirim pada {{ $order->shipped_at?->format('d M Y, H:i') }}</p>
                        @else
                            <p class="mt-3 text-sm text-muted">Nomor resi akan muncul di sini setelah admin mengirim pesanan.</p>
                        @endif
                        <p class="mt-3 text-sm">Ongkos kirim: <span class="font-medium">{{ rupiah($order->shipping_cost) }}</span></p>
                    </div>
                </div>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Ringkasan Pembayaran</h2>
                    <div class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between text-muted"><span>Subtotal</span><span class="text-ink">{{ rupiah($order->subtotal) }}</span></div>
                        <div class="flex justify-between text-muted"><span>Ongkos kirim</span><span class="text-ink">{{ rupiah($order->shipping_cost) }}</span></div>
                        <div class="flex justify-between border-t border-line pt-3">
                            <span class="font-semibold">Total</span>
                            <span class="text-lg font-semibold text-burgundy-700">{{ rupiah($order->total) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-1.5 rounded-xl bg-cream-50 p-3 text-xs text-muted">
                        <p>Status pembayaran: <span class="font-semibold text-ink">{{ $order->payment_status->label() }}</span></p>
                        @if ($order->payment?->payment_type)
                            <p>Metode: <span class="font-medium text-ink">{{ $order->payment->payment_type }}</span></p>
                        @endif
                        @if ($order->paid_at)
                            <p>Dibayar: <span class="font-medium text-ink">{{ $order->paid_at->format('d M Y, H:i') }}</span></p>
                        @endif
                        @if ($order->payment?->expiry_at && $order->can_be_paid)
                            <p>Batas bayar: <span class="font-medium text-ink">{{ $order->payment->expiry_at->format('d M Y, H:i') }}</span></p>
                        @endif
                    </div>

                    @if ($order->can_be_paid)
                        <a href="{{ route('orders.payment', $order) }}" class="btn-primary mt-4 w-full">Lanjutkan Pembayaran</a>
                    @endif

                    @if ($order->payment_status->value === 'pending')
                        <form action="{{ route('orders.payment.refresh', $order) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn-soft w-full text-xs">Cek Status Pembayaran</button>
                        </form>
                        <p class="mt-2 text-center text-[11px] text-muted">Status hanya diperbarui dari hasil verifikasi gateway.</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-line bg-white p-5 text-xs text-muted">
                    <p class="font-semibold text-ink">Butuh bantuan?</p>
                    <p class="mt-2">Hubungi kami di {{ config('shop.email') }} atau {{ config('shop.phone') }} dengan menyertakan nomor pesanan.</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
