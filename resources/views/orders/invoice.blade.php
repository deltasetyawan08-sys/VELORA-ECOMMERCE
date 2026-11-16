<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }} — VELORA</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .invoice-sheet { box-shadow: none !important; border: 0 !important; margin: 0 !important; max-width: 100% !important; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body class="bg-cream-100 py-8">
    <div class="no-print mx-auto mb-4 flex max-w-3xl items-center justify-between px-4">
        <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-burgundy-700 hover:underline">&larr; Kembali ke detail pesanan</a>
        <button type="button" onclick="window.print()" class="btn-primary btn-sm">Cetak / Simpan PDF</button>
    </div>

    <main class="invoice-sheet mx-auto max-w-3xl border border-line bg-white p-8 shadow-sm sm:p-10">
        <header class="flex flex-wrap items-start justify-between gap-6 border-b border-line pb-6">
            <div>
                <p class="font-display text-3xl font-bold tracking-[0.18em] text-burgundy-700">VELORA</p>
                <p class="mt-1 text-[10px] uppercase tracking-[0.3em] text-muted">Fashion E-Commerce</p>
                <div class="mt-4 space-y-0.5 text-xs text-muted">
                    <p>{{ config('shop.address') }}</p>
                    <p>{{ config('shop.phone') }} · {{ config('shop.email') }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-widest text-muted">Invoice</p>
                <p class="mt-1 text-lg font-semibold text-ink">{{ $order->order_number }}</p>
                <p class="mt-1 text-xs text-muted">{{ $order->created_at->format('d F Y, H:i') }} WIB</p>
                @if ($order->payment_status->value === 'paid')
                    <span class="badge-success mt-3">LUNAS</span>
                @else
                    <span class="badge-danger mt-3">BELUM DIBAYAR</span>
                @endif
            </div>
        </header>

        <div class="grid gap-6 border-b border-line py-6 sm:grid-cols-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Ditagihkan kepada</p>
                <p class="mt-2 text-sm font-semibold">{{ $order->user->name }}</p>
                <p class="text-sm text-muted">{{ $order->user->email }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Dikirim kepada</p>
                <p class="mt-2 text-sm font-semibold">{{ $order->recipient_name }}</p>
                <p class="text-sm text-muted">{{ $order->phone }}</p>
                <p class="mt-1 text-xs leading-relaxed text-muted">{{ $order->address }}, {{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}</p>
            </div>
        </div>

        <div class="py-6">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-line text-[11px] uppercase tracking-wider text-muted">
                        <th class="pb-2 pr-3 font-semibold">Produk</th>
                        <th class="pb-2 pr-3 font-semibold">Varian</th>
                        <th class="pb-2 pr-3 text-right font-semibold">Harga</th>
                        <th class="pb-2 pr-3 text-center font-semibold">Qty</th>
                        <th class="pb-2 text-right font-semibold">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-line/70">
                            <td class="py-3 pr-3">
                                <p class="font-medium">{{ $item->product_name }}</p>
                                <p class="text-[11px] text-muted">SKU {{ $item->sku }}</p>
                            </td>
                            <td class="py-3 pr-3 text-xs text-muted">{{ $item->size }} / {{ $item->color }}</td>
                            <td class="py-3 pr-3 text-right">{{ rupiah($item->unit_price) }}</td>
                            <td class="py-3 pr-3 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-medium">{{ rupiah($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end border-t border-line pt-5">
            <div class="w-full max-w-xs space-y-2 text-sm">
                <div class="flex justify-between text-muted"><span>Subtotal</span><span class="text-ink">{{ rupiah($order->subtotal) }}</span></div>
                <div class="flex justify-between text-muted"><span>Ongkos kirim</span><span class="text-ink">{{ rupiah($order->shipping_cost) }}</span></div>
                <div class="flex justify-between border-t border-line pt-2 text-base">
                    <span class="font-semibold">Total</span>
                    <span class="font-semibold text-burgundy-700">{{ rupiah($order->total) }}</span>
                </div>
                <div class="flex justify-between text-xs text-muted">
                    <span>Status pembayaran</span>
                    <span class="font-semibold text-ink">{{ $order->payment_status->label() }}</span>
                </div>
                @if ($order->paid_at)
                    <div class="flex justify-between text-xs text-muted">
                        <span>Tanggal bayar</span>
                        <span class="font-medium text-ink">{{ $order->paid_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-8 border-t border-line pt-4 text-[11px] leading-relaxed text-muted">
            <p>Invoice ini dibuat secara otomatis oleh sistem VELORA. Pembayaran diverifikasi melalui Midtrans. Invoice yang belum lunas ditandai <span class="font-semibold">Belum Dibayar</span>.</p>
            <p class="mt-1">Status pesanan: {{ $order->order_status->label() }}. Terima kasih telah berbelanja di VELORA.</p>
        </div>
    </main>
</body>
</html>
