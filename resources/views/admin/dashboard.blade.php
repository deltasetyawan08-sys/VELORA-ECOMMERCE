@extends('layouts.admin')

@section('title', 'Dashboard Admin — VELORA')
@section('header', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @php
            $cards = [
                ['label' => 'Total Produk', 'value' => $stats['products'], 'hint' => 'Produk aktif & nonaktif', 'tone' => 'bg-burgundy-50 text-burgundy-700'],
                ['label' => 'Total Kategori', 'value' => $stats['categories'], 'hint' => 'Kategori katalog', 'tone' => 'bg-cream-100 text-burgundy-700'],
                ['label' => 'Total Pelanggan', 'value' => $stats['customers'], 'hint' => 'Akun role customer', 'tone' => 'bg-green-50 text-success'],
                ['label' => 'Total Pesanan', 'value' => $stats['orders'], 'hint' => 'Seluruh pesanan', 'tone' => 'bg-blue-50 text-blue-700'],
                ['label' => 'Menunggu Pembayaran', 'value' => $stats['pending_payment'], 'hint' => 'Belum dibayar', 'tone' => 'bg-amber-50 text-warning'],
                ['label' => 'Pesanan Dibayar', 'value' => $stats['paid_orders'], 'hint' => 'Terverifikasi gateway', 'tone' => 'bg-green-50 text-success'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-muted">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-semibold text-ink">{{ number_format($card['value'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-[11px] text-muted">{{ $card['hint'] }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $card['tone'] }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 18V9m6 9V5m6 13v-6"/></svg>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold">Pendapatan Terverifikasi</h2>
                    <p class="mt-1 text-2xl font-semibold text-burgundy-700">{{ rupiah($revenue) }}</p>
                </div>
                <p class="text-[11px] text-muted">Dari pesanan dengan status pembayaran paid</p>
            </div>

            <div class="mt-6 flex h-44 items-end gap-2 sm:gap-3">
                @foreach ($chart as $day)
                    <div class="group flex flex-1 flex-col items-center justify-end gap-2">
                        <div class="relative flex w-full flex-1 items-end">
                            <div class="w-full rounded-t-lg bg-burgundy-200 transition group-hover:bg-burgundy-500"
                                 style="height: {{ max(4, (int) round(($day['revenue'] / $chartMax) * 100)) }}%"
                                 title="{{ rupiah($day['revenue']) }}"></div>
                        </div>
                        <p class="text-[10px] font-medium text-muted">{{ $day['label'] }}</p>
                        <p class="text-[9px] text-muted/70">{{ $day['orders'] }} order</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-[11px] text-muted">Grafik 7 hari terakhir berdasarkan tanggal pesanan dibuat; tinggi batang menunjukkan pendapatan terverifikasi.</p>
        </div>

        <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Stok Menipis</h2>
                <span class="badge-warning">≤ 3 unit</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($lowStock as $variant)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-cream-50 px-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-medium">{{ $variant->product?->name ?? 'Produk dihapus' }}</p>
                            <p class="text-[11px] text-muted">{{ $variant->size }} / {{ $variant->color }} · {{ $variant->sku }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2 py-1 text-[11px] font-bold {{ $variant->stock > 0 ? 'text-warning' : 'text-danger' }}">{{ $variant->stock }} unit</span>
                    </div>
                @empty
                    <p class="rounded-xl bg-cream-50 px-3 py-6 text-center text-xs text-muted">Semua varian memiliki stok yang aman.</p>
                @endforelse
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn-soft mt-4 w-full text-xs">Kelola Produk</a>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-line bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-line px-5 py-4">
            <h2 class="text-sm font-semibold">Pesanan Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-burgundy-700 hover:underline">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td class="font-medium text-ink">{{ $order->order_number }}</td>
                            <td>{{ $order->user?->name ?? '-' }}</td>
                            <td class="font-medium">{{ rupiah($order->total) }}</td>
                            <td><span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->shortLabel() }}</span></td>
                            <td><span class="{{ $order->order_status->badgeClass() }}">{{ $order->order_status->label() }}</span></td>
                            <td class="text-xs text-muted">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-burgundy-700 hover:underline">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-muted">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
