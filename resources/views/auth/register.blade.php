@extends('layouts.auth')

@section('title', 'Daftar — VELORA')

@section('content')
    <p class="section-eyebrow">Bergabung dengan VELORA</p>
    <h2 class="mt-2 font-display text-3xl text-ink">Buat akun customer</h2>
    <p class="mt-2 text-sm text-muted">Daftar gratis untuk mulai berbelanja koleksi terbaru kami.</p>

    <form action="{{ route('register.store') }}" method="POST" class="mt-8 space-y-5" novalidate>
        @csrf

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="input @error('name') border-danger @enderror" placeholder="Nama lengkap Anda">
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="input @error('email') border-danger @enderror" placeholder="nama@email.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="input @error('password') border-danger @enderror" placeholder="Minimal 8 karakter">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input" placeholder="Ulangi password">
        </div>

        <button type="submit" class="btn-primary w-full">Daftar Akun</button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-burgundy-700 hover:underline">Masuk di sini</a>
    </p>
@endsection
