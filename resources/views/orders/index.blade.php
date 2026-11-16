@extends('layouts.public')

@section('title', 'Pesanan Saya — VELORA')

@section('content')
    <div class="container-shop py-8 lg:py-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="font-display text-3xl text-ink sm:text-4xl">Pesanan Saya</h1>
                <p class="mt-1 text-sm text-muted">Pantau pembayaran, pemrosesan, dan pengiriman pesanan Anda.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn-outline">Belanja Lagi</a>
        </div>

        <div class="mt-6 flex gap-2 overflow-x-auto pb-2">
            @php
                $tabs = [
                    'all' => ['label' => 'Semua', 'status' => null],
                    'pending_payment' => ['label' => 'Menunggu Pembayaran', 'status' => 'pending_payment'],
                    'processing' => ['label' => 'Diproses', 'status' => 'processing'],
                    'shipped' => ['label' => 'Dikirim', 'status' => 'shipped'],
                    'completed' => ['label' => 'Selesai', 'status' => 'completed'],
                    'cancelled' => ['label' => 'Dibatalkan', 'status' => 'cancelled'],
                ];
            @endphp
            @foreach ($tabs as $key => $tab)
                @php
                    $count = $key === 'all' ? (int) $counts->sum() : (int) ($counts[$key] ?? 0);
                @endphp
                <a href="{{ route('orders.index', $key === 'all' ? [] : ['status' => $key]) }}"
                   class="whitespace-nowrap rounded-full border px-4 py-2 text-xs font-semibold transition {{ $status === $key ? 'border-burgundy-700 bg-burgundy-700 text-white' : 'border-line bg-white text-muted hover:border-burgundy-200 hover:text-burgundy-700' }}">
                    {{ $tab['label'] }}
                    <span class="ml-1 opacity-70">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <x-empty-state title="Belum ada pesanan" description="Pesanan akan muncul di sini setelah Anda melakukan checkout." class="mt-6">
                <a href="{{ route('products.index') }}" class="btn-primary">Mulai Belanja</a>
            </x-empty-state>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($orders as $order)
                    <article class="overflow-hidden rounded-2xl border border-line bg-white">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-cream-50 px-5 py-3">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span class="font-semibold text-ink">{{ $order->order_number }}</span>
                                <span class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="{{ $order->payment_status->badgeClass() }}">{{ $order->payment_status->label() }}</span>
                                <span class="{{ $order->order_status->badgeClass() }}">{{ $order->order_status->label() }}</span>
                            </div>
                        </div>

                        <div class="divide-y divide-line px-5">
                            @foreach ($order->items as $item)
                                <div class="flex items-center gap-3 py-3">
                                    <div class="h-16 w-14 shrink-0 overflow-hidden rounded-lg bg-cream-100">
                                        @if ($item->variant?->product?->image_url)
                                            <img src="{{ $item->variant->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-[10px] text-muted">VELORA</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-1 text-sm font-medium">{{ $item->product_name }}</p>
                                        <p class="text-xs text-muted">{{ $item->size }} / {{ $item->color }} · {{ $item->quantity }}x · {{ rupiah($item->unit_price) }}</p>
                                    </div>
                                    <p class="text-sm font-semibold">{{ rupiah($item->subtotal) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-5 py-3">
                            <p class="text-sm">
                                Total: <span class="text-base font-semibold text-burgundy-700">{{ rupiah($order->total) }}</span>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @if ($order->can_be_paid)
                                    <a href="{{ route('orders.payment', $order) }}" class="btn-primary btn-sm">Bayar Sekarang</a>
                                @endif
                                @if ($order->order_status === \App\Enums\OrderStatus::Completed && $order->is_paid)
                                    <a href="{{ route('orders.show', $order) }}#penilaian-produk" class="btn-primary btn-sm">{{ $order->items->contains(fn ($item) => $item->rating === null) ? 'Beri Penilaian' : 'Lihat Penilaian' }}</a>
                                @endif
                                <a href="{{ route('orders.show', $order) }}" class="btn-outline btn-sm">Detail</a>
                                <a href="{{ route('orders.invoice', $order) }}" class="btn-soft btn-sm">Invoice</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
