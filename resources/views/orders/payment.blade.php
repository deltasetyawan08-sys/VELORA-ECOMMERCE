@extends('layouts.public')

@section('title', 'Pembayaran '.$order->order_number.' — VELORA')

@section('content')
    <div class="container-shop py-8 lg:py-12" x-data="{ paying: false }" data-payment-component>
        <nav class="flex flex-wrap items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a><span>/</span>
            <a href="{{ route('orders.index') }}" class="hover:text-burgundy-700">Pesanan Saya</a><span>/</span>
            <span class="text-burgundy-700">Pembayaran</span>
        </nav>

        <div class="mt-3">
            <h1 class="font-display text-3xl text-ink sm:text-4xl">Pembayaran</h1>
            <p class="mt-1 text-sm text-muted">Pesanan <span class="font-semibold text-ink">{{ $order->order_number }}</span> · dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>

        @if (! $configured)
            <div class="alert-warning mt-5">
                <div>
                    <p class="font-semibold">Kredensial Midtrans belum dikonfigurasi</p>
                    <p class="mt-1 text-xs leading-relaxed">Isi <code>MIDTRANS_CLIENT_KEY</code> dan <code>MIDTRANS_SERVER_KEY</code> pada file <code>.env</code> dengan kredensial Sandbox, lalu jalankan <code>php artisan config:clear</code>. Pembayaran tidak dapat diproses dan status tidak akan pernah dianggap berhasil tanpa verifikasi gateway.</p>
                </div>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px]">
            <div class="space-y-6">
                <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Total Pembayaran</p>
                            <p class="mt-1 text-3xl font-semibold text-burgundy-700">{{ rupiah($order->total) }}</p>
                        </div>
                        <div class="text-right">
                            <span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->label() }}</span>
                            @if ($order->payment?->expiry_at)
                                <p class="mt-2 text-xs text-muted">Batas bayar {{ $order->payment->expiry_at->format('d M Y, H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl bg-cream-50 p-4">
                        <p class="text-sm font-semibold">Status saat ini menunggu pembayaran</p>
                        <p class="mt-1 text-xs leading-relaxed text-muted">Gunakan tombol di samping untuk membuka halaman pembayaran Midtrans Snap Sandbox. Status berhasil hanya akan tercatat setelah notifikasi Midtrans terverifikasi atau pengecekan status server-to-server.</p>
                    </div>

                    <div class="mt-5 divide-y divide-line">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-3 py-3 text-sm">
                                <div class="min-w-0">
                                    <p class="line-clamp-1 font-medium">{{ $item->product_name }}</p>
                                    <p class="text-xs text-muted">{{ $item->size }} / {{ $item->color }} · {{ $item->quantity }}x</p>
                                </div>
                                <p class="font-medium">{{ rupiah($item->subtotal) }}</p>
                            </div>
                        @endforeach
                        <div class="flex justify-between py-3 text-sm text-muted"><span>Ongkos kirim</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                    <h2 class="text-sm font-semibold">Cara Pembayaran</h2>
                    <ol class="mt-3 space-y-3 text-sm text-muted">
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-burgundy-50 text-xs font-bold text-burgundy-700">1</span> Klik <span class="font-medium text-ink">Bayar Sekarang</span> untuk membuka Midtrans Snap.</li>
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-burgundy-50 text-xs font-bold text-burgundy-700">2</span> Pilih metode pembayaran Sandbox yang tersedia pada akun merchant Anda.</li>
                        <li class="flex gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-burgundy-50 text-xs font-bold text-burgundy-700">3</span> Selesaikan pembayaran, lalu kembali ke halaman pesanan. Status diverifikasi oleh server.</li>
                    </ol>
                    <p class="mt-4 text-xs text-muted">Jika status belum berubah, tunggu beberapa saat lalu gunakan tombol <span class="font-medium text-ink">Cek Status Pembayaran</span>.</p>
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Aksi Pembayaran</h2>
                    <p class="mt-1 text-xs text-muted">Lingkungan: <span class="badge-neutral">{{ $isProduction ? 'Production' : 'Sandbox' }}</span></p>

                    @if ($configured && $snapToken)
                        <button type="button"
                                class="btn-primary mt-4 w-full"
                                :disabled="paying"
                                @click="paying = true; window.payWithSnap()">
                            <span x-show="! paying">Bayar Sekarang</span>
                            <span x-cloak x-show="paying">Membuka pembayaran...</span>
                        </button>
                        <p class="mt-2 text-center text-[11px] text-muted">Token pembayaran sudah siap. Callback Snap tidak menetapkan status berhasil.</p>
                    @elseif ($configured)
                        <form action="{{ route('orders.payment.token', $order) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="btn-primary w-full">Buat Token Pembayaran</button>
                        </form>
                        <p class="mt-2 text-center text-[11px] text-muted">Token dibuat oleh server menggunakan kredensial Sandbox.</p>
                    @else
                        <button type="button" disabled class="btn-primary mt-4 w-full">Bayar Sekarang</button>
                    @endif

                    <form action="{{ route('orders.payment.refresh', $order) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-outline w-full text-xs">Cek Status Pembayaran</button>
                    </form>

                    <a href="{{ route('orders.show', $order) }}" class="btn-ghost mt-2 w-full text-xs">Lihat Detail Pesanan</a>

                    <div class="mt-4 rounded-xl border border-line bg-cream-50 p-3 text-[11px] leading-relaxed text-muted">
                        Jangan menutup halaman sebelum proses pembayaran selesai. Pembayaran tertunda tetap dapat dilanjutkan dari riwayat pesanan.
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@if ($configured && $snapToken)
    @push('scripts')
        <script src="{{ $snapScriptUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            window.payWithSnap = function () {
                window.snap.pay(@json($snapToken), {
                    onSuccess: function () { window.location.href = @json(route('orders.show', $order)); },
                    onPending: function () { window.location.href = @json(route('orders.show', $order)); },
                    onError: function () {
                        const state = window.Alpine.$data(document.querySelector('[data-payment-component]'));
                        state.paying = false;
                        alert('Pembayaran gagal diproses. Silakan coba lagi.');
                    },
                    onClose: function () {
                        const state = window.Alpine.$data(document.querySelector('[data-payment-component]'));
                        state.paying = false;
                    }
                });
            };
        </script>
    @endpush
@endif
