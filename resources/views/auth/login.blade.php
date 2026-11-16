@extends('layouts.auth')

@section('title', 'Masuk — VELORA')

@section('content')
    <p class="section-eyebrow">Selamat datang kembali</p>
    <h2 class="mt-2 font-display text-3xl text-ink">Masuk ke akun Anda</h2>
    <p class="mt-2 text-sm text-muted">Lanjutkan belanja dan pantau pesanan VELORA Anda.</p>

    <form action="{{ route('login.attempt') }}" method="POST" class="mt-8 space-y-5" novalidate>
        @csrf
        @if (request('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') border-danger @enderror" placeholder="nama@email.com">
            @error('email')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="input @error('password') border-danger @enderror" placeholder="••••••••">
            @error('password')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-muted">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-line text-burgundy-700 focus:ring-burgundy-400" @checked(old('remember'))>
            Ingat saya
        </label>

        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-burgundy-700 hover:underline">Daftar sekarang</a>
    </p>

@endsection
