@extends('layouts.admin')

@section('title', 'Detail Pelanggan — VELORA Admin')
@section('header', 'Detail Pelanggan')

@section('content')
    <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-burgundy-700 hover:underline">&larr; Kembali ke daftar pelanggan</a>

    <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_2fr]">
        <div class="space-y-5">
            <div class="rounded-2xl border border-line bg-white p-5 text-center shadow-sm">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-burgundy-50 text-lg font-bold text-burgundy-700">{{ initials($customer->name) }}</span>
                <h2 class="mt-3 text-lg font-semibold">{{ $customer->name }}</h2>
                <p class="text-xs text-muted">{{ $customer->email }}</p>
                <span class="badge-info mt-3">Pelanggan</span>
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold">Informasi Akun</h3>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-muted">Telepon</dt><dd class="font-medium">{{ $customer->phone ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Terdaftar</dt><dd class="font-medium">{{ $customer->created_at->format('d M Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Total pesanan</dt><dd class="font-medium">{{ $customer->orders->count() }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Total belanja lunas</dt><dd class="font-semibold text-burgundy-700">{{ rupiah($totalSpent) }}</dd></div>
                </dl>
                @if ($customer->address)
                    <div class="mt-3 rounded-xl bg-cream-50 px-3 py-2 text-xs text-muted">
                        <p class="font-medium text-ink">Alamat tersimpan</p>
                        <p class="mt-1 leading-relaxed">{{ $customer->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-white shadow-sm">
            <div class="border-b border-line px-5 py-4">
                <h3 class="text-sm font-semibold">Riwayat Pesanan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customer->orders as $order)
                            <tr>
                                <td class="font-medium">{{ $order->order_number }}</td>
                                <td>{{ rupiah($order->total) }}</td>
                                <td><span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->shortLabel() }}</span></td>
                                <td><span class="{{ $order->order_status->badgeClass() }}">{{ $order->order_status->label() }}</span></td>
                                <td class="text-xs text-muted">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-burgundy-700 hover:underline">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-sm text-muted">Pelanggan ini belum pernah membuat pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
