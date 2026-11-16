@extends('layouts.admin')

@section('title', 'Detail Pesanan ' . $order->order_number . ' — VELORA Admin')
@section('header', 'Detail Pesanan')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-burgundy-700 hover:underline">&larr; Kembali ke daftar pesanan</a>
        <form action="{{ route('admin.orders.payment.refresh', $order) }}" method="POST">
            @csrf
            <button type="submit" class="btn-soft btn-sm">Muat Ulang Status Pembayaran</button>
        </form>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
        <div class="space-y-5">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-muted">Nomor Pesanan</p>
                        <h2 class="mt-1 text-xl font-semibold">{{ $order->order_number }}</h2>
                        <p class="mt-1 text-xs text-muted">Dibuat {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->label() }}</span>
                        <span class="{{ $order->order_status->badgeClass() }}">{{ $order->order_status->label() }}</span>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="table-admin">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>
                                        <p class="font-medium">{{ $item->product_name }}</p>
                                        <p class="text-[11px] text-muted">{{ $item->size }} / {{ $item->color }} · {{ $item->sku }}</p>
                                    </td>
                                    <td>{{ rupiah($item->unit_price) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-right font-medium">{{ rupiah($item->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ml-auto mt-4 max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between text-muted"><span>Subtotal</span><span class="text-ink">{{ rupiah($order->subtotal) }}</span></div>
                    <div class="flex justify-between text-muted"><span>Ongkos kirim</span><span class="text-ink">{{ rupiah($order->shipping_cost) }}</span></div>
                    <div class="flex justify-between border-t border-line pt-2 text-base font-semibold"><span>Total</span><span class="text-burgundy-700">{{ rupiah($order->total) }}</span></div>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold">Alamat Pengiriman</h3>
                    <p class="mt-3 text-sm font-medium">{{ $order->recipient_name }}</p>
                    <p class="text-xs text-muted">{{ $order->phone }}</p>
                    <p class="mt-2 text-xs leading-relaxed text-muted">{{ $order->address }}<br>{{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
                    @if ($order->notes)
                        <p class="mt-2 rounded-lg bg-cream-50 px-3 py-2 text-[11px] text-muted">Catatan: {{ $order->notes }}</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold">Info Pelanggan</h3>
                    <p class="mt-3 text-sm font-medium">{{ $order->user?->name ?? '-' }}</p>
                    <p class="text-xs text-muted">{{ $order->user?->email ?? '-' }}</p>
                    @if ($order->user)
                        <a href="{{ route('admin.customers.show', $order->user) }}" class="mt-3 inline-block text-xs font-semibold text-burgundy-700 hover:underline">Lihat profil pelanggan &rarr;</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-5">
            @if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
                @php($next = $order->order_status->nextStatus())
                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold">Perbarui Status Pesanan</h3>
                    @if ($next)
                        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-3 space-y-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="order_status" value="{{ $next->value }}">

                            @if ($next === \App\Enums\OrderStatus::Shipped)
                                <div>
                                    <label for="carrier" class="label text-xs">Jasa pengiriman</label>
                                    <input id="carrier" name="carrier" type="text" value="{{ old('carrier', $order->carrier) }}" class="input input-sm" placeholder="JNE / J&T / SiCepat" required>
                                    @error('carrier')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="tracking_number" class="label text-xs">Nomor resi</label>
                                    <input id="tracking_number" name="tracking_number" type="text" value="{{ old('tracking_number', $order->tracking_number) }}" class="input input-sm" placeholder="Nomor resi pengiriman" required>
                                    @error('tracking_number')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                            @endif

                            <p class="text-[11px] text-muted">Status saat ini: <span class="font-semibold text-ink">{{ $order->order_status->label() }}</span> &rarr; <span class="font-semibold text-burgundy-700">{{ $next->label() }}</span></p>
                            <button type="submit" class="btn-primary btn-sm w-full">Tandai {{ $next->label() }}</button>
                        </form>
                    @else
                        <p class="mt-3 rounded-xl bg-cream-50 px-3 py-4 text-xs text-muted">Pesanan berstatus <span class="font-semibold text-ink">{{ $order->order_status->label() }}</span> dan tidak memerlukan perubahan status lagi.</p>
                    @endif

                    @if ($order->carrier || $order->tracking_number)
                        <div class="mt-4 space-y-1 rounded-xl bg-cream-50 px-3 py-3 text-xs">
                            <p><span class="text-muted">Kurir:</span> <span class="font-medium">{{ $order->carrier ?: '-' }}</span></p>
                            <p><span class="text-muted">Resi:</span> <span class="font-medium">{{ $order->tracking_number ?: '-' }}</span></p>
                            @if ($order->shipped_at)<p><span class="text-muted">Dikirim:</span> {{ $order->shipped_at->format('d M Y H:i') }}</p>@endif
                        </div>
                    @endif
                </div>
            @else
                <div class="alert-warning flex-col text-xs">
                    <p class="font-semibold">Pesanan belum lunas.</p>
                    <p>Status pemrosesan hanya dapat diubah setelah pembayaran terverifikasi oleh Midtrans. Gunakan tombol muat ulang status untuk memverifikasi.</p>
                </div>
            @endif

            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold">Informasi Pembayaran</h3>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-muted">Status</dt><dd><span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->shortLabel() }}</span></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Jumlah</dt><dd class="font-medium">{{ rupiah($order->payment?->gross_amount ?? $order->total) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Metode</dt><dd class="font-medium">{{ $order->payment?->payment_type ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Status gateway</dt><dd class="font-medium">{{ $order->payment?->gateway_status ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Transaksi</dt><dd class="max-w-40 truncate font-mono text-[10px]">{{ $order->payment?->midtrans_transaction_id ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Dibayar pada</dt><dd class="font-medium">{{ $order->paid_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Kedaluwarsa</dt><dd class="font-medium">{{ $order->payment?->expiry_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                </dl>
                @if ($order->payment?->requires_review)
                    <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-[11px] text-danger">Pembayaran ini ditandai perlu tinjauan manual karena terjadi konflik data transaksi.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold">Riwayat Waktu</h3>
                <ul class="mt-3 space-y-2 text-xs text-muted">
                    <li class="flex justify-between gap-3"><span>Dibuat</span><span class="font-medium text-ink">{{ $order->created_at->format('d M Y H:i') }}</span></li>
                    <li class="flex justify-between gap-3"><span>Dibayar</span><span class="font-medium text-ink">{{ $order->paid_at?->format('d M Y H:i') ?? '-' }}</span></li>
                    <li class="flex justify-between gap-3"><span>Dikirim</span><span class="font-medium text-ink">{{ $order->shipped_at?->format('d M Y H:i') ?? '-' }}</span></li>
                    <li class="flex justify-between gap-3"><span>Selesai</span><span class="font-medium text-ink">{{ $order->completed_at?->format('d M Y H:i') ?? '-' }}</span></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
