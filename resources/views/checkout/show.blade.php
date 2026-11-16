@extends('layouts.public')

@section('title', 'Checkout — VELORA')

@section('content')
    <div class="container-shop py-8 lg:py-12">
        <nav class="flex items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" class="hover:text-burgundy-700">Beranda</a><span>/</span>
            <a href="{{ route('cart.index') }}" class="hover:text-burgundy-700">Keranjang</a><span>/</span>
            <span class="text-burgundy-700">Checkout</span>
        </nav>
        <h1 class="mt-2 font-display text-3xl text-ink sm:text-4xl">Checkout</h1>
        <p class="mt-1 text-sm text-muted">Lengkapi alamat pengiriman, lalu lanjutkan ke pembayaran Midtrans.</p>

        @if ($errors->has('checkout'))
            <div class="alert-error mt-5">{{ $errors->first('checkout') }}</div>
        @endif

        <form action="{{ route('checkout.store') }}" method="POST" class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px]">
            @csrf

            <div class="space-y-6">
                <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Alamat Pengiriman</h2>
                        <a href="{{ route('profile.edit') }}" class="text-xs font-medium text-burgundy-700 hover:underline">Gunakan alamat profil</a>
                    </div>
                    <p class="mt-1 text-xs text-muted">Data di bawah ini disimpan sebagai snapshot pada pesanan Anda.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="recipient_name" class="label">Nama penerima</label>
                            <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', $user->name) }}" required class="input @error('recipient_name') border-danger @enderror" placeholder="Nama lengkap penerima">
                            @error('recipient_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="label">Nomor telepon</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required class="input @error('phone') border-danger @enderror" placeholder="08xxxxxxxxxx">
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="province" class="label">Provinsi</label>
                            <input id="province" name="province" type="text" value="{{ old('province') }}" required class="input @error('province') border-danger @enderror" placeholder="Contoh: Jawa Barat">
                            @error('province')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="city" class="label">Kota / Kabupaten</label>
                            <input id="city" name="city" type="text" value="{{ old('city') }}" required class="input @error('city') border-danger @enderror" placeholder="Contoh: Bandung">
                            @error('city')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="label">Alamat lengkap</label>
                            <textarea id="address" name="address" rows="3" required class="input @error('address') border-danger @enderror" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan">{{ old('address', $user->address) }}</textarea>
                            @error('address')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="postal_code" class="label">Kode pos</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" required class="input @error('postal_code') border-danger @enderror" placeholder="40123">
                            @error('postal_code')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="label">Catatan (opsional)</label>
                            <textarea id="notes" name="notes" rows="2" class="input @error('notes') border-danger @enderror" placeholder="Patokan alamat atau instruksi khusus">{{ old('notes') }}</textarea>
                            @error('notes')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                    <h2 class="text-lg font-semibold">Produk yang Dipesan</h2>
                    <div class="mt-4 divide-y divide-line">
                        @foreach ($items as $item)
                            <div class="flex gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="h-20 w-16 shrink-0 overflow-hidden rounded-lg bg-cream-100">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-[10px] text-muted">No image</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-2 text-sm font-medium">{{ $item['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $item['size'] }} / {{ $item['color'] }} · {{ $item['quantity'] }}x</p>
                                    <p class="mt-1 text-xs text-muted">Stok tersedia: {{ $item['stock'] }}</p>
                                </div>
                                <p class="text-sm font-semibold">{{ rupiah($item['subtotal']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">Ringkasan Pembayaran</h2>
                    <div class="mt-4 space-y-2.5 text-sm">
                        <div class="flex justify-between text-muted"><span>Subtotal ({{ $items->sum('quantity') }} item)</span><span class="font-medium text-ink">{{ rupiah($subtotal) }}</span></div>
                        <div class="flex justify-between text-muted"><span>Ongkos kirim</span><span class="font-medium text-ink">{{ rupiah($shipping) }}</span></div>
                        <div class="text-[11px] text-muted">{{ config('shop.free_shipping_note') }}</div>
                        <div class="flex justify-between border-t border-line pt-3 text-base">
                            <span class="font-semibold">Total pembayaran</span>
                            <span class="text-xl font-semibold text-burgundy-700">{{ rupiah($total) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary mt-5 w-full">Buat Pesanan &amp; Bayar</button>
                    <a href="{{ route('cart.index') }}" class="btn-ghost mt-2 w-full text-xs">&larr; Kembali ke keranjang</a>

                    <div class="mt-4 rounded-xl bg-cream-50 p-3 text-[11px] leading-relaxed text-muted">
                        Stok akan direservasi saat pesanan dibuat. Pembayaran diproses melalui Midtrans Sandbox; status berhasil hanya berasal dari verifikasi gateway.
                    </div>
                </div>
            </aside>
        </form>
    </div>
@endsection
