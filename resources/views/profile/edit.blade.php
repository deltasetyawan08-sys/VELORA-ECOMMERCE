@extends($isAdminArea ? 'layouts.admin' : 'layouts.public')

@section('title', 'Profil Saya — VELORA')
@section('header', 'Profil Saya')

@section('content')
    <div class="{{ $isAdminArea ? '' : 'container-shop py-10' }}">
        @unless ($isAdminArea)
            <div class="mb-8">
                <p class="section-eyebrow">Akun</p>
                <h1 class="section-title">Profil Saya</h1>
                <p class="mt-2 text-sm text-muted">Kelola informasi akun dan keamanan password Anda.</p>
            </div>
        @endunless

        <div class="grid gap-5 lg:grid-cols-[1fr_2fr]">
            <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-burgundy-50 text-base font-bold text-burgundy-700">{{ initials($user->name) }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                        <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                    </div>
                </div>
                <dl class="mt-4 space-y-2 border-t border-line pt-4 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-muted">Peran</dt><dd><span class="badge-info">{{ $user->isAdmin() ? 'Administrator' : 'Pelanggan' }}</span></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Telepon</dt><dd class="font-medium">{{ $user->phone ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">Bergabung</dt><dd class="font-medium">{{ $user->created_at->format('d M Y') }}</dd></div>
                </dl>
                @if ($user->address)
                    <div class="mt-3 rounded-xl bg-cream-50 px-3 py-2 text-xs text-muted">
                        <p class="font-medium text-ink">Alamat tersimpan</p>
                        <p class="mt-1 leading-relaxed">{{ $user->address }}</p>
                    </div>
                @endif
                @unless ($isAdminArea)
                    <a href="{{ route('orders.index') }}" class="btn-soft btn-sm mt-4 w-full">Pesanan Saya</a>
                @endunless
            </div>

            <div class="space-y-5">
                <form action="{{ route('profile.update') }}" method="POST" class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    @csrf
                    @method('PATCH')
                    <h2 class="text-sm font-semibold">Informasi Akun</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="label">Nama lengkap</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input @error('name') border-danger @enderror">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input @error('email') border-danger @enderror">
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="label">Nomor telepon</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input @error('phone') border-danger @enderror" placeholder="08xxxxxxxxxx">
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="label">Alamat lengkap (opsional)</label>
                            <textarea id="address" name="address" rows="3" class="input @error('address') border-danger @enderror" placeholder="Jalan, nomor rumah, kota, kode pos">{{ old('address', $user->address) }}</textarea>
                            @error('address')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-primary btn-sm mt-4">Simpan Profil</button>
                </form>

                <form action="{{ route('profile.password') }}" method="POST" class="rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-6">
                    @csrf
                    @method('PUT')
                    <h2 class="text-sm font-semibold">Ganti Password</h2>
                    <p class="mt-1 text-[11px] text-muted">Gunakan minimal 8 karakter.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="current_password" class="label">Password saat ini</label>
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input @error('current_password') border-danger @enderror">
                            @error('current_password')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="label">Password baru</label>
                            <input id="password" name="password" type="password" required autocomplete="new-password" class="input @error('password') border-danger @enderror">
                            @error('password')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="label">Konfirmasi password baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                        </div>
                    </div>
                    <button type="submit" class="btn-outline btn-sm mt-4">Ganti Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
