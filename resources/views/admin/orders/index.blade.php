@extends('layouts.admin')

@section('title', 'Pesanan — VELORA Admin')
@section('header', 'Manajemen Pesanan')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">Status pembayaran hanya berasal dari notifikasi Midtrans yang terverifikasi. Admin tidak dapat menandai pesanan lunas secara manual.</p>
    </div>

    <div class="mt-5 rounded-2xl border border-line bg-white shadow-sm">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid gap-2 border-b border-line px-5 py-3 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_1fr_1fr_auto_auto]">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="No. pesanan, nama, email..." class="input input-sm">
            <select name="payment_status" class="input input-sm">
                <option value="">Semua pembayaran</option>
                @foreach (\App\Enums\PaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['payment_status'] ?? '') === $status->value)>{{ $status->shortLabel() }}</option>
                @endforeach
            </select>
            <select name="order_status" class="input input-sm">
                <option value="">Semua status pesanan</option>
                @foreach (\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['order_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input input-sm" title="Dari tanggal">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input input-sm" title="Sampai tanggal">
            <button type="submit" class="btn-soft btn-sm">Terapkan</button>
            <a href="{{ route('admin.orders.index') }}" class="btn-ghost btn-sm text-center">Reset</a>
        </form>

        <div class="overflow-x-auto">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <p class="font-medium text-ink">{{ $order->order_number }}</p>
                                <p class="text-[11px] text-muted">{{ $order->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td>
                                <p class="text-sm">{{ $order->recipient_name }}</p>
                                <p class="text-[11px] text-muted">{{ $order->user?->email ?? '-' }}</p>
                            </td>
                            <td>
                                @php($itemCount = $order->items->sum('quantity'))
                                <span class="badge-neutral">{{ $itemCount }} item</span>
                            </td>
                            <td class="font-medium">{{ rupiah($order->total) }}</td>
                            <td><span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->shortLabel() }}</span></td>
                            <td><span class="{{ $order->order_status->badgeClass() }}">{{ $order->order_status->label() }}</span></td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn-soft btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-muted">Belum ada pesanan yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line px-5 py-3">{{ $orders->links() }}</div>
    </div>
@endsection
